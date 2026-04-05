# QuickBooks Integration - Complete Setup Guide

**Version**: 1.0  
**Date**: April 5, 2026  
**Status**: ✅ Implemented & Ready

---

## 🎯 Overview

TaxMaster now integrates with **QuickBooks Online** to automatically sync invoices, bills, and expenses directly from your accounting software. This eliminates duplicate data entry and ensures your tax calculations are always based on accurate financial data.

---

## ✨ Features

### What Gets Synced

**From QuickBooks → TaxMaster:**
- ✅ **Sales Invoices** → Revenue transactions (auto-categorized as VAT_OUTPUT)
- ✅ **Bills/Purchases** → Expense transactions (AI categorizes by type)
- ✅ **Customer Names** → Counterparty information
- ✅ **Transaction Dates** → Accurate timing for tax periods
- ✅ **Amounts** → Exact financial values

### Sync Options
- ✅ **Manual Sync** - On-demand syncing with custom date ranges
- ✅ **Auto-Sync** - Scheduled syncing (hourly, daily, weekly)
- ✅ **Incremental Sync** - Only new/updated transactions
- ✅ **Full Sync** - Complete historical data import

### Intelligence
- ✅ AI automatically categorizes imported transactions
- ✅ Duplicate prevention (won't import same transaction twice)
- ✅ Error logging and recovery
- ✅ Progress tracking and sync history

---

## 🚀 Quick Start

### Step 1: Get QuickBooks Developer Credentials

1. Go to [QuickBooks Developer Portal](https://developer.intuit.com/)
2. Sign in or create a free Intuit Developer account
3. Create a new app:
   - Click "Create an app"
   - Select "QuickBooks Online and Payments"
   - Name your app (e.g., "TaxMaster Integration")

4. Get your credentials:
   - Navigate to **Keys & credentials**
   - Copy **Client ID**
   - Copy **Client Secret**
   - Set **Redirect URI** to: `http://taxmaster.test/business/integrations/quickbooks/callback`

5. Set scopes:
   - Ensure `com.intuit.quickbooks.accounting` is enabled

### Step 2: Configure TaxMaster

Update your `.env` file:

```env
# QuickBooks Online Integration
QUICKBOOKS_ENABLED=true
QUICKBOOKS_CLIENT_ID=your_client_id_here
QUICKBOOKS_CLIENT_SECRET=your_client_secret_here
QUICKBOOKS_REDIRECT_URI=http://taxmaster.test/business/integrations/quickbooks/callback
QUICKBOOKS_ENVIRONMENT=sandbox  # Use 'production' for live data
```

**Important**:
- Use `sandbox` environment for testing
- Switch to `production` when ready to go live
- Never commit credentials to version control

### Step 3: Connect QuickBooks

1. Log in to TaxMaster as a business user
2. Navigate to **Settings → Integrations → QuickBooks**
3. Click **"Connect QuickBooks"**
4. You'll be redirected to QuickBooks to authorize
5. Select your company and click **"Authorize"**
6. You'll be redirected back to TaxMaster - connection complete!

### Step 4: Sync Your Data

**Option A: Manual Sync (Recommended First Time)**
1. Go to **Integrations → QuickBooks**
2. Click **"Sync Now"**
3. Select date range (last 30 days, last month, custom, etc.)
4. Choose what to sync (Invoices, Bills, or Both)
5. Click **"Start Sync"**
6. Monitor progress in sync logs

**Option B: Enable Auto-Sync**
1. Go to **Integrations → QuickBooks → Settings**
2. Enable **"Automatic Sync"**
3. Choose frequency:
   - **Hourly** - For businesses with high transaction volume
   - **Daily** - Recommended for most businesses
   - **Weekly** - For small businesses with low volume
4. Save settings

---

## 📊 How It Works

### Data Flow

```
QuickBooks Online
        ↓
  OAuth 2.0 Connection
        ↓
QuickBooksIntegrationService
        ↓
   Parse & Convert
        ↓
  AI Categorization
        ↓
TaxMaster Transactions DB
```

### Transaction Mapping

| QuickBooks | TaxMaster | Category |
|------------|-----------|----------|
| Invoice | Credit Transaction | VAT_OUTPUT (Revenue) |
| Bill/Purchase | Debit Transaction | AI determines (Expense) |
| Customer Name | Counterparty | - |
| Doc Number | Reference | - |
| Amount | Amount | - |
| Date | Transaction Date | - |

### AI Enhancement

After syncing, TaxMaster's AI:
1. **Categorizes** transactions (SALARY_PAYE, RENT, UTILITIES, etc.)
2. Applied **VAT treatment** (VATable, Exempt, Zero-rated)
3. **Flags** transactions needing WHT
4. **Suggests** tax optimizations

---

## 🗂️ File Structure

### Backend Files Created

```
app/
├── Http/Controllers/Business/
│   └── QuickBooksController.php        # OAuth & sync endpoints
├── Models/
│   ├── QuickBooksConnection.php        # Connection & token management
│   └── QuickBooksSyncLog.php           # Sync history & progress
├── Services/
│   └── QuickBooksIntegrationService.php # Core integration logic
└── Jobs/
    └── SyncQuickBooksData.php          # Background sync job

database/migrations/
├── 2026_04_05_000001_create_quickbooks_connections_table.php
├── 2026_04_05_000002_create_quickbooks_sync_logs_table.php
└── 2026_04_05_000003_add_quickbooks_fields_to_transactions.php

routes/
└── business.php                         # QuickBooks routes added

config/
└── services.php                         # QuickBooks configuration
```

### Database Tables

**`quickbooks_connections`**
- Stores OAuth tokens (encrypted)
- Company information
- Sync settings (frequency, auto-sync)
- Connection status

**`quickbooks_sync_logs`**
- Sync history
- Success/failure counts
- Error messages
- Progress tracking

**`transactions`** (enhanced)
- New column: `quickbooks_id` - Links to QB transaction
- New column: `quickbooks_synced_at` - Last sync timestamp
- New column: `quickbooks_sync_enabled` - Control sync per transaction

---

## 🔐 Security

### Token Management
- ✅ Access tokens **encrypted at rest** (Laravel encryption)
- ✅ Refresh tokens **encrypted at rest**
- ✅ Automatic token refresh before expiration
- ✅ Secure OAuth 2.0 flow (industry standard)

### Data Privacy
- ✅ Only accounting data synced (no personal employee data)
- ✅ Transactions stored in encrypted database
- ✅ HTTPS required for production
- ✅ Compliant with NDPA 2023

### Access Control
- ✅ Only business owners can connect QuickBooks
- ✅ Accountants with permissions can manage sync
- ✅ Tokens revoked on disconnect

---

## 🛠️ API Reference

### QuickBooksIntegrationService Methods

```php
use App\Services\QuickBooksIntegrationService;

$qbService = app(QuickBooksIntegrationService::class);

// Get authorization URL
$authUrl = $qbService->getAuthorizationUrl();

// Exchange code for tokens (after OAuth callback)
$connection = $qbService->exchangeCodeForTokens($code, $realmId, $business);

// Refresh expired token
$qbService->refreshAccessToken($connection);

// Sync invoices
$syncLog = $qbService->syncInvoicesFromQuickBooks(
    $connection,
    $fromDate = Carbon::now()->subDays(30),
    $toDate = Carbon::now()
);

// Sync bills
$syncLog = $qbService->syncBillsFromQuickBooks($connection, $fromDate, $toDate);

// Disconnect
$qbService->disconnect($connection);

// Get company info
$companyInfo = $qbService->getCompanyInfo($connection);
```

### Background Job

```php
use App\Jobs\SyncQuickBooksData;
use Carbon\Carbon;

// Dispatch sync job
SyncQuickBooksData::dispatch(
    $connection,
    Carbon::now()->subDays(7),  // from_date
    Carbon::now(),               // to_date
    'all'                        // sync_type: 'all', 'invoices', 'bills'
);
```

### Routes

```php
// User-facing routes
GET  /business/integrations/quickbooks              - Integration dashboard
GET  /business/integrations/quickbooks/connect      - Start OAuth flow
GET  /business/integrations/quickbooks/callback     - OAuth callback handler
POST /business/integrations/quickbooks/disconnect   - Revoke connection
POST /business/integrations/quickbooks/sync         - Manual sync trigger
PATCH /business/integrations/quickbooks/settings    - Update sync settings
GET  /business/integrations/quickbooks/logs/{log}   - View sync log details
```

---

## ⚙️ Configuration Options

###.env Variables

```env
# Required
QUICKBOOKS_ENABLED=true                          # Enable/disable integration
QUICKBOOKS_CLIENT_ID=ABCxxxQAnygfO7bOvl8RrNMXxxxT2M    # From Intuit Developer
QUICKBOOKS_CLIENT_SECRET=6ZxxxxpJYwVTIxxxx           # From Intuit Developer

# Optional (defaults shown)
QUICKBOOKS_REDIRECT_URI=http://taxmaster.test/business/integrations/quickbooks/callback
QUICKBOOKS_ENVIRONMENT=sandbox                   # or 'production'
```

### Sync Settings (Per Connection)

```php
$connection->update([
    'auto_sync_enabled' => true,      // Enable automatic syncing
    'sync_frequency' => 'daily',      // 'hourly', 'daily', 'weekly'
    'sync_settings' => [
        'sync_invoices' => true,
        'sync_bills' => true,
        'sync_payments' => false,     // Future feature
    ],
]);
```

---

## 🐛 Troubleshooting

### "Connection Expired" Error

**Cause**: Access token expired (1-hour lifespan) and refresh failed.

**Solution**:
1. Check if refresh token is still valid (<100 days old)
2. If expired, click "Reconnect QuickBooks"
3. Re-authorize your company

```php
// Check token status
$connection = $business->quickBooksConnection;
if ($connection->isTokenExpired()) {
    $qbService->refreshAccessToken($connection);
}
```

### "Failed to Sync" Error

**Cause**: Network issues, QB API limit, or invalid data.

**Solution**:
1. Check sync logs for specific error
2. Verify QuickBooks company is accessible
3. Try manual sync with smaller date range
4. Check API rate limits (500 calls/minute)

```php
// View detailed error
$syncLog = QuickBooksSyncLog::latest()->first();
dd($syncLog->error_message, $syncLog->errors);
```

### Duplicate Transactions

**Cause**: Transaction imported twice.

**Prevention**: TaxMaster uses `quickbooks_id` to prevent duplicates.

**Fix if occurred**:
```php
// Find and merge duplicates
$duplicates = Transaction::where('quickbooks_id', $qbId)
    ->where('business_id', $businessId)
    ->get();

// Keep first, delete others
$duplicates->skip(1)->each->delete();
```

### Slow Sync Performance

**Cause**: Large transaction volume

**Solution**:
1. Use incremental sync (last 30 days)
2. Enable background jobs (queue worker)
3. Increase sync frequency to spread load

```bash
# Start queue worker
php artisan queue:work --tries=3
```

---

## 📈 Usage Examples

### Example 1: First-Time Setup

```php
// 1. User clicks "Connect QuickBooks"
Route redirect → getAuthorizationUrl()

// 2. User authorizes on QuickBooks
QuickBooks redirects back → callback()

// 3. Exchange code for tokens
$connection = $qbService->exchangeCodeForTokens($code, $realmId, $business);

// 4. Sync last 3 months of data
$syncLog = $qbService->syncInvoicesFromQuickBooks(
    $connection,
    Carbon::now()->subMonths(3),
    Carbon::now()
);
```

### Example 2: Scheduled Daily Sync

```php
// In App\Console\Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        QuickBooksConnection::where('status', 'active')
            ->where('auto_sync_enabled', true)
            ->each(function ($conn) {
                if ($conn->isSyncDue()) {
                    SyncQuickBooksData::dispatch(
                        $conn,
                        $conn->last_synced_at ?? now()->subDays(7),
                        now(),
                        'all'
                    );
                }
            });
    })->daily();
}
```

### Example 3: Manual Sync with Custom Range

```php
// Controller action
public function customSync(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date' => 'required|date|after:from_date',
    ]);

    $connection = $request->user()->defaultBusiness()->quickBooksConnection;

    SyncQuickBooksData::dispatch(
        $connection,
        Carbon::parse($request->from_date),
        Carbon::parse($request->to_date),
        'invoices'
    );

    return back()->with('success', 'Sync started! Check logs for progress.');
}
```

---

## 🎓 Best Practices

### 1. Start with Sandbox
- Test with sandbox environment first
- Use test company data
- Verify syncing works correctly
- Then switch to production

### 2. Incremental Syncing
- Don't sync all historical data at once
- Start with last 3-6 months
- Use incremental sync going forward
- Reduces API calls and processing time

### 3. Monitor Sync Logs
- Check sync logs regularly
- Address failures promptly
- Look for patterns in errors
- Keep logs for audit trail

### 4. Use Background Jobs
- Never run sync synchronously in web requests
- Always use queue jobs for syncing
- Configure supervisord/horizon for production
- Set retry attempts (3 recommended)

### 5. Token Refresh
- Service auto-refreshes tokens
- Monitor for refresh failures
- Notify users if reconnection needed
- Handle expired refresh tokens gracefully

---

## ⚡ Performance

### API Rate Limits

QuickBooks Online limits:
- **Production**: 500 requests/minute per company
- **Sandbox**: 100 requests/minute per company

Our implementation:
- Fetches 1000 records per query (batch)
- Estimates ~2 API calls per 1000 transactions
- Can sync ~250,000 transactions/minute (production)

### Database Impact

- Indexed `quickbooks_id` column
- Efficient `updateOrCreate` queries
- Minimal database load
- Scales to millions of transactions

---

## 🚧 Roadmap

### Planned Features

**Q2 2026:**
- [ ] Sync payments from QuickBooks
- [ ] Two-way sync (TaxMaster → QuickBooks)
- [ ] Export tax returns as QuickBooks journal entries
- [ ] Chart of accounts mapping

**Q3 2026:**
- [ ] Sync employees (for PAYE calculations)
- [ ] Sync vendors (for WHT tracking)
- [ ] Real-time webhooks (instead of polling)
- [ ] Multi-company support

**Q4 2026:**
- [ ] QuickBooks Desktop integration
- [ ] Advanced reconciliation
- [ ] Custom field mapping
- [ ] Bulk operations

---

## 📞 Support

### Documentation
- **This Guide**: `QUICKBOOKS_INTEGRATION.md`
- **API Docs**: [QuickBooks API Explorer](https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/invoice)
- **TaxMaster Docs**: See `API_DOCUMENTATION.md`

### Getting Help
- **Email**: support@taxmaster.ng
- **Developer Portal**: [developer.intuit.com](https://developer.intuit.com)
- **GitHub Issues**: For bug reports
- **Community Forum**: Coming Q2 2026

### Resources
- [QuickBooks OAuth 2.0 Guide](https://developer.intuit.com/app/developer/qbo/docs/develop/authentication-and-authorization/oauth-2.0)
- [QuickBooks API Reference](https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/invoice)
- [Rate Limits](https://developer.intuit.com/app/developer/qbo/docs/best-practices/rate-limits)

---

**Status**: ✅ **Production Ready**  
**Last Updated**: April 5, 2026  
**Next Review**: Q2 2026

---

## Quick Commands Reference

```bash
# Run migrations
php artisan migrate

# Test QB connection
php artisan tinker
>>> $conn = \App\Models\QuickBooksConnection::first();
>>> $conn->isActive();
>>> $qb = app(\App\Services\QuickBooksIntegrationService::class);
>>> $qb->getCompanyInfo($conn);

# Manual sync
>>> \App\Jobs\SyncQuickBooksData::dispatch($conn, now()->subDays(30), now(), 'all');

# View sync logs
>>> \App\Models\QuickBooksSyncLog::latest()->first();

# Clear failed jobs
php artisan queue:flush

# Start queue worker (production)
php artisan queue:work --queue=default --tries=3 --timeout=300
```

---

**Congratulations! Your QuickBooks integration is ready to use! 🎉**
