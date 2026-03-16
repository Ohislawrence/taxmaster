<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;
use App\Notifications\AffiliatePayoutApproved as AffiliatePayoutApprovedNotification;
use App\Notifications\AffiliatePayoutPaid as AffiliatePayoutPaidNotification;

class AffiliatePayoutController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter');

        $query = AffiliatePayout::with(['referral.accountant', 'referral.business', 'subscription'])->orderBy('created_at', 'desc');

        if ($filter === 'approved') {
            $query->where('approved', true);
        } elseif ($filter === 'unapproved') {
            $query->where('approved', false);
        } elseif ($filter === 'paid') {
            $query->where('paid', true);
        } elseif ($filter === 'unpaid') {
            $query->where('paid', false);
        }

        $payouts = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Affiliate/Payouts/Index', [
            'payouts' => $payouts,
            'filter' => $filter,
        ]);
    }

    public function approve(Request $request, AffiliatePayout $payout)
    {
        $payout->update(['approved' => true]);

        // Notify accountant
        if ($payout->referral && $payout->referral->accountant) {
            $payout->referral->accountant->notify(new AffiliatePayoutApprovedNotification($payout));
        }

        return redirect()->back()->with('success', 'Payout approved.');
    }

    public function markPaid(Request $request, AffiliatePayout $payout)
    {
        $payout->update(['paid' => true, 'paid_at' => now()]);

        // Notify accountant
        if ($payout->referral && $payout->referral->accountant) {
            $payout->referral->accountant->notify(new AffiliatePayoutPaidNotification($payout));
        }

        return redirect()->back()->with('success', 'Payout marked as paid.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);

        $payouts = AffiliatePayout::whereIn('id', $ids)->get();

        foreach ($payouts as $payout) {
            $payout->update(['approved' => true]);
            if ($payout->referral && $payout->referral->accountant) {
                $payout->referral->accountant->notify(new AffiliatePayoutApprovedNotification($payout));
            }
        }

        return redirect()->back()->with('success', 'Selected payouts approved.');
    }
}
