# Subscription Enforcement - Quick Reference

## For Developers: Adding Feature Gating to New Features

### 1. Define Feature in Subscription Plans

Update subscription plans with new feature:
```php
// In database or via script
$plan->update([
    'features' => [
        // ... existing features
        'new_feature_name',
    ]
]);
```

---

### 2. Add Feature Check to SubscriptionService

**File:** `app/Services/SubscriptionService.php`

Add case to `canPerformAction()` method:
```php
public function canPerformAction(Business $business, string $action): bool
{
    // ... existing code ...
    
    // Your new feature
    case 'new_feature_name':
        // Free plan check
        if ($this->hasPlan($business, 'free')) {
            return false;
        }
        // Basic+ allowed
        return $this->hasPlan($business, 'basic') && $this->hasFeature($business, 'new_feature_name');
    
    // ... rest of switch ...
}
```

---

### 3. Apply Middleware to Routes

**File:** `routes/business.php`

```php
Route::middleware('subscription.features:new_feature_name')->group(function () {
    Route::get('/new-feature', [NewFeatureController::class, 'index']);
    Route::post('/new-feature', [NewFeatureController::class, 'store']);
});
```

---

### 4. Add Controller Check

**File:** `app/Http/Controllers/Business/NewFeatureController.php`

```php
use App\Services\SubscriptionService;

class NewFeatureController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}
    
    public function create(Request $request)
    {
        $business = $request->user()->ownedBusiness;
        
        // Check subscription
        if (!$this->subscriptionService->canPerformAction($business, 'new_feature_name')) {
            return redirect()->route('business.plans.index')
                ->with('error', 'Your current plan does not include this feature. Please upgrade to Basic or higher.');
        }
        
        // ... rest of method
    }
}
```

---

### 5. Add Frontend Check

**File:** `resources/js/Pages/Business/NewFeature/Index.vue`

```vue
<script setup>
import { useSubscription } from '@/composables/useSubscription';
import UpgradePrompt from '@/Components/UpgradePrompt.vue';

const { can, getUpgradeMessage, getRequiredPlan } = useSubscription();

// Add custom feature check if needed
const canUseNewFeature = computed(() => {
    return can.hasPlan('basic') && can.canUseFeature('new_feature_name');
});
</script>

<template>
    <!-- Upgrade prompt -->
    <UpgradePrompt
        v-if="!canUseNewFeature"
        :show="true"
        feature="new_feature_name"
        required-plan="basic"
        title="Upgrade to Use New Feature"
        message="This feature is available on Basic plan and above."
    />
    
    <!-- Feature button -->
    <button
        v-if="canUseNewFeature"
        @click="doSomething"
        class="btn-primary"
    >
        Use Feature
    </button>
    
    <button
        v-else
        disabled
        class="btn-disabled"
    >
        <i class="fas fa-lock"></i>
        Use Feature
    </button>
</template>
```

---

### 6. Update useSubscription Composable (Optional)

**File:** `resources/js/composables/useSubscription.js`

Add convenience method:
```javascript
const can = {
    // ... existing checks ...
    
    useNewFeature: computed(() => hasPlan('basic') && canUseFeature('new_feature_name')),
};
```

---

## Feature Plan Requirements

| Plan Tier | Available From |
|-----------|---------------|
| Free | All users |
| Basic | `hasPlan('basic')` |
| Professional | `hasPlan('professional')` |
| Enterprise | `hasPlan('enterprise')` |

---

## Common Feature Checks

### Backend (Controller)
```php
// Single feature check
if (!$this->subscriptionService->canPerformAction($business, 'feature_name')) {
    return redirect()->route('business.plans.index')
        ->with('error', 'Upgrade required.');
}

// Multiple checks
if (!$this->subscriptionService->canPerformAction($business, 'feature_1') 
    || !$this->subscriptionService->canPerformAction($business, 'feature_2')) {
    return redirect()->route('business.plans.index')
        ->with('error', 'Multiple features required.');
}
```

### Frontend (Vue)
```vue
<script setup>
const { can } = useSubscription();

// Simple check
if (can.fileCIT.value) { /* ... */ }

// Plan tier check
if (can.hasPlan('professional')) { /* ... */ }

// Custom feature check
if (can.canUseFeature('custom_feature')) { /* ... */ }
</script>
```

---

## Testing Subscription Enforcement

### 1. Test with Tinker
```bash
php artisan tinker
```

```php
$business = Business::first();
$service = app(App\Services\SubscriptionService::class);

// Check feature access
$service->canPerformAction($business, 'file_cit'); // true/false

// Check plan
$service->hasPlan($business, 'professional'); // true/false

// Get active subscription
$subscription = $service->getActiveSubscription($business);
echo $subscription->plan->name; // "Professional"
```

### 2. Test with Browser
1. Login as user with specific plan
2. Try to access gated feature
3. Verify:
   - Middleware blocks request (403 or redirect)
   - Controller shows upgrade message
   - Frontend hides/disables buttons
   - Upgrade prompts appear

### 3. Test Upgrades
```php
// In tinker
$business = Business::first();
$service = app(App\Services\SubscriptionService::class);

// Simulate upgrade
$subscription = $business->activeSubscription;
$subscription->update(['plan_id' => 2]); // Basic plan

// Verify new features available
$service->canPerformAction($business, 'file_cit'); // Should now be true
```

---

## Troubleshooting

### Issue: Middleware blocks but feature should be available
**Solution:** Check that:
1. Subscription is active: `$subscription->isActive()`
2. Plan has feature: `$plan->features` JSON includes feature name
3. SubscriptionService has correct case in `canPerformAction()`

### Issue: Frontend shows feature as available but backend blocks
**Solution:** Clear frontend cache:
```bash
npm run build       # Rebuild frontend
php artisan cache:clear
```

### Issue: User upgraded but features still locked
**Solution:** Session may be stale:
1. Log out and log back in
2. Or reload Inertia props:
```javascript
router.reload({ only: ['subscription', 'plan'] })
```

---

## Plan Feature Matrix Reference

```
FREE PLAN (₦0/month)
├── Tax Returns
│   ├── ✅ PAYE (unlimited)
│   └── ✅ WHT (unlimited)
├── Staff
│   └── 1 staff member
└── Storage: 1GB

BASIC PLAN (₦5,000/month)
├── Everything in Free +
├── Tax Returns
│   ├── ✅ CIT (unlimited)
│   └── ✅ VAT (unlimited)
├── Banking
│   ├── ✅ Link bank accounts
│   └── ✅ Auto-sync transactions
├── Export
│   └── ✅ PDF exports
├── Staff
│   └── 5 staff members
└── Storage: 5GB

PROFESSIONAL PLAN (₦15,000/month)
├── Everything in Basic +
├── Tax Returns
│   └── ✅ CGT (unlimited)
├── AI Features
│   ├── ✅ AI analysis
│   ├── ✅ AI chat
│   └── ✅ AI optimization
├── Reporting
│   ├── ✅ Financial statements
│   ├── ✅ CAC forms
│   └── ✅ Advanced reports
├── API
│   └── ✅ API access
├── Staff
│   └── 20 staff members
└── Storage: 50GB

ENTERPRISE PLAN (₦50,000/month)
├── Everything in Professional +
├── Enterprise Features
│   ├── ✅ Custom branding
│   ├── ✅ White label
│   ├── ✅ Multi-business
│   ├── ✅ SSO integration
│   ├── ✅ Priority support
│   └── ✅ Dedicated account manager
├── Staff
│   └── Unlimited
└── Storage: 500GB
```

---

## Example: Full Implementation Flow

Let's say we want to add "Expense Tracking" feature for Professional+ users.

### Step 1: Update Plans
```sql
UPDATE subscription_plans 
SET features = JSON_ARRAY_APPEND(features, '$', 'track_expenses')
WHERE name IN ('Professional', 'Enterprise');
```

### Step 2: Update SubscriptionService
```php
case 'track_expenses':
    return $this->hasPlan($business, 'professional') 
        && $this->hasFeature($business, 'track_expenses');
```

### Step 3: Add Routes with Middleware
```php
Route::middleware('subscription.features:track_expenses')->group(function () {
    Route::resource('expenses', ExpenseController::class);
});
```

### Step 4: Add Controller Check
```php
public function create()
{
    if (!$this->subscriptionService->canPerformAction($business, 'track_expenses')) {
        return redirect()->route('business.plans.index')
            ->with('error', 'Expense tracking requires Professional plan.');
    }
    
    return Inertia::render('Business/Expenses/Create');
}
```

### Step 5: Update Frontend Composable
```javascript
// In useSubscription.js
trackExpenses: computed(() => 
    hasPlan('professional') && canUseFeature('track_expenses')
),
```

### Step 6: Add to Navigation (Conditional)
```vue
<Link 
    v-if="can.trackExpenses.value"
    :href="route('business.expenses.index')"
>
    Expenses
</Link>
```

### Step 7: Test
```bash
# Test as Free user
curl -X GET http://taxmaster.test/business/expenses
# Expected: 403 Forbidden

# Test as Professional user
curl -X GET http://taxmaster.test/business/expenses
# Expected: 200 OK (Expenses page)
```

---

## Best Practices

1. **Always implement all 3 layers:**
   - Route middleware (security)
   - Controller checks (defense in depth)
   - Frontend validation (UX)

2. **Use descriptive feature names:**
   - ✅ Good: `track_expenses`, `file_cit`, `use_ai_chat`
   - ❌ Bad: `feature1`, `exp`, `ai`

3. **Show upgrade prompts, not errors:**
   - Users should know WHY they can't use a feature
   - Provide clear upgrade path

4. **Test with all plan tiers:**
   - Create test accounts for Free, Basic, Professional, Enterprise
   - Verify feature access matrix

5. **Document feature requirements:**
   - Update plan comparison page
   - Add tooltips to locked features
   - Create upgrade prompts with benefit lists

---

## Quick Commands

```bash
# Check user's plan
php artisan tinker
>>> $user = User::find(1);
>>> $user->ownedBusiness->activeSubscription->plan->name

# List all features for a plan
>>> $plan = SubscriptionPlan::where('name', 'Professional')->first();
>>> json_decode($plan->features)

# Test feature access
>>> $business = Business::first();
>>> app(App\Services\SubscriptionService::class)->canPerformAction($business, 'file_cit');

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
npm run build
```

---

**Need Help?** Check:
- [SUBSCRIPTION_ENFORCEMENT_COMPLETE.md](SUBSCRIPTION_ENFORCEMENT_COMPLETE.md) - Full implementation guide
- [SUBSCRIPTION_SYSTEM.md](SUBSCRIPTION_SYSTEM.md) - System architecture
- [app/Services/SubscriptionService.php](app/Services/SubscriptionService.php) - Feature logic
- [resources/js/composables/useSubscription.js](resources/js/composables/useSubscription.js) - Frontend helper
