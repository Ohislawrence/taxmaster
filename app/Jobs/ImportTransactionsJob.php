<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $businessId;
    public string $path;
    public $mapping;
    public $bankAccountId;
    public $userId;
    public bool $isJson;

    public function __construct(int $businessId, string $path, $mapping = null, $bankAccountId = null, $userId = null, $isJson = false)
    {
        $this->businessId = $businessId;
        $this->path = $path;
        $this->mapping = $mapping;
        $this->bankAccountId = $bankAccountId;
        $this->userId = $userId;
        $this->isJson = (bool) $isJson;
    }

    public function handle()
    {
        $business = Business::find($this->businessId);
        if (!$business) {
            Log::warning('ImportTransactionsJob: business not found', ['business_id' => $this->businessId]);
            return;
        }

        $rows = [];

        try {
            if ($this->isJson || Str::endsWith($this->path, '.json')) {
                $content = Storage::get($this->path);
                $data = json_decode($content, true);
                $rows = $data['rows'] ?? [];
            } else {
                $fullPath = storage_path('app/' . $this->path);
                if (!file_exists($fullPath)) {
                    Log::warning('ImportTransactionsJob: file not found', ['path' => $fullPath]);
                    return;
                }

                $reader = IOFactory::createReaderForFile($fullPath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($fullPath);
                $sheet = $spreadsheet->getActiveSheet();
                $sheetArray = $sheet->toArray(null, true, true, true);

                $headers = [];
                foreach ($sheetArray as $rIndex => $rData) {
                    // first row -> headers
                    if ($rIndex === 1) {
                        $headers = array_values($rData);
                        continue;
                    }

                    $assoc = [];
                    $i = 0;
                    foreach ($rData as $cell) {
                        $key = $headers[$i] ?? ('col_' . $i);
                        $assoc[$key] = is_string($cell) ? trim($cell) : $cell;
                        $i++;
                    }

                    // skip empty rows
                    $allEmpty = true;
                    foreach ($assoc as $v) { if ($v !== null && $v !== '') { $allEmpty = false; break; } }
                    if ($allEmpty) continue;

                    $rows[] = $assoc;
                }
            }
        } catch (\Throwable $e) {
            Log::error('ImportTransactionsJob: parse failed', ['error' => $e->getMessage(), 'path' => $this->path]);
            return;
        }

        // Normalize mapping: if mapping maps field->header or header->field
        $fieldKeys = ['amount','transaction_date','description','reference','counterparty','balance','currency','type','category'];
        $normalizedMapping = [];
        if (is_array($this->mapping) && count($this->mapping) > 0) {
            // detect whether keys are field names
            $lowerKeys = array_map('strtolower', array_keys($this->mapping));
            $isFieldKeyed = count(array_intersect($lowerKeys, $fieldKeys)) > 0;
            if ($isFieldKeyed) {
                foreach ($this->mapping as $field => $header) {
                    $normalizedMapping[$field] = $header;
                }
            } else {
                // header => field
                foreach ($this->mapping as $header => $field) {
                    $normalizedMapping[$field] = $header;
                }
            }
        }

        $created = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 1;

            // build a mappedRow with expected keys
            $mapped = [];
            if (!empty($normalizedMapping)) {
                foreach ($normalizedMapping as $field => $header) {
                    $mapped[$field] = $row[$header] ?? null;
                }
            } else {
                // try to infer by header names
                foreach ($row as $colHeader => $val) {
                    $k = strtolower(preg_replace('/[^a-z0-9]/', '_', $colHeader));
                    if (str_contains($k, 'deposit') && !str_contains($k, 'withdrawal')) {
                        $mapped['deposit'] = $val;
                    } elseif (str_contains($k, 'withdrawal') || str_contains($k, 'withdraw')) {
                        $mapped['withdrawal'] = $val;
                    } elseif (str_contains($k, 'amount') || str_contains($k, 'amt') || str_contains($k, 'value')) {
                        $mapped['amount'] = $val;
                    } elseif (str_contains($k, 'date')) {
                        $mapped['transaction_date'] = $val;
                    } elseif (str_contains($k, 'desc') || str_contains($k, 'narr')) {
                        $mapped['description'] = $val;
                    } elseif (str_contains($k, 'ref') || str_contains($k, 'transaction') || str_contains($k, 'id')) {
                        $mapped['reference'] = $val;
                    } elseif (str_contains($k, 'from') || str_contains($k, 'to') || str_contains($k, 'counterparty') || str_contains($k, 'payee') || str_contains($k, 'payer')) {
                        $mapped['counterparty'] = $val;
                    } elseif (str_contains($k, 'balance')) {
                        $mapped['balance'] = $val;
                    } elseif (str_contains($k, 'currency')) {
                        $mapped['currency'] = $val;
                    } elseif (str_contains($k, 'type')) {
                        $mapped['type'] = $val;
                    } else {
                        // keep as fallback in description if empty
                        if (empty($mapped['description'])) $mapped['description'] = $mapped['description'] ?? $val;
                    }
                }
            }

            // Handle deposit/withdrawal columns OR single amount column
            $amount = null;
            $type = null;

            if (isset($mapped['deposit']) || isset($mapped['withdrawal'])) {
                $depositVal = isset($mapped['deposit']) ? preg_replace('/[^0-9\.\-]/', '', (string)$mapped['deposit']) : '';
                $withdrawalVal = isset($mapped['withdrawal']) ? preg_replace('/[^0-9\.\-]/', '', (string)$mapped['withdrawal']) : '';

                $depositAmount = ($depositVal !== '' && is_numeric($depositVal)) ? floatval($depositVal) : 0;
                $withdrawalAmount = ($withdrawalVal !== '' && is_numeric($withdrawalVal)) ? floatval($withdrawalVal) : 0;

                if ($depositAmount > 0 && $withdrawalAmount == 0) {
                    $amount = $depositAmount;
                    $type = 'credit';
                } elseif ($withdrawalAmount > 0 && $depositAmount == 0) {
                    $amount = $withdrawalAmount;
                    $type = 'debit';
                } elseif ($depositAmount > 0 && $withdrawalAmount > 0) {
                    $amount = $depositAmount >= $withdrawalAmount ? $depositAmount : $withdrawalAmount;
                    $type = $depositAmount >= $withdrawalAmount ? 'credit' : 'debit';
                } else {
                    $errors[] = "Row {$rowNum}: both deposit and withdrawal are empty";
                    continue;
                }
            } elseif (!empty($mapped['amount'])) {
                $amount = floatval(preg_replace('/[^0-9\.\-]/', '', (string)$mapped['amount']));
            }

            // Basic required checks
            if ($amount === null || empty($mapped['transaction_date'])) {
                $errors[] = "Row {$rowNum}: missing amount or transaction_date";
                continue;
            }

            try {

                // normalize date
                try {
                    $date = Carbon::parse($mapped['transaction_date']);
                } catch (\Throwable $e) {
                    // try Excel timestamp
                    if (is_numeric($mapped['transaction_date'])) {
                        $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($mapped['transaction_date']));
                    } else {
                        throw $e;
                    }
                }

                $monoId = $mapped['reference'] ?? null;

                // simple duplicate check by mono_transaction_id
                if ($monoId) {
                    $exists = Transaction::where('business_id', $this->businessId)->where('mono_transaction_id', $monoId)->exists();
                    if ($exists) {
                        $errors[] = "Row {$rowNum}: duplicate mono_transaction_id {$monoId}";
                        continue;
                    }
                }

                Transaction::create([
                    'business_id' => $this->businessId,
                    'bank_account_id' => $this->bankAccountId,
                    'mono_transaction_id' => $monoId,
                    'type' => $type ?? ($mapped['type'] ?? ($amount >= 0 ? 'credit' : 'debit')),
                    'amount' => abs($amount),
                    'currency' => $mapped['currency'] ?? 'NGN',
                    'description' => $mapped['description'] ?? null,
                    'counterparty' => $mapped['counterparty'] ?? null,
                    'transaction_date' => $date->toDateTimeString(),
                    'balance' => isset($mapped['balance']) ? floatval(preg_replace('/[^0-9\.\-]/', '', (string)$mapped['balance'])) : null,
                    'category' => $mapped['category'] ?? null,
                    'confidence' => 0.5,
                ]);

                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
            }
        }

        // Save a short import log
        $summary = ['created' => $created, 'errors' => $errors, 'processed' => count($rows)];
        try {
            $logPath = 'imports/log_' . time() . '_' . uniqid() . '.json';
            Storage::put($logPath, json_encode($summary));
        } catch (\Throwable $e) {
            Log::warning('ImportTransactionsJob: failed to write import log', ['error' => $e->getMessage()]);
        }

        Log::info('ImportTransactionsJob completed', ['business_id' => $this->businessId, 'created' => $created, 'errors' => count($errors)]);
    }
}
