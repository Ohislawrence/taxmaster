# FIRS E-Invoicing Compliance - README

## 🎯 What This Is

This implementation makes your TaxMaster NG e-invoicing system compliant with Federal Inland Revenue Service (FIRS) requirements for Nigeria. All invoices created in the system can now be automatically validated, signed, and submitted to FIRS.

## ✅ What's Included

### Core Services
- ✅ **FIRS API Integration** - Direct connection to FIRS e-invoicing portal
- ✅ **UBL 2.1 Compliant Invoices** - Standard invoice format with all mandatory fields
- ✅ **TIN Validation** - Real-time Tax Identification Number verification
- ✅ **Digital Signatures** - JAdES-compliant ECDSA signatures
- ✅ **Automated Submission** - Queue-based asynchronous processing
- ✅ **Status Tracking** - Real-time submission and approval status
- ✅ **QR Codes** - Invoice verification codes

### User Features
- ✅ **Enhanced Invoice Form** - Buyer details, TIN validation, payment terms
- ✅ **FIRS Status Dashboard** - Visual status tracking on invoice details
- ✅ **Manual Submission** - Option to resubmit failed invoices
- ✅ **Error Display** - Clear validation error messages
- ✅ **Signed Downloads** - JAdES JSON and signed PDF exports

## 📁 Files Created/Modified

### New Files
```
app/Services/EInvoice/
├── FirsApiService.php          # FIRS API integration
├── TinValidationService.php    # TIN validation
└── (Enhanced) UBLInvoice.php   # UBL 2.1 invoice model

app/Jobs/
├── SubmitInvoiceToFirs.php         # Single submission job
└── BatchSubmitInvoicesToFirs.php   # Batch submission job

database/migrations/
└── 2026_03_28_000001_add_firs_fields_to_invoices_table.php

Documentation/
├── FIRS_EINVOICING_IMPLEMENTATION.md  # Complete documentation
├── FIRS_QUICK_REF.md                  # Quick reference guide
└── .env.firs.example                   # Configuration template
```

### Modified Files
```
config/services.php                          # Added FIRS config
routes/business.php                          # Added FIRS routes
app/Http/Controllers/Business/
└── SalesInvoiceController.php              # Enhanced with FIRS
resources/js/Pages/Business/Invoices/
├── InvoiceCreate.vue                       # Enhanced form
└── InvoiceShow.vue                         # Added FIRS status
```

## 🚀 Quick Start

### 1. Configure Environment
```bash
# Copy FIRS configuration to .env
cat .env.firs.example >> .env

# Edit the values:
# FIRS_API_KEY=your_key_here
# FIRS_TAXPAYER_ID=your_taxpayer_id
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Generate Keys
```bash
openssl ecparam -genkey -name secp256r1 -out storage/app/ecdsa_private.pem
openssl ec -in storage/app/ecdsa_private.pem -pubout -out storage/app/ecdsa_public.pem
```

### 4. Start Queue
```bash
php artisan queue:work --tries=3
```

### 5. Test
- Create an invoice through the UI
- Check FIRS status on invoice details page
- Verify submission in logs: `tail -f storage/logs/laravel.log`

## 📊 How It Works

```
User Creates Invoice
        ↓
Form Validates TIN (real-time)
        ↓
Invoice Saved to Database
        ↓
Job Queued for FIRS Submission
        ↓
    [Queue Worker]
        ↓
Generate IRN + Digital Signature
        ↓
Submit to FIRS API
        ↓
    ┌─────────────┬─────────────┐
    ↓             ↓             ↓
Success       Retryable     Permanent
Approved      Error         Failure
    ↓             ↓             ↓
Update        Retry 3x      Mark Failed
Status        (backoff)     Show Errors
```

## 🎨 UI Updates

### Invoice Creation
- **Added Fields**: Buyer email, phone, full address
- **TIN Validation**: Real-time check with visual feedback (✓ or ✗)
- **Payment Terms**: Free-text field for terms
- **Default VAT**: Auto-filled at 7.5%

### Invoice Details
- **FIRS Status Card**: New section showing:
  - Status badge (Pending, Submitted, Approved, Failed)
  - FIRS reference number
  - Submission and approval timestamps
  - Validation errors (if any)
  - Manual submission button

## 🔧 Configuration Options

In `.env`:
```env
FIRS_EINVOICING_ENABLED=true   # Enable/disable FIRS
FIRS_AUTO_SUBMIT=true          # Auto-submit on creation
FIRS_BATCH_SUBMIT=false        # Use batch mode
FIRS_ENVIRONMENT=sandbox       # sandbox or production
FIRS_VAT_RATE=7.5             # Default VAT percentage
```

## 🔍 Monitoring

### Check Submission Status
```sql
SELECT 
    invoice_number,
    firs_status,
    firs_reference,
    firs_submitted_at
FROM invoices
WHERE firs_submitted_at IS NOT NULL
ORDER BY firs_submitted_at DESC;
```

### Failed Submissions
```sql
SELECT 
    invoice_number,
    firs_validation_errors
FROM invoices
WHERE firs_status IN ('failed', 'validation_failed');
```

### Queue Status
```bash
php artisan queue:status
php artisan queue:failed
```

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| Invoices not submitting | Check queue is running, verify `FIRS_EINVOICING_ENABLED=true` |
| TIN validation fails | Verify internet connection, check API credentials |
| Authentication error | Verify `FIRS_API_KEY` and `FIRS_TAXPAYER_ID` |
| No signature | Check ECDSA key exists at `storage/app/ecdsa_private.pem` |
| Status not updating | Refresh page, check queue worker logs |

## 📖 Full Documentation

- **Complete Guide**: [FIRS_EINVOICING_IMPLEMENTATION.md](FIRS_EINVOICING_IMPLEMENTATION.md)
- **Quick Reference**: [FIRS_QUICK_REF.md](FIRS_QUICK_REF.md)
- **FIRS Portal**: https://einvoice.firs.gov.ng

## 🔐 Security Notes

- ✅ API credentials stored in `.env` (not in code)
- ✅ ECDSA private keys secured in `storage/app/`
- ✅ All API calls over HTTPS
- ✅ Digital signatures on all invoices
- ✅ TIN validation cached (prevents abuse)
- ✅ Queue-based processing (non-blocking)

## 🎯 Production Checklist

Before deploying to production:

- [ ] Obtain production FIRS API credentials
- [ ] Generate production ECDSA key pair
- [ ] Submit public key to FIRS
- [ ] Set `FIRS_ENVIRONMENT=production`
- [ ] Update `FIRS_API_URL` to production endpoint
- [ ] Configure production queue driver (Redis/SQS)
- [ ] Set up Supervisor for queue worker
- [ ] Enable error monitoring/alerting
- [ ] Test with sample invoices
- [ ] Train team on new features

## 📞 Support

**FIRS Support:**
- Portal: https://einvoice.firs.gov.ng
- Email: support@firs.gov.ng

**Technical Issues:**
- Review logs: `storage/logs/laravel.log`
- Check documentation: `FIRS_EINVOICING_IMPLEMENTATION.md`
- Inspect failed jobs: `php artisan queue:failed`

## 📜 License & Compliance

This implementation:
- ✅ Complies with FIRS e-invoicing requirements
- ✅ Follows UBL 2.1 international standard
- ✅ Implements JAdES digital signatures
- ✅ Supports Nigerian Data Protection Act (NDPA)
- ✅ Maintains full audit trail

---

**Version**: 1.0  
**Implementation Date**: March 28, 2026  
**Status**: Production Ready ✅  
**Technology**: Laravel 11, Vue 3, Inertia.js
