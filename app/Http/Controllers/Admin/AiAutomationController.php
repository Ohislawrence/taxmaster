<?php

namespace App\Http\Controllers\Admin;

use App\Models\AiSuggestion;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AiAutomationController
{
    /**
     * Display AI suggestions dashboard
     */
    public function index(Request $request)
    {
        $suggestions = AiSuggestion::query()
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_suggestions' => AiSuggestion::count(),
            'pending' => AiSuggestion::where('status', 'pending')->count(),
            'applied' => AiSuggestion::where('status', 'applied')->count(),
            'categorizations' => AiSuggestion::where('type', 'categorization')->count(),
            'compliance_reminders' => AiSuggestion::where('type', 'compliance_reminder')->count(),
            'payment_recoveries' => AiSuggestion::where('type', 'payment_recovery')->count(),
        ];

        return Inertia::render('Admin/AiAutomation/Index', [
            'suggestions' => $suggestions,
            'stats' => $stats,
            'filters' => $request->only(['type', 'status']),
        ]);
    }

    /**
     * View single suggestion
     */
    public function show($id)
    {
        $suggestion = AiSuggestion::findOrFail($id);

        return Inertia::render('Admin/AiAutomation/Show', [
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * Mark suggestion as applied
     */
    public function apply($id)
    {
        $suggestion = AiSuggestion::findOrFail($id);
        $suggestion->markAsApplied();

        return back()->with('success', 'Suggestion marked as applied');
    }

    /**
     * Dismiss suggestion
     */
    public function dismiss($id)
    {
        $suggestion = AiSuggestion::findOrFail($id);
        $suggestion->markAsDismissed();

        return back()->with('success', 'Suggestion dismissed');
    }

    /**
     * Add feedback to suggestion
     */
    public function feedback($id, Request $request)
    {
        $validated = $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        $suggestion = AiSuggestion::findOrFail($id);
        $suggestion->addFeedback($validated['feedback']);

        return back()->with('success', 'Feedback recorded, thank you!');
    }
}
