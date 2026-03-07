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
        if ($user->ownedBusiness()->exists()) {
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
            'registration_number' => ['required', 'string', 'max:100', 'unique:businesses,registration_number'],
            'tax_identification_number' => ['required', 'string', 'max:50', 'regex:/^\d{8,14}(-\d{1,4})?$/', 'unique:businesses,tax_identification_number'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'in:technology,healthcare,retail,manufacturing,finance,education,hospitality,transportation,real_estate,entertainment,consulting,agriculture,other'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'registration_number.unique' => 'This registration number is already registered. Please check and try again.',
            'tax_identification_number.unique' => 'This Tax ID (TIN) is already registered. Please check and try again.',
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

        // Create business record
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'business_type' => $validated['business_type'],
            'registration_number' => $validated['registration_number'],
            'tax_identification_number' => $validated['tax_identification_number'],
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
    private function getStates()
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
