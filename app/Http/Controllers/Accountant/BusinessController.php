<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BusinessController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }
    public function index()
    {
        $user = auth()->user();
        $owned = $user->businesses()->get();
        $managed = $user->managedBusinesses()->get();

        return Inertia::render('Accountant/Businesses/Index', [
            'businesses' => $owned,
            'managedBusinesses' => $managed,
        ]);
    }

    public function create()
    {
        // provide states and industry options like BusinessSetup
        $states = (new \App\Http\Controllers\BusinessSetupController(app(SubscriptionService::class)))->getStates();

        $industries = [
            'technology','healthcare','retail','manufacturing','finance','education','hospitality','transportation',
            'real_estate','entertainment','consulting','agriculture','other'
        ];

        return Inertia::render('Accountant/Businesses/Create', [
            'states' => $states,
            'industries' => $industries,
        ]);
    }

    public function show(Request $request, Business $business)
    {
        $user = auth()->user();

        if (! $user->managesBusiness($business)) {
            abort(403);
        }

        return Inertia::render('Accountant/Businesses/Show', [
            'business' => $business,
            'openView' => $request->get('view'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
        ]);

        $user = auth()->user();

        // Generate unique slug
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Business::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Ensure uniqueness against deterministic hashes (encrypted columns cannot be indexed reliably)
        $registrationHash = $data['registration_number'] ? hash('sha256', strtolower(trim($data['registration_number']))) : null;
        $tinHash = $data['tax_identification_number'] ? hash('sha256', strtolower(trim($data['tax_identification_number']))) : null;

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

        // Create business but DO NOT set owner_id — record that an accountant created it
        $business = Business::create([
            'owner_id' => null,
            'created_by_accountant_id' => $user->id,
            'name' => $data['name'],
            'slug' => $slug,
            'business_type' => $data['business_type'],
            'registration_number' => $data['registration_number'],
            'tax_identification_number' => $data['tax_identification_number'],
            'registration_number_hash' => $registrationHash,
            'tax_identification_number_hash' => $tinHash,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'industry' => $data['industry'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'active',
            'country' => 'Nigeria',
            'billing_managed_by_platform' => true,
        ]);

        // Attach accountant via pivot so they manage the business
        $user->managedBusinesses()->attach($business->id);

        // Optionally enroll in free plan if one exists and billing is managed by platform
        $freePlan = SubscriptionPlan::where('slug', 'free')->orWhere('monthly_price', 0)->first();
        if ($freePlan && ($business->billing_managed_by_platform ?? true) !== false) {
            $this->subscriptionService->createSubscription($business, $freePlan, 'monthly');
        }

        return redirect()->route('accountant.businesses.index')->with('success', 'Business created successfully.');
    }

    public function detach(Business $business)
    {
        $user = auth()->user();

        if (! $user->managesBusiness($business)) {
            abort(403);
        }
        // Create affiliate referral for the accountant (if not present)
        try {
            if (method_exists($user, 'hasRole') && $user->hasRole('accountant')) {
                if (! $user->affiliate_code) {
                    $user->affiliate_code = strtoupper(\Illuminate\Support\Str::random(8));
                    $user->save();
                }

                \App\Models\AffiliateReferral::create([
                    'accountant_id' => $user->id,
                    'business_id' => $business->id,
                    'source' => 'accountant_created',
                    'commission_percent' => $user->affiliate_commission_percent ?? 10.00,
                    'starts_at' => now(),
                    'expires_at' => now()->addYear(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create affiliate referral for accountant-created business: ' . $e->getMessage());
        }

        $user->managedBusinesses()->detach($business->id);

        return redirect()->route('accountant.businesses.index')->with('message', 'Detached from business');
    }
}
