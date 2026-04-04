# Mono API Configuration Guide

## Overview

TaxMaster supports bank account integration via Mono.co API. When not configured, the system automatically falls back to CSV/Excel import only.

---

## Environment Configuration

Add these variables to your `.env` file:

```bash
# Mono Bank Integration
MONO_ENABLED=false                    # Set to true when you have Mono API credentials
MONO_SECRET_KEY=                      # Your Mono secret key
MONO_PUBLIC_KEY=                      # Your Mono public key (for widget)
MONO_WEBHOOK_SECRET=                  # Webhook secret for verification
MONO_REDIRECT_URL=                    # Optional: Custom redirect URL
MONO_BASE_URL=https://api.withmono.com  # Default API endpoint
```

---

## Usage Scenarios

### Scenario 1: Mono NOT Available (Default)

**Configuration:**
```bash
MONO_ENABLED=false
# Leave other MONO_* variables empty or commented out
```

**Result:**
- ✅ "Connect Bank" button is **hidden**
- ✅ CSV/Excel import **available and promoted**
- ✅ Users see message: "Bank connection via Mono is not configured. Use CSV/Excel import instead."
- ✅ Empty state shows "Import Transactions" button instead

**Best for:**
- Development/testing environments
- Users without Mono API access
- Businesses preferring manual control

---

### Scenario 2: Mono IS Available

**Configuration:**
```bash
MONO_ENABLED=true
MONO_SECRET_KEY=sk_live_xxxxxxxxxxxxxxxxx
MONO_PUBLIC_KEY=live_pk_xxxxxxxxxxxxxxxxx
MONO_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

**Result:**
- ✅ "Connect Bank" button is **visible**
- ✅ Mono widget opens when clicked
- ✅ Automatic transaction syncing enabled
- ✅ CSV/Excel import still available as fallback

**Best for:**
- Production environments
- Users with Mono partnership
- Businesses wanting automated syncing

---

## How It Works

### Backend (Controller)

The `BankAccountController` passes `monoEnabled` flag to the frontend:

```php
return Inertia::render('Business/BankAccounts/Index', [
    'monoEnabled' => config('services.mono.enabled', false),
    // ... other props
]);
```

### Frontend (Vue Component)

The component conditionally renders UI based on `monoEnabled`:

```vue
<button
    v-if="monoEnabled"
    @click="showConnectModal = true"
>
    Connect Bank
</button>
```

### Config (services.php)

```php
'mono' => [
    'enabled' => env('MONO_ENABLED', false),
    'secret_key' => env('MONO_SECRET_KEY'),
    'public_key' => env('MONO_PUBLIC_KEY'),
    // ...
],
```

---

## User Experience

### When MONO_ENABLED=false

**Bank Accounts Page:**
- Header shows: "Import transactions via CSV/Excel files"
- Warning badge: "⚠️ Bank connection via Mono is not configured"
- Only "Import" button visible
- Empty state promotes CSV/Excel import

**Import Page:**
- Fully functional CSV/Excel upload
- AI-powered column mapping
- Transaction categorization works normally

---

### When MONO_ENABLED=true

**Bank Accounts Page:**
- Header shows: "Connect your bank accounts to auto-import transactions"
- Shows account count: "2 of 5 accounts linked"
- "Connect Bank" + "Import" buttons both visible
- Empty state shows "Connect Your Bank" button
- Mono widget opens on click

**Connected Accounts:**
- Shows bank name, balance, last sync
- Sync Now, Auto-sync toggle, Disconnect actions
- Transaction count per account

---

## Getting Mono API Access

### Step 1: Sign Up
Visit [mono.co](https://mono.co) and create an account

### Step 2: Create Application
1. Go to Dashboard → Applications
2. Click "Create Application"
3. Fill in application details
4. Note your API keys

### Step 3: Configure Webhooks
1. Set webhook URL: `https://your-domain.com/webhooks/mono`
2. Copy webhook secret
3. Save configuration

### Step 4: Update .env
```bash
MONO_ENABLED=true
MONO_SECRET_KEY=sk_live_your_secret_key
MONO_PUBLIC_KEY=live_pk_your_public_key
MONO_WEBHOOK_SECRET=whsec_your_webhook_secret
```

### Step 5: Test
```bash
php artisan config:clear
php artisan cache:clear
```

Visit `/business/banks` and verify "Connect Bank" button appears.

---

## Testing

### Test with Mono Disabled
```bash
# .env
MONO_ENABLED=false
```

1. Visit `/business/banks`
2. Verify "Connect Bank" button is hidden
3. Verify warning message appears
4. Click "Import" → Should work normally

### Test with Mono Enabled
```bash
# .env
MONO_ENABLED=true
MONO_PUBLIC_KEY=test_pk_xxxxxxxxxx
```

1. Visit `/business/banks`
2. Verify "Connect Bank" button appears
3. Click button → Mono widget should open
4. Complete connection flow

---

## Troubleshooting

### "Connect Bank" button not showing

**Check:**
1. `.env` has `MONO_ENABLED=true`
2. Config cache cleared: `php artisan config:clear`
3. Browser cache cleared (Ctrl+Shift+R)

### Mono widget not opening

**Check:**
1. `MONO_PUBLIC_KEY` is set and valid
2. Check browser console for JavaScript errors
3. Ensure Mono Connect.js is loaded: Check Network tab

### Transactions not syncing

**Check:**
1. `MONO_SECRET_KEY` is correct
2. Webhook URL is accessible from Mono servers
3. `MONO_WEBHOOK_SECRET` matches Mono dashboard
4. Check Laravel logs: `storage/logs/laravel.log`

---

## Development vs Production

### Development (Local)
```bash
MONO_ENABLED=false
# Use CSV import for testing
```

### Staging
```bash
MONO_ENABLED=true
MONO_SECRET_KEY=sk_test_xxxxxxxxx
MONO_PUBLIC_KEY=test_pk_xxxxxxxxx
# Use Mono test mode
```

### Production
```bash
MONO_ENABLED=true
MONO_SECRET_KEY=sk_live_xxxxxxxxx
MONO_PUBLIC_KEY=live_pk_xxxxxxxxx
MONO_WEBHOOK_SECRET=whsec_xxxxxxxxx
# Use Mono live keys
```

---

## Security Notes

1. **Never commit API keys** to version control
2. Use `.env` file (already in `.gitignore`)
3. Rotate keys periodically
4. Monitor webhook activity for suspicious requests
5. Validate webhook signatures in `MonoWebhookController`

---

## Related Files

- Config: `config/services.php`
- Controller: `app/Http/Controllers/Business/BankAccountController.php`
- View: `resources/js/Pages/Business/BankAccounts/Index.vue`
- Service: `app/Services/MonoIntegrationService.php`
- Webhook: `app/Http/Controllers/Webhooks/MonoWebhookController.php`

---

## Summary

✅ **MONO_ENABLED=false** → CSV/Excel import only (safe default)  
✅ **MONO_ENABLED=true** → Bank connection available (requires API keys)  
✅ Graceful fallback when API not configured  
✅ Users always have CSV/Excel import option  
✅ No errors or broken UI when Mono is disabled  

**Default state:** Mono disabled, CSV/Excel import promoted. Turn on when ready!
