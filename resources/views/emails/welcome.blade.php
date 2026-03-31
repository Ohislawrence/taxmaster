@component('mail::message')
# Welcome to TaxMaster, {{ $userName }}! 🎉

We're excited to have you on board. TaxMaster is here to simplify Nigerian tax compliance and make your tax filing effortless.

@if($userRole === 'business')
## Getting Started with TaxMaster for Businesses

Here's how to make the most of your account:

### 1. **Set Up Your Business Profile**
Complete your business details including your TIN (Tax Identification Number), VAT registration status, and business type. This ensures accurate tax calculations.

@component('mail::button', ['url' => config('app.url') . '/business/settings'])
Complete Business Profile
@endcomponent

### 2. **Connect Your Bank Account**
Link your business bank account via Mono to automatically import and categorize transactions. This saves hours of manual data entry.

**Benefits:**
- Auto-sync transactions daily
- Smart categorization for tax purposes
- Real-time financial insights

### 3. **Record Your First Transaction**
Start recording income and expenses to track your tax obligations:
- **VAT (7.5%)** - Track input and output VAT
- **PAYE** - Manage employee payroll taxes
- **WHT** - Record withholding tax deductions
- **CIT** - Monitor corporate income tax

### 4. **Generate Tax Reports**
Access ready-to-file tax returns and compliance reports anytime. TaxMaster automatically calculates your tax obligations based on FIRS regulations.

@component('mail::panel')
💡 **Pro Tip:** Set up your VAT exempt status in Settings if your business qualifies. TaxMaster will automatically adjust calculations for the 18 FIRS-approved categories.
@endcomponent

### 5. **Never Miss a Deadline**
Enable compliance reminders for:
- VAT returns (21st of every month)
- PAYE filing (10th of every month)
- WHT remittance (21st of every month)
- CIT deadlines (6 months after year-end)

@elseif($userRole === 'accountant')
## Getting Started with TaxMaster for Accountants

Welcome to your professional tax management workspace:

### 1. **Set Up Your Accountant Profile**
Complete your professional profile with your practice details and areas of specialization.

@component('mail::button', ['url' => config('app.url') . '/accountant/settings'])
Complete Profile
@endcomponent

### 2. **Add Client Businesses**
Manage multiple client businesses from one dashboard:
- Invite clients via email
- Switch between client accounts seamlessly
- Centralized oversight of all compliance tasks

@component('mail::button', ['url' => config('app.url') . '/accountant/clients'])
Add Your First Client
@endcomponent

### 3. **Manage Client Compliance**
Track all your clients' tax obligations in one place:
- Monitor upcoming deadlines across all clients
- Bulk generate tax returns
- Review and approve filings before submission

### 4. **Streamline Client Workflows**
TaxMaster automates the heavy lifting:
- Auto-categorize transactions from bank feeds
- Generate multiple tax returns simultaneously
- Export reports in FIRS-compliant formats

@component('mail::panel')
💡 **Pro Tip:** Use the dashboard's "Clients at Risk" widget to identify which clients have upcoming deadlines or missing data. Stay ahead of compliance issues.
@endcomponent

### 5. **E-Invoicing for Clients**
Generate FIRS-compliant e-invoices with UBL 2.1 standard:
- Digital signatures included
- TIN validation
- Ready for FIRS submission

@endif

---

## Key Features You'll Love

✅ **Automated Tax Calculations** - PAYE, VAT, WHT, CIT computed per FIRS regulations
✅ **Bank Integration** - Connect via Mono for automatic transaction sync
✅ **Compliance Calendar** - Never miss a tax deadline
✅ **E-Invoicing** - Generate FIRS-compliant digital invoices
✅ **Multi-Tax Support** - Manage all Nigerian taxes in one place
✅ **AI Tax Assistant** - Get instant answers to tax questions

---

## Need Help?

We're here to support you every step of the way:

@component('mail::button', ['url' => config('app.url') . '/help'])
View Help Center
@endcomponent

📧 **Email Support:** support@taxmaster.ng
💬 **Live Chat:** Available in your dashboard
📚 **Documentation:** Comprehensive guides and video tutorials

---

## Quick Links

@component('mail::table')
| Resource | Link |
|:---------|:-----|
@if($userRole === 'business')
| Dashboard | [Go to Dashboard]({{ config('app.url') }}/business/dashboard) |
| Record Transaction | [Add Transaction]({{ config('app.url') }}/business/vat/create) |
| Tax Reports | [View Reports]({{ config('app.url') }}/business/reports) |
@else
| Dashboard | [Go to Dashboard]({{ config('app.url') }}/accountant/dashboard) |
| Client Management | [Manage Clients]({{ config('app.url') }}/accountant/clients) |
| Compliance Overview | [View Compliance]({{ config('app.url') }}/accountant/compliance) |
@endif
| Help Center | [Get Help]({{ config('app.url') }}/help) |
| Settings | [Account Settings]({{ config('app.url') }}/settings) |
@endcomponent

---

@component('mail::subcopy')
**Stay Compliant, Stay Ahead**

TaxMaster automates Nigerian tax compliance so you can focus on growing your business. Have questions? Reply to this email anytime.
@endcomponent

Best regards,
**The TaxMaster Team**

@endcomponent
