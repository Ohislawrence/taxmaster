<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\ComplianceDeadline;
use App\Services\ComplianceCalendarService;
use App\Jobs\GenerateComplianceDeadlines;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    public function __construct(
        protected ComplianceCalendarService $complianceService
    ) {}

    /**
     * Display compliance calendar
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->ownedBusiness) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $business = $user->defaultBusiness();
        $allDeadlines = ComplianceDeadline::where('business_id', $business->id)
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($deadline) {
                // Parse period to extract start and end dates
                $periodParts = explode(' - ', $deadline->period);
                $periodStart = isset($periodParts[0]) ? date('Y-m-d', strtotime($periodParts[0])) : $deadline->due_date->format('Y-m-01');
                $periodEnd = isset($periodParts[1]) ? date('Y-m-d', strtotime($periodParts[1])) : $deadline->due_date->format('Y-m-t');

                return [
                    'id' => $deadline->id,
                    'deadline_type' => $deadline->deadline_type,
                    'type_label' => $deadline->deadline_type,
                    'description' => $deadline->description,
                    'period' => $deadline->period,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'due_date' => $deadline->due_date->format('Y-m-d'),
                    'due_date_human' => $deadline->due_date->format('M d, Y'),
                    'days_until' => $deadline->days_until,
                    'urgency' => $deadline->urgency,
                    'is_completed' => $deadline->status === 'completed',
                    'is_overdue' => $deadline->is_overdue,
                    'status' => $deadline->status,
                    'frequency' => $deadline->frequency,
                    'required_documents' => $deadline->required_documents ?? [],
                    'attachments' => $deadline->attachments ?? [],
                ];
            });

        return Inertia::render('Business/Compliance/Calendar', [
            'deadlines' => $allDeadlines,
        ]);
    }

    /**
     * Show single deadline details
     */
    public function show(Request $request, ComplianceDeadline $deadline)
    {
        $this->authorize('view', $deadline);

        return Inertia::render('Business/Compliance/Show', [
            'deadline' => [
                'id' => $deadline->id,
                'type' => $deadline->deadline_type,
                'type_label' => $deadline->type_label,
                'period' => $deadline->period,
                'description' => $deadline->description,
                'due_date' => $deadline->due_date->format('Y-m-d'),
                'due_date_human' => $deadline->due_date->format('F d, Y'),
                'days_until' => $deadline->days_until,
                'urgency' => $deadline->urgency,
                'status' => $deadline->status,
                'required_documents' => $deadline->required_documents,
                'frequency' => $deadline->frequency,
                'notes' => $deadline->notes,
                'attachments' => $deadline->attachments,
                'completed_at' => $deadline->completed_at?->format('M d, Y H:i'),
            ],
        ]);
    }

    /**
     * Mark deadline as completed
     */
    public function complete(Request $request, ComplianceDeadline $deadline)
    {
        $this->authorize('update', $deadline);

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
        ]);

        $this->complianceService->markCompleted(
            $deadline,
            $request->notes,
            $request->attachments
        );

        return back()->with('success', 'Deadline marked as completed.');
    }

    /**
     * Regenerate deadlines
     */
    public function regenerate(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        GenerateComplianceDeadlines::dispatch($business);

        return back()->with('success', 'Compliance deadlines are being regenerated.');
    }

    /**
     * Dismiss a deadline
     */
    public function dismiss(Request $request, ComplianceDeadline $deadline)
    {
        $this->authorize('update', $deadline);

        $deadline->update(['status' => 'dismissed']);

        return back()->with('success', 'Deadline dismissed.');
    }

    /**
     * Upload attachment for deadline
     */
    public function uploadAttachment(Request $request, ComplianceDeadline $deadline)
    {
        $this->authorize('update', $deadline);

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $path = $request->file('file')->store('compliance-attachments', 'public');

        $attachments = $deadline->attachments ?? [];
        $attachments[] = [
            'path' => $path,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'uploaded_at' => now()->toISOString(),
        ];

        $deadline->update(['attachments' => $attachments]);

        return back()->with('success', 'Attachment uploaded successfully.');
    }
}
