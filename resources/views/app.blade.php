<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563eb">

        <title inertia>{{ config('app.name', 'TaxMaster') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" sizes="32x32" href="/taxmaster-icon.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/taxmaster-icon.png">
        <link rel="shortcut icon" href="/taxmaster-icon.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/taxmaster-icon.png">
        <link rel="manifest" href="/manifest.json">

        <!-- Default SEO (overridden by Inertia Head) -->
        <meta name="description" content="Simplifying tax compliance for Nigerian businesses. Automate PAYE, VAT, WHT & CIT filings.">
        <meta property="og:site_name" content="TaxMaster">
        <meta property="og:locale" content="en_NG">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
