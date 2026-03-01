#!/bin/bash
# TaxMaster.ng - Mono Setup Script
# This script helps configure Mono API credentials

echo "╔════════════════════════════════════════════════════════╗"
echo "║   TaxMaster.ng - Mono Integration Setup                ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Check if .env exists
if [ -f ".env" ]; then
    echo "✅ .env file found"
else
    echo "📋 .env file not found. Creating from .env.example..."
    if cp .env.example .env; then
        echo "✅ .env created successfully"
    else
        echo "❌ Failed to create .env"
        exit 1
    fi
fi

echo ""
echo "📱 Mono Setup Instructions:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "1. Go to: https://app.withmono.com"
echo "2. Sign up or log in"
echo "3. Navigate to Settings → API Keys"
echo "4. Copy your credentials"
echo ""

# Prompt for credentials
read -p "🔐 Enter your MONO_SECRET_KEY: " MONO_SECRET
read -p "🔑 Enter your MONO_PUBLIC_KEY: " MONO_PUBLIC
read -p "🔔 Enter your MONO_WEBHOOK_SECRET (leave blank if not ready): " MONO_WEBHOOK

# Update .env file
echo ""
echo "📝 Updating .env file..."

# Use sed to update .env (works on Linux/Mac)
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "s|MONO_SECRET_KEY=.*|MONO_SECRET_KEY=$MONO_SECRET|" .env
    sed -i '' "s|MONO_PUBLIC_KEY=.*|MONO_PUBLIC_KEY=$MONO_PUBLIC|" .env
    [ -n "$MONO_WEBHOOK" ] && sed -i '' "s|MONO_WEBHOOK_SECRET=.*|MONO_WEBHOOK_SECRET=$MONO_WEBHOOK|" .env
else
    # Linux
    sed -i "s|MONO_SECRET_KEY=.*|MONO_SECRET_KEY=$MONO_SECRET|" .env
    sed -i "s|MONO_PUBLIC_KEY=.*|MONO_PUBLIC_KEY=$MONO_PUBLIC|" .env
    [ -n "$MONO_WEBHOOK" ] && sed -i "s|MONO_WEBHOOK_SECRET=.*|MONO_WEBHOOK_SECRET=$MONO_WEBHOOK|" .env
fi

echo "✅ .env updated"
echo ""

# Clear config cache
echo "🔄 Clearing configuration cache..."
php artisan config:cache 2>/dev/null
echo "✅ Cache cleared"
echo ""

# Test credentials
echo "🧪 Testing credentials..."
php artisan tinker --execute="
try {
    \$service = app(\App\Services\MonoIntegrationService::class);
    \$service->verifyCredentials();
    echo \"✅ Mono credentials are configured correctly!\n\";
} catch (\Exception \$e) {
    echo \"❌ Error: \" . \$e->getMessage() . \"\n\";
}
" 2>/dev/null

echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║   ✅ Setup Complete!                                   ║"
echo "║                                                        ║"
echo "║   Next steps:                                          ║"
echo "║   1. Start your Laravel server: php artisan serve     ║"
echo "║   2. Visit: http://localhost:8000/business/banks      ║"
echo "║   3. Click 'Connect Bank' to test                     ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
