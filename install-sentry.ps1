# Install Sentry for Laravel
Write-Host "Installing Sentry SDK for Laravel..." -ForegroundColor Green
composer require sentry/sentry-laravel

Write-Host "`nPublishing Sentry configuration..." -ForegroundColor Green
php artisan sentry:publish --dsn

Write-Host "`nSentry installation complete!" -ForegroundColor Green
Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. Sign up at https://sentry.io (free tier: 5,000 errors/month)"
Write-Host "2. Create a new Laravel project"
Write-Host "3. Copy your DSN from Settings > Projects > [Your Project] > Client Keys (DSN)"
Write-Host "4. Add to .env: SENTRY_LARAVEL_DSN=your_dsn_here"
Write-Host "5. Test with: php artisan sentry:test"
