<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'business' => \App\Http\Middleware\BusinessMiddleware::class,
            'ensure.business.setup' => \App\Http\Middleware\EnsureBusinessSetup::class,
            'ensure.subscription' => \App\Http\Middleware\EnsureSubscription::class,
            'subscription.features' => \App\Http\Middleware\CheckSubscriptionFeatures::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log errors to database and send notifications for critical errors
        $exceptions->reportable(function (\Throwable $e) {
            try {
                // Determine severity
                $severity = 'error';
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $statusCode = $e->getStatusCode();
                    if ($statusCode >= 500) {
                        $severity = 'critical';
                    } elseif ($statusCode >= 400) {
                        $severity = 'warning';
                    }
                } elseif (
                    $e instanceof \Error ||
                    $e instanceof \ErrorException ||
                    $e instanceof \ParseError
                ) {
                    $severity = 'critical';
                }

                // Log to database
                \App\Models\ErrorLog::create([
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'user_id' => auth()->id(),
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'context' => [
                        'request_data' => request()->except(['password', 'password_confirmation', 'token']),
                        'session_id' => session()->getId(),
                    ],
                    'severity' => $severity,
                ]);

                // Send email notification for critical errors in production
                if ($severity === 'critical' && app()->environment('production')) {
                    $adminEmail = config('mail.admin_email', config('mail.from.address'));
                    if ($adminEmail) {
                        \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
                            ->notify(new \App\Notifications\CriticalErrorNotification(
                                $e,
                                auth()->id(),
                                [
                                    'url' => request()->fullUrl(),
                                    'user_agent' => request()->userAgent(),
                                ]
                            ));
                    }
                }
            } catch (\Throwable $loggingException) {
                // If logging fails, log to Laravel's default logger
                \Illuminate\Support\Facades\Log::error('Failed to log exception to database', [
                    'original_exception' => $e->getMessage(),
                    'logging_exception' => $loggingException->getMessage(),
                ]);
            }
        });
    })->create();
