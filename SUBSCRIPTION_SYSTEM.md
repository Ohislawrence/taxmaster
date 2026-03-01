# Subscription & Payment System Documentation

## Overview

TaxMaster now has a complete subscription and payment system with:
- **4 Tiered Plans**: Free, Basic, Professional, Enterprise
- **Admin Plan Management**: Create, edit, delete plans
- **Business Subscribing**: Browse plans, select billing cycle, process payments
- **Payment Processing**: Paystack integration for secure payments
- **Feature Gating**: Restrict features based on subscription tier
- **Usage Tracking**: Monitor staff members, tax returns, and storage usage

---

## Architecture

### Database Schema

#### `subscription_plans` Table
Stores all available subscription plans managed by admins.

**Fields:**
- `id` - Primary key
- `name` - Plan name (Free, Basic, Professional, Enterprise)
- `slug` - URL-friendly identifier (free, basic, professional, enterprise)
- `description` - Plan description
- `monthly_price` - Monthly billing amount (₦)
- `annual_price` - Annual billing amount (₦)
- `max_staff_members` - Maximum team members allowed
- `max_returns_per_year` - Maximum tax returns per year
- `storage_gb` - Storage allocation in GB
- `ai_analysis_included` - Boolean: AI feature access
- `payment_automation` - Boolean: Payment automation feature
- `priority_support` - Boolean: 24/7 support access
- `custom_branding` - Boolean: White-label branding
- `features` - JSON array of additional features
- `is_active` - Whether plan appears on pricing page
- `display_order` - Sort order for display (lower = first)
- `timestamps` - created_at, updated_at

#### `business_subscriptions` Table (Enhanced)
Updated with new fields to link plans and track payments.

**New Fields Added:**
- `plan_id` - Foreign key to subscription_plans
- `payment_status` - pending, completed, failed
- `payment_method` - paystack, bank_transfer, etc.
- `transaction_reference` - Unique Paystack reference

---

## Models

### SubscriptionPlan Model
**Location:** `app/Models/SubscriptionPlan.php`

**Key Methods:**
```php
// Relationships
hasMany('subscriptions') // All subscriptions for this plan
activeSubscriptions()    // Only active subscriptions

// Scopes
active()          // Plans marked as is_active = true
ordered()         // Ordered by display_order

// Helpers
isFree()          // Returns true if monthly_price = 0
getFeaturesList() // Returns array of enabled features
```

### BusinessSubscription Model
**Location:** `app/Models/BusinessSubscription.php`

**Enhanced with:**
```php
// New relationship
plan() // BelongsTo SubscriptionPlan

// Updated fillable fields
plan_id, payment_status, payment_method, transaction_reference
```

---

## Services

### SubscriptionService
**Location:** `app/Services/SubscriptionService.php`

**Core Methods:**

```php
// Plan retrieval
getAvailablePlans()               // Get active plans
getPlanBySlug(string $slug)       // Get plan by slug

// Subscription management
createSubscription($business, $plan, $cycle, $method, $ref)
activateSubscription($subscription)    // Activate after payment
cancelSubscription($subscription, $reason)
upgradeSubscription($subscription, $newPlan, $cycle)
downgradeSubscription($subscription, $newPlan, $cycle)

// Subscription info
getActiveSubscription($business)
getUsageStats($business)           // Returns usage percentages
isExpiringsoon($subscription, $days)

// Feature checking
hasFeature($business, $feature)     // Check if feature available
canPerformAction($business, $action) // Check subscription limits

// Renewal
renewSubscription($subscription)    // Extend subscription
```

---

## Controllers

### Admin\PlanController
**Location:** `app/Http/Controllers/Admin/PlanController.php`

**Routes:**
- `GET /admin/plans` - List all plans (index)
- `GET /admin/plans/create` - Create form (create)
- `POST /admin/plans` - Store new plan (store)
- `GET /admin/plans/{plan}/edit` - Edit form (edit)
- `PUT /admin/plans/{plan}` - Update plan (update)
- `DELETE /admin/plans/{plan}` - Delete plan (destroy)

**Features:**
- CRUD operations for subscription plans
- Prevents deletion if plan has active subscriptions
- Validates pricing and feature combinations
- Middleware: `auth`, `admin`

### Business\SubscribeController
**Location:** `app/Http/Controllers/Business/SubscribeController.php`

**Routes:**
- `GET /business/plans` - Show pricing page (showPlans)
- `GET /business/plans/{plan}` - Show checkout page (selectPlan)
- `POST /business/plans/{plan}/checkout` - Process checkout (processCheckout)
- `GET /business/plans/payment/callback` - Payment callback (paymentCallback)
- `POST /business/subscription/upgrade` - Upgrade plan (upgrade)
- `POST /business/subscription/cancel` - Cancel subscription (cancel)

**Features:**
- Browse available plans
- Select billing cycle (monthly/annual)
- Process free plan signups immediately
- Initialize Paystack payments for paid plans
- Handle payment verification and callbacks
- Support plan upgrades and downgrades
- Cancel subscriptions with optional reason

---

## Vue Components

### Admin Pages

#### Admin/Plans/Index.vue
- Display all plans in table format
- Show pricing, features, active subscriptions count
- Edit/delete plan buttons
- Create new plan link
- Pagination support

#### Admin/Plans/Form.vue
- Create/edit subscription plans
- Form fields for all plan attributes
- Auto-generate slug from plan name
- Feature checkboxes
- Display order input
- Form validation and error display

### Business Pages

#### Business/Plans/Pricing.vue
- Show all active plans in responsive grid
- Monthly/annual billing toggle
- Current plan highlight
- Plan features comparison
- FAQ section
- Call-to-action buttons
- Savings amount display for annual billing

#### Business/Plans/Checkout.vue
- Review business details
- Confirm plan selection
- Choose billing cycle
- Display pricing breakdown
- Feature list confirmation
- Paystack payment integration
- Sticky price summary panel
- Terms and conditions acceptance

---

## Seeded Plans

### 1. Free Plan
- **Price:** ₦0/month
- **Max Staff:** 1
- **Returns/Year:** 5
- **Storage:** 1 GB
- **Features:** Basic filing, community support

### 2. Basic Plan
- **Price:** ₦5,000/month (₦50,000/year)
- **Max Staff:** 3
- **Returns/Year:** 50
- **Storage:** 5 GB
- **Features:** AI analysis, email support

### 3. Professional Plan
- **Price:** ₦15,000/month (₦150,000/year)
- **Max Staff:** 10
- **Returns/Year:** 500
- **Storage:** 50 GB
- **Features:** All features, priority support (24/7), API access

### 4. Enterprise Plan
- **Price:** ₦50,000/month (₦500,000/year)
- **Max Staff:** Unlimited
- **Returns/Year:** Unlimited
- **Storage:** 500 GB
- **Features:** All features + white-label, custom AI, dedicated support

---

## Payment Integration (Paystack)

### Setup Required

1. **Config file:** `config/services.php`
```php
'paystack' => [
    'public' => env('PAYSTACK_PUBLIC_KEY'),
    'secret' => env('PAYSTACK_SECRET_KEY'),
],
```

2. **Environment variables:** `.env`
```
PAYSTACK_PUBLIC_KEY=pk_live_xxxxxxxx
PAYSTACK_SECRET_KEY=sk_live_xxxxxxxx
```

### Payment Flow

1. **Checkout Initialization**
   - Calculate amount in kobo (₦ × 100)
   - Prepare metadata with subscription details
   - Call Paystack initialize endpoint
   - Redirect user to Paystack form

2. **Payment Processing**
   - Paystack handles card/bank payment
   - User redirected to callback URL with reference
   - Verify payment status with Paystack API
   - Activate subscription on successful payment

3. **Webhook (Optional)**
   - Implement `/business/payments/webhook/paystack` for async verification
   - Backup verification method for failed callbacks

---

## Feature Gating

### Middleware: CheckSubscriptionFeatures
**Location:** `app/Http/Middleware/CheckSubscriptionFeatures.php`

**Usage in Routes:**
```php
Route::post('ai/analyze', [AiController::class, 'analyze'])
    ->middleware('check-subscription-features:use_ai_analysis');
```

**Supported Actions:**
- `add_staff` - Check staff limit
- `file_return` - Check returns limit
- `use_ai_analysis` - Check AI feature
- `use_payment_automation` - Check payment automation
- `access_premium_features` - Generic premium feature check

**Response (if denied):**
```json
{
    "error": "This feature is not available on your current plan",
    "action": "upgrade",
    "feature": "use_ai_analysis"
}
```

---

## Usage Examples

### For Admins

**Create a new plan:**
1. Navigate to `/admin/plans`
2. Click "Create Plan"
3. Fill in plan details, pricing, features
4. Set display order and active status
5. Save

**Manage existing plans:**
- Edit: Click "Edit" button on any plan
- Delete: Click "Delete" (only if no active subscriptions)
- View subscriptions: Count shown in table

### For Businesses

**Browse plans:**
1. Visit `/business/plans`
2. Toggle between monthly/annual billing
3. Compare features across plans
4. Click plan to proceed

**Subscribe to plan:**
1. Select plan from pricing page
2. Choose billing cycle
3. Review checkout details
4. Click "Proceed to Payment" (or "Activate" for free plan)
5. Complete Paystack payment
6. Subscription activates immediately

**Check subscription status:**
- View in Settings page
- See usage (staff, returns, storage)
- See renewal date
- Upgrade or downgrade options available

**Upgrade plan:**
1. From settings or pricing page
2. Select higher tier plan
3. Confirm upgrade
4. Process payment if required
5. Features immediately available

**Cancel subscription:**
1. From settings page
2. Provide optional cancellation reason
3. Confirm cancellation
4. Downgrade to free plan or lose access

---

## Admin Routes (New)

```php
// Plan management
Route::resource('plans', PlanController::class)->middleware('admin');

// Routes registered in routes/admin.php
GET|HEAD   /admin/plans                     # index
GET|HEAD   /admin/plans/create              # create
POST       /admin/plans                     # store
GET|HEAD   /admin/plans/{plan}/edit         # edit
PUT|PATCH  /admin/plans/{plan}              # update
DELETE     /admin/plans/{plan}              # destroy
```

---

## Business Routes (New)

```php
// Subscription management
GET|HEAD   /business/plans                              # showPlans
GET|HEAD   /business/plans/{plan}                       # selectPlan
POST       /business/plans/{plan}/checkout              # processCheckout
GET|HEAD   /business/plans/payment/callback             # paymentCallback
POST       /business/subscription/upgrade               # upgrade
POST       /business/subscription/cancel                # cancel
GET|HEAD   /business/subscription                       # subscription (in SettingsController)
```

---

## Key Features

### ✅ Implemented

- [x] Subscription plan CRUD (admin)
- [x] 4 default plans with features
- [x] Plan selection and checkout flow
- [x] Monthly/annual billing options
- [x] Paystack payment integration
- [x] Free plan instant activation
- [x] Subscription activation on payment
- [x] Plan upgrades and downgrades
- [x] Subscription cancellation
- [x] Feature access gating
- [x] Usage tracking (staff, returns, storage)
- [x] Responsive UI components
- [x] Admin plan management dashboard

### 🔄 Feature Gating Integration Required

To fully enable feature gating, apply this middleware to routes:

```php
// In business routes
Route::post('ai/analyze', [AiController::class, 'analyze'])
    ->middleware('check-subscription-features:use_ai_analysis');

Route::post('staff/{staff}', [StaffController::class, 'store'])
    ->middleware('check-subscription-features:add_staff');

Route::post('tax-returns', [TaxReturnController::class, 'store'])
    ->middleware('check-subscription-features:file_return');
```

### 📋 Next Steps (Optional Enhancements)

1. **Webhook Implementation** - Async payment confirmation
2. **Billing History** - Display past invoices/receipts
3. **Usage Alerts** - Notify when approaching limits
4. **Auto-Renewal** - Automatic plan renewal
5. **Custom Plans** - Enterprise custom pricing
6. **Coupon System** - Discount codes
7. **Invoicing** - Auto-generated invoices
8. **Analytics Dashboard** - Revenue reports for admins

---

## Migration History

```bash
# Create subscription_plans table
2026_02_24_100001_create_subscription_plans_table

# Add plan_id and payment fields to business_subscriptions
2026_02_24_100002_add_plan_id_to_business_subscriptions
```

## Seeding

```bash
php artisan db:seed --class=SubscriptionPlanSeeder
```

Creates the 4 default plans: Free, Basic, Professional, Enterprise.

---

## Testing Checklist

- [ ] Admin can create new plans
- [ ] Admin can edit plans
- [ ] Admin can delete plans (if no active subscriptions)
- [ ] Free plan activates immediately without payment
- [ ] Paid plan redirects to Paystack on checkout
- [ ] Payment callback activates subscription
- [ ] Business can upgrade to higher plan
- [ ] Business can downgrade to lower plan
- [ ] Usage tracking shows correct limits
- [ ] Free plan subscribers have feature access
- [ ] Premium features blocked without proper subscription
- [ ] Pricing page displays correctly
- [ ] Monthly/annual toggle calculates savings
- [ ] Checkout page shows complete order summary

---

## Troubleshooting

### Subscription not activating after payment
- Verify Paystack credentials in `.env`
- Check payment callback route is not protected
- Ensure transaction_reference matches in database

### Free plan not activating
- Ensure plan.monthly_price = 0 in database
- Verify create endpoint returns proper response
- Check browser console for JavaScript errors

### Feature gating not working
- Apply middleware to protected routes
- Verify active subscription exists for business
- Check subscription status = 'active'

### Payment redirect not working
- Verify Paystack API keys are correct
- Check curl extension is enabled in PHP
- Verify firewall/proxy allows curl requests
