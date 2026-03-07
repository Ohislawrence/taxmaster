<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayeReturn;
use App\Models\PayeSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayeController extends Controller
{
    /**
     * List all PAYE returns across businesses
     */
    public function index(Request $request)
    {
        $returns = PayeReturn::with(['schedules', 'business'])
            ->orderBy('period', 'desc')
            ->paginate(20);

        $stats = [
            'total_returns' => PayeReturn::count(),
            'total_tax_collected' => PayeReturn::where('status', 'paid')->sum('total_tax_deducted'),
            'pending_returns' => PayeReturn::whereIn('status', ['draft', 'filed'])->count(),
        ];

        return Inertia::render('Admin/PAYE/Index', [
            'returns' => $returns,
            'stats' => $stats,
        ]);
    }

    /**
     * Show details for a specific PAYE return
     */
    public function show($id)
    {
        $return = PayeReturn::with(['schedules', 'business'])->findOrFail($id);
        return Inertia::render('Admin/PAYE/Show', [
            'payeReturn' => $return,
        ]);
    }

    /**
     * PAYE statistics dashboard
     */
    public function statistics()
    {
        $stats = [
            'total_returns' => PayeReturn::count(),
            'total_tax_collected' => PayeReturn::where('status', 'paid')->sum('total_tax_deducted'),
            'pending_returns' => PayeReturn::whereIn('status', ['draft', 'filed'])->count(),
        ];
        return Inertia::render('Admin/PAYE/Statistics', [
            'stats' => $stats,
        ]);
    }

    /**
     * Overdue PAYE returns report
     */
    public function overdueReport()
    {
        $overdue = PayeReturn::where('status', 'overdue')->with('business')->get();
        return Inertia::render('Admin/PAYE/Overdue', [
            'overdue' => $overdue,
        ]);
    }
}
