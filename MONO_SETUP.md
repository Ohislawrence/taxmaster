# Mono Integration Setup Guide

## Overview
This guide helps you set up the Mono API integration for bank account connection functionality in TaxMaster.ng.

---

## What is Mono?

Mono is Nigeria's leading bank connection service that allows apps to securely connect user bank accounts and retrieve transaction data without storing banking credentials.

**Website:** https://www.withmono.com  
**Docs:** https://docs.getmono.co

---

## Step 1: Create a Mono Account

1. Go to [Mono Dashboard](https://app.withmono.com)
2. Sign up with your email address
3. Verify your email
4. Complete your organization profile

---

## Step 2: Get API Credentials

### For Development (Sandbox):

1. Log in to [Mono Dashboard](https://app.withmono.com)
2. Navigate to **Settings** → **API Keys**
3. You'll see two keys:
   - **Secret Key (SK)** - Keep this private!
   - **Public Key (PK)** - Safe to expose in frontend

### For Production:

1. Complete your business verification on Mono
2. Request production credentials from Mono support
3. Production keys will be provided separately

---

## Step 3: Add Credentials to .env

Open `.env` file and add:

```dotenv
# Mono Integration
MONO_SECRET_KEY=sk_live_XXXXXXXXXXXXXXXX
MONO_PUBLIC_KEY=pk_live_XXXXXXXXXXXXXXXX
MONO_WEBHOOK_SECRET=whsec_XXXXXXXXXXXXXXXX
MONO_BASE_URL=https://api.withmono.com
MONO_REDIRECT_URL=http://localhost:8000/business/banks/callback
```

**Replace with your actual values from Mono Dashboard**

---

## Step 4: Test the Connection

### Option A: Via Artisan Tinker

```bash
php artisan tinker

# Inside tinker:
>>> $service = app(\App\Services\MonoIntegrationService::class);
>>> $service->verifyCredentials();
# If no error, credentials are configured!
```

### Option B: Test in Browser

1. Start your Laravel server: `php artisan serve`
2. Navigate to: `http://localhost:8000/business/banks`
3. Click "Connect Bank"
4. The Mono modal should appear
5. Try connecting a test account

---

## Step 5: Create Test Bank Accounts

In Mono sandbox environment, you can test with these virtual accounts:

**Test Bank Details:**
```
Bank Name: Zenith Bank
Account Number: 1234567890
Username: testuser
Password: testpass
```

**Other Available Test Banks:**
- GTBank
- Access Bank
- UBA
- First Bank
- Others (check Mono dashboard)

---

## Troubleshooting

### Error: "Mono API secret key is not configured"

**Solution:**
1. Ensure `.env` file has `MONO_SECRET_KEY` defined
2. Verify the key is copied correctly from Mono Dashboard
3. Run `php artisan config:cache` to clear cache
4. Restart Laravel server

### Error: "Failed to exchange Mono token"

**Possible causes:**
1. Invalid Secret Key
2. Mono API is down (check status: https://status.withmono.com)
3. Network connectivity issue

**Solution:**
1. Verify Secret Key in Mono Dashboard
2. Check server logs: `storage/logs/laravel.log`
3. Test curl request:
```bash
curl -X POST https://api.withmono.com/account/auth \
  -H "mono-sec-key: your_secret_key" \
  -H "Content-Type: application/json" \
  -d '{"code":"test_code"}'
```

### Modal doesn't appear

**Solution:**
1. Check browser console for JavaScript errors
2. Verify `MONO_PUBLIC_KEY` is correct
3. Ensure Mono SDK script is loading: `https://cdn.getmono.co/mono.js`

---

## Security Best Practices

✅ **DO:**
- Keep `MONO_SECRET_KEY` in `.env` (never commit to git)
- Add `.env` to `.gitignore`
- Use different keys for development and production
- Rotate keys regularly
- Monitor webhook signatures

❌ **DON'T:**
- Expose `MONO_SECRET_KEY` in frontend code
- Hardcode credentials in source files
- Share credentials via email/Slack
- Use production keys in development

---

## File Permissions

Ensure `.env` file is properly protected:

```bash
# Linux/Mac:
chmod 600 .env

# Windows PowerShell:
icacls .env /inheritance:r /grant:r "%username%:F"
```

---

## Testing Webhook

Mono sends webhooks when transactions are added/updated.

### Setup Webhook:

1. Go to Mono Dashboard → **Settings** → **Webhooks**
2. Set webhook URL: `https://yourdomain.com/business/banks/webhook`
3. Copy the webhook secret to `.env`:
   ```
   MONO_WEBHOOK_SECRET=whsec_xxx
   ```

### Test Webhook Locally:

Use `ngrok` to expose local server:

```bash
# Install ngrok
# https://ngrok.com/download

# Start ngrok
ngrok http 8000

# Your public URL: https://xxxxx.ngrok.io

# Add to Mono webhook: https://xxxxx.ngrok.io/business/banks/webhook
```

---

## Environment Checklist

- [ ] Created Mono account at https://app.withmono.com
- [ ] Obtained Secret Key and Public Key
- [ ] Added credentials to `.env` file
- [ ] Ran `php artisan config:cache`
- [ ] Verified credentials via Tinker
- [ ] Tested bank connection in browser
- [ ] Set up webhook (optional for development)
- [ ] Added `.env` to `.gitignore`

---

## Next Steps

Once Mono is configured:
1. ✅ Bank Accounts feature is ready
2. ✅ Transaction sync works
3. ✅ User can categorize transactions
4. Build VAT calculations
5. Set up compliance calendar

---

## Resources

- [Mono API Documentation](https://docs.getmono.co)
- [Mono Dashboard](https://app.withmono.com)
- [Mono Status Page](https://status.withmono.com)
- [Mono Support](https://support.withmono.com)

---

## Support

If you encounter issues:

1. **Check Mono Status:** https://status.withmono.com
2. **Read Docs:** https://docs.getmono.co
3. **Contact Mono:** support@withmono.com
4. **Local Logs:** `storage/logs/laravel.log`

For TaxMaster.ng specific issues, contact the development team.

---

**Last Updated:** February 25, 2026
**Version:** 1.0
