# TaxMaster - AI-Powered Tax SaaS for Nigerian Businesses

## Project Overview

TaxMaster is a comprehensive SaaS platform designed for Nigerian businesses to automate tax returns, calculate taxes, and process payments. The platform leverages AI (Deepseek/Gemini) to provide intelligent tax analysis and optimization recommendations.

## Phase 1: Completed ✓

### What's Been Built

#### 1. **Database Schema** (7 Migrations)
- `businesses` - Main business entity
- `business_staff` - Staff members with tax tracking
- `tax_returns` - Tax return submissions
- `tax_payments` - Payment tracking with Paystack integration
- `ai_configurations` - AI provider configurations
- `ai_agent_logs` - AI interaction logging
- `business_subscriptions` - Subscription management
- `business_activity_logs` - Audit trail

#### 2. **Models** (8 Models)
- `Business` - Core business entity
- `BusinessStaff` - Employee records with tax calculation
- `TaxReturn` - Tax return with AI analysis
- `TaxPayment` - Payment transactions
- `AiConfiguration` - AI API settings
- `AiAgentLog` - AI execution logs
- `BusinessSubscription` - Subscription tracking
- `BusinessActivityLog` - Activity audit

#### 3. **Core Services** (4 Services)
- **TaxCalculationService**: Tax calculation logic, staff tax computation, Nigerian tax rules
- **PaymentService**: Paystack integration, payment initialization, verification, webhooks
- **AiAgentService**: AI API integration (Deepseek/Gemini), tax analysis, optimization recommendations
- **BusinessService**: Business lifecycle management, staff management, activity logging

#### 4. **Controllers** (7 Controllers)
**Admin:**
- `DashboardController` - Admin dashboards and reports
- `BusinessController` - Business management
- `SubscriptionController` - Subscription management

**Business:**
- `DashboardController` - Business dashboard
- `TaxReturnController` - Tax return CRUD and AI analysis
- `PaymentController` - Payment processing
- `StaffController` - Staff management
- `SettingsController` - Business settings and AI configuration

#### 5. **Routes**
- `routes/admin.php` - Admin routes (protected with admin role)
- `routes/business.php` - Business routes (protected with business role)
- Integrated with `web.php`

#### 6. **Configuration**
- `config/taxmaster.php` - Main configuration for:
  - Paystack settings
  - AI providers (Deepseek, Gemini)
  - Tax rates and reliefs
  - Pricing plans (Basic, Professional, Enterprise)

#### 7. **Vue Components** (Partial - Initial Structure)
- `Admin/Dashboard.vue`
- `Business/Dashboard.vue`
- `Business/TaxReturns/Index.vue`
- `Business/Staff/Index.vue`

---

## Setup Instructions

### Prerequisites
- PHP 8.1+
- Laravel 11+
- PostgreSQL/MySQL
- Node.js 18+
- Composer
- Docker (optional)

### Installation Steps

1. **Clone and Setup**
```bash
cd c:\laragon\www\taxmaster
composer install
npm install
```

2. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Update .env File**
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taxmaster
DB_USERNAME=postgres
DB_PASSWORD=yourpassword

# Paystack
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxx
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxx
PAYSTACK_VERIFY_WEBHOOK=true

# AI Providers
DEFAULT_AI_PROVIDER=deepseek
DEEPSEEK_API_KEY=your_deepseek_api_key
GEMINI_API_KEY=your_gemini_api_key

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=xxxxx
```

4. **Database Setup**
```bash
php artisan migrate
php artisan db:seed
```

5. **Build Assets**
```bash
npm run build  # Production
npm run dev    # Development
```

6. **Start Development Server**
```bash
php artisan serve
npm run dev    # In another terminal for hot reload
```

---

## Project Structure

```
taxmaster/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Admin controllers
│   │   │   └── Business/        # Business controllers
│   │   └── Middleware/
│   ├── Models/                  # Eloquent models
│   └── Services/                # Business logic
├── config/
│   └── taxmaster.php            # App configuration
├── database/
│   ├── migrations/              # Schema migrations
│   └── seeders/
├── resources/
│   └── js/Pages/
│       ├── Admin/               # Admin Vue pages
│       └── Business/            # Business Vue pages
├── routes/
│   ├── web.php
│   ├── admin.php                # Admin routes
│   └── business.php             # Business routes
└── public/
```

---

## Folder Structure Explanation

### Admin Controllers (`app/Http/Controllers/Admin/`)
Handle administrative tasks:
- Business management
- User management
- Subscription management
- System reports and analytics

### Business Controllers (`app/Http/Controllers/Business/`)
Handle business operations:
- Tax return management
- Staff management
- Payment processing
- Business settings

### Services (`app/Services/`)
Encapsulate business logic:
- TaxCalculationService - All tax calculations
- PaymentService - Payment processing
- AiAgentService - AI interactions
- BusinessService - Business operations

### Routes
- **admin.php**: All admin routes with `admin` middleware
- **business.php**: All business routes with `business` middleware

### Vue Components
- **Admin/**: Admin dashboard and management pages
- **Business/**: Business user interfaces

---

## Key Features Implemented

### 1. Multi-Tenancy Ready
- Businesses isolated by `business_id`
- Activity logging for audit trails
- Role-based access control via Spatie

### 2. Tax Management
- Automatic tax calculation based on Nigerian tax rules
- Staff-wise tax breakdown
- Personal reliefs consideration
- Tax period tracking

### 3. Payment Integration
- Paystack integration for secure payments
- Payment reference generation
- Webhook handling for payment verification
- Payment status tracking

### 4. AI Integration Framework
- Support for multiple AI providers (Deepseek, Gemini)
- Tax analysis and recommendations
- Prompt engineering for tax optimization
- AI interaction logging for cost tracking

### 5. Subscription Management
- Three-tier pricing (Basic, Professional, Enterprise)
- Feature-based plan limits
- Billing cycle management
- Active subscription tracking

---

## Next Steps (Phase 2 & Beyond)

### Phase 2: Admin Dashboard
- [ ] Complete admin dashboard with charts
- [ ] Business list with filters
- [ ] User management interface
- [ ] Tax report generation
- [ ] Payment report dashboard

### Phase 3: Business Dashboard
- [ ] Complete tax return creation flow
- [ ] Payment processing UI
- [ ] Staff management UI
- [ ] Settings page with AI configuration
- [ ] Activity log viewer

### Phase 4: AI Integration
- [ ] Implement Deepseek API integration
- [ ] Implement Gemini API integration
- [ ] Background job for AI analysis
- [ ] Tax optimization recommendations
- [ ] Automated return filing

### Phase 5: Polish & Optimization
- [ ] Unit tests for services
- [ ] Feature tests for controllers
- [ ] Performance optimization
- [ ] Email notifications
- [ ] API documentation

---

## API Endpoints (Business)

### Tax Returns
- `GET /business/tax-returns` - List returns
- `POST /business/tax-returns` - Create return
- `GET /business/tax-returns/{id}` - View return
- `PUT /business/tax-returns/{id}` - Update return
- `POST /business/tax-returns/{id}/submit` - Submit return
- `POST /business/tax-returns/{id}/analyze` - AI analysis

### Payments
- `GET /business/payments` - List payments
- `POST /business/payments/{payment}/initialize` - Initialize Paystack
- `GET /business/payments/{payment}/verify` - Verify payment
- `POST /business/payments/webhook/paystack` - Paystack webhook

### Staff
- `GET /business/staff` - List staff
- `POST /business/staff` - Add staff
- `GET /business/staff/{id}` - View staff
- `PUT /business/staff/{id}` - Update staff
- `DELETE /business/staff/{id}` - Remove staff

### Settings
- `GET /business/settings` - Get settings
- `PUT /business/settings` - Update settings
- `POST /business/settings/ai-config` - Update AI config

---

## Configuration Files

### `config/taxmaster.php`
Contains all application configuration:
- Paystack API credentials
- AI provider settings
- Tax rules and rates
- Pricing plans

---

## Environment Variables Required

```env
# AI Providers
DEEPSEEK_API_KEY=
GEMINI_API_KEY=
DEFAULT_AI_PROVIDER=deepseek

# Payment Gateway
PAYSTACK_SECRET_KEY=
PAYSTACK_PUBLIC_KEY=
PAYSTACK_VERIFY_WEBHOOK=true

# Database
DB_CONNECTION=pgsql
DB_DATABASE=taxmaster
DB_USERNAME=postgres
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
```

---

## Database Relationships

**One-to-Many:**
- User → Businesses (owner)
- Business → Staff
- Business → Tax Returns
- Business → Tax Payments
- Business → Subscriptions
- Business → Activity Logs
- Tax Return → Payments

**Configuration:**
- Business ← AI Configurations (multiple providers per business)
- Business → AI Agent Logs

---

## Development Notes

### Adding New Features
1. Create migration if needed
2. Create/update model with relationships
3. Implement service logic
4. Create controller methods
5. Add routes
6. Create Vue components

### Tax Calculation
The system uses Nigerian tax rules:
- Personal relief: ₦500,000/year
- Standard rate: 10%
- Higher brackets available for higher incomes

### Payment Flow
1. Business initiates payment
2. System calls Paystack API
3. User redirected to Paystack
4. Paystack processes payment
5. Webhook verifies payment
6. System updates records

---

## Security Considerations

1. **Role-based access**: Admin and Business roles via Spatie
2. **Middleware protection**: All routes protected
3. **API key encryption**: Store sensitive keys in env
4. **Webhook verification**: Paystack signature validation
5. **Activity logging**: Audit trail for all changes

---

## Support & Documentation

For detailed information on each component, check:
- Models: `app/Models/`
- Services: `app/Services/`
- Controllers: `app/Http/Controllers/`
- Routes: `routes/`

---

**Version**: 1.0.0 (Phase 1)  
**Last Updated**: February 23, 2026  
**Status**: Foundation Complete - Ready for Phase 2
