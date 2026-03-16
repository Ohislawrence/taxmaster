<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

class SwitchController extends Controller
{
    public function switch(Request $request)
    {
        $data = $request->validate([
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
            'redirect' => ['nullable', 'string', 'max:50'],
        ]);

        $businessId = $data['business_id'];
        $user = $request->user();

        if (! $user->managesBusiness($businessId)) {
            abort(403);
        }

        // Set selected business in session
        $request->session()->put('business_id', $businessId);

        // Determine safe redirect targets
        $redirectMap = [
            'dashboard' => route('business.dashboard'),
            'vat' => route('business.vat.index'),
            'paye' => route('business.paye.index'),
            'wht' => route('business.wht.index'),
            'cit' => route('business.cit.index'),
        ];

        $target = $data['redirect'] ?? 'dashboard';
        $url = $redirectMap[$target] ?? route('business.dashboard');

        return redirect($url)->with('success', 'Switched business context.');
    }

    /**
     * Clear the selected business from session and redirect back to appropriate dashboard.
     */
    public function leave(Request $request)
    {
        $request->session()->forget('business_id');

        $user = $request->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('accountant')) {
            return redirect()->route('accountant.dashboard')->with('success', 'Returned to accountant account.');
        }

        return redirect()->route('dashboard')->with('success', 'Returned to your dashboard.');
    }
}
