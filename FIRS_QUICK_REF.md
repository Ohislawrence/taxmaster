# FIRS E-Invoicing Quick Reference

## Quick Setup

### 1. Add Environment Variables
Copy settings from `.env.firs.example` to your `.env` file:
```bash
cp .env.firs.example .env.firs
# Then copy relevant lines to .env
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Generate ECDSA Keys
```bash
openssl ecparam -genkey -name secp256r1 -out storage/app/ecdsa_private.pem
openssl ec -in storage/app/ecdsa_private.pem -pubout -out storage/app/ecdsa_public.pem
```

### 4. Start Queue Worker
```bash
php artisan queue:work --tries=3
```

## Quick Commands

### Check FIRS Status
```sql
-- In database
SELECT invoice_number, firs_status, firs_reference 
FROM invoices 
WHERE firs_submitted_at IS NOT NULL;
```

### Resubmit Failed Invoices
```php
php artisan tinker

// Get failed invoices
$failed = App\Models\Invoice::where('firs_status', 'failed')->get();

// Resubmit
foreach ($failed as $invoice) {
    App\Jobs\SubmitInvoiceToFirs::dispatch($invoice, true);
}
```

### Clear Cache
```bash
php artisan cache:clear
php artisan optimize:clear
```

## FIRS Status Codes

| Status | Meaning | Action |
|--------|---------|--------|
| `pending` | Not submitted | Click "Submit to FIRS" |
| `submitted` | Awaiting approval | Wait for FIRS processing |
| `approved` | Compliant ✓ | No action needed |
| `failed` | Submission failed | Check errors, resubmit |
| `validation_failed` | Invalid data | Fix errors, resubmit |
| `error` | System error | Check logs, retry |

## TIN Format

**Valid formats:**
- 10 digits: `1234567890`
- With branch: `1234567890-0001`
- 14 digits: `12345678900001`

**Validation:**
- Format check: Instant
- FIRS verification: 2-3 seconds
- Cache duration: 24 hours

## Invoice Type Codes

| Code | Description |
|------|-------------|
| `380` | Standard commercial invoice (default) |
| `381` | Credit note |
| `383` | Debit note |

## Payment Means Codes

| Code | Description |
|------|-------------|
| `30` | Credit transfer (default) |
| `48` | Bank card |
| `49` | Direct debit |
| `10` | Cash |

## API Endpoints (Business Portal)

### Invoice Operations
- `GET /business/invoices` - List invoices
- `POST /business/invoices` - Create invoice
- `GET /business/invoices/{id}` - View invoice
- `PUT /business/invoices/{id}` - Update invoice

### FIRS Operations
- `POST /business/invoices/{id}/submit-to-firs` - Submit to FIRS
- `GET /business/invoices/{id}/firs-status` - Get FIRS status
- `POST /business/validate-tin` - Validate TIN

### E-Invoice Downloads
- `GET /business/invoices/{id}/jades` - Download JAdES JSON
- `GET /business/invoices/{id}/pdf/signed` - Download signed PDF
- `GET /business/invoices/{id}/qr` - Get QR code

## Common Issues & Fixes

### Issue: Invoice Not Submitting
**Solution:**
1. Check `.env`: `FIRS_EINVOICING_ENABLED=true`
2. Verify API credentials
3. Ensure queue is running: `php artisan queue:work`
4. Check logs: `tail -f storage/logs/laravel.log`

### Issue: TIN Validation Fails
**Solution:**
1. Verify format (10-14 digits)
2. Check internet connection
3. Test API: `php artisan tinker` then run:
   ```php
   $service = new App\Services\EInvoice\TinValidationService();
   $service->validate('1234567890');
   ```

### Issue: Authentication Failed
**Solution:**
1. Verify `FIRS_API_KEY` and `FIRS_TAXPAYER_ID`
2. Check API URL is correct for environment
3. Clear cache: `php artisan cache:clear`

### Issue: Digital Signature Missing
**Solution:**
1. Check ECDSA key exists: `ls -la storage/app/ecdsa_private.pem`
2. Or verify `ECDSA_PRIVATE_KEY` in `.env`
3. Regenerate if needed (see Quick Setup step 3)

## Testing Checklist

- [ ] Environment variables configured
- [ ] Database migrated
- [ ] ECDSA keys generated
- [ ] Queue worker running
- [ ] Create test invoice
- [ ] Verify TIN validation works
- [ ] Submit invoice to FIRS (sandbox)
- [ ] Check FIRS status updates
- [ ] Download JAdES JSON
- [ ] Download signed PDF
- [ ] Test QR code generation

## Production Deployment

**Before going live:**
1. ✅ Obtain production FIRS credentials
2. ✅ Set `FIRS_ENVIRONMENT=production`
3. ✅ Update `FIRS_API_URL` to production
4. ✅ Configure production queue (Redis/SQS)
5. ✅ Set up Supervisor for queue worker
6. ✅ Enable monitoring/alerting
7. ✅ Test with real invoice
8. ✅ Submit public key to FIRS
9. ✅ Document API credentials securely
10. ✅ Train users on new features

## Support

**For FIRS API issues:**
- Email: support@firs.gov.ng
- Portal: https://einvoice.firs.gov.ng

**For code issues:**
- Review logs in `storage/logs/laravel.log`
- Check queue failures: `php artisan queue:failed`
- Review documentation: `FIRS_EINVOICING_IMPLEMENTATION.md`

---

**Quick Reference Version**: 1.0  
**Last Updated**: March 28, 2026
