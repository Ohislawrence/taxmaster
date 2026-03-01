# Get Started Feature - Quick Reference

## 🎯 Feature Overview
User onboarding guide with 7 essential setup steps, progress tracking, and auto-detection of completed tasks.

## 📂 Core Files

### Database & Model
```
database/migrations/2026_02_27_000008_create_get_started_progress_table.php
app/Models/GetStartedProgress.php (88 lines)
```

### Backend Logic
```
app/Http/Controllers/Business/GetStartedController.php (227 lines)
  ├─ index() - Show get started page
  ├─ completeStep() - Mark step done (AJAX)
  ├─ incompleteStep() - Unmark step
  ├─ dismiss() - Hide guide
  ├─ undismiss() - Show guide again
  ├─ updateCompletedSteps() - Auto-detect progress
  └─ getStepsData() - Return 7 steps with metadata
```

### Frontend Logic
```
resources/js/composables/useGetStarted.js (158 lines)
  ├─ state: progress, completionPercentage, isCompleted, steps
  ├─ computed: stepsByPriority, nextIncompleteStep, progressColor
  └─ methods: completeStep(), dismissGuide(), undismissGuide()
```

### UI Components
```
resources/js/Pages/Business/GetStarted/Index.vue (141 lines)
  → Full checklist page with stats and grouped steps
  
resources/js/Components/GetStarted/StepCard.vue (108 lines)
  → Individual step card with checkbox and CTA
  
resources/js/Components/GetStarted/Widget.vue (69 lines)
  → Compact dashboard widget with progress bar
```

### Integration Points
```
routes/business.php
  → 5 routes under 'get-started' prefix
  → GET / → index
  → POST /complete-step, /incomplete-step, /dismiss, /undismiss

resources/js/Layouts/BusinessLayout.vue
  → "Get Started" menu link (first in sidebar)
  
resources/js/Pages/Business/Dashboard.vue
  → GetStartedWidget component below SubscriptionBanner

app/Models/Business.php
  → getStartedProgress() hasOne relationship
```

---

## 🔄 The 7 Steps

| # | Step | Priority | Auto-Detect |
|---|------|----------|-----------|
| 1 | Complete Your Business Profile | HIGH | ✅ (email + phone + address + type) |
| 2 | Link Your Bank Account | HIGH | ✅ (BankAccount count > 0) |
| 3 | Choose Your Subscription Plan | HIGH | ✅ (activeSubscription plan !== 'Free') |
| 4 | Add Your Team Members | MEDIUM | ✅ (BusinessStaff count > 0) |
| 5 | File Your First Tax Return | HIGH | ✅ (CitReturn OR VatReturn exists) |
| 6 | Enable Transaction Sync | MEDIUM | ✅ (BankAccount.last_synced_at not null) |
| 7 | Check Your Usage & Limits | LOW | ✅ (staff >= 3 OR returns >= 2) |

---

## 🚀 Quick Flows

### Display Progress
```javascript
import { useGetStarted } from '@/composables/useGetStarted';

const { completionPercentage, nextIncompleteStep, stepsByPriority } = useGetStarted();

// Output: 45, { id: 'link_bank', title: '...', order: 2 }, { high: [...], medium: [...], low: [...] }
```

### Mark Step Complete
```javascript
const { completeStep } = useGetStarted();
await completeStep('complete_profile');  // Updates DB + UI
```

### Dismiss Guide
```javascript
const { dismissGuide } = useGetStarted();
await dismissGuide(720);  // Hide for 12 hours (720 minutes)
await dismissGuide(0);    // Hide permanently (0 = no snooze)
```

---

## 📊 Data Structure

### GetStartedProgress Model
```javascript
{
    id: 1,
    business_id: 1,
    completed_steps: ['complete_profile', 'link_bank', 'choose_plan'],
    completion_percentage: 43,
    dismissed: false,
    dismissed_at: null,
    remind_at: null,
    started_at: '2025-02-27 10:00:00',
    completed_at: null,
    created_at: '2025-02-27 10:00:00',
    updated_at: '2025-02-27 10:15:00'
}
```

### Step Object
```javascript
{
    id: 'complete_profile',
    order: 1,
    title: 'Complete Your Business Profile',
    description: 'Set up your business information...',
    benefits: ['Professional appearance', 'Accurate tax filings'],
    action_label: 'Go to Settings',
    action_url: 'http://taxmaster.test/business/settings',
    estimated_time: '5 min',
    priority: 'high',
    icon: 'briefcase',
    is_completed: true,
    requires_step: null,
    progress_indicators: {
        email: true,
        phone: false,
        address: true,
        business_type: true
    }
}
```

---

## 🎨 UI Patterns

### Index Page Layout
```
Header
├─ Title: "Get Started with TaxMaster"
├─ Icon + description
└─ Gradient background

Stats Section (3-column grid)
├─ Overall Progress % + animated bar
├─ Completed: X of 7
└─ Next Step: (step title)

Steps Section (by priority)
├─ High Priority Section
│  ├─ StepCard 1
│  ├─ StepCard 2
│  └─ StepCard 3
├─ Medium Priority Section
│  ├─ StepCard 4
│  └─ StepCard 5
└─ Low Priority Section
   ├─ StepCard 6
   └─ StepCard 7

Help Section
├─ Documentation link
└─ Support email
```

### Widget on Dashboard
```
Blue gradient box
├─ Header: "Get Started with TaxMaster"
├─ Subheader: "45% Complete"
├─ Progress bar (white)
├─ "3 of 7 steps completed"
├─ Next step preview
├─ [View Checklist] button
└─ [Remind Later] button
```

---

## 🔧 Configuration

### Step Metadata (Edit in GetStartedController)
```php
'benefits' => [
    'Benefit 1 text',
    'Benefit 2 text',
    'Benefit 3 text',
],
'estimated_time' => '5 min',
'priority' => 'high', // or 'medium', 'low'
'action_label' => 'Go to Settings',
'action_url' => route('business.settings.index'),
```

### Progress Colors (Edit in useGetStarted.js)
```javascript
const progressColor = computed(() => {
    const p = completionPercentage.value;
    if (p >= 100) return 'bg-green-500';      // Completed
    if (p >= 75) return 'bg-blue-500';        // Almost done
    if (p >= 50) return 'bg-yellow-500';      // Halfway
    return 'bg-orange-500';                   // Just started
});
```

### Snooze Duration (Edit in Widget)
```vue
<button @click="dismissGuide(720)">
  Remind Later
  <!-- 720 minutes = 12 hours -->
</button>
```

---

## 🧪 Testing Points

```
✓ Auto-detection triggers on:
  - Profile fields filled
  - Bank account linked
  - Plan upgraded
  - Staff added
  - Tax return filed
  - Transaction synced
  - Usage thresholds met

✓ Manual steps work:
  - Click checkbox → marked complete
  - Click again → marked incomplete
  - Page reflects changes instantly

✓ Widget behavior:
  - Shows on dashboard
  - Dismissible
  - Can snooze
  - Can undo dismiss
  - Hides when snoozed

✓ Progress tracking:
  - Percentage updates
  - Completion message appears at 100%
  - Timestamp set when completed

✓ UI responsive:
  - Mobile: full-width, stacked stats
  - Tablet: 3-column grid
  - Desktop: max-width container
```

---

## 💡 Enhance & Extend

### Add Step 8: Post-Launch?
1. Update controller: change `count($completed_steps)` logic
2. Add step to `getStepsData()` array
3. Add auto-detection case in `updateCompletedSteps()`
4. Components auto-render new step

### Track Custom Metrics?
1. Add columns to migration
2. Update GetStartedProgress model
3. Populate in controller
4. Access in composable

### Change Widget Color?
Edit `Widget.vue`:
```vue
<div class="bg-gradient-to-r from-blue-500 to-cyan-500">
  <!-- Change colors here -->
</div>
```

### Adjust Step Benefits?
Edit `GetStartedController.php` → `getStepsData()` method:
```php
'benefits' => [
    'Your custom benefit 1',
    'Your custom benefit 2',
]
```

---

## 📍 Navigation Paths

**Access Get Started:**
1. Via Menu: Sidebar → "Get Started" (top)
2. Via Dashboard: "View Checklist" button on widget
3. Direct URL: `/business/get-started`

**Related Pages:**
- Dashboard: `/business/dashboard`
- Settings: `/business/settings`
- Subscriptions: `/business/subscriptions`
- Team: `/business/team`
- Tax Returns: `/business/tax-returns`

---

## 🐛 Debug Tips

### Check Progress State
```javascript
// In browser console on Get Started page:
import { useGetStarted } from '@/composables/useGetStarted';
const g = useGetStarted();
console.log(g.completionPercentage);  // Should be 0-100
console.log(g.stepsByPriority);       // Check step grouping
```

### Verify Auto-Detection
1. Edit business profile → Refresh page
2. Step 1 should auto-check
3. Same for steps 2-7 when conditions met

### Check Database
```bash
# SSH into server
php artisan tinker

# Check progress record
$business = Business::first();
$business->getStartedProgress;
$business->getStartedProgress->completed_steps;
```

### Clear Cache
```bash
php artisan cache:clear
npm run build
```

---

## 📚 Source Files Location

```
Backend:
  app/Models/GetStartedProgress.php
  app/Http/Controllers/Business/GetStartedController.php
  app/Models/Business.php (add relationship)
  database/migrations/2026_02_27_000008_*
  routes/business.php (add routes)

Frontend:
  resources/js/composables/useGetStarted.js
  resources/js/Pages/Business/GetStarted/Index.vue
  resources/js/Components/GetStarted/StepCard.vue
  resources/js/Components/GetStarted/Widget.vue
  resources/js/Layouts/BusinessLayout.vue (add menu link)
  resources/js/Pages/Business/Dashboard.vue (add widget)
```

---

## ✅ Pre-Launch Checklist

- [ ] Database migration executed: `php artisan migrate`
- [ ] Frontend rebuilt: `npm run build`
- [ ] Menu link visible in sidebar
- [ ] Widget appears on dashboard
- [ ] Can access /business/get-started
- [ ] Steps auto-detect on profile changes
- [ ] Manual checkbox toggle works
- [ ] Dismiss/snooze functionality works
- [ ] Progress percentage calculates correctly
- [ ] 100% completion shows success message
- [ ] Responsive design works on mobile

---

**Status:** ✅ READY FOR TESTING
**Date:** 2025-02-27
**Next Phase:** CGT Implementation (Phase 3B.4)
