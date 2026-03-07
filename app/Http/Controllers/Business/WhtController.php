<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\WhtTransaction;
use App\Models\WhtReturn;
use App\Services\WHTCalculationService;
use App\Services\GovernmentPaymentService;
use App\Services\ReturnPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Inertia\Inertia;

class WhtController extends Controller
{
    public function __construct(
        private WHTCalculationService $whtService,
        private GovernmentPaymentService $paymentService
    ) {}

    /**
     * Display WHT transactions dashboard
     */
    public function index(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $transactions = WhtTransaction::where('business_id', $business->id)
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        $stats = [
            'total_transactions' => WhtTransaction::where('business_id', $business->id)->count(),
            'total_wht_deducted' => WhtTransaction::where('business_id', $business->id)->sum('wht_amount'),
            'this_month_wht' => WhtTransaction::where('business_id', $business->id)
                ->whereMonth('transaction_date', date('m'))
                ->whereYear('transaction_date', date('Y'))
                ->sum('wht_amount'),
            'pending_returns' => WhtReturn::where('business_id', $business->id)
                ->whereIn('status', ['draft'])
                ->count(),
        ];

        return Inertia::render('Business/WHT/Transactions', [
            'transactions' => $transactions,
            'stats' => $stats,
            'transactionTypes' => $this->whtService->getTransactionTypeOptions(),
        ]);
    }

    /**
     * Show form to create a new WHT transaction
     */
    public function create()
    {
        return Inertia::render('Business/WHT/Create', [
            'transactionTypes' => $this->whtService->getTransactionTypeOptions(),
        ]);
    }

    /**
     * Store a new WHT transaction
     */
    public function store(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:' . implode(',', array_keys($this->whtService->getAllWHTRates())),
            'beneficiary_type' => 'required|in:company,individual',
            'vendor_name' => 'required|string|max:255',
            'vendor_tin' => 'nullable|string|max:50',
            'gross_amount' => 'required|numeric|min:0',
            'wht_rate' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        // Calculate WHT
        $calculation = $this->whtService->calculateWHT(
            $validated['gross_amount'],
            $validated['transaction_type'],
            $validated['wht_rate'] ?? null
        );

        // Create transaction
        $transaction = WhtTransaction::create([
            'business_id' => $business->id,
            'transaction_date' => $validated['transaction_date'],
            'transaction_type' => $validated['transaction_type'],
            'beneficiary_type' => $validated['beneficiary_type'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_tin' => $validated['vendor_tin'],
            'gross_amount' => $calculation['gross_amount'],
            'wht_rate' => $calculation['wht_rate'],
            'wht_amount' => $calculation['wht_amount'],
            'net_amount' => $calculation['net_amount'],
            'description' => $validated['description'],
            'payment_reference' => $validated['payment_reference'],
        ]);

        return redirect()->route('business.wht.index')
            ->with('success', 'WHT transaction recorded successfully');
    }

    /**
     * Display a specific transaction
     */
    public function show(Request $request, WhtTransaction $whtTransaction)
    {
        $this->authorize('view', $whtTransaction);

        return Inertia::render('Business/WHT/Show', [
            'transaction' => $whtTransaction,
        ]);
    }

    /**
     * Update a transaction
     */
    public function update(Request $request, WhtTransaction $whtTransaction)
    {
        $this->authorize('update', $whtTransaction);

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:' . implode(',', array_keys($this->whtService->getAllWHTRates())),
            'vendor_name' => 'required|string|max:255',
            'vendor_tin' => 'nullable|string|max:50',
            'gross_amount' => 'required|numeric|min:0',
            'wht_rate' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        // Recalculate WHT
        $calculation = $this->whtService->calculateWHT(
            $validated['gross_amount'],
            $validated['transaction_type'],
            $validated['wht_rate'] ?? null
        );

        $whtTransaction->update([
            'transaction_date' => $validated['transaction_date'],
            'transaction_type' => $validated['transaction_type'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_tin' => $validated['vendor_tin'],
            'gross_amount' => $calculation['gross_amount'],
            'wht_rate' => $calculation['wht_rate'],
            'wht_amount' => $calculation['wht_amount'],
            'net_amount' => $calculation['net_amount'],
            'description' => $validated['description'],
            'payment_reference' => $validated['payment_reference'],
        ]);

        return back()->with('success', 'Transaction updated successfully');
    }

    /**
     * Delete a transaction
     */
    public function destroy(Request $request, WhtTransaction $whtTransaction)
    {
        $this->authorize('delete', $whtTransaction);

        $whtTransaction->delete();

        return redirect()->route('business.wht.index')
            ->with('success', 'Transaction deleted successfully');
    }

    /**
     * Display WHT returns
     */
    public function returns(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $returns = WhtReturn::where('business_id', $business->id)
            ->with(['payments'])
            ->orderBy('period', 'desc')
            ->paginate(12);

        // Manually append computed attributes with error handling
        $returns->getCollection()->transform(function ($return) {
            try {
                $return->period_label = $return->period ? $return->period_label : 'Unknown Period';
                $return->status_label = $return->status ? $return->status_label : 'Unknown';
                $return->filed_date_formatted = $return->filed_date ? $return->filed_date_formatted : null;
            } catch (\Exception $e) {
                $return->period_label = 'Unknown Period';
                $return->status_label = 'Unknown';
                $return->filed_date_formatted = null;
            }

            if ($return->payments && $return->payments->count() > 0) {
                $return->payments->transform(function ($payment) {
                    try {
                        $payment->payment_date_formatted = $payment->payment_date ? $payment->payment_date_formatted : null;
                        $payment->payment_method_label = $payment->payment_method ? $payment->payment_method_label : 'N/A';
                        $payment->tax_type_label = $payment->tax_type ? $payment->tax_type_label : 'Unknown';
                        $payment->status_label = $payment->status ? $payment->status_label : 'Unknown';
                    } catch (\Exception $e) {
                        $payment->payment_date_formatted = null;
                        $payment->payment_method_label = 'N/A';
                        $payment->tax_type_label = 'Unknown';
                        $payment->status_label = 'Unknown';
                    }
                    return $payment;
                });
            }

            return $return;
        });

        return Inertia::render('Business/WHT/Returns', [
            'returns' => $returns,
        ]);
    }

    /**
     * Generate WHT return for a specific period
     */
    public function generateReturn(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        // Check if return already exists
        $existingReturn = WhtReturn::where('business_id', $business->id)
            ->where('period', $validated['period'])
            ->first();

        if ($existingReturn) {
            return redirect()->route('business.wht.return.show', $existingReturn)
                ->with('info', 'Return already exists for this period');
        }

        // Generate return
        $whtReturn = $this->whtService->createOrUpdateWHTReturn($business->id, $validated['period']);

        if ($whtReturn->transaction_count === 0) {
            return back()->withErrors(['period' => 'No WHT transactions found for this period']);
        }

        return redirect()->route('business.wht.return.show', $whtReturn)
            ->with('success', 'WHT return generated successfully');
    }

    /**
     * Show a specific WHT return
     */
    public function showReturn(Request $request, WhtReturn $whtReturn)
    {
        $this->authorize('view', $whtReturn);

        $whtReturn->load(['payments', 'business']);

        // Manually append computed attributes with error handling
        try {
            $whtReturn->period_label = $whtReturn->period ? $whtReturn->period_label : 'Unknown Period';
            $whtReturn->status_label = $whtReturn->status ? $whtReturn->status_label : 'Unknown';
            $whtReturn->filed_date_formatted = $whtReturn->filed_date ? $whtReturn->filed_date_formatted : null;
        } catch (\Exception $e) {
            $whtReturn->period_label = 'Unknown Period';
            $whtReturn->status_label = 'Unknown';
            $whtReturn->filed_date_formatted = null;
        }

        if ($whtReturn->payments && $whtReturn->payments->count() > 0) {
            $whtReturn->payments->transform(function ($payment) {
                try {
                    $payment->payment_date_formatted = $payment->payment_date ? $payment->payment_date_formatted : null;
                    $payment->payment_method_label = $payment->payment_method ? $payment->payment_method_label : 'N/A';
                    $payment->tax_type_label = $payment->tax_type ? $payment->tax_type_label : 'Unknown';
                    $payment->status_label = $payment->status ? $payment->status_label : 'Unknown';
                } catch (\Exception $e) {
                    $payment->payment_date_formatted = null;
                    $payment->payment_method_label = 'N/A';
                    $payment->tax_type_label = 'Unknown';
                    $payment->status_label = 'Unknown';
                }
                return $payment;
            });
        }

        return Inertia::render('Business/WHT/ReturnDetails', [
            'whtReturn' => $whtReturn,
        ]);
    }

    /**
     * Update WHT return status
     */
    public function updateReturnStatus(Request $request, WhtReturn $whtReturn)
    {
        $this->authorize('update', $whtReturn);

        $validated = $request->validate([
            'status' => 'required|in:filed,paid',
            'filed_date' => 'required_if:status,filed|date',
            'firs_reference' => 'nullable|string|max:100',
        ]);

        $whtReturn->update($validated);

        return back()->with('success', 'WHT return status updated successfully');
    }

    /**
     * Generate payment RRR for WHT return
     */
    public function generateReturnPaymentRRR(Request $request, WhtReturn $whtReturn)
    {
        $this->authorize('update', $whtReturn);

        $business = $this->resolveBusiness($request);

        // Generate RRR via Remita
        $rrrResult = $this->paymentService->generateRRR('WHT', $whtReturn, $business);

        if (!$rrrResult['success']) {
            return back()->withErrors(['payment' => $rrrResult['message']]);
        }

        // Create payment record
        $payment = $this->paymentService->createPayment(
            $business,
            'WHT',
            $whtReturn,
            $whtReturn->total_wht_deducted,
            'remita',
            $rrrResult['rrr']
        );

        return back()->with('success', 'Payment RRR generated successfully')
            ->with('payment', $payment);
    }

    /**
     * Calculate WHT for preview (AJAX)
     */
    public function calculatePreview(Request $request)
    {
        $validated = $request->validate([
            'gross_amount' => 'required|numeric|min:0',
            'transaction_type' => 'required|string',
            'wht_rate' => 'nullable|numeric',
        ]);

        $calculation = $this->whtService->calculateWHT(
            $validated['gross_amount'],
            $validated['transaction_type'],
            $validated['wht_rate'] ?? null
        );

        return response()->json($calculation);
    }

    /**
     * Get WHT summary for a period (AJAX)
     */
    public function getPeriodSummary(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $schedule = $this->whtService->generateWHTSchedule($business->id, $validated['period']);

        return response()->json($schedule);
    }

    /**
     * Export WHT return as PDF
     */
    public function exportReturnPdf(WhtReturn $whtReturn)
    {
        $this->authorize('view', $whtReturn);

        $generator = new ReturnPdfGenerator();
        $pdf = $generator->generateWhtReturnPdf($whtReturn);

        $filename = 'wht-return-' . $whtReturn->period . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveBusiness(Request $request)
    {
        $business = $request->user()?->ownedBusiness;

        if (!$business) {
            throw new HttpResponseException(
                redirect()->route('business.setup')
                    ->with('error', 'Please complete business setup first.')
            );
        }

        return $business;
    }
}
