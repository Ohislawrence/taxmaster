<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ErrorLog::with(['user', 'resolver'])
            ->recent();

        // Filter by resolution status
        if ($request->has('status')) {
            if ($request->status === 'unresolved') {
                $query->unresolved();
            } elseif ($request->status === 'resolved') {
                $query->whereNotNull('resolved_at');
            }
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('exception_class', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $errors = $query->paginate(20);

        // Stats
        $stats = [
            'total' => ErrorLog::count(),
            'unresolved' => ErrorLog::unresolved()->count(),
            'critical' => ErrorLog::critical()->unresolved()->count(),
            'today' => ErrorLog::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/ErrorLogs/Index', [
            'errors' => $errors,
            'stats' => $stats,
            'filters' => $request->only(['status', 'severity', 'search']),
        ]);
    }

    public function show(ErrorLog $errorLog)
    {
        $errorLog->load(['user', 'resolver']);

        return Inertia::render('Admin/ErrorLogs/Show', [
            'error' => $errorLog,
        ]);
    }

    public function resolve(ErrorLog $errorLog)
    {
        $errorLog->markAsResolved(auth()->id());

        return redirect()->back()->with('success', 'Error marked as resolved.');
    }

    public function bulkResolve(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:error_logs,id',
        ]);

        ErrorLog::whereIn('id', $request->ids)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);

        return redirect()->back()->with('success', count($request->ids) . ' errors marked as resolved.');
    }

    public function destroy(ErrorLog $errorLog)
    {
        $errorLog->delete();

        return redirect()->back()->with('success', 'Error log deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:error_logs,id',
        ]);

        ErrorLog::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' errors deleted.');
    }

    public function clearResolved()
    {
        $count = ErrorLog::whereNotNull('resolved_at')->delete();

        return redirect()->back()->with('success', "{$count} resolved errors cleared.");
    }
}
