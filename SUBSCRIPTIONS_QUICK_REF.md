# Quick Reference: Subscription System

## File Structure

```
app/
├── Models/
│   ├── SubscriptionPlan.php          ← Plan model with relationships
│   └── BusinessSubscription.php      ← Updated with plan_id
├── Http/
│   ├── Controllers/
│   │   ├── Admin/PlanController.php  ← Admin CRUD for plans
│   │   └── Business/SubscribeController.php ← Business checkout
│   └── Middleware/
│       └── CheckSubscriptionFeatures.php ← Feature gating
└── Services/
    └── SubscriptionService.php       ← All subscription logic

database/
├── migrations/
│   ├── 2026_02_24_100001_create_subscription_plans_table.php
│   ├── 2026_02_24_100002_add_plan_id_to_business_subscriptions.php
└── seeders/
    └── SubscriptionPlanSeeder.php   ← Default plans

resources/views/
├── Admin/Plans/
│   ├── Index.vue                    ← Plan list
│   └── Form.vue                     ← Create/edit plan
└── Business/Plans/
    ├── Pricing.vue                  ← Public pricing page
    └── Checkout.vue                 ← Payment checkout

routes/
├── admin.php                        ← Admin plan routes
└── business.php                     ← Business subscription routes
```

---

## Admin Usage

### View All Plans
```
URL: /admin/plans
Action: GET request to PlanController@index
Display: Table with plan names, pricing, features, active subscriptions
```

### Create New Plan
```
URL: /admin/plans/create
Action: GET request to PlanController@create
Display: Form with fields for name, slug, pricing, features, limits
```

### Save Plan
```
URL: /admin/plans
Action: POST request to PlanController@store
Validates: All form fields, unique slug
Redirects: To /admin/plans with success message
```

### Edit Plan
```
URL: /admin/plans/{id}/edit
Action: GET request to PlanController@edit
Display: Pre-filled form with current plan data
```

### Update Plan
```
URL: /admin/plans/{id}
Action: PUT request to PlanController@update
Validates: All form fields (slug unique for others)
Redirects: To /admin/plans with success message
```

### Delete Plan
```
URL: /admin/plans/{id}
Action: DELETE request to PlanController@destroy
Check: Prevents deletion if plan has active subscriptions
Redirects: To /admin/plans with success/error message
```

---

## Business Usage

### Browse Plans
```
URL: /business/plans
Action: GET request to SubscribeController@showPlans
Display: All active plans in grid, monthly/annual toggle, FAQ
Features: Compare pricing, toggle billing, see current subscription
```

### Select Plan & Checkout
```
URL: /business/plans/{id}
Action: GET request to SubscribeController@selectPlan
Display: Selected plan details, billing options, review page
Button: "Proceed to Payment" or "Activate Free Plan"
```

### Process Checkout (Free Plan)
```
URL: /business/plans/{id}/checkout
Method: POST with { billing_cycle: 'monthly'|'annual' }
Action: Creates subscription with status = 'active'
Response: { success: true, redirect: '/business/dashboard' }
```

### Process Checkout (Paid Plan)
```
URL: /business/plans/{id}/checkout
Method: POST with { billing_cycle: 'monthly'|'annual' }
Action: Creates subscription with status = 'pending'
Response: { success: true, payment_url: 'https://checkout.paystack.com/...' }
Redirect: To Paystack payment form
```

### Payment Callback
```
URL: /business/plans/payment/callback?reference=ref_xxxx
Action: GET request to SubscribeController@paymentCallback
Check: Verify payment with Paystack API
Update: Activate subscription if payment successful
Redirect: To /business/dashboard with message
```

### Upgrade Plan
```
URL: /business/subscription/upgrade
Method: POST with { plan_id: X, billing_cycle: 'monthly'|'annual' }
Action: Changes subscription to new plan
Check: Must be higher tier (prevents downgrades)
Response: { success: true, message: '...' }
```

### Cancel Subscription
```
URL: /business/subscription/cancel
Method: POST with { reason: 'optional cancellation reason' }
Action: Sets status = 'cancelled', stores reason in metadata
Response: { success: true, message: '...' }
```

---

## API Endpoints

### Admin Routes (Protected)
```
GET    /admin/plans                    # List plans
GET    /admin/plans/create             # Create form
POST   /admin/plans                    # Store new plan
GET    /admin/plans/{id}/edit          # Edit form
PUT    /admin/plans/{id}               # Update plan
DELETE /admin/plans/{id}               # Delete plan
```

### Business Routes (Protected)
```
GET    /business/plans                         # Show pricing
GET    /business/plans/{id}                    # Checkout page
POST   /business/plans/{id}/checkout           # Process checkout
GET    /business/plans/payment/callback        # Payment callback
POST   /business/subscription/upgrade          # Upgrade plan
POST   /business/subscription/cancel           # Cancel plan
```

---

## Service Methods

### Usage: Inject into controller
```php
public function __construct(SubscriptionService $subscriptionService)
{
    $this->subscriptionService = $subscriptionService;
}
```

### Core Methods
```php
// Get plans
$plans = $subscriptionService->getAvailablePlans();
$plan = $subscriptionService->getPlanBySlug('professional');

// Create subscription
$subscription = $subscriptionService->createSubscription(
    $business,           // Business model
    $plan,              // SubscriptionPlan model
    'monthly',          // 'monthly' or 'annual'
    'paystack',         // payment method
    'ref_xxxxx'         // transaction reference
);

// Manage subscription
$subscriptionService->activateSubscription($subscription);
$subscriptionService->cancelSubscription($subscription, 'Too expensive');
$subscriptionService->upgradeSubscription($subscription, $newPlan, 'monthly');

// Check subscription
$active = $subscriptionService->getActiveSubscription($business);
$stats = $subscriptionService->getUsageStats($business);
$canAdd = $subscriptionService->canPerformAction($business, 'add_staff');
$hasAI = $subscriptionService->hasFeature($business, 'ai_analysis');
```

---

## Feature Gating

### Apply to Protected Routes
```php
// In routes/business.php
Route::post('ai/analyze', [AiController::class, 'analyze'])
    ->middleware('check-subscription-features:use_ai_analysis');

Route::post('staff', [StaffController::class, 'store'])
    ->middleware('check-subscription-features:add_staff');

Route::post('tax-returns', [TaxReturnController::class, 'store'])
    ->middleware('check-subscription-features:file_return');
```

### Supported Actions
```
'add_staff'
'file_return'
'use_ai_analysis'
'use_payment_automation'
'access_premium_features'
'view'
'manage_profile'
```

---

## Database Queries

### Get Plan by Slug
```php
$plan = SubscriptionPlan::where('slug', 'professional')->first();
```

### Get All Active Subscriptions for a Business
```php
$subscriptions = $business->subscriptions()
    ->where('status', 'active')
    ->get();
```

### Get Current Active Subscription
```php
$subscription = $business->subscriptions()
    ->where('status', 'active')
    ->latest()
    ->first();
```

### Get Subscriptions Expiring Soon (7 days)
```php
$expiring = BusinessSubscription::where('status', 'active')
    ->whereBetween('renews_at', [now(), now()->addDays(7)])
    ->get();
```

### Count Subscriptions Per Plan
```php
$counts = BusinessSubscription::where('status', 'active')
    ->groupBy('plan_id')
    ->selectRaw('plan_id, count(*) as count')
    ->get();
```

---

## Default Plans

All created by SubscriptionPlanSeeder:

**Free** (slug: free)
- Price: ₦0/month
- Staff: 1 | Returns: 5/year | Storage: 1GB
- Features: Basic filing only

**Basic** (slug: basic)
- Price: ₦5,000/month (₦50,000/year)
- Staff: 3 | Returns: 50/year | Storage: 5GB
- Features: AI analysis included

**Professional** (slug: professional)
- Price: ₦15,000/month (₦150,000/year)
- Staff: 10 | Returns: 500/year | Storage: 50GB
- Features: All + 24/7 support + API

**Enterprise** (slug: enterprise)
- Price: ₦50,000/month (₦500,000/year)
- Staff: ∞ | Returns: ∞ | Storage: 500GB
- Features: Everything + white-label + custom support

---

## Paystack Integration

### Configuration
```bash
# In .env file
PAYSTACK_PUBLIC_KEY=pk_live_xxxxx
PAYSTACK_SECRET_KEY=sk_live_xxxxx
```

### Initialize Payment
```php
$payload = [
    'email' => 'user@example.com',
    'amount' => 50000 * 100,  // Amount in kobo
    'metadata' => [
        'subscription_id' => $subscription->id,
        'plan_id' => $plan->id,
    ],
];

// POST to https://api.paystack.co/transaction/initialize
// Returns: { status: true, data: { authorization_url: '...' } }
```

### Verify Payment
```php
// GET to https://api.paystack.co/transaction/verify/{reference}
// Pass Authorization header with Bearer token
// Returns: { status: true, data: { status: 'success'... } }
```

---

## Common Tasks

### Add a New Feature to Plan
1. Go to plan form
2. Check feature checkbox
3. Save plan
4. Will appear in database as part of features JSON array

### Change Plan Pricing
1. Go to /admin/plans/{id}/edit
2. Update monthly_price and/or annual_price
3. Save
4. New pricing applies to new subscriptions automatically

### Create Custom Plan
1. Go to /admin/plans/create
2. Fill in all fields:
   - Name: e.g., "Startup"
   - Slug: e.g., "startup"
   - Pricing: monthly + annual
   - Limits: staff, returns, storage
   - Features: checkboxes
   - Display order: priority
3. Save plan
4. Plan appears on pricing page if is_active = true

### Check Business Subscription Status
```php
$sub = Business::find($id)->subscriptions()
    ->where('status', 'active')
    ->latest()
    ->first();

if ($sub) {
    echo "Plan: " . $sub->plan->name;
    echo "Status: " . $sub->status;
    echo "Renews: " . $sub->renews_at;
}
```

---

## Troubleshooting

### Free Plan not activating
- Verify: plan.monthly_price = 0
- Check: Status should be 'active'
- Debug: Check browser console for errors

### Paystack payment not redirecting
- Verify: API keys in .env
- Check: curl extension enabled
- Debug: Test with Paystack test keys first

### Feature not gating properly
- Verify: Middleware applied to route
- Check: Business has active subscription
- Debug: Test getActiveSubscription() query

### Subscription not found in payment callback
- Verify: transaction_reference saved
- Check: Callback URL matches config
- Debug: Check logs for Paystack errors

---

**Keep this file open while working with subscription system!**
