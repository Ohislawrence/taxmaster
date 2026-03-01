# TaxMaster.ng - Mono Setup Script for Windows PowerShell
# This script helps configure Mono API credentials

Write-Host "`n╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   TaxMaster.ng - Mono Integration Setup                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# Check if .env exists
if (Test-Path ".env") {
    Write-Host "✅ .env file found`n" -ForegroundColor Green
} else {
    Write-Host "📋 .env file not found. Creating from .env.example..." -ForegroundColor Yellow
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env" -Force
        Write-Host "✅ .env created successfully`n" -ForegroundColor Green
    } else {
        Write-Host "❌ .env.example not found!" -ForegroundColor Red
        exit 1
    }
}

Write-Host "📱 Mono Setup Instructions:" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Go to: https://app.withmono.com"
Write-Host "2. Sign up or log in"
Write-Host "3. Navigate to Settings → API Keys"
Write-Host "4. Copy your credentials"
Write-Host ""

# Prompt for credentials
$MONO_SECRET = Read-Host "🔐 Enter your MONO_SECRET_KEY"
$MONO_PUBLIC = Read-Host "🔑 Enter your MONO_PUBLIC_KEY"
$MONO_WEBHOOK = Read-Host "🔔 Enter your MONO_WEBHOOK_SECRET (leave blank if not ready)"

# Read .env file
$envContent = Get-Content ".env" -Raw

# Update credentials
$envContent = $envContent -replace 'MONO_SECRET_KEY=.*', "MONO_SECRET_KEY=$MONO_SECRET"
$envContent = $envContent -replace 'MONO_PUBLIC_KEY=.*', "MONO_PUBLIC_KEY=$MONO_PUBLIC"
if ($MONO_WEBHOOK) {
    $envContent = $envContent -replace 'MONO_WEBHOOK_SECRET=.*', "MONO_WEBHOOK_SECRET=$MONO_WEBHOOK"
}

# Write updated .env
$envContent | Set-Content ".env" -Force

Write-Host "`n📝 Updating .env file..." -ForegroundColor Yellow
Write-Host "✅ .env updated`n" -ForegroundColor Green

# Clear config cache
Write-Host "🔄 Clearing configuration cache..." -ForegroundColor Yellow
$null = & php artisan config:cache 2>$null
Write-Host "✅ Cache cleared`n" -ForegroundColor Green

# Test credentials
Write-Host "🧪 Testing credentials..." -ForegroundColor Yellow
$testResult = & php artisan tinker --execute="
try {
    \$service = app(\App\Services\MonoIntegrationService::class);
    \$service->verifyCredentials();
    echo \"✅ Mono credentials are configured correctly!\n\";
} catch (\Exception \$e) {
    echo \"❌ Error: \" . \$e->getMessage() . \"\n\";
}
" 2>$null

Write-Host $testResult

Write-Host "`n╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   ✅ Setup Complete!                                   ║" -ForegroundColor Green
Write-Host "║                                                        ║" -ForegroundColor Cyan
Write-Host "║   Next steps:                                          ║" -ForegroundColor Cyan
Write-Host "║   1. Start your Laravel server: php artisan serve     ║" -ForegroundColor Cyan
Write-Host "║   2. Visit: http://localhost:8000/business/banks      ║" -ForegroundColor Cyan
Write-Host "║   3. Click 'Connect Bank' to test                     ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "📚 For more information, see MONO_SETUP.md" -ForegroundColor Cyan
