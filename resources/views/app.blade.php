  <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563eb">

        <title inertia>{{ isset($page['props']['title']) ? $page['props']['title'] : 'Simplifying tax compliance for Nigerian businesses' }} </title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/taxmaster-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/taxmaster-icon.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/taxmaster-icon.png">
        <link rel="shortcut icon" href="/taxmaster-icon.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/taxmaster-icon.png">
        <link rel="manifest" href="/manifest.json">

        <!-- Default SEO (overridden by Inertia Head / per-page props) -->
        @php
            $seoTitle = ($page['props']['title'] ?? null) ? ($page['props']['title'] . ' - ' . config('app.name')) : ('Simplifying tax compliance for Nigerian businesses - ' . config('app.name'));
            $seoDescription = $page['props']['description'] ?? 'Automates tax computations and prepares ready-to-file returns for Nigerian businesses --from PAYE, VAT, WHT & CIT filings. Connect your bank, import staff data, and generate exportable filing and payment instructions so teams can file and reconcile faster.';
            $seoImage = isset($page['props']['ogImage']) ? url($page['props']['ogImage']) : url('/company-Income-Tax.jpg');
            $seoUrl = url()->current();
        @endphp

        <meta name="description" content="{{ $seoDescription }}">
        <meta property="og:site_name" content="{{ config('app.name', 'TaxMaster') }}">
        <meta property="og:locale" content="en_NG">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:url" content="{{ $seoUrl }}">
        <meta property="og:type" content="website">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        <meta name="twitter:image" content="{{ $seoImage }}">

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
