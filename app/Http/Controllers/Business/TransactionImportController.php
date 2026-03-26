<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionImportController extends Controller
{
    /**
     * Parse uploaded file and return headers + sample rows
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['message' => 'Uploaded file is missing or invalid'], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension());

        // Use PhpSpreadsheet readers where available
        try {
            if (in_array($ext, ['xlsx', 'xls', 'csv', 'ods'])) {
                // Lazy-load reader classes to avoid hard dependency at runtime if missing
                if ($ext === 'csv') {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                } elseif ($ext === 'xls') {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                }

                // Some upload streams may not provide a real path; prefer getRealPath() but fallback to path()
                $filePath = $file->getRealPath() ?: $file->path();

                // If the detected path is not readable, persist the upload to storage and load from there
                if (!$filePath || !is_readable($filePath)) {
                    try {
                        $clientName = $file->getClientOriginalName();
                        $storeName = 'imports/upload_' . time() . '_' . Str::random(8) . '.' . $ext;
                        // Try storeAs first to preserve extension, fall back to store
                        try {
                            $stored = $file->storeAs('imports', basename($storeName));
                        } catch (\Throwable $e) {
                            Log::warning('Transaction import: storeAs failed, trying store()', ['error' => $e->getMessage(), 'client_name' => $clientName]);
                            $stored = $file->store('imports');
                        }

                        if (!$stored) {
                            throw new \RuntimeException('Failed to write uploaded file to storage (store returned falsy)');
                        }

                        $filePath = storage_path('app/' . $stored);
                    } catch (\Throwable $e) {
                        $clientName = $file->getClientOriginalName();
                        $debug = [
                            'error' => $e->getMessage(),
                            'client_name' => $clientName,
                            'size' => $file->getSize(),
                            'tmp_path' => $file->getRealPath(),
                            'path_method' => method_exists($file, 'path') ? $file->path() : null,
                            'upload_error' => $file->getError(),
                        ];

                        Log::error('Transaction import: failed to persist uploaded file', $debug);

                        return response()->json([
                            'message' => 'File "' . $clientName . '" does not exist or is not readable.',
                            'debug' => $debug,
                        ], 422);
                    }
                }

                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                $highestRow = $sheet->getHighestDataRow();
                $highestColumn = $sheet->getHighestDataColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                // Headers from first row
                $headers = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $headers[] = trim((string) $sheet->getCellByColumnAndRow($col, 1)->getValue());
                }

                // Sample rows (up to 3)
                $sampleRows = [];
                for ($row = 2; $row <= min(4, $highestRow); $row++) {
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $rowData[] = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
                    $sampleRows[] = $rowData;
                }

                return response()->json([
                    'headers' => $headers,
                    'sample_rows' => $sampleRows,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Transaction import parse failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to parse uploaded file: ' . $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Unsupported file type'], 422);
    }

    /**
     * Show the import page
     */
    public function showImportForm()
    {
        $business = auth()->user()->defaultBusiness();
        $bankAccounts = [];
        if ($business) {
            $bankAccounts = $business->bankAccounts()->where('is_active', true)->get(['id', 'bank_name', 'account_number']);
        }

        return Inertia::render('Business/Transactions/Import', [
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Map uploaded headers to transaction fields using AI (or fuzzy fallback)
     */
    public function mapColumns(Request $request)
    {
        $request->validate([
            'headers' => 'required|array|min:1',
            'headers.*' => 'string',
            'sample_rows' => 'nullable|array',
        ]);

        $headers = $request->input('headers');
        $sampleRows = $request->input('sample_rows', []);

        $targetFields = [
            'transaction_date' => 'Date of the transaction (YYYY-MM-DD or similar)',
            'description' => 'Transaction description or narration',
            'amount' => 'Amount of the transaction (positive number)',
            'type' => "Transaction type: 'credit' or 'debit'",
            'balance' => 'Account balance after transaction',
            'counterparty' => 'Counterparty or merchant name',
            'reference' => 'Payment or transaction reference',
            'category' => 'Suggested category or merchant category',
        ];

        $mapping = $this->tryAiColumnMapping($headers, $sampleRows, $targetFields);

        if (!$mapping) {
            $mapping = $this->fuzzyMapColumns($headers);
        }

        return response()->json(['mapping' => $mapping, 'target_fields' => $targetFields]);
    }

    /**
     * Process mapped rows and create Transaction records
     */
    public function processImport(Request $request)
    {
        // Accept either mapped_rows (array of associative rows) OR an uploaded file + mapping
        $request->validate([
            'mapped_rows' => 'nullable|array|min:1',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bank_name' => 'nullable|string',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'file' => 'nullable|file',
            'mapping' => 'nullable|array',
        ]);

        $business = auth()->user()->defaultBusiness();
        if (!$business) return response()->json(['message' => 'No active business'], 403);

        $bankAccountId = $request->input('bank_account_id');
        $mappedRows = $request->input('mapped_rows', []);
        $mapping = $request->input('mapping', []);

        // If bank_account_id not provided but bank details are provided, create a manual bank account
        if (empty($bankAccountId)) {
            $bankName = $request->input('bank_name');
            $accountName = $request->input('account_name');
            $accountNumber = $request->input('account_number');
            $currency = $request->input('currency') ?: 'NGN';

            if ($bankName && $accountNumber) {
                // Create a synthetic mono_account_id to satisfy DB non-null + unique constraint
                $monoId = 'imported-' . $business->id . '-' . time() . '-' . Str::random(6);
                $bankAccount = BankAccount::create([
                    'business_id' => $business->id,
                    'bank_name' => $bankName,
                    'account_name' => $accountName ?: $accountNumber,
                    'account_number' => $accountNumber,
                    'currency' => $currency,
                    'mono_account_id' => $monoId,
                    'mono_access_token' => null,
                    'balance' => 0,
                    'last_synced_at' => null,
                    'is_active' => true,
                    'auto_sync' => false,
                    'meta' => ['created_via' => 'transaction_import'],
                ]);

                $bankAccountId = $bankAccount->id;
            }
        }

        // If a file was uploaded, store it and dispatch a background job for processing
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('imports');

            // Dispatch job
            dispatch(new \App\Jobs\ImportTransactionsJob($business->id, $path, $mapping, $bankAccountId, auth()->id()));

            return response()->json(['message' => 'File queued for import', 'queued' => true], 202);
        }

        // If mapped rows are provided and small, process immediately in batches
        $rowCount = count($mappedRows);
        $batchThreshold = 100; // anything above this will be queued

        if ($rowCount === 0) {
            return response()->json(['message' => 'No rows provided'], 422);
        }

        if ($rowCount > $batchThreshold) {
            // Save mapped rows to storage as json and dispatch job
            $filename = 'imports/mapped_' . time() . '_' . uniqid() . '.json';
            \Illuminate\Support\Facades\Storage::put($filename, json_encode(['rows' => $mappedRows]));
            dispatch(new \App\Jobs\ImportTransactionsJob($business->id, $filename, null, $bankAccountId, auth()->id(), true));
            return response()->json(['message' => 'Large import queued for background processing', 'queued' => true], 202);
        }

        $created = 0;
        $errors = [];

        foreach ($mappedRows as $i => $row) {
            $rowNum = $i + 1;

            // Normalize values
            $rawAmount = isset($row['amount']) ? (string)$row['amount'] : '';
            $cleanAmount = preg_replace('/[^0-9\.\-]/', '', $rawAmount);
            if ($cleanAmount === '' || !is_numeric($cleanAmount)) {
                $errors[] = "Row {$rowNum}: missing or invalid amount";
                continue;
            }
            $amount = floatval($cleanAmount);

            if (empty($row['transaction_date'])) {
                $errors[] = "Row {$rowNum}: missing transaction_date";
                continue;
            }

            // Validate/parse transaction_date
            try {
                $txDate = Carbon::parse($row['transaction_date'])->toDateTimeString();
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: invalid transaction_date ({$row['transaction_date']})";
                continue;
            }

            // Determine bank account for this row
            $rowBankAccountId = $bankAccountId ?: ($row['bank_account_id'] ?? null);
            if (empty($rowBankAccountId)) {
                $errors[] = "Row {$rowNum}: missing bank_account_id (select a bank account or provide bank details before importing)";
                continue;
            }

            // Ensure description is present; try counterparty as fallback
            $description = isset($row['description']) ? trim((string)$row['description']) : '';
            if ($description === '') {
                $description = isset($row['counterparty']) ? trim((string)$row['counterparty']) : '';
            }

            // If still missing description, attempt AI-generated fallback using row context
            if ($description === '') {
                try {
                    $ai = new AiAgentService($business);
                    $promptParts = [];
                    $promptParts[] = "Generate a concise transaction description (1 short sentence) for an accounting ledger using the provided details.";
                    if (isset($row['counterparty'])) $promptParts[] = "Counterparty/Merchant: {$row['counterparty']}";
                    if (isset($row['amount'])) $promptParts[] = "Amount: {$row['amount']}";
                    if (isset($row['type'])) $promptParts[] = "Type: {$row['type']}";
                    if (isset($row['transaction_date'])) $promptParts[] = "Date: {$row['transaction_date']}";
                    if (isset($row['reference'])) $promptParts[] = "Reference: {$row['reference']}";
                    if (isset($row['category'])) $promptParts[] = "Category: {$row['category']}";
                    $prompt = implode("\n", $promptParts) . "\n\nReturn just the description text.";

                    $aiResp = $ai->callAiApi($prompt, 'generate_description');
                    if (!empty($aiResp['success']) && !empty($aiResp['analysis'])) {
                        $candidate = trim($aiResp['analysis']);
                        // strip possible triple-backtick code fences
                        $candidate = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $candidate);
                        // take first line if multi-line
                        $candidate = strtok($candidate, "\n");
                        if ($candidate) {
                            $description = trim($candidate);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::debug('AI description generation failed', ['row' => $rowNum, 'error' => $e->getMessage()]);
                }
            }

            if ($description === '') {
                // At this point we've already attempted AI generation. If AI failed,
                // generate a deterministic fallback description so the row is not rejected.
                $fallbackParts = [];
                if (!empty($row['counterparty'])) $fallbackParts[] = $row['counterparty'];
                $fallbackParts[] = ($type === 'credit' ? 'Credit' : 'Debit') . ' ' . number_format(abs($amount), 2);
                if (!empty($row['transaction_date'])) $fallbackParts[] = substr($txDate, 0, 10);
                $description = 'Imported: ' . implode(' • ', $fallbackParts);
            }

            $type = isset($row['type']) ? strtolower($row['type']) : ($amount >= 0 ? 'credit' : 'debit');
            if (!in_array($type, ['credit', 'debit'])) {
                $type = $amount >= 0 ? 'credit' : 'debit';
            }

            // If category missing, ask AI to suggest a concise category label
            $category = isset($row['category']) ? trim((string)$row['category']) : null;
            if (empty($category)) {
                try {
                    $aiCat = new AiAgentService($business);
                    $catPrompt = "Suggest a single concise accounting category for this transaction (one short word or phrase).\n";
                    if (isset($row['counterparty'])) $catPrompt .= "Counterparty: {$row['counterparty']}\n";
                    $catPrompt .= "Amount: {$row['amount']}\n";
                    $catPrompt .= "Type: {$type}\n";
                    if (isset($row['transaction_date'])) $catPrompt .= "Date: {$row['transaction_date']}\n";
                    if (isset($row['reference'])) $catPrompt .= "Reference: {$row['reference']}\n";
                    $catPrompt .= "\nReturn only the category label (e.g., REVENUE, EXPENSES, OFFICE_SUPPLIES).";

                    $catResp = $aiCat->callAiApi($catPrompt, 'suggest_category');
                    if (!empty($catResp['success']) && !empty($catResp['analysis'])) {
                        $cand = trim($catResp['analysis']);
                        $cand = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $cand);
                        $cand = strtok($cand, "\n");
                        if ($cand) {
                            // Normalize: uppercase, replace spaces with underscores
                            $category = strtoupper(str_replace(' ', '_', trim($cand)));
                        }
                    }
                } catch (\Throwable $e) {
                    Log::debug('AI category generation failed', ['row' => $rowNum, 'error' => $e->getMessage()]);
                }
            }

            try {
                Transaction::create([
                    'business_id' => $business->id,
                    'bank_account_id' => $rowBankAccountId,
                    'mono_transaction_id' => $row['reference'] ?? null,
                    'type' => $type,
                    'amount' => abs($amount),
                    'currency' => $row['currency'] ?? 'NGN',
                    'description' => $description,
                    'counterparty' => $row['counterparty'] ?? null,
                    'transaction_date' => $txDate,
                    'balance' => isset($row['balance']) ? floatval(preg_replace('/[^0-9\.\-]/', '', (string)$row['balance'])) : null,
                    'category' => $category ?? ($row['category'] ?? null),
                    'confidence' => 0.5,
                ]);

                $created++;
            } catch (\Throwable $e) {
                Log::warning('Import row failed', ['row' => $rowNum, 'error' => $e->getMessage()]);
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
            }
        }

        // If this was submitted via Inertia, prefer a redirect with a session flash so the flash appears on the transactions page
        if ($created > 0 && empty($errors) && $request->header('X-Inertia')) {
            return redirect('/business/transactions')->with('success', "{$created} transactions imported successfully");
        }

        return response()->json(['created' => $created, 'errors' => $errors]);
    }

    /**
     * Try AI-powered column mapping
     */
    protected function tryAiColumnMapping(array $headers, array $sampleRows, array $targetFields): ?array
    {
        try {
            $business = auth()->user()->defaultBusiness();
            if (!$business) return null;

            $ai = new AiAgentService($business);

            $sampleDataStr = '';
            if (!empty($sampleRows)) {
                $sampleDataStr = "\n\nSample rows:\n";
                foreach (array_slice($sampleRows, 0, 3) as $i => $r) {
                    $sampleDataStr .= "Row " . ($i + 1) . ": " . json_encode($r) . "\n";
                }
            }

            $prompt = "You are an assistant that maps uploaded CSV/Excel column headers to a transaction schema.\n\n";
            $prompt .= "Uploaded columns:\n" . implode(', ', $headers) . "\n";
            $prompt .= $sampleDataStr . "\n";
            $prompt .= "Target fields:\n";
            foreach ($targetFields as $k => $v) {
                $prompt .= "- {$k}: {$v}\n";
            }

            $prompt .= "\nFor each uploaded column header, return a JSON object mapping the header to one of the target field keys or \"skip\". Example: {\"Column A\": \"amount\"}";

            $result = $ai->callAiForCategorization($prompt);
            if ($result) {
                $result = trim($result);
                if (str_contains($result, '```')) {
                    preg_match('/```(?:json)?\s*\n?(.*?)\n?```/s', $result, $m);
                    $result = $m[1] ?? $result;
                }

                $mapping = json_decode($result, true);
                if (is_array($mapping)) {
                    return $mapping;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('AI mapping failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Simple fuzzy mapping for transaction columns
     */
    protected function fuzzyMapColumns(array $headers): array
    {
        $aliases = [
            'transaction_date' => ['date', 'transaction date', 'posted', 'value date', 'booking date'],
            'description' => ['description', 'narration', 'details', 'memo', 'particulars'],
            'amount' => ['amount', 'amt', 'value', 'credit', 'debit', 'amount (ngn)'],
            'type' => ['type', 'debit/credit', 'dr/cr', 'credit/debit'],
            'balance' => ['balance', 'running balance', 'acct balance'],
            'counterparty' => ['merchant', 'counterparty', 'payee', 'payer', 'beneficiary'],
            'reference' => ['reference', 'txn ref', 'transaction id', 'id', 'mono id', 'transaction reference'],
            'category' => ['category', 'merchant category', 'mcc', 'tag'],
        ];

        $mapping = [];
        foreach ($headers as $h) {
            $norm = strtolower(trim($h));
            $found = false;
            foreach ($aliases as $field => $words) {
                foreach ($words as $w) {
                    if ($norm === $w || str_contains($norm, $w) || str_contains($w, $norm)) {
                        $mapping[$h] = $field;
                        $found = true;
                        break 2;
                    }
                }
            }
            if (!$found) $mapping[$h] = 'skip';
        }

        return $mapping;
    }
}
