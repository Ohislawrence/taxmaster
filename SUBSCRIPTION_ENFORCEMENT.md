# Subscription Plan Enforcement System

## Overview
A complete subscription management system that ensures businesses select and maintain an active subscription plan to access features. The system redirects users without subscriptions to the plans page and displays subscription status across the application.

## Components Implemented

### 1. **EnsureSubscription Middleware**
- **Location**: `app/Http/Middleware/EnsureSubscription.php`
- **Purpose**: Checks if a business has an active subscription before allowing access to protected features
- **Behavior**:
  - Exempts subscription management routes (plans, settings) and logout
  - Redirects users without active subscriptions to `/business/plans`
  - Checks subscription status (must be 'active' and not expired)
  - Stores subscription in request for easy access in controllers

**Exempt Routes**:
- `business.plans.index`
- `business.plans.select`
- `business.plans.checkout`
- `business.plans.payment-callback`
- `business.subscription`
- `business.subscription.upgrade-plan`
- `business.settings.index`

### 2. **SubscriptionBanner Component**
- **Location**: `resources/js/Components/Business/SubscriptionBanner.vue`
- **Features**:
  - Displays alert when no active subscription exists
  - Shows active subscription details (plan name, renewal date, billing cycle)
  - Displays usage statistics (staff count, tax returns filed)
  - Shows warnings when approaching subscription limits (80%+ usage)
  - Color-coded banners (yellow for no plan, green for active)
  - Quick access to manage subscription

### 3. **Database Model Updates**
- **Business Model**:
  - Added `subscriptions()` method (alias for `subscription()`)
  - Added `activeSubscription()` helper method
  - Returns active, non-expired subscription

### 4. **SubscriptionService Enhancements**
- **Methods Available**:
  - `getAvailablePlans()` - List active plans
  - `getActiveSubscription(Business)` - Get current active subscription
  - `getUsageStats(Business)` - Get detailed usage information
  - `canPerformAction(Business, action)` - Check if action is allowed
  - `isExpiringSoon(Subscription)` - Check if renewal is approaching
  - `hasFeature(Business, feature)` - Check if feature is available

- **Usage Stats Include**:
  - Staff count vs limit
  - Tax returns filed vs limit
  - Feature availability (AI, payment automation)
  - Billing cycle and renewal date
  - Usage percentages

### 5. **Middleware Registration**
- **File**: `bootstrap/app.php`
- **Registered As**: `ensure.subscription`
- Added to business route group middleware stack

### 6. **Routes Configuration**
- **File**: `routes/business.php`
- All business routes now protected by `ensure.subscription` middleware
- Plan and subscription management routes exempt from the check

### 7. **Dashboard Updates**
- **File**: `resources/js/Pages/Business/Dashboard.vue`
- Now displays SubscriptionBanner at the top
- Shows current subscription status
- Passes subscription data to template

**Dashboard Props**:
- `currentSubscription` - Active subscription object
- `usageStats` - Usage statistics

### 8. **Global Data Sharing**
- **File**: `app/Http/Middleware/HandleInertiaRequests.php`
- Subscription data shared to all pages via props
- Available in any component under `subscription`

**Shared Data Structure**:
```javascript
{
  subscription: {
    active: SubscriptionObject,
    stats: UsageStatistics
  }
}
```

## How It Works

### User Flow Without Subscription:
1. Business user logs in
2. Tries to access dashboard or any protected feature
3. `EnsureSubscription` middleware checks for active subscription
4. If none exists, redirects to `/business/plans`
5. User selects a plan and completes payment
6. Subscription becomes active
7. User can now access all features

### User Flow With Active Subscription:
1. User logs in
2. Can access all features
3. Dashboard shows:
   - Current plan name
   - Renewal date
   - Usage statistics
   - Warnings if approaching limits

### Limit Enforcement:
The system can check if a business can perform actions like:
- `add_staff` - Check against max_staff_members
- `file_return` - Check against max_returns_per_year
- `use_ai_analysis` - Check if included in plan
- `use_payment_automation` - Check if included in plan

## Example Usage in Controllers

```php
// Inject SubscriptionService
public function __construct(SubscriptionService $subscriptionService)
{
    $this->subscriptionService = $subscriptionService;
}

// Check if user can add staff
$business = auth()->user()->ownedBusiness;
if (!$this->subscriptionService->canPerformAction($business, 'add_staff')) {
    return redirect()->route('business.plans')
        ->with('error', 'Staff limit reached. Please upgrade your plan.');
}

// Get usage stats
$stats = $this->subscriptionService->getUsageStats($business);
return view('staff.create', ['usageStats' => $stats]);
```

## Example Usage in Views/Components

```vue
<!-- Check subscription -->
<SubscriptionBanner 
  :currentSubscription="currentSubscription" 
  :usageStats="usageStats" 
/>

<!-- Or use shared global data -->
<div v-if="!$page.props.subscription.active" class="alert">
  No active subscription
</div>
```

## Key Features

✅ **Automatic Redirection** - Users without subscriptions redirected to plans page
✅ **Visual Status** - Clear banners showing subscription status
✅ **Usage Tracking** - Real-time view of usage vs limits
✅ **Expiration Warnings** - Alerts when approaching renewal
✅ **Feature Control** - Different features based on plan
✅ **Easy Upgrade** - Direct links to upgrade plans
✅ **Global Access** - Subscription data available everywhere

## Testing the System

1. **Create a business without subscription**:
   - Log in and setup business
   - Try to access dashboard
   - Should redirect to plans page

2. **Subscribe to a plan**:
   - Choose plan
   - Complete payment
   - Subscription becomes active

3. **View dashboard with subscription**:
   - See subscription banner
   - View usage statistics
   - Check feature availability

4. **Approach limits**:
   - When staff/returns reach 80%
   - Warnings appear on banner
   - Option to upgrade plan

## Configuration

No additional configuration needed. The system uses:
- Existing `BusinessSubscription` model
- Existing `SubscriptionPlan` model
- Configuration in `config/taxmaster.php` for plan details

## Future Enhancements

- Automatic subscription renewal
- Subscription downgrade options
- Free trial period support
- Coupon/discount codes
- Email reminders before expiration
- Usage analytics dashboard
