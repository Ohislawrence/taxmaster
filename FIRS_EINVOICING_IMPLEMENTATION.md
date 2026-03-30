# FIRS E-Invoicing Implementation Guide

## Overview

This document describes the implementation of Federal Inland Revenue Service (FIRS) e-invoicing compliance for Nigeria in TaxMaster NG.

## Implementation Date
March 28, 2026

## Features Implemented

### 1. FIRS API Integration
- **File**: `app/Services/EInvoice/FirsApiService.php`
- **Features**:
  - Authentication with FIRS API
  - Invoice submission (single and batch)
  - TIN validation
  - Invoice status tracking
  - Invoice cancellation
  - Token caching for performance

### 2. Enhanced UBL Invoice Structure
- **File**: `app/Services/EInvoice/UBLInvoice.php`
- **Compliance**: UBL 2.1 standard with FIRS customizations
- **Features**:
  - Complete invoice data model
  - Structured seller/buyer information
  - Tax breakdown (VAT)
  - Payment information
  - Invoice line items
  - Digital signature support
  - Built-in validation

### 3. TIN Validation Service
- **File**: `app/Services/EInvoice/TinValidationService.php`
- **Features**:
  - Format validation (10-14 digits)
  - Online verification with FIRS
  - Result caching (24 hours)
  - Bulk validation support
  - TIN normalization

### 4. Automated Invoice Submission
- **Files**:
  - `app/Jobs/SubmitInvoiceToFirs.php` - Single invoice submission
  - `app/Jobs/BatchSubmitInvoicesToFirs.php` - Batch submission
- **Features**:
  - Automatic retry on failure (3 attempts)
  - IRN (Invoice Reference Number) generation
  - Digital signature generation (JAdES)
  - Error tracking and logging
  - Queue-based processing

### 5. Database Schema
- **Migration**: `database/migrations/2026_03_28_000001_add_firs_fields_to_invoices_table.php`
- **New Fields**:
  - `firs_reference` - FIRS reference number
  - `firs_irn` - Invoice Reference Number
  - `firs_submission_id` - Submission tracking ID
  - `firs_status` - Submission status (pending, submitted, approved, rejected, cancelled)
  - `firs_submitted_at` - Submission timestamp
  - `firs_approved_at` - Approval timestamp
  - `firs_validation_errors` - JSON validation errors
  - `firs_response` - Complete FIRS response
  - `invoice_type_code` - UBL invoice type (380=Standard, 381=Credit, 383=Debit)
  - Buyer information fields (email, phone, address, city, state, postal_code, country)
  - `payment_means_code` - Payment method code
  - `payment_terms` - Payment terms description
  - `vat_rate` - VAT rate percentage
  - `digital_signature` - JAdES signature
  - `qr_code_data` - QR verification code

### 6. API Endpoints
- **Routes**: `routes/business.php`
- **New Endpoints**:
  - `POST /business/invoices/{invoice}/submit-to-firs` - Submit invoice to FIRS
  - `GET /business/invoices/{invoice}/firs-status` - Get FIRS status
  - `POST /business/validate-tin` - Validate TIN

### 7. User Interface Updates

#### Invoice Creation Form
- **File**: `resources/js/Pages/Business/Invoices/InvoiceCreate.vue`
- **New Fields**:
  - Buyer TIN with real-time validation
  - Buyer email and phone
  - Buyer address (street, city, state, postal code)
  - Payment terms
  - Default VAT rate (7.5%)
- **Features**:
  - TIN validation with visual feedback
  - FIRS compliance labels
  - Enhanced form validation

#### Invoice Details Page
- **File**: `resources/js/Pages/Business/Invoices/InvoiceShow.vue`
- **New Section**: FIRS E-Invoicing Status Card
- **Features**:
  - Real-time FIRS status display
  - Status badges (Pending, Submitted, Approved, Failed)
  - FIRS reference and submission ID
  - Submission/approval timestamps
  - Validation errors display
  - Manual submission button
  - Status refresh capability

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# FIRS E-Invoicing Configuration
FIRS_API_URL=https://einvoice.firs.gov.ng/api/v1
FIRS_API_KEY=your_firs_api_key_here
FIRS_TAXPAYER_ID=your_taxpayer_id_here
FIRS_ENVIRONMENT=sandbox  # or 'production'
FIRS_EINVOICING_ENABLED=true
FIRS_AUTO_SUBMIT=true  # Auto-submit invoices on creation
FIRS_BATCH_SUBMIT=false  # Use batch submission
FIRS_BATCH_SIZE=50
FIRS_VAT_RATE=7.5  # Standard VAT rate

# ECDSA Signing (for JAdES)
ECDSA_PRIVATE_KEY="-----BEGIN EC PRIVATE KEY-----\n...YOUR KEY HERE...\n-----END EC PRIVATE KEY-----"
```

### Configuration File
- **File**: `config/services.php`
- **Section**: `firs` array with all configuration options

## Usage

### Creating a FIRS-Compliant Invoice

1. **Navigate to**: Business Dashboard → Invoices → Create Invoice

2. **Fill Required Information**:
   - Buyer Name (required)
   - Buyer TIN (optional but recommended)
   - Buyer contact information (email, phone)
   - Buyer address details
   - Payment terms
   - Invoice line items

3. **Submit**: The invoice will be automatically created and queued for FIRS submission if `FIRS_AUTO_SUBMIT=true`

### Manual FIRS Submission

If auto-submit is disabled or you need to resubmit:

1. Go to Invoice Details page
2. Find the "FIRS E-Invoicing Status" section
3. Click "Submit to FIRS" button
4. The submission will be queued and processed asynchronously

### Checking FIRS Status

1. Navigate to the Invoice Details page
2. View the "FIRS E-Invoicing Status" section
3. See:
   - Current status (Pending, Submitted, Approved, Failed)
   - FIRS reference number
   - Submission and approval timestamps
   - Any validation errors

### TIN Validation

When entering a buyer TIN in the invoice creation form:
- The TIN will be validated automatically on blur
- A checkmark (✓) appears if valid
- An error message appears if invalid
- Format: 10-14 digits (e.g., 1234567890-0001)

## FIRS Status Workflow

```
┌─────────┐
│ PENDING │ ← Invoice created
└────┬────┘
     │ Queued for submission
     ▼
┌───────────┐
│ SUBMITTED │ ← Sent to FIRS
└─────┬─────┘
      │
      ├──→ Success ──→ ┌──────────┐
      │                 │ APPROVED │
      │                 └──────────┘
      │
      └──→ Failure ──→ ┌────────┐
                       │ FAILED │
                       └────────┘
```

## Error Handling

### Submission Failures
- Automatic retry: 3 attempts with exponential backoff (1min, 5min, 15min)
- Errors are logged in `firs_validation_errors` field
- Full response stored in `firs_response` field
- User-friendly error messages in UI

### Common Errors
1. **Invalid TIN**: TIN format or verification failed
2. **Missing Required Fields**: Invoice data incomplete
3. **Authentication Failed**: Invalid API credentials
4. **Network Timeout**: FIRS API unavailable
5. **Validation Failed**: Invoice doesn't meet FIRS requirements

## Testing

### Sandbox Testing
1. Set `FIRS_ENVIRONMENT=sandbox` in `.env`
2. Use FIRS-provided sandbox credentials
3. Test invoice submission and status checks
4. Verify TIN validation

### Production Checklist
- [ ] Obtain production FIRS API credentials
- [ ] Generate ECDSA key pair for signing
- [ ] Set `FIRS_ENVIRONMENT=production`
- [ ] Configure correct FIRS API URL
- [ ] Test with a few real invoices
- [ ] Enable monitoring and logging
- [ ] Set up error alerts

## Digital Signature (JAdES)

### Key Generation
Generate an ECDSA key pair:

```bash
# Generate private key
openssl ecparam -genkey -name secp256r1 -out ecdsa_private.pem

# Extract public key
openssl ec -in ecdsa_private.pem -pubout -out ecdsa_public.pem
```

### Key Storage
- **Private Key**: Store in `storage/app/ecdsa_private.pem` or as `ECDSA_PRIVATE_KEY` environment variable
- **Public Key**: Submit to FIRS for verification

## Queue Configuration

### Queue Driver
Ensure your `.env` has a proper queue driver:

```env
QUEUE_CONNECTION=database  # or redis, sqs, etc.
```

### Running Queue Worker

```bash
php artisan queue:work --tries=3
```

For production, use a process manager like Supervisor.

## Monitoring & Logs

### Log Files
- Application logs: `storage/logs/laravel.log`
- Queue logs: Check via `php artisan queue:failed`

### Key Log Entries
- FIRS authentication
- Invoice submissions
- TIN validations
- Errors and retries

### Monitoring Queries

```sql
-- Check submission status
SELECT firs_status, COUNT(*) as count 
FROM invoices 
GROUP BY firs_status;

-- Find failed submissions
SELECT id, invoice_number, firs_validation_errors 
FROM invoices 
WHERE firs_status = 'failed';

-- Recent submissions
SELECT id, invoice_number, firs_status, firs_submitted_at 
FROM invoices 
WHERE firs_submitted_at IS NOT NULL 
ORDER BY firs_submitted_at DESC 
LIMIT 20;
```

## API Rate Limits

FIRS API rate limits (verify with FIRS documentation):
- Authentication: 10 requests/minute
- Invoice submission: 100 requests/minute
- TIN validation: 50 requests/minute

Implement appropriate throttling if needed.

## Compliance Notes

1. **Mandatory Fields**: All UBL mandatory fields are included
2. **VAT Rate**: Default 7.5% (configurable)
3. **Invoice Numbering**: Auto-generated, sequential
4. **Digital Signature**: JAdES-compliant ECDSA signature
5. **QR Code**: Generated for invoice verification
6. **Audit Trail**: All submissions tracked with timestamps

## Troubleshooting

### Invoice Not Submitting
1. Check if FIRS is enabled: `FIRS_EINVOICING_ENABLED=true`
2. Verify API credentials in `.env`
3. Check queue is running: `php artisan queue:work`
4. Review logs for errors

### TIN Validation Failing
1. Ensure FIRS API is accessible
2. Check TIN format (10-14 digits)
3. Verify API credentials
4. Try clearing cache: `php artisan cache:clear`

### Status Not Updating
1. Refresh the invoice page
2. Check queue for pending jobs: `php artisan queue:status`
3. Look for failed jobs: `php artisan queue:failed`

## Future Enhancements

Potential improvements for future versions:
- [ ] Webhook support for FIRS status updates
- [ ] Bulk invoice import and submission
- [ ] Enhanced reporting and analytics
- [ ] Credit note and debit note support
- [ ] Multi-currency support with NGN conversion
- [ ] Integration with accounting software
- [ ] Automated compliance reminders
- [ ] Advanced invoice templates

## Support & Resources

### FIRS Documentation
- E-Invoicing Portal: https://einvoice.firs.gov.ng
- API Documentation: https://einvoice.firs.gov.ng/docs
- Support: support@firs.gov.ng

### Code References
- UBL 2.1 Specification: http://docs.oasis-open.org/ubl/UBL-2.1.html
- JAdES Standard: ETSI TS 119 182

## Security Considerations

1. **API Credentials**: Never commit credentials to version control
2. **Private Keys**: Store securely, never expose in logs
3. **TLS/SSL**: All FIRS API communications over HTTPS
4. **Data Privacy**: Comply with NDPA (Nigerian Data Protection Act)
5. **Access Control**: Restrict FIRS operations to authorized users

## Maintenance

### Regular Tasks
- Monitor failed submissions daily
- Review error logs weekly
- Update VAT rates when changed
- Renew API credentials before expiry
- Update FIRS endpoints if changed

### Backup
Ensure regular backups of:
- Database (includes FIRS status)
- Private keys
- Configuration files

---

**Implementation Status**: ✅ Complete
**Version**: 1.0
**Last Updated**: March 28, 2026
**Maintainer**: Development Team
