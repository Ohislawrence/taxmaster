# Phase 1 Frontend Quick Reference

## Component Routes & Navigation

### Quick Navigation Guide

```
Business Dashboard (authenticated users)
├── /business/banks                    → BankAccounts/Index.vue
│   ├── List connected bank accounts
│   ├── Connect new account (Mono)
│   ├── Sync transactions (manual/auto)
│   └── Disconnect account
│
├── /business/transactions             → Transactions/Index.vue
│   ├── List all transactions
│   ├── Filter by category/type
│   ├── Search transactions
│   └── Assign categories
│
├── /business/compliance               → Compliance/Calendar.vue
│   ├── View deadline calendar
│   ├── Mark deadlines complete
│   ├── Upload proof documents
│   └── Track compliance status
│
└── /business/vat                      → Tax/VAT/Index.vue
    ├── View VAT returns
    ├── Calculate VAT manually
    ├── Submit returns
    ├── Download Form 002
    └── Track payment status
```

---

## API Endpoint Reference

### BankAccount APIs

```javascript
// GET - List all bank accounts
GET /business/banks
Response: {
  accounts: [
    {
      id, bank_name, account_name, account_number,
      balance, currency, is_active, auto_sync,
      last_synced_at, transactions_count
    }
  ],
  monoPublicKey: "string"
}

// POST - Handle Mono callback
POST /business/banks/callback
Body: { code: "string" }
Response: 201 Created or 422 Validation Error

// POST - Sync bank account
POST /business/banks/{id}/sync
Response: 204 No Content or error

// POST - Toggle auto-sync
POST /business/banks/{id}/toggle-auto-sync
Response: 204 No Content

// DELETE - Disconnect account
DELETE /business/banks/{id}
Response: 204 No Content
```

### Transaction APIs

```javascript
// GET - List transactions (filtered)
GET /business/transactions?search=&category=&type=
Response: {
  transactions: [
    {
      id, type, amount, currency, description, reference,
      transaction_date, category, ai_confidence,
      vat_applicable, is_business_expense, user_verified,
      bank_account: { id, bank_name, account_number }
    }
  ],
  categories: ["Sales/Revenue", "Operating Expenses", ...],
  stats: { total_transactions, uncategorized, revenue, ... }
}

// PUT - Update transaction category
PUT /business/transactions/{id}/category
Body: { category: "string" }
Response: 201 Created
```

### Compliance APIs

```javascript
// GET - Get compliance deadlines
GET /business/compliance
Response: {
  deadlines: [
    {
      id, deadline_type, period, period_start, period_end,
      description, due_date, is_completed, urgency,
      attachments: [{ id, file_name, file_path }]
    }
  ]
}

// POST - Mark deadline complete
POST /business/compliance/{id}/complete
Response: 204 No Content

// POST - Upload attachment
POST /business/compliance/{id}/upload
Body: FormData { file: File }
Response: 201 Created (returns updated deadline)
```

### VAT APIs

```javascript
// GET - VAT dashboard
GET /business/vat
Response: {
  vatReturns: [
    {
      id, period, period_start, period_end,
      vat_collected, vat_paid, net_vat,
      filing_status, payment_status, form_002_reference,
      due_date_human
    }
  ]
}

// POST - Create VAT return
POST /business/vat/create
Body: {
  period: "YYYY-MM",
  vat_collected: number,
  vat_paid: number,
  net_vat: number,
  notes: string (optional)
}
Response: { success: true, return_id: number }

// POST - Submit VAT return
POST /business/vat/{id}/submit
Response: 204 No Content

// GET - Download Form 002
GET /business/vat/{id}/form-002
Response: PDF file
```

---

## Vue Component Props & Emissions

### BankAccounts/Index.vue

**Props:**
```javascript
props: {
  accounts: Array,
  monoPublicKey: String
}
```

**Emits:**
- None (uses direct fetch calls)

**Key Methods:**
- `syncAccount(account)` - Manual sync
- `toggleAutoSync(account)` - Toggle auto-sync
- `disconnectAccount(account)` - Unlink account
- `initiateMonoAuth()` - Open Mono SDK

---

### Transactions/Index.vue

**Props:**
```javascript
props: {
  transactions: Array,
  categories: Array
}
```

**Key Methods:**
- `applyFilters()` - Filter transactions
- `saveCategory()` - Save category to transaction
- `formatCurrency(value)` - Format to ₦ NGN

---

### Compliance/Calendar.vue

**Props:**
```javascript
props: {
  deadlines: Array
}
```

**Key Methods:**
- `previousMonth()` - Navigate calendar
- `nextMonth()` - Navigate calendar
- `markComplete(deadline)` - Mark deadline complete
- `uploadFile()` - Upload proof document
- `getDaysUntil(dueDate)` - Calculate days remaining

---

### Tax/VAT/Index.vue

**Props:**
```javascript
props: {
  vatReturns: Array
}
```

**Key Methods:**
- `calculateVAT()` - Calculate VAT based on inputs
- `saveCalculation()` - Create VAT return
- `submitVAT(vat)` - Submit return to FIRS

---

## Integration Checklist

### Prerequisites:
- [ ] Mono sandbox account created
- [ ] Mono API keys configured in `.env`
- [ ] Mono public key in `config/services.php`
- [ ] Database migrations run
- [ ] Queue worker running (for async jobs)
- [ ] Email service configured (SendGrid/AWS SES)

### Frontend Setup:
- [x] All Vue components created
- [x] Routes configured
- [x] Controllers updated
- [x] API endpoints mapped
- [ ] Navigation menu updated with links
- [ ] CSS/Tailwind classes verified

### Testing:
- [ ] Bank connection flow tested
- [ ] Transaction list loads correctly
- [ ] Categorization works end-to-end
- [ ] Compliance calendar displays
- [ ] VAT calculation works
- [ ] File uploads complete successfully

### Deployment:
- [ ] Build assets: `npm run build`
- [ ] Test in production environment
- [ ] Verify Mono production credentials
- [ ] Set up monitoring/logging
- [ ] Create user documentation

---

## Common Issues & Solutions

### Issue: "Cannot find module BusinessLayout"

**Solution:**
```bash
# Ensure BusinessLayout.vue exists and is properly imported
# File path: resources/js/Layouts/BusinessLayout.vue
```

### Issue: CSRF token missing

**Solution:**
```html
<!-- Ensure meta tag exists in app.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue: Routes not found (404)

**Solution:**
1. Check route prefix in `routes/business.php`
2. Ensure middleware includes 'auth:sanctum', 'verified', 'business'
3. Run `php artisan route:cache`

### Issue: File upload fails

**Solution:**
1. Check storage directory permissions: `chmod -R 755 storage/`
2. Verify max file size in `php.ini`: `upload_max_filesize = 10M`
3. Check form multipart encoding: `enctype="multipart/form-data"`

### Issue: Mono SDK not loading

**Solution:**
```javascript
// Ensure script is loaded before MonoConnect instantiation
const script = document.createElement('script')
script.src = 'https://cdn.getmono.co/mono.js'
script.onload = () => {
  // MonoConnect now available
}
```

---

## Performance Optimization Tips

### Lazy Load Routes:
```javascript
const BankAccounts = lazy(() => 
  import('./Pages/Business/BankAccounts/Index.vue')
)
```

### Optimize Images:
- Use WebP format for screenshots
- Compress bank logos
- Lazy load SVG icons

### Cache API Responses:
```javascript
// Implement React Query or SWR for caching
const { data } = useQuery(['banks'], fetchBanks, {
  staleTime: 5 * 60 * 1000 // 5 minutes
})
```

### Code Splitting:
- Separate components are already split via Inertia
- Use dynamic imports for modals

---

## Styling Guide

### Color Scheme

**Status Indicators:**
- ✅ Success: `bg-green-100 text-green-800`
- ⚠️ Warning: `bg-yellow-100 text-yellow-800`
- ⛔ Error: `bg-red-100 text-red-800`
- ℹ️ Info: `bg-blue-100 text-blue-800`

**Opacity for Completed Items:**
- Apply `opacity-60` to completed deadlines
- Strikethrough text for paid expenses

### Typography
- Headers: `font-bold text-gray-900`
- Body: `text-gray-600`
- Labels: `text-sm text-gray-700 uppercase`
- Amounts: `font-mono text-lg`

---

## Debugging Tips

### Enable Query Logging:
```php
// In .env
DB_LOG=true
```

### Check Route Definition:
```bash
php artisan route:list --path=business
```

### View Inertia Props:
```javascript
// In Vue component
console.log(props)
```

### Inspect Network Requests:
- Use Browser DevTools > Network tab
- Check request headers for CSRF token
- Verify response status codes

---

## Documentation Links

- [Inertia.js Docs](https://inertiajs.com)
- [Laravel Breeze](https://laravel.com/docs/breeze)
- [Mono API Docs](https://docs.getmono.co)
- [Tailwind CSS](https://tailwindcss.com)
- [Vue 3 Composition API](https://vuejs.org)

---

**Last Updated:** February 25, 2026
**Version:** 1.0
**Status:** Ready for Testing
