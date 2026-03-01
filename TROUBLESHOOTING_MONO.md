# Troubleshooting Guide - MonoIntegrationService Errors

## Error: "Cannot assign null to property App\Services\MonoIntegrationService::$secretKey of type string"

### What This Means

Your Laravel application tried to use the Mono integration service, but the `MONO_SECRET_KEY` environment variable is not configured in your `.env` file.

### Root Cause

The MonoIntegrationService requires three configuration values from the Mono API:
- `MONO_SECRET_KEY` - Your secret API key (from Mono Dashboard)
- `MONO_PUBLIC_KEY` - Your public API key (safe for frontend)
- `MONO_WEBHOOK_SECRET` - For webhook validation (optional for development)

When these are not set in `.env`, PHP 8.1+ typed properties cannot accept `null` values.

---

## How to Fix

### Step 1: Get Mono Credentials

1. Visit [Mono Dashboard](https://app.withmono.com)
2. Log in or create a free account
3. Go to **Settings** → **API Keys**
4. You'll see:
   - **Secret Key:** `sk_live_XXXXXXXXXXXXXXXX` (keep private!)
   - **Public Key:** `pk_live_XXXXXXXXXXXXXXXX`

### Step 2: Update .env File

Edit your `.env` file and add/update these lines:

```dotenv
MONO_SECRET_KEY=sk_live_YOUR_SECRET_KEY_HERE
MONO_PUBLIC_KEY=pk_live_YOUR_PUBLIC_KEY_HERE
MONO_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET_HERE
MONO_BASE_URL=https://api.withmono.com
MONO_REDIRECT_URL=http://localhost:8000/business/banks/callback
```

### Step 3: Clear Configuration Cache

```bash
php artisan config:cache
```

### Step 4: Verify Configuration

```bash
php artisan tinker

# Inside tinker prompt:
>>> $service = app(\App\Services\MonoIntegrationService::class);
>>> $service->verifyCredentials();
# Should return without error if configured correctly
```

---

## Automated Setup

### Option A: Windows (PowerShell)

```powershell
.\setup-mono.ps1
```

This script will:
1. Create `.env` from `.env.example` (if needed)
2. Prompt you for Mono credentials
3. Update `.env` automatically
4. Clear the config cache
5. Test the configuration

### Option B: Linux/Mac (Bash)

```bash
bash setup-mono.sh
```

Same steps as PowerShell version.

### Option C: Manual Setup

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` and find the Mono section:
   ```dotenv
   # Mono Integration (Bank Account Connection)
   MONO_SECRET_KEY=your_mono_secret_key_here
   MONO_PUBLIC_KEY=your_mono_public_key_here
   MONO_WEBHOOK_SECRET=your_mono_webhook_secret_here
   ```

3. Replace with your actual keys from Mono Dashboard

4. Save the file

5. Clear cache:
   ```bash
   php artisan config:cache
   ```

---

## Common Issues & Solutions

### Issue 1: Still Getting "null to property" Error After Updating .env

**Cause:** Config cache wasn't cleared

**Solution:**
```bash
# Clear all caches
php artisan config:cache
php artisan cache:clear
php artisan config:clear

# Restart Laravel
php artisan serve
```

### Issue 2: "verifyCredentials() threw exception: Mono API secret key is not configured"

**Cause:** The .env file has the correct variable names, but values are still placeholder text

**Solution:**
1. Go to Mono Dashboard
2. Copy the actual Secret Key (not the placeholder)
3. Paste into `.env`
4. Verify file was saved
5. Run `php artisan config:cache`

### Issue 3: Mono SDK Modal Doesn't Appear in Browser

**Cause:** Public Key not set or incorrect

**Solution:**
1. Verify `MONO_PUBLIC_KEY` is set in `.env`
2. Check browser console for JavaScript errors
3. Ensure Mono SDK CDN is accessible: https://cdn.getmono.co/mono.js
4. Check if PUBLIC_KEY is being passed to the Vue component

### Issue 4: "Failed to authenticate" When Connecting Bank Account

**Cause:** Secret Key is invalid or incorrect

**Solution:**
1. Double-check Secret Key in Mono Dashboard
2. Copy again carefully (no extra spaces)
3. Update `.env`
4. Clear cache: `php artisan config:cache`
5. Restart Laravel server
6. Try again

### Issue 5: Webhook Errors in Logs

**Cause:** Webhook secret is not configured or incorrect

**Solution:**
1. Mono Webhooks are optional for development
2. To enable, set `MONO_WEBHOOK_SECRET` in `.env`
3. Get the secret from Mono Dashboard → Settings → Webhooks
4. For local testing, use [ngrok](https://ngrok.com) to expose your local server:
   ```bash
   ngrok http 8000
   # Add ngrok URL to Mono webhook: https://xxxxx.ngrok.io/business/banks/webhook
   ```

---

## Testing Checklist

After setup, verify everything works:

- [ ] `.env` file has `MONO_SECRET_KEY` with actual value (not placeholder)
- [ ] `.env` file has `MONO_PUBLIC_KEY` with actual value
- [ ] Ran `php artisan config:cache`
- [ ] Restarted Laravel server (`php artisan serve`)
- [ ] No errors in logs: `tail -f storage/logs/laravel.log`
- [ ] Browser console has no JavaScript errors (F12 → Console tab)
- [ ] Mono SDK script loads: Inspect Network tab, search for "mono.js"
- [ ] Click "Connect Bank" button - modal should appear

---

## Environment Files & Security

### Important Security Notes

⚠️ **NEVER:**
- Commit `.env` file to git
- Share your Secret Key via email, Slack, or chat
- Put credentials in source code
- Expose Secret Key to frontend JavaScript

✅ **ALWAYS:**
- Keep Secret Key in `.env` file (private)
- Add `.env` to `.gitignore`
- Keep `.env` file secure (chmod 600 on Linux)
- Rotate keys if they're accidentally exposed
- Use different keys for dev/staging/production

### Verify .gitignore

Check that `.gitignore` includes:

```gitignore
.env
.env.local
.env.*.local
```

To verify `.env` is not tracked:

```bash
git status

# Should NOT show .env file
# If it does, remove it with:
# git rm --cached .env
```

---

## Debugging Commands

### View Current Configuration

```bash
php artisan config:show services.mono
```

### Check if Service Loads

```bash
php artisan tinker
>>> app(\App\Services\MonoIntegrationService::class)
# Should return service instance without error
```

### Test Full Connection

```bash
php artisan tinker
>>> $service = app(\App\Services\MonoIntegrationService::class);
>>> $service->verifyCredentials();
>>> echo "✅ All good!";
```

### View Recent Errors

```bash
# Last 50 lines of log
tail -n 50 storage/logs/laravel.log

# Or on Windows PowerShell:
Get-Content storage/logs/laravel.log -Tail 50
```

### Clear Everything

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
```

---

## Still Having Issues?

### Resource Links

1. **Mono Documentation:** https://docs.getmono.co
2. **Mono Dashboard:** https://app.withmono.com
3. **Mono Support:** support@withmono.com
4. **Laravel Config Docs:** https://laravel.com/docs/configuration
5. **TaxMaster Setup Guide:** See [MONO_SETUP.md](MONO_SETUP.md)

### Getting Help

When reporting issues, include:

1. **Error message** (full text)
2. **Relevant log entries** from `storage/logs/laravel.log`
3. **Steps to reproduce**
4. **Your environment:**
   ```bash
   php -v
   laravel --version
   composer --version
   ```

### Example Debug Output to Collect

```bash
# 1. Show .env has the variables (no values shown for security)
grep "^MONO_" .env | cut -d= -f1

# 2. Show Laravel version
php artisan --version

# 3. Check config is readable
php artisan config:show services.mono

# 4. Test service load
php artisan tinker --execute="
try {
    \$s = app(\App\Services\MonoIntegrationService::class);
    \$s->verifyCredentials();
    echo '✅ OK';
} catch (\Throwable \$e) {
    echo '❌ ' . \$e->getMessage();
}
"

# 5. Last 5 errors from log
tail -5 storage/logs/laravel.log
```

---

## Prevention Tips

To avoid this error in the future:

1. **Always start with .env.example:**
   ```bash
   cp .env.example .env
   ```

2. **Add to your setup checklist:**
   - [ ] Copy .env.example → .env
   - [ ] Add all required API keys
   - [ ] Run `php artisan config:cache`
   - [ ] Verify with `php artisan config:show`

3. **Use environment validation:**
   Add to `.env`:
   ```dotenv
   # Quick validation
   MONO_SECRET_KEY=your_sk_live_xxxxx  # ⚠️ MUST START WITH sk_live_
   MONO_PUBLIC_KEY=your_pk_live_xxxxx  # ⚠️ MUST START WITH pk_live_
   ```

4. **Document dependencies:**
   Create a `REQUIRED_ENV_VARS.txt`:
   ```
   MONO_SECRET_KEY - Get from https://app.withmono.com/settings/keys
   MONO_PUBLIC_KEY - Get from https://app.withmono.com/settings/keys
   ```

---

**Last Updated:** February 25, 2026  
**Version:** 1.0  
**Status:** Ready for Production Use

See [MONO_SETUP.md](MONO_SETUP.md) for complete setup instructions.
