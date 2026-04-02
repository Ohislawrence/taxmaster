# Nigerian Tax Compliance Prompts Library

This document contains AI prompts and reference data for Nigerian tax compliance automation.

## Table of Contents
- [VAT (Value Added Tax)](#vat-value-added-tax)
- [PAYE (Pay As You Earn)](#paye-pay-as-you-earn)
- [WHT (Withholding Tax)](#wht-withholding-tax)
- [CIT (Companies Income Tax)](#cit-companies-income-tax)
- [Compliance Assessment](#compliance-assessment)

---

## VAT (Value Added Tax)

### Current Regulations (2026)
- **Rate**: 7.5% (increased from 5% in 2020)
- **Registration Threshold**: ₦25,000,000 annual turnover
- **Filing Deadline**: 21st of following month
- **Form**: VAT 001 (Monthly Return)
- **Penalties**: 
  - Late filing: ₦50,000 first month + ₦25,000 per additional month
  - Non-registration: ₦50,000 plus 5% of tax due
  - False returns: ₦50,000 or 200% of tax, whichever is higher

### VAT Classification Categories
1. **Standard-rated (7.5%)**
   - Most goods and services
   - Professional services
   - Hospitality services
   - Telecommunication services

2. **Zero-rated (0%)**
   - Exported goods and services
   - Goods and services purchased by diplomats
   - Goods purchased for use in humanitarian donor-funded projects
   - Plant, machinery, and equipment purchased for utilization in Export Processing Zones (EPZ)

3. **Exempt**
   - Medical services provided by registered hospitals
   - Basic food items (bread, cereals, fish, meat, milk, salt, vegetables)
   - Books and educational materials
   - Baby products
   - Farming inputs and equipment

### AI Prompts for VAT Analysis

#### Transaction Classification Prompt
```
You are a Nigerian VAT specialist. Analyze this transaction and determine:
1. VAT applicability (standard-rated, zero-rated, or exempt)
2. Correct VAT rate (0% or 7.5%)
3. Whether input VAT is claimable
4. Any special considerations or documentation required

Transaction Details:
- Description: {transaction_description}
- Amount: ₦{amount}
- Vendor/Customer: {party_name}
- Category: {category}

Provide your analysis in this JSON format:
{
  "vat_treatment": "standard-rated|zero-rated|exempt",
  "vat_rate": 0|7.5,
  "input_vat_claimable": true|false,
  "vat_amount": amount,
  "reasoning": "explanation",
  "documentation_required": ["list of docs"],
  "confidence_score": 0-100
}
```

#### Monthly VAT Calculation Prompt
```
You are a Nigerian VAT compliance expert. Calculate the VAT liability for this month:

Period: {month} {year}

Output VAT (Sales):
{list of output vat transactions}

Input VAT (Purchases):
{list of input vat transactions}

Calculate:
1. Total Output VAT
2. Total Input VAT (claimable only)
3. Net VAT Payable/Refundable
4. Identify any transactions requiring special treatment
5. Recommend adjustments or corrections

Provide comprehensive calculation with confidence scores for each section.
```

---

## PAYE (Pay As You Earn)

### Current Regulations (2026)
- **Filing Deadline**: 10th of following month
- **Income Tax Brackets**:
  - First ₦300,000: 7%
  - Next ₦300,000: 11%
  - Next ₦500,000: 15%
  - Next ₦500,000: 19%
  - Next ₦1,600,000: 21%
  - Above ₦3,200,000: 24%

### Relief Allowances
1. **Consolidated Relief Allowance (CRA)**: Higher of
   - 1% of gross income + 20% of gross income, or
   - ₦200,000 annually

2. **Other Reliefs**:
   - Pension contribution: 8% of basic salary
   - NHF (National Housing Fund): 2.5% of basic salary
   - NHIS (National Health Insurance Scheme): As applicable
   - Life assurance: Up to ₦500,000 or 10% of income

### AI Prompts for PAYE Calculation

#### Individual PAYE Calculation Prompt
```
You are a Nigerian PAYE tax expert. Calculate the monthly PAYE for this employee:

Employee Details:
- Name: {name}
- Annual Gross Salary: ₦{annual_gross}
- Basic Salary (monthly): ₦{basic_monthly}
- Housing Allowance: ₦{housing}
- Transport Allowance: ₦{transport}
- Other Allowances: ₦{other}
- Pension Contribution (8%): ₦{pension}
- NHF Contribution (2.5%): ₦{nhf}

Apply:
1. Nigerian PAYE tax brackets (7%, 11%, 15%, 19%, 21%, 24%)
2. Consolidated Relief Allowance (CRA) - higher of (1% + 20% of gross) or ₦200K
3. Pension relief (8% of basic)
4. NHF relief (2.5% of basic)
5. Other applicable reliefs

Provide breakdown with confidence scores and warnings for any anomalies.
```

---

## WHT (Withholding Tax)

### Current Regulations (2026)
- **Filing Deadline**: 21st of following month
- **Form**: WHT 001 (Monthly Return)
- **Credit Treatment**: WHT suffered can offset final tax liability

### WHT Rates by Transaction Type

| Transaction Type | Rate | Legal Basis |
|-----------------|------|-------------|
| Dividends | 10% | CITA S80 |
| Interest | 10% | CITA S80 |
| Rent (Building/Land) | 10% | CITA S80 |
| Royalties | 10% | CITA S80 |
| Commissions | 5% | CITA S80 |
| Consultancy/Professional Fees | 10% | CITA S80 |
| Construction/Contracts | 5% | CITA S80 |
| Management Fees | 10% | CITA S80 |
| Directors Fees | 10% | PITA |
| Technical Services | 10% | CITA S80 |

### AI Prompts for WHT Classification

#### Transaction Classification Prompt
```
You are a Nigerian WHT specialist. Classify this transaction:

Transaction:
- Description: {description}
- Amount: ₦{amount}
- Beneficiary: {beneficiary_name}
- Beneficiary Type: {individual|company}
- Service Category: {category}

Determine:
1. WHT category (dividends, interest, rent, royalties, commissions, etc.)
2. Applicable WHT rate (5% or 10%)
3. Whether WHT certificate is required
4. Beneficiary's TIN verification status
5. Any exemptions applicable

Provide detailed reasoning and compliance requirements.
```

---

## CIT (Companies Income Tax)

### Current Regulations (2026)
- **Standard Rate**: 30% of chargeable profits
- **Small Companies**: 20% (turnover < ₦25M)
- **Filing Deadline**: 6 months after year-end
- **Minimum Tax**: 0.5% of gross turnover (if no taxable profit)

### Allowable Deductions
- Staff costs (salaries, pensions, bonuses)
- Depreciation (capital allowances on qualifying assets)
- Repairs and maintenance
- Professional fees
- Interest on business loans
- Bad debts (written off)

### Non-Allowable Deductions
- Capital expenditure
- Personal expenses
- Provisions for doubtful debts
- Donations (except to approved charities, max 10%)
- Entertainment expenses exceeding allowable limits

---

## Compliance Assessment

### Key Compliance Indicators

#### 1. Filing Compliance
- VAT returns filed on time (21st of month)
- PAYE returns filed on time (10th of month)
- WHT returns filed on time (21st of month)
- CIT returns filed on time (6 months after year-end)
- Annual returns filed with CAC

#### 2. Payment Compliance
- Timely remittance of VAT collected
- Timely remittance of PAYE withheld
- Timely remittance of WHT deducted
- CIT installment payments (if applicable)

#### 3. Registration Compliance
- Business registered with CAC
- Tax Identification Number (TIN) obtained
- VAT registration (if turnover > ₦25M)
- PAYE registration (employers)
- Pension registration (employers)

### AI Prompt for Compliance Assessment

```
You are a Nigerian tax compliance auditor. Assess this business's compliance status:

Business Profile:
- Name: {business_name}
- TIN: {tin}
- Annual Turnover: ₦{turnover}
- Number of Employees: {employee_count}
- Years in Operation: {years}

Recent Activity:
- Last VAT Return: {last_vat_date}
- Last PAYE Remittance: {last_paye_date}
- Last WHT Filing: {last_wht_date}
- Outstanding Obligations: {obligations}

Perform:
1. Registration status verification
2. Filing compliance review (last 12 months)
3. Payment compliance assessment
4. Penalty calculation for any late/missed filings
5. Risk scoring (0-100, 0=no risk, 100=critical)
6. 90-day action plan to achieve full compliance

Provide detailed report with:
- Compliance score (0-100)
- Red flags and warnings
- Required actions with deadlines
- Estimated penalties and costs
- Recommendations for improvement
```

---

## Penalty Calculations

### VAT Penalties
- **Late Filing**: ₦50,000 for first month + ₦25,000 per additional month
- **Non-Remittance**: 5% of tax per annum + 2% per month (max 10%)
- **False Information**: ₦50,000 or 200% of tax deficiency

### PAYE Penalties
- **Late Filing**: 10% of tax due + 5% of outstanding amount per annum
- **Non-Remittance**: 10% penalty + interest at CBN rate

### WHT Penalties
- **Late Filing**: 10% of tax due + interest
- **Non-Compliance**: Additional 5% of tax per annum

---

## Prompt Engineering Best Practices

### 1. Context Setting
Always provide:
- Current date and tax period
- Business profile (size, industry, location)
- Relevant transaction history
- Applicable tax laws and amendments

### 2. Output Format
Request structured JSON for:
- Machine-readable responses
- Easy integration with existing systems
- Consistent data extraction

### 3. Confidence Scoring
Require AI to provide:
- Overall confidence (0-100)
- Confidence per calculation step
- Reasoning for low confidence scores
- Recommended human review thresholds

### 4. Validation Requirements
Instruct AI to:
- Cross-check calculations
- Verify against tax law citations
- Flag unusual patterns
- Suggest documentation requirements

### 5. Error Handling
Include instructions for:
- Missing data scenarios
- Ambiguous classifications
- Conflicting regulations
- Edge cases

---

## Legal References

### Primary Legislation
1. **Companies Income Tax Act (CITA)** - Cap C21 LFN 2004 (as amended)
2. **Personal Income Tax Act (PITA)** - Cap P8 LFN 2004 (as amended)
3. **Value Added Tax Act (VATA)** - Cap V1 LFN 2004 (as amended)
4. **Petroleum Profits Tax Act (PPTA)** - Cap P13 LFN 2004 (as amended)
5. **Capital Gains Tax Act (CGTA)** - Cap C1 LFN 2004 (as amended)

### Regulatory Bodies
- **Federal Inland Revenue Service (FIRS)** - Federal tax administration
- **State Internal Revenue Service (SIRS)** - State and local government taxes
- **Corporate Affairs Commission (CAC)** - Business registration

### Useful Resources
- FIRS Official Website: https://www.firs.gov.ng
- Tax Laws: https://www.firs.gov.ng/tax-management/tax-laws
- TaxPro Max Portal: https://taxpromax.firs.gov.ng
- CAC Portal: https://pre.cac.gov.ng

---

## Update History

| Date | Change | Updated By |
|------|--------|------------|
| 2026-04-02 | Initial creation with 2026 rates and regulations | AI Workflow System |

## Notes

1. **Regular Updates Required**: Tax laws change frequently. Verify current rates before use.
2. **Professional Advice**: AI outputs should be reviewed by qualified tax professionals.
3. **Legal Compliance**: This is a guide only. Consult official FIRS publications.
4. **Confidentiality**: Never include actual business data in prompt templates.

---

**Last Updated**: April 2, 2026
**Version**: 1.0
**Maintained By**: TaxMaster NG Development Team
