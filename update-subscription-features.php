<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== UPDATING SUBSCRIPTION PLAN FEATURES ===\n\n";

// Free Plan Features
$freeFeatures = [
    "Basic tax return filing (PAYE, WHT)",
    "Up to 5 returns per year",
    "1 staff member",
    "1 GB storage",
    "View compliance calendar",
    "Basic transaction tracking",
    "Community support"
];

DB::table('subscription_plans')
    ->where('slug', 'free')
    ->update([
        'features' => json_encode($freeFeatures),
        'updated_at' => now()
    ]);

echo "✓ Updated Free Plan (" . count($freeFeatures) . " features)\n";

// Basic Plan Features
$basicFeatures = [
    "All tax returns (PAYE, WHT, VAT, CIT)",
    "Up to 50 returns per year",
    "Up to 3 staff members",
    "5 GB storage",
    "AI tax analysis & insights",
    "Automated transaction categorization",
    "Compliance calendar with reminders",
    "Bank account integration (Mono)",
    "Transaction management",
    "Payment tracking",
    "Email support (48hr response)"
];

DB::table('subscription_plans')
    ->where('slug', 'basic')
    ->update([
        'features' => json_encode($basicFeatures),
        'updated_at' => now()
    ]);

echo "✓ Updated Basic Plan (" . count($basicFeatures) . " features)\n";

// Professional Plan Features
$professionalFeatures = [
    "Unlimited tax returns (PAYE, WHT, VAT, CIT, CGT)",
    "Up to 500 returns per year",
    "Up to 10 staff members",
    "50 GB storage",
    "Advanced AI tax optimization",
    "AI chat assistant",
    "Automated payment processing",
    "Payment recovery automation",
    "Bank integration with auto-sync",
    "Transaction auto-categorization",
    "Financial statements generation",
    "CAC annual returns",
    "Automated compliance reminders",
    "RRR generation (Remita)",
    "Document attachments",
    "Priority support (24/7)",
    "Advanced reporting & analytics",
    "API access",
    "Export to PDF"
];

DB::table('subscription_plans')
    ->where('slug', 'professional')
    ->update([
        'features' => json_encode($professionalFeatures),
        'updated_at' => now()
    ]);

echo "✓ Updated Professional Plan (" . count($professionalFeatures) . " features)\n";

// Enterprise Plan Features
$enterpriseFeatures = [
    "Unlimited everything",
    "Unlimited returns per year",
    "Unlimited staff members",
    "500 GB storage",
    "Custom AI models for your business",
    "White-label solution",
    "Multi-business management",
    "Custom integrations",
    "Advanced analytics & dashboards",
    "Automated tax optimization",
    "AI-powered compliance monitoring",
    "Automated payment reconciliation",
    "Bank integration (all Nigerian banks)",
    "Financial forecasting",
    "Custom report templates",
    "Bulk operations",
    "Audit trail & logging",
    "Advanced security (2FA, IP whitelisting)",
    "Dedicated account manager",
    "24/7 priority support",
    "SLA guarantee (99.9% uptime)",
    "On-premise deployment option",
    "Custom branding",
    "Training & onboarding",
    "API access with higher limits"
];

DB::table('subscription_plans')
    ->where('slug', 'enterprise')
    ->update([
        'features' => json_encode($enterpriseFeatures),
        'updated_at' => now()
    ]);

echo "✓ Updated Enterprise Plan (" . count($enterpriseFeatures) . " features)\n";

echo "\n=== SUMMARY ===\n";
echo "Free: " . count($freeFeatures) . " features\n";
echo "Basic: " . count($basicFeatures) . " features\n";
echo "Professional: " . count($professionalFeatures) . " features\n";
echo "Enterprise: " . count($enterpriseFeatures) . " features\n";
echo "\n✅ All subscription plans updated successfully!\n";
