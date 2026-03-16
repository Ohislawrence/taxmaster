<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;

class AccountantSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'accountant_can_create_businesses' => env('ACCOUNTANT_CAN_CREATE_BUSINESSES', true),
            'accountant_require_admin_approval' => env('ACCOUNTANT_REQUIRE_ADMIN_APPROVAL', true),
            'accountant_onboarding_email' => env('ACCOUNTANT_ONBOARDING_EMAIL', ''),
            'accountant_default_permissions' => env('ACCOUNTANT_DEFAULT_PERMISSIONS', 'manage-businesses,view-reports'),
            'accountant_default_commission' => env('ACCOUNTANT_DEFAULT_COMMISSION', '0.00'),
        ];

        return Inertia::render('Admin/AccountantSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'accountant_can_create_businesses' => 'required|boolean',
            'accountant_require_admin_approval' => 'required|boolean',
            'accountant_onboarding_email' => 'nullable|string',
            'accountant_default_permissions' => 'nullable|string',
            'accountant_default_commission' => 'nullable|numeric|min:0',
        ]);

        $envUpdates = [
            'ACCOUNTANT_CAN_CREATE_BUSINESSES' => $validated['accountant_can_create_businesses'] ? 'true' : 'false',
            'ACCOUNTANT_REQUIRE_ADMIN_APPROVAL' => $validated['accountant_require_admin_approval'] ? 'true' : 'false',
            'ACCOUNTANT_ONBOARDING_EMAIL' => $validated['accountant_onboarding_email'] ?? '',
            'ACCOUNTANT_DEFAULT_PERMISSIONS' => $validated['accountant_default_permissions'] ?? '',
            'ACCOUNTANT_DEFAULT_COMMISSION' => isset($validated['accountant_default_commission']) ? (string) $validated['accountant_default_commission'] : '0.00',
        ];

        $this->updateEnvFile($envUpdates);

        Artisan::call('config:cache');

        return back()->with('success', 'Accountant settings updated');
    }

    private function updateEnvFile(array $updates)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        foreach ($updates as $key => $value) {
            $pattern = '/^' . preg_quote($key) . '=.*/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "$key=$value", $content);
            } else {
                $content .= "\n$key=$value";
            }
        }

        file_put_contents($envPath, $content);

        return true;
    }
}
