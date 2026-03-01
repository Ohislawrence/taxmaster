<?php

namespace App\Http\Controllers\Business;

use App\Models\Business;
use App\Models\TaxPayment;
use App\Models\TaxReturn;
use App\Services\PaymentService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display all payments
     */
    public function index()
    {
        $business = auth()->user()->ownedBusiness;

        $payments = $business->taxPayments()
            ->with('taxReturn')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Business/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Show payment details
     */
    public function show(TaxPayment $payment)
    {
        $this->authorize('view', $payment);

        return Inertia::render('Business/Payments/Show', [
            'payment' => $payment->load('taxReturn'),
        ]);
    }

    /**
     * Show create payment form
     */
    public function create(Request $request)
    {
        $business = auth()->user()->ownedBusiness;

        $taxReturn = null;
        if ($request->tax_return_id) {
            $taxReturn = TaxReturn::find($request->tax_return_id);
            if ($taxReturn->business_id !== $business->id) {
                abort(403);
            }
        }

        $pendingReturns = $business->taxReturns()
            ->where('status', '!=', 'paid')
            ->get();

        return Inertia::render('Business/Payments/Create', [
            'taxReturn' => $taxReturn,
            'pendingReturns' => $pendingReturns,
        ]);
    }

    /**
     * Initialize Paystack payment
     */
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'tax_return_id' => 'required|exists:tax_returns,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $business = auth()->user()->ownedBusiness;
        $taxReturn = TaxReturn::findOrFail($validated['tax_return_id']);

        if ($taxReturn->business_id !== $business->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($validated['amount'] > $taxReturn->balance) {
            return response()->json([
                'error' => 'Payment amount cannot exceed balance',
            ], 422);
        }

        $result = $this->paymentService->initializePayment(
            $business,
            $taxReturn,
            $validated['amount']
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json(['error' => $result['message']], 422);
    }

    /**
     * Verify payment
     */
    public function verify(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('business.payments.index')
                ->withErrors(['error' => 'Invalid payment reference']);
        }

        $result = $this->paymentService->verifyPayment($reference);

        if ($result['success']) {
            return redirect()->route('business.payments.index')
                ->with('message', 'Payment verified successfully');
        }

        return redirect()->route('business.payments.index')
            ->withErrors(['error' => 'Payment verification failed']);
    }

    /**
     * Handle Paystack webhook
     */
    public function webhookPaystack(Request $request)
    {
        $signature = $request->header('X-Paystack-Signature');

        // Verify webhook signature
        $hash = hash_hmac('sha512', $request->getContent(), config('taxmaster.paystack.secret_key'));

        if ($hash !== $signature) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->json()->all();

        try {
            $this->paymentService->handleWebhook($data);

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
