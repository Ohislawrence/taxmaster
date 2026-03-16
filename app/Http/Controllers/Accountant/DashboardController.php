<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $businesses = $user->businesses()->get();

        // Businesses explicitly assigned to this accountant
        $managed = $user->managedBusinesses()->with([
            'vatReturns' => function($q) { $q->latest()->limit(1); },
            'payeReturns' => function($q) { $q->latest()->limit(1); },
            'whtReturns' => function($q) { $q->latest()->limit(1); },
            'citReturns' => function($q) { $q->latest()->limit(1); },
            'complianceDeadlines' => function($q) { $q->where('status','pending')->whereBetween('due_date',[now(), now()->addDays(30)])->orderBy('due_date'); },
        ])->get();

        // Prepare summaries for the frontend to avoid client-side queries
        $summaries = $managed->map(function($b) {
            $latestVat = $b->vatReturns->first();
            $latestPaye = $b->payeReturns->first();
            $latestWht = $b->whtReturns->first();
            $latestCit = $b->citReturns->first();

            $upcoming = $b->complianceDeadlines()->where('status','pending')->whereBetween('due_date',[now(), now()->addDays(30)])->orderBy('due_date')->get();

            return [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'description' => $b->description,
                'paye_status' => $latestPaye?->status ?? 'none',
                'vat_status' => $latestVat?->status ?? 'none',
                'wht_status' => $latestWht?->status ?? 'none',
                'cit_status' => $latestCit?->status ?? 'none',
                'latest_vat_due' => $latestVat?->due_date?->toDateString() ?? null,
                'latest_paye_period' => $latestPaye?->period ?? null,
                'upcoming_deadlines_count' => $upcoming->count(),
                'next_deadline' => $upcoming->first()?->due_date?->toDateString() ?? null,
            ];
        });

        return Inertia::render('Accountant/Dashboard', [
            'businesses' => $businesses,
            'managedBusinesses' => $managed,
            'managedSummaries' => $summaries,
        ]);
    }
}
