<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563eb">

        <title inertia>{{ $page['props']['title'] ?? 'Simplifying tax compliance for Nigerian businesses' }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('taxmaster-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('taxmaster-icon.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('taxmaster-icon.png') }}">
        <link rel="shortcut icon" href="{{ asset('taxmaster-icon.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('taxmaster-icon.png') }}">
        <link rel="manifest" href="{{ asset('manifest.json') }}">

        <!-- Default SEO (overridden by Inertia Head / per-page props) -->
        @php
            $seoTitle = $page['props']['title'] ?? null;
            $seoTitle = $seoTitle ? ($seoTitle . ' - ' . config('app.name')) : ('Nigerian Tax Compliance & E-Invoicing Software - ' . config('app.name'));
            $seoDescription = $page['props']['description'] ?? 'Automate Nigerian tax compliance with PAYE, VAT, WHT, CIT calculations and FIRS e-invoicing. Generate UBL 2.1 compliant invoices, connect your bank via Mono, and file ready-to-submit returns with automated TIN validation. Built for Nigerian businesses, accountants and tax consultants.';
            $seoImage = $page['props']['ogImage'] ?? '/company-Income-Tax.jpg';
            $seoImage = url($seoImage);
            $seoUrl = url()->current();
            $seoKeywords = 'Nigerian tax software, PAYE calculator Nigeria, VAT filing Nigeria, WHT remittance, CIT computation, FIRS e-invoicing, UBL invoice Nigeria, tax compliance software, TaxMaster NG, Mono bank integration, automated tax filing Nigeria';
        @endphp

        <meta name="description" content="{{ $seoDescription }}">
        <meta name="keywords" content="{{ $seoKeywords }}">
        <meta name="author" content="TaxMaster NG">
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

        <!-- Open Graph -->
        <meta property="og:site_name" content="{{ config('app.name', 'TaxMaster NG') }}">
        <meta property="og:locale" content="en_NG">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:url" content="{{ $seoUrl }}">
        <meta property="og:type" content="website">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        <meta name="twitter:image" content="{{ $seoImage }}">
        <meta name="twitter:site" content="@TaxMasterNG">
        <meta name="twitter:creator" content="@TaxMasterNG">

        <!-- Canonical -->
        <link rel="canonical" href="{{ $seoUrl }}">



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
