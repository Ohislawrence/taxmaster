# Get Started Guide - Complete Implementation

## Overview

The "Get Started" feature is a comprehensive onboarding guide that helps new users understand important areas they need to complete to enjoy the full TaxMaster experience. Similar to Mono's dashboard, it provides:

- **Progress tracking** with completion percentages
- **Step-by-step checklist** with 7 essential onboarding tasks
- **Priority levels** (high, medium, low) to guide focus
- **Visual feedback** with badges and progress bars
- **Auto-detection** of completed steps based on actual user activity
- **Dismissible widget** on dashboard with snooze functionality

---

## 7 Essential Onboarding Steps

| # | Step | Priority | Completion | Estimated Time |
|---|------|----------|-----------|-----------------|
| 1 | Complete Your Business Profile | HIGH | Auto-detected | 5 min |
| 2 | Link Your Bank Account | HIGH | Auto-detected | 3 min |
| 3 | Choose Your Subscription Plan | HIGH | Auto-detected | 5 min |
| 4 | Add Your Team Members | MEDIUM | Auto-detected | 5 min |
| 5 | File Your First Tax Return | HIGH | Manual | 10 min |
| 6 | Enable Transaction Sync | MEDIUM | Auto-detected | 3 min |
| 7 | Check Your Usage & Limits | LOW | Manual/Auto | 5 min |

---

## Architecture

### Database
**Table:** `get_started_progress`
```sql
- id (Primary Key)
- business_id (Foreign Key)
- completed_steps (JSON array of step IDs)
- completion_percentage (0-100)
- dismissed (Boolean)
- dismissed_at (Timestamp)
- remind_at (Timestamp)
- started_at (Timestamp)
- completed_at (Timestamp)
- timestamps
```

### Models

#### GetStartedProgress Model
**File:** [app/Models/GetStartedProgress.php](app/Models/GetStartedProgress.php)

**Key Methods:**
- `isStepCompleted(stepId)` - Check if a step is completed
- `markStepCompleted(stepId)` - Mark step as done and update progress %
- `markStepIncomplete(stepId)` - Revert step completion
- `dismiss()` - User dismisses the guide
- `undismiss()` - Show guide again
- `snoozeUntil($minutes)` - Remind user later
- `isSnoozed()` - Check if currently snoozed
- `canShowGuide()` - Determine if guide should display

**Relationships:**
```php
$progress->business(); // Get associated business
```

#### Business Model Enhancement
Added relationship to GetStartedProgress:
```php
$business->getStartedProgress(); // Get progress tracking
```

---

### Controller

**File:** [app/Http/Controllers/Business/GetStartedController.php](app/Http/Controllers/Business/GetStartedController.php)

**Methods:**

1. **index(Request $request)**
   - Display the Get Started guide page
   - Auto-detect completed steps based on business data
   - Show progress stats and step details
   - Returns Inertia page with full step data

2. **completeStep(Request $request)**
   - Mark a step as completed via AJAX
   - Updates completion percentage
   - Response: JSON with updated stats

3. **incompleteStep(Request $request)**
   - Mark a step as incomplete via AJAX
   - Updates progress percentage
   - Response: JSON confirmation

4. **dismiss(Request $request)**
   - User dismisses the guide
   - Optional: snooze for N minutes
   - Response: JSON success

5. **undismiss(Request $request)**
   - Show guide again (for widget "Show Again")
   - Response: JSON success

**Auto-Detection Logic:**
```php
// Private method updateCompletedSteps()
// Auto-completes steps when users:
- Add business profile fields (email, phone, address, type)
- Link active bank accounts
- Upgrade from Free plan
- Add team members/staff
- Sync transactions from bank
- File tax returns or add multiple staff (triggers plan check)
```

---

### Frontend

#### Composable: useGetStarted()
**File:** [resources/js/composables/useGetStarted.js](resources/js/composables/useGetStarted.js)

**Reactive Properties:**
```javascript
// State
progress                    // Get Started progress object
completionPercentage       // 0-100 percentage
isCompleted               // Boolean: all steps done?
steps                     // Array of step objects
sortedSteps              // Steps sorted by order
nextIncompleteStep       // Next step to complete
completedStepsCount      // Number of done steps
totalStepsCount          // Total steps (always 7)

// Grouped
stepsByPriority          // { high, medium, low }

// Computed
progressColor            // Tailwind color class based on %
progressMessage          // User-friendly progress text
```

**Methods:**
```javascript
isStepCompleted(stepId)    // Check step status
getStep(stepId)            // Get step object by ID
completeStep(stepId)       // Mark as done (async)
incompleteStep(stepId)     // Mark as not done (async)
dismissGuide(minutes)      // Dismiss or snooze (async)
undismissGuide()           // Show guide again (async)
```

**Usage Example:**
```vue
<script setup>
import { useGetStarted } from '@/composables/useGetStarted';

const { 
    completionPercentage, 
    nextIncompleteStep, 
    completeStep 
} = useGetStarted();
</script>
```

---

#### Page Component: GetStarted/Index.vue
**File:** [resources/js/Pages/Business/GetStarted/Index.vue](resources/js/Pages/Business/GetStarted/Index.vue)

**Features:**
- Header with icon and description
- 3-column stat cards (progress, completed, next step)
- Success banner when 100% complete
- Steps organized by priority level:
  - **Essential Setup** (high priority)
  - **Optimize Your Setup** (medium priority)
  - **Additional Resources** (low priority)
- Help section with documentation links

**Layout:**
```
┌─────────────────────────────────────┐
│ Get Started with TaxMaster          │
├─────────────────────────────────────┤
│ Overall Progress │ Completed │ Next │
├─────────────────────────────────────┤
│ Essential Setup (High Priority)      │
│ ├─ Step Card 1                       │
│ ├─ Step Card 2                       │
│ └─ Step Card 3                       │
├─────────────────────────────────────┤
│ Optimize Your Setup (Medium)         │
│ ├─ Step Card 4                       │
│ └─ Step Card 5                       │
├─────────────────────────────────────┤
│ Additional Resources (Low)           │
│ └─ Step Card 6, 7                    │
├─────────────────────────────────────┤
│ Need Help? [Documentation] [Support] │
└─────────────────────────────────────┘
```

---

#### Card Component: GetStarted/StepCard.vue
**File:** [resources/js/Components/GetStarted/StepCard.vue](resources/js/Components/GetStarted/StepCard.vue)

**Step Card Features:**
```
┌─────────────────────────────────────────┐
│ ☑ #1. Complete Your Business Profile    │ PRIORITY BADGE
│    Set up your business information,    │ 5 MIN
│    contact details, and settings       │
│                                         │
│ Benefits:                               │
│  ✓ Professional business appearance    │
│  ✓ Accurate tax filings                │
│  ✓ Better record keeping              │
│                                         │
│ Progress: Email ✓│Phone ✗│Address ✓    │
│                                         │
│ [→ Go to Settings] [Requires: —]       │
└─────────────────────────────────────────┘
```

**Interactive Checkbox:**
- Click checkbox to mark complete/incomplete
- Green checkmark when done
- Optional: can be unmarked to redo

**Content:**
- Step title with order number
- Description
- List of benefits (2-column grid)
- Progress indicators (current status)
- CTA button linking to step's action page
- Dependencies note (if requires another step)

---

#### Dashboard Widget: GetStarted/Widget.vue
**File:** [resources/js/Components/GetStarted/Widget.vue](resources/js/Components/GetStarted/Widget.vue)

**Display on Dashboard:**
```
┌─────────────────────────────────────┐
│ ✓ Get Started with TaxMaster    [✕] │
│ 45% Complete                        │
├─────────────────────────────────────┤
│ ▓▓▓░░░░░░░░░░░░ 45%                │
│ 3 of 7 steps completed              │
├─────────────────────────────────────┤
│ Next step: Link Your Bank Account    │
│                                     │
│ [View Checklist] [Remind Later]     │
└─────────────────────────────────────┘
```

**Features:**
- Dismissible/snooze functionality
- Progress bar with color coding:
  - Green (>=100%) - Complete
  - Blue (>=75%) - Almost done
  - Yellow (>=50%) - Halfway there
  - Orange (<50%) - Just started
- Shows next incomplete step
- Success banner when finished (replaces progress)
- Links to full checklist and reminders

---

## Routes

**File:** [routes/business.php](routes/business.php#L32-L39)

```php
Route::prefix('get-started')->name('get-started.')->group(function () {
    Route::get('/', [GetStartedController::class, 'index'])->name('index');
    Route::post('/complete-step', [GetStartedController::class, 'completeStep'])->name('complete-step');
    Route::post('/incomplete-step', [GetStartedController::class, 'incompleteStep'])->name('incomplete-step');
    Route::post('/dismiss', [GetStartedController::class, 'dismiss'])->name('dismiss');
    Route::post('/undismiss', [GetStartedController::class, 'undismiss'])->name('undismiss');
});
```

---

## Menu Integration

**File:** [resources/js/Layouts/BusinessLayout.vue](resources/js/Layouts/BusinessLayout.vue#L47-L56)

"Get Started" link added to main navigation sidebar:
- Position: Top of menu (before Dashboard)
- Icon: Checkmark in circle (shield icon)
- Active state: Blue highlight when on Get Started page
- Always visible to all authenticated users

---

## User Workflows

### Workflow 1: First Time User

```
1. User logs in → Lands on Dashboard
2. Sees Get Started Widget (30% complete)
3. Clicks "View Checklist"
4. Sees 7 steps with benefits
5. Follows steps in order:
   - Completes profile (auto-detected)
   - Links bank account (auto-detected)
   - Chooses plan (auto-detected)
   - Adds staff (if applicable)
   - Files first return (auto-detected or manual)
   - Enables sync (auto-detected)
   - Checks limits (manual)
6. Sees "Congratulations! You're all set"
```

### Workflow 2: Partial User (Want to Revisit Later)

```
1. User on Dashboard
2. Sees Get Started Widget at 60%
3. Clicks "Remind Later" → Widget hidden for 12 hours
4. After 12 hours, widget reappears
5. User can dismiss permanently or continue
```

### Workflow 3: User Dismisses Guide

```
1. User clicks X or "Dismiss" on widget
2. Widget is hidden (dismissed = true)
3. Get Started link still visible in menu
4. User can access via menu: Sidebar → Get Started
5. Page shows undismiss button to re-show widget
```

### Workflow 4: Manual Step Completion

```
1. User on Get Started page
2. Some steps are auto-completed (checked)
3. Some steps still pending (unchecked)
4. User can manually click checkbox to mark done
5. Progress percentage updates instantly
6. Reloads page to sync with server
```

---

## Data Flow

### Auto-Detection Process

When user accesses Get Started page:

```
1. Controller receives request
2. Gets or creates GetStartedProgress record
3. Runs updateCompletedSteps() private method:
   a. Checks business.email, .phone, .address, .business_type
      → If all filled, add 'complete_profile'
   b. Checks BankAccount.count (active)
      → If > 0, add 'link_bank'
   c. Checks activeSubscription.plan (not = 'Free')
      → If upgraded, add 'choose_plan'
   d. Checks BusinessStaff.count
      → If > 0, add 'add_staff'
   e. Checks CitReturn.count OR VatReturn.count
      → If > 0, add 'file_first_return'
   f. Checks BankAccount.last_synced_at (not null)
      → If any synced, add 'sync_transactions'
   g. Checks staff count >= 3 OR returns >= 2
      → If true, add 'check_limits'
4. Calculates completion_percentage = (completed / 7) * 100
5. If percentage == 100 and completed_at null, set completed_at = now()
6. Saves progress record
7. Returns step data to frontend
```

### Frontend Update Flow

When user clicks step checkbox:

```
1. User clicks checkbox on step
2. Frontend calls completeStep(stepId) via AJAX
3. Controller receives POST request
4. Calls progress.markStepCompleted(stepId)
5. Returns JSON with updated stats
6. Composable triggers page reload
7. Frontend updates UI with new progress
```

---

## Features & Benefits

### For Users
- 🚀 Clear guidance on what to do first
- 📊 Visual progress tracking
- ⏰ Flexible (can snooze reminders)
- 🎯 Prioritized by importance
- 💡 Shows benefits of each step
- ✅ Celebrates milestones

### For Business
- 📈 Increased feature adoption
- 🎯 Better onboarding metrics
- 💾 Reduced support tickets
- 📊 Data on which features users skip
- 🔄 Encourages subscription upgrades through step 3

---

## Tracking & Analytics

Data points collected in `get_started_progress`:
- `started_at` - When user first accessed guide
- `completed_at` - When all steps finished
- `completed_steps` - Which features they used
- `dismissed_at` - If/when user dismissed
- `remind_at` - Next reminder time
- `completion_percentage` - Final progress %

**Use Cases:**
- User behavior analysis
- Feature adoption metrics
- Support/success correlation
- Engagement tracking

---

## Testing Checklist

- [ ] Create new business → Widget shows 0%
- [ ] Fill business profile → Auto-detects completion
- [ ] Link bank account → Auto-detects completion
- [ ] Upgrade plan → Auto-detects completion
- [ ] Add staff → Auto-detects completion
- [ ] File CIT/VAT return → Auto-detects completion
- [ ] Sync bank transactions → Auto-detects completion
- [ ] Check limits → Auto-detects completion
- [ ] Manually mark step → Updates instantly
- [ ] Dismiss widget → Hidden on dashboard
- [ ] Dismiss with snooze → Hidden for 12 hours
- [ ] Reach 100% completion → Shows success banner
- [ ] Access via menu → Works when dismissed
- [ ] Progress percentage accuracy → Math is correct

---

## Developer Notes

### Adding a New Step

To add an 8th step to the guide:

1. **Update Order:** Change total from 7 to 8 in controller
2. **Add Step Data:** Add object to `getStepsData()` array
3. **Add Auto-Detection:** Add case in `updateCompletedSteps()`
4. **Update Frontend:** Component automatically renders new step

### Customizing Step Benefits

Edit the `getStepsData()` method in GetStartedController:

```php
'benefits' => [
    'Benefit 1',
    'Benefit 2',
    'Benefit 3',
],
```

### Changing Progress Colors

Edit `progressColor` computed property in useGetStarted.js:

```javascript
const progressColor = computed(() => {
    if (completionPercentage.value >= 100) return 'bg-green-500';
    // ... etc
});
```

---

## Files Summary

### Backend Files
- `database/migrations/2026_02_27_000008_create_get_started_progress_table.php` - Database table
- `app/Models/GetStartedProgress.php` - Model with helpers
- `app/Http/Controllers/Business/GetStartedController.php` - Main controller

### Frontend Files
- `resources/js/composables/useGetStarted.js` - Reusable logic
- `resources/js/Pages/Business/GetStarted/Index.vue` - Main page
- `resources/js/Components/GetStarted/StepCard.vue` - Step display
- `resources/js/Components/GetStarted/Widget.vue` - Dashboard widget

### Configuration
- `routes/business.php` - Routes
- `resources/js/Layouts/BusinessLayout.vue` - Menu link

---

## Next Steps

1. **Run migration:** `php artisan migrate`
2. **Test widget:** Login → Check dashboard
3. **Test guide:** Navigate to /business/get-started
4. **Verify auto-detection:** Complete profile → Check progress
5. **Monitor adoption:** Track completion_percentage metrics
6. **Customize:** Adjust benefits and CTA text as needed

---

**Status:** ✅ COMPLETE
**Date:** 2025-02-27
**Version:** 1.0
