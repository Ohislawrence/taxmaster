# QuickBooks Integration - Quick Reference

**Status**: ✅ Production Ready  
**Version**: 1.0

---

## 🚀 Quick Setup (5 Minutes)

### 1. Get Credentials
```
1. Visit: https://developer.intuit.com/
2. Create app → QuickBooks Online
3. Copy Client ID & Client Secret
4. Set Redirect URI: http://taxmaster.test/business/integrations/quickbooks/callback
```

### 2. Configure .env
```env
QUICKBOOKS_ENABLED=true
QUICKBOOKS_CLIENT_ID=your_client_id
QUICKBOOKS_CLIENT_SECRET=your_client_secret
QUICKBOOKS_REDIRECT_URI=http://taxmaster.test/business/integrations/quickbooks/callback
QUICKBOOKS_ENVIRONMENT=sandbox  # or 'production'
```

### 3. Connect & Sync
```
1. Login → Settings → Integrations → QuickBooks
2. Click "Connect QuickBooks"
3. Authorize your company
4. Click "Sync Now" → Select options → Start
```

---

## 📁 File Locations

### Backend
```
app/
├── Http/Controllers/Business/QuickBooksController.php    # Web routes
├── Models/QuickBooksConnection.php                        # Connection model
├── Models/QuickBooksSyncLog.php                           # Sync history
├── Services/QuickBooksIntegrationService.php              # Core logic
└── Jobs/SyncQuickBooksData.php                            # Background job

database/migrations/
├── 2026_04_05_000001_create_quickbooks_connections_table.php
├── 2026_04_05_000002_create_quickbooks_sync_logs_table.php
└── 2026_04_05_000003_add_quickbooks_fields_to_transactions.php
```

### Frontend
```
resources/js/Pages/Business/Integrations/QuickBooks.vue    # Main UI
```

### Configuration
```
config/services.php                                         # QB config
routes/business.php                                         # QB routes
.env                                                        # Credentials
```

---

## 🔌 API Usage

### Service Methods
```php
use App\Services\QuickBooksIntegrationService;

$qb = app(QuickBooksIntegrationService::class);

// Get OAuth URL
$authUrl = $qb->getAuthorizationUrl();

// Exchange code for tokens
$connection = $qb->exchangeCodeForTokens($code, $realmId, $business);

// Refresh token
$qb->refreshAccessToken($connection);

// Sync invoices
$log = $qb->syncInvoicesFromQuickBooks($connection, $fromDate, $toDate);

// Sync bills
$log = $qb->syncBillsFromQuickBooks($connection, $fromDate, $toDate);

// Disconnect
$qb->disconnect($connection);

// Get company info
$info = $qb->getCompanyInfo($connection);
```

### Background Jobs
```php
use App\Jobs\SyncQuickBooksData;
use Carbon\Carbon;

// Dispatch sync
SyncQuickBooksData::dispatch(
    $connection,
    Carbon::now()->subDays(30),  // from
    Carbon::now(),               // to
    'all'                        // type: all/invoices/bills
);

// Start queue worker
php artisan queue:work --tries=3 --timeout=300
```

### Model Relationships
```php
// Business → QuickBooksConnection
$business = Business::find(1);
$connection = $business->quickBooksConnection;

// Connection → Sync Logs
$logs = $connection->syncLogs()->latest()->get();

// Connection status checks
$connection->isActive();         // true/false
$connection->isTokenExpired();   // true/false
$connection->isSyncDue();        // true/false
```

---

## 🛣️ Routes

```php
GET    /business/integrations/quickbooks              → index (dashboard)
GET    /business/integrations/quickbooks/connect      → connect (OAuth start)
GET    /business/integrations/quickbooks/callback     → callback (OAuth return)
POST   /business/integrations/quickbooks/disconnect   → disconnect
POST   /business/integrations/quickbooks/sync         → sync (manual trigger)
PATCH  /business/integrations/quickbooks/settings     → updateSettings
GET    /business/integrations/quickbooks/logs/{log}   → getSyncLog
```

---

## 💾 Database Schema

### quickbooks_connections
```sql
id, business_id, realm_id, company_name,
access_token (encrypted), refresh_token (encrypted),
token_expires_at, refresh_token_expires_at,
status (active/expired/error),
last_synced_at, last_sync_status,
auto_sync_enabled, sync_frequency (hourly/daily/weekly),
sync_settings (JSON), created_at, updated_at
```

### quickbooks_sync_logs
```sql
id, quickbooks_connection_id, sync_type, entity_type,
status (pending/in_progress/completed/failed),
total_records, processed_records,
success_count, failure_count, skipped_count,
started_at, completed_at, duration_seconds,
error_message, errors (JSON), summary (JSON),
created_at, updated_at
```

### transactions (enhanced)
```sql
-- New columns added:
quickbooks_id VARCHAR(255) NULLABLE UNIQUE
quickbooks_synced_at TIMESTAMP NULLABLE
quickbooks_sync_enabled BOOLEAN DEFAULT TRUE
```

---

## 🔧 Common Tasks

### Check Connection Status
```php
$connection = Business::find(1)->quickBooksConnection;

if (!$connection) {
    echo "Not connected";
} elseif ($connection->isActive()) {
    echo "Active - Ready to sync";
} elseif ($connection->status === 'expired') {
    echo "Expired - Needs reconnection";
}
```

### Manual Sync
```php
use App\Services\QuickBooksIntegrationService;
use Carbon\Carbon;

$qb = app(QuickBooksIntegrationService::class);
$connection = Business::find(1)->quickBooksConnection;

// Sync last 30 days
$log = $qb->syncInvoicesFromQuickBooks(
    $connection,
    Carbon::now()->subDays(30),
    Carbon::now()
);

echo "Synced: {$log->success_count} successful, {$log->failure_count} failed";
```

### View Sync History
```php
$connection = Business::find(1)->quickBooksConnection;

$logs = $connection->syncLogs()
    ->latest()
    ->take(10)
    ->get();

foreach ($logs as $log) {
    echo "{$log->sync_type} - {$log->status} - {$log->success_count} synced\n";
}
```

### Force Token Refresh
```php
$connection = Business::find(1)->quickBooksConnection;

if ($connection->isTokenExpired()) {
    $qb = app(QuickBooksIntegrationService::class);
    $qb->refreshAccessToken($connection);
    echo "Token refreshed successfully";
}
```

---

## 🐛 Troubleshooting

### Issue: "Connection Expired"
```php
// Solution: Reconnect
// User clicks "Reconnect QuickBooks" in UI
// OR programmatically:
$qb->refreshAccessToken($connection);
```

### Issue: "Sync Failed"
```php
// View error details
$log = QuickBooksSyncLog::latest()->first();
dd($log->error_message, $log->errors);

// Check QB API status
// https://status.developer.intuit.com/

// Verify token validity
if ($connection->isTokenExpired()) {
    $qb->refreshAccessToken($connection);
}
```

### Issue: "Rate Limit Exceeded"
```php
// QB allows 500 requests/minute
// Solution: Batch syncs, reduce frequency

// Recommended:
// - Hourly sync: For high-volume businesses
// - Daily sync: For most businesses
// - Weekly sync: For small businesses
```

### Issue: "Duplicate Transactions"
```php
// Prevention: Uses `quickbooks_id` unique constraint
// If occurred:
Transaction::where('quickbooks_id', $qbId)
    ->where('business_id', $businessId)
    ->orderBy('id')
    ->skip(1)
    ->delete();
```

---

## ⚙️ Configuration Options

### Sync Settings
```php
$connection->update([
    'auto_sync_enabled' => true,
    'sync_frequency' => 'daily',  // hourly/daily/weekly
    'sync_settings' => [
        'sync_invoices' => true,
        'sync_bills' => true,
    ],
]);
```

### Environment-Specific
```env
# Development
QUICKBOOKS_ENVIRONMENT=sandbox

# Production
QUICKBOOKS_ENVIRONMENT=production
QUICKBOOKS_REDIRECT_URI=https://taxmaster.ng/business/integrations/quickbooks/callback
```

---

## 📊 Data Mapping

| QuickBooks Field | TaxMaster Field | Notes |
|-----------------|-----------------|-------|
| Invoice | Transaction (Credit) | Auto-categorized as VAT_OUTPUT |
| Bill | Transaction (Debit) | AI categorizes expense type |
| Customer Name | Counterparty | |
| Doc Number | Reference | |
| Amount | Amount | |
| TxnDate | Transaction Date | |
| Id | quickbooks_id | Prevents duplicates |

---

## 🧪 Testing

### Test OAuth Flow
```bash
# 1. Start app
php artisan serve

# 2. Visit
http://taxmaster.test/business/integrations/quickbooks

# 3. Click "Connect QuickBooks"
# 4. Use QB sandbox credentials
# 5. Verify callback returns success
```

### Test Sync
```php
php artisan tinker

>>> $conn = QuickBooksConnection::first();
>>> $qb = app(\App\Services\QuickBooksIntegrationService::class);
>>> $log = $qb->syncInvoicesFromQuickBooks($conn, now()->subDays(30), now());
>>> $log->success_count;  // Should show synced count
```

### Test Background Job
```bash
# Terminal 1: Start queue
php artisan queue:work

# Terminal 2: Dispatch job
php artisan tinker
>>> SyncQuickBooksData::dispatch($conn, now()->subDays(7), now(), 'all');

# Check Terminal 1 for job output
```

---

## 📈 Performance

### Batch Sizes
- Invoices: 1000 per query
- Bills: 1000 per query
- API calls: ~2 per 1000 records

### Estimated Sync Times
- 100 transactions: ~5 seconds
- 1,000 transactions: ~30 seconds
- 10,000 transactions: ~5 minutes

### Optimization Tips
1. Use incremental sync (last 30 days)
2. Enable background jobs (queue worker)
3. Sync during off-peak hours
4. Monitor rate limits

---

## 🔒 Security

### Token Encryption
```php
// Tokens automatically encrypted in database
use Illuminate\Database\Eloquent\Casts\Encrypted;

protected $casts = [
    'access_token' => Encrypted::class,
    'refresh_token' => Encrypted::class,
];
```

### Access Control
```php
// Middleware applied to all QB routes
Route::middleware(['auth', 'subscription.features:link_bank_account'])
    ->prefix('integrations/quickbooks')
    ->group(/* routes */);
```

---

## 📝 Useful Commands

```bash
# Run migrations
php artisan migrate

# Test connection
php artisan tinker
>>> $conn = QuickBooksConnection::first();
>>> $conn->isActive();

# View sync logs
>>> QuickBooksSyncLog::latest()->first();

# Clear failed jobs
php artisan queue:flush

# Start queue worker
php artisan queue:work --tries=3

# Restart queue after code changes
php artisan queue:restart
```

---

## 🎯 Next Steps

**For Production:**
1. ✅ Switch to production environment
2. ✅ Update .env with production credentials
3. ✅ Setup queue worker (Supervisor/Horizon)
4. ✅ Monitor rate limits
5. ✅ Enable auto-sync for users

**For Development:**
1. ✅ Create QB sandbox account
2. ✅ Test OAuth flow
3. ✅ Test sync with sample data
4. ✅ Test error handling
5. ✅ Test token refresh

---

## 📞 Resources

- **QuickBooks Developer**: https://developer.intuit.com/
- **API Explorer**: https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/invoice
- **OAuth Guide**: https://developer.intuit.com/app/developer/qbo/docs/develop/authentication-and-authorization/oauth-2.0
- **SDK GitHub**: https://github.com/intuit/QuickBooks-V3-PHP-SDK
- **Rate Limits**: https://developer.intuit.com/app/developer/qbo/docs/best-practices/rate-limits
- **Full Documentation**: See `QUICKBOOKS_INTEGRATION.md`

---

**Last Updated**: April 5, 2026  
**Status**: ✅ Production Ready
