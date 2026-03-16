<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BusinessSetupController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show the business setup form
     */
    public function create()
    {
        $user = auth()->user();

        // If user already owns a business, redirect to dashboard
        // Business-role users may only have one owned business; accountants may create multiple
        if ($user->isBusiness() && $user->ownedBusiness()->exists()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/BusinessSetup', [
            'states' => $this->getStates(),
        ]);
    }

    /**
     * Store the business details
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'in:sole_proprietor,partnership,limited_liability,corporation'],
            'registration_number' => ['required', 'string', 'max:100'],
            'tax_identification_number' => ['required', 'string', 'max:50', 'regex:/^\d{8,14}(-\d{1,4})?$/'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'in:technology,healthcare,retail,manufacturing,finance,education,hospitality,transportation,real_estate,entertainment,consulting,agriculture,other'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'tax_identification_number.regex' => 'Invalid TIN format. Nigerian TIN must be 8-14 digits (e.g., 12345678 or 12345678-0001).',
            'email.unique' => 'This business email is already in use. Please use a different email.',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Business::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Ensure uniqueness against deterministic hashes (encrypted columns cannot be indexed reliably)
        $registrationHash = $validated['registration_number'] ? hash('sha256', strtolower(trim($validated['registration_number']))) : null;
        $tinHash = $validated['tax_identification_number'] ? hash('sha256', strtolower(trim($validated['tax_identification_number']))) : null;

        if ($registrationHash && Business::where('registration_number_hash', $registrationHash)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registration_number' => ['This registration number is already registered. Please check and try again.'],
            ]);
        }

        if ($tinHash && Business::where('tax_identification_number_hash', $tinHash)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tax_identification_number' => ['This Tax ID (TIN) is already registered. Please check and try again.'],
            ]);
        }

        // Create business record
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'business_type' => $validated['business_type'],
            'registration_number' => $validated['registration_number'],
            'tax_identification_number' => $validated['tax_identification_number'],
            'registration_number_hash' => $registrationHash,
            'tax_identification_number_hash' => $tinHash,
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'industry' => $validated['industry'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => 'active',
            'country' => 'Nigeria',
        ]);

        // If there's an affiliate code in session or request, record the referral
        try {
            $affiliateCode = $request->session()->get('affiliate_code') ?? $request->get('ref');
            if ($affiliateCode) {
                $referrer = \App\Models\User::where('affiliate_code', $affiliateCode)->first();
                if ($referrer) {
                    \App\Models\AffiliateReferral::firstOrCreate([
                        'business_id' => $business->id,
                    ], [
                        'accountant_id' => $referrer->id,
                        'source' => 'link',
                        'commission_percent' => $referrer->affiliate_commission_percent ?? 10.00,
                        'starts_at' => now(),
                        'expires_at' => now()->addYear(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to record affiliate referral during business signup: ' . $e->getMessage());
        }

        // Auto-assign free plan to new business
        $freePlan = SubscriptionPlan::where('slug', 'free')
            ->orWhere('monthly_price', 0)
            ->first();

        if ($freePlan) {
            $this->subscriptionService->createSubscription($business, $freePlan, 'monthly');
        }

        return redirect()->route('business.dashboard')->with('success', 'Business profile created successfully! You\'ve been enrolled in the Free plan.');
    }

    /**
     * Get list of Nigerian states
     */
    public function getStates()
    {
        return [
            'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
            'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'Gombe', 'Imo',
            'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos',
            'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers',
            'Sokoto', 'Taraba', 'Yobe', 'Zamfara', 'FCT'
        ];
    }
}
