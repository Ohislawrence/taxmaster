# TaxMaster API Documentation - Phase 1

## Base URL
```
http://localhost/api
http://localhost/business
http://localhost/admin
```

---

## Authentication
All business and admin endpoints require:
- `Authorization: Bearer {token}` (via Laravel Sanctum)
- Verified email
- Appropriate role (admin or business)

---

## Admin Endpoints

### Dashboard
```
GET /admin/dashboard
- Returns: Admin dashboard data (stats, charts, recent data)
```

### Businesses Management
```
GET /admin/businesses
- Filters: ?search=text&status=active|inactive|suspended&page=1
- Returns: Paginated list of businesses

GET /admin/businesses/{id}
- Returns: Business details with relationships

PUT /admin/businesses/{id}
- Body: { name, email, phone, state, city, address, description }
- Returns: Updated business

DELETE /admin/businesses/{id}
- Returns: Soft delete confirmation

PUT /admin/businesses/{id}/status
- Body: { status: active|inactive|suspended }
- Returns: Updated status

GET /admin/businesses/{id}/activity
- Returns: Activity log for business
```

### Subscriptions
```
GET /admin/subscriptions
- Filters: ?status=active|inactive&plan=basic|professional|enterprise
- Returns: Paginated subscriptions

GET /admin/subscriptions/{id}
- Returns: Subscription details

POST /admin/subscriptions/{id}/manage
- Body: { action: upgrade|downgrade|cancel|renew, plan_type?: basic|professional|enterprise }
- Returns: Updated subscription
```

### Reports
```
GET /admin/reports/tax
- Returns: Tax return analysis and summaries

GET /admin/reports/payments
- Returns: Payment analytics

GET /admin/reports/revenue
- Returns: Revenue reports
```

---

## Business Endpoints

### Dashboard
```
GET /business/dashboard
- Returns: Dashboard with stats, recent returns, and payments
```

### Tax Returns
```
GET /business/tax-returns
- Filters: ?search=period&status=draft|submitted|approved|paid&page=1
- Returns: Paginated tax returns

POST /business/tax-returns
- Body: {
    tax_period: "2026-01",
    return_type: "monthly|quarterly|annual",
    due_date: "2026-02-15",
    gross_income: 1000000,
    deductions: 100000
  }
- Returns: Created tax return with calculated tax

GET /business/tax-returns/{id}
- Returns: Tax return with full details and payments

PUT /business/tax-returns/{id}
- Body: { gross_income, deductions }
- Returns: Updated return with recalculated tax

POST /business/tax-returns/{id}/submit
- Returns: Submitted return

POST /business/tax-returns/{id}/analyze
- Returns: AI analysis result
- Body: {} (optional AI provider override)

GET /business/tax-returns/{id}/analysis
- Returns: Current AI analysis if available

DELETE /business/tax-returns/{id}
- Requirements: Return must be in "draft" status
- Returns: Deletion confirmation
```

### Payments
```
GET /business/payments
- Filters: ?status=pending|completed|failed&page=1
- Returns: Payment history

GET /business/payments/{id}
- Returns: Payment details

POST /business/payments/{payment}/initialize
- Body: {
    tax_return_id: 1,
    amount: 50000
  }
- Returns: {
    success: true,
    authorization_url: "https://checkout.paystack.com/...",
    access_code: "...",
    reference: "PAY-..."
  }

GET /business/payments/{id}/verify
- Query: ?reference=PAY-xxx
- Returns: Payment verification result
- Updates: Tax return balance if successful

POST /business/payments/webhook/paystack
- Webhook endpoint (auto-called by Paystack)
- Headers: X-Paystack-Signature required
- Returns: { status: ok }
```

### Staff Management
```
GET /business/staff
- Filters: ?status=active|terminated&page=1
- Returns: Paginated staff with tax calculations

POST /business/staff
- Body: {
    first_name: "John",
    last_name: "Doe",
    email: "john@example.com",
    phone: "+234...",
    monthly_salary: 250000,
    employment_type: "full_time|part_time|contract",
    designation: "Manager",
    date_employed: "2020-01-15",
    tax_identification_number?: "..."
  }
- Returns: Created staff member

GET /business/staff/{id}
- Returns: Staff details with tax analysis

PUT /business/staff/{id}
- Body: { first_name, last_name, email, phone, monthly_salary, designation, status }
- Returns: Updated staff member

GET /business/staff/{id}/tax-analysis
- Returns: {
    monthly_tax: 25000,
    annual_tax: 300000,
    breakdown: { ... }
  }

DELETE /business/staff/{id}
- Returns: Soft delete confirmation
```

### Settings
```
GET /business/settings
- Returns: Business settings and AI configuration

PUT /business/settings
- Body: { name, email, phone, state, city, address, description }
- Returns: Updated settings

POST /business/settings/ai-config
- Body: {
    api_provider: "deepseek|gemini",
    api_key: "...",
    model: "deepseek-chat|gemini-1.5-pro",
    max_tokens: 2000,
    temperature: 0.7
  }
- Returns: Updated AI configuration

GET /business/settings/activity
- Returns: Activity log with pagination

GET /business/subscription
- Returns: Current subscription with available plans

POST /business/subscription/upgrade
- Body: { plan_type: "basic|professional|enterprise" }
- Returns: New subscription (payment required)
```

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "errors": {
    "field": ["Error message"]
  },
  "message": "Validation failed"
}
```

### Paginated Response
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 20,
  "total": 100,
  "last_page": 5,
  "links": { ... }
}
```

---

## Status Codes

- `200` - OK
- `201` - Created
- `202` - Accepted (async operations)
- `204` - No Content
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Failed
- `500` - Server Error

---

## Common Filters & Queries

### Pagination
```
?page=1&per_page=20
```

### Sorting
```
?sort_by=created_at&sort_order=desc
```

### Date Range (for reports)
```
?from_date=2026-01-01&to_date=2026-02-23
```

---

## Tax Return Calculation Example

```
Input:
{
  "tax_period": "2026-01",
  "return_type": "monthly",
  "due_date": "2026-02-15",
  "gross_income": 1000000,
  "deductions": 100000
}

Calculation:
- Taxable Income = 1,000,000 - 100,000 = 900,000
- Tax Rate = 10%
- Business Tax = 900,000 × 10% = 90,000
- Staff Tax = (Sum of all active staff monthly taxes) = 50,000
- Total Tax Due = 90,000 + 50,000 = 140,000

Response includes:
- Tax breakdown
- Staff-wise breakdown
- Individual monthly tax per staff
- Balance calculation
```

---

## Payment Flow Example

### Step 1: Initialize Payment
```bash
POST /business/payments/100/initialize
{
  "tax_return_id": 1,
  "amount": 50000
}

Response:
{
  "success": true,
  "authorization_url": "https://checkout.paystack.com/...",
  "reference": "PAY-20260223150530ABC123"
}
```

### Step 2: Redirect User
User is redirected to `authorization_url` to complete payment

### Step 3: Paystack Webhook
```
POST /business/payments/webhook/paystack
Headers: X-Paystack-Signature: hash_signature
Body: { "event": "charge.success", "data": { "reference": "PAY-..." } }
```

### Step 4: Verify Payment
```bash
GET /business/payments/100/verify?reference=PAY-20260223150530ABC123

Response:
{
  "success": true,
  "status": "completed",
  "amount": 50000
}
```

---

## AI Analysis Flow

### Step 1: Submit Tax Return
Create and submit a tax return

### Step 2: Trigger AI Analysis
```bash
POST /business/tax-returns/1/analyze
{}

# Optional: Specify AI provider
POST /business/tax-returns/1/analyze
{
  "ai_provider": "gemini"
}
```

### Step 3: Get Analysis
```bash
GET /business/tax-returns/1/analysis

Response:
{
  "analysis": "Detailed AI analysis...",
  "recommendations": [...]
}
```

---

## Error Examples

### Invalid Tax Period
```json
{
  "success": false,
  "errors": {
    "tax_period": ["This tax period has already been submitted"]
  }
}
```

### Insufficient Balance
```json
{
  "success": false,
  "errors": {
    "amount": ["Payment exceeds remaining balance"]
  }
}
```

### Unauthorized Access
```json
{
  "message": "Unauthorized access. Admin privileges required.",
  "status": 403
}
```

---

## Rate Limiting

Currently no rate limiting implemented (Phase 2)

---

## Webhook Signature Verification

Paystack webhooks include signature in header:
```
X-Paystack-Signature: sha512(request_body, secret_key)
```

The system automatically verifies this signature.

---

## Notes

- All amounts are in Nigerian Naira (₦)
- All dates use ISO 8601 format: YYYY-MM-DD
- Staff IDs are specific to a business
- AI analysis is async (returns queued status)
- Activity logs capture all changes for audit trail
