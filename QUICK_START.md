# TaxMaster - Quick Start Guide

## Phase 1 Complete! ✅

Your AI-powered tax SaaS for Nigerian businesses is now initialized and ready for development.

---

## What's Built in Phase 1

### 📊 Database (7 tables)
- `businesses` - Business profiles
- `business_staff` - Employee records
- `tax_returns` - Tax submission tracking
- `tax_payments` - Payment records with Paystack integration
- `ai_configurations` - AI API settings
- `ai_agent_logs` - AI interaction tracking
- `business_subscriptions` - Subscription management
- `business_activity_logs` - Audit trail

### 🔧 Backend (10 Controllers, 4 Services)
- **Admin Controllers**: Dashboard, Businesses, Subscriptions
- **Business Controllers**: Dashboard, Tax Returns, Payments, Staff, Settings
- **Core Services**: TaxCalculation, Payment, AiAgent, BusinessOps

### 🎨 Frontend Foundation
- Vue component structure for Admin and Business
- Responsive design with Tailwind CSS
- Initial dashboard and list components

### ⚙️ Configuration
- Paystack integration ready
- AI providers (Deepseek & Gemini) configured
- Nigerian tax rules implemented
- Pricing plans defined

---

## Getting Started

### 1. Setup Database
```bash
cd c:\laragon\www\taxmaster
php artisan migrate
```

### 2. Create Roles & Permissions
```bash
# Create the seeder file
php artisan make:seeder RoleAndPermissionSeeder

# Copy code from SPATIE_SETUP.md to the seeder
# Then run:
php artisan db:seed --class=RoleAndPermissionSeeder
```

### 3. Create Test User
```bash
php artisan tinker
$user = User::factory()->create();
$user->assignRole('business');
exit
```

### 4. Configure Environment
Edit `.env`:
```env
PAYSTACK_SECRET_KEY=sk_test_xxx
PAYSTACK_PUBLIC_KEY=pk_test_xxx
DEEPSEEK_API_KEY=your_key
```

### 5. Start Development
```bash
php artisan serve
# In another terminal:
npm run dev
```

---

## File Structure

```
✅ = Completed    |    🔄 = In Progress    |    ⏳ = To Do

Database Layer
├── ✅ Migrations (7 files)
├── ✅ Models (8 models)
└── ✅ Seeders (config ready)

Backend Layer
├── ✅ Services (4 core services)
├── ✅ Controllers (10 controllers)
├── ✅ Routes (admin.php, business.php)
└── ✅ Config (taxmaster.php)

Frontend Layer
├── ⏳ Vue Pages (Admin and Business)
├── ⏳ Components (Forms, Tables, etc.)
├── ⏳ Layouts (Admin, Business, App)
└── 🔄 Basic skeleton created

Documentation
├── ✅ PHASE1_COMPLETE.md - Overview
├── ✅ API_DOCUMENTATION.md - API Reference
├── ✅ SPATIE_SETUP.md - Roles setup
├── ✅ ROADMAP.md - Phase breakdown
└── ✅ This file - Quick start
```

---

## Project Features

### ✅ Implemented
- Multi-tenancy (isolated by business_id)
- Nigerian tax calculation with staff payroll
- Paystack payment integration framework
- AI integration framework (Deepseek & Gemini)
- Role-based access control (Spatie)
- Activity logging & audit trail
- Subscription management structure
- API routes with proper validation

### 🔄 In Progress (Phase 2)
- Admin dashboard UI
- Business dashboard UI
- User management
- Advanced filtering & search

### ⏳ To Do (Phases 3-5)
- Tax return submission UI
- Payment processing UI
- AI analysis implementation
- Automated tax filing
- Testing suite
- Performance optimization

---

## Key Services Overview

### TaxCalculationService
Calculates taxes based on Nigerian rules:
```php
$service = new TaxCalculationService();
$taxes = $service->calculateBusinessTax($business, '2026-01');
```

### PaymentService
Handles Paystack integration:
```php
$service = new PaymentService();
$result = $service->initializePayment($business, $taxReturn, 50000);
```

### AiAgentService
Provides AI analysis:
```php
$service = new AiAgentService($business);
$analysis = $service->analyzeTaxReturn($taxReturn);
```

### BusinessService
Manages business operations:
```php
$service = new BusinessService();
$business = $service->createBusiness($data);
```

---

## API Endpoints Ready

### Admin Routes
- `GET /admin/dashboard` - Dashboard
- `GET|POST /admin/businesses` - Business management
- `GET|POST /admin/subscriptions` - Subscription management
- `GET /admin/reports/*` - Reports

### Business Routes
- `GET /business/dashboard` - Dashboard
- `GET|POST /business/tax-returns` - Tax returns
- `POST /business/payments/{payment}/initialize` - Initialize payment
- `GET|POST /business/staff` - Staff management
- `GET|PUT /business/settings` - Settings

---

## Next Phase: Phase 2

### What's Coming Next
1. **Admin Dashboard UI**
   - Business list with filters
   - Tax return monitoring
   - Payment analytics
   - Revenue reports

2. **Admin Features**
   - User management
   - Business approval/suspension
   - Subscription management
   - View activity logs

3. **Estimated Timeline**: 3-4 days of development

### To Start Phase 2
When ready, create the remaining Vue components and complete the admin interfaces.

---

## Important Files to Know

```
Core Business Logic:
- app/Services/TaxCalculationService.php
- app/Services/PaymentService.php
- app/Services/AiAgentService.php
- app/Services/BusinessService.php

API Endpoints:
- routes/admin.php
- routes/business.php
- app/Http/Controllers/Admin/*
- app/Http/Controllers/Business/*

Database:
- database/migrations/ (all 7 files)
- app/Models/ (all 8 models)

Configuration:
- config/taxmaster.php (main config)
- .env (environment variables)

Documentation:
- PHASE1_COMPLETE.md
- API_DOCUMENTATION.md
- SPATIE_SETUP.md
- ROADMAP.md
```

---

## Common Tasks

### Create a Test Business
```bash
php artisan tinker
$business = Business::create([
    'owner_id' => 1,
    'name' => 'Test Ltd',
    'email' => 'test@example.com',
    'phone' => '08000000000',
    'country' => 'NG',
    'state' => 'Lagos',
    'city' => 'Lagos',
    'address' => '123 Test St',
    'business_type' => 'company',
    'industry' => 'Technology',
    'registration_number' => 'CAC/12345/2023',
]);
```

### Add Staff Member
```bash
$business->staff()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@test.com',
    'monthly_salary' => 250000,
    'employment_type' => 'full_time',
    'designation' => 'Manager',
    'date_employed' => now(),
]);
```

### Create Tax Return
```bash
$taxReturn = $business->taxReturns()->create([
    'tax_period' => '2026-01',
    'return_type' => 'monthly',
    'due_date' => now()->addMonth(),
    'gross_income' => 1000000,
    'deductions' => 100000,
    'taxable_income' => 900000,
    'total_tax_due' => 140000,
    'status' => 'draft',
]);
```

---

## Database Relationships

```
User → Business (one-to-many, owner)
Business → Staff (one-to-many)
Business → TaxReturns (one-to-many)
Business → TaxPayments (one-to-many)
Business → Subscriptions (one-to-many)
Business → AiConfigurations (one-to-many)
Business → ActivityLogs (one-to-many)
TaxReturn → TaxPayments (one-to-many)
```

---

## Troubleshooting

### Migration Fails
```bash
php artisan migrate:refresh
# or
php artisan migrate:reset && php artisan migrate
```

### Routes Not Found
```bash
php artisan route:cache
php artisan route:clear
```

### Permissions Not Working
```bash
php artisan permission:cache-reset
```

### Database Connection
Check `.env` file:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taxmaster
DB_USERNAME=postgres
DB_PASSWORD=yourpassword
```

---

## Performance Tips

1. **Cache Roles & Permissions**
   ```bash
   php artisan permission:cache-reset
   ```

2. **Optimize Database Queries**
   - Use `with()` for eager loading
   - Add indexes on frequently queried columns
   - Use pagination

3. **Frontend Optimization**
   ```bash
   npm run build  # Production build
   ```

---

## Security Reminders

✅ **Implemented**:
- Role-based access control
- Middleware protection on all routes
- Activity logging for audit trails
- Database encryption for sensitive data

⚠️ **Still To Do**:
- API rate limiting
- CORS configuration
- HTTPS enforcement
- API key encryption
- Input validation hardening

---

## Support & Documentation

### Read First
1. `PHASE1_COMPLETE.md` - Phase overview
2. `API_DOCUMENTATION.md` - All endpoints
3. `ROADMAP.md` - Full roadmap
4. `SPATIE_SETUP.md` - Roles setup

### External Resources
- Laravel: https://laravel.com/docs
- Laravel Inertia: https://inertiajs.com
- Spatie Permissions: https://spatie.be/docs/laravel-permission
- Paystack Docs: https://paystack.com/developers

---

## Contact & Issues

For implementation questions or issues:
1. Check the relevant documentation file
2. Review the code in `app/Services/` for logic
3. Check `app/Http/Controllers/` for endpoint implementation
4. Review migrations in `database/migrations/` for schema

---

## What's Unique About This Build

✨ **Features**:
1. **Multi-tenancy from the ground up** - Each business is isolated
2. **AI-Ready Architecture** - Switch between Deepseek and Gemini
3. **Nigerian Tax Compliance** - Built-in PAYE calculations
4. **Activity Audit Trail** - Complete tracking of all actions
5. **Service-Layer Architecture** - Clean separation of concerns
6. **Scalable Folder Structure** - Admin and Business separation

---

## Ready to Build?

### Phase 2 Checklist
- [ ] All migrations run successfully
- [ ] Roles and permissions seeded
- [ ] Test users created with different roles
- [ ] Environment variables configured
- [ ] `php artisan serve` works
- [ ] `npm run dev` works
- [ ] Can access routes via browser

Once all checked, you're ready for Phase 2!

---

**Version**: 1.0.0  
**Status**: Phase 1 Complete ✅  
**Date**: February 23, 2026  
**Next**: Phase 2 - Admin Dashboard
