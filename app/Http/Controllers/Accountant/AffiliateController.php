<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AffiliateReferral;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $referrals = AffiliateReferral::where('accountant_id', $user->id)
            ->with(['business', 'payouts'])
            ->get();

        return Inertia::render('Accountant/Affiliate/Index', [
            'referrals' => $referrals,
            'user' => $user,
        ]);
    }

    /**
     * Update affiliate bank details for the current accountant.
     */
    public function updateBank(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'affiliate_bank_name' => ['nullable', 'string', 'max:191'],
            'affiliate_bank_account_name' => ['nullable', 'string', 'max:191'],
            'affiliate_bank_account_number' => ['nullable', 'string', 'max:64'],
            'affiliate_bank_code' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $user->update($data);
            return back()->with('success', 'Bank details saved.');
        } catch (\Throwable $e) {
            Log::error('Failed to save affiliate bank details: ' . $e->getMessage());
            return back()->with('error', 'Unable to save bank details.');
        }
    }
}
