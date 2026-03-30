<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="theme-color" content="#2563eb">

        <title inertia><?php echo e($page['props']['title'] ?? 'Simplifying tax compliance for Nigerian businesses'); ?></title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('taxmaster-icon.png')); ?>">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('taxmaster-icon.png')); ?>">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('taxmaster-icon.png')); ?>">
        <link rel="shortcut icon" href="<?php echo e(asset('taxmaster-icon.png')); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('taxmaster-icon.png')); ?>">
        <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">

        <!-- Default SEO (overridden by Inertia Head / per-page props) -->
        <?php
            $seoTitle = $page['props']['title'] ?? null;
            $seoTitle = $seoTitle ? ($seoTitle . ' - ' . config('app.name')) : ('Nigerian Tax Compliance & E-Invoicing Software - ' . config('app.name'));
            $seoDescription = $page['props']['description'] ?? 'Automate Nigerian tax compliance with PAYE, VAT, WHT, CIT calculations and FIRS e-invoicing. Generate UBL 2.1 compliant invoices, connect your bank via Mono, and file ready-to-submit returns with automated TIN validation. Built for Nigerian businesses, accountants and tax consultants.';
            $seoImage = $page['props']['ogImage'] ?? '/company-Income-Tax.jpg';
            $seoImage = url($seoImage);
            $seoUrl = url()->current();
            $seoKeywords = 'Nigerian tax software, PAYE calculator Nigeria, VAT filing Nigeria, WHT remittance, CIT computation, FIRS e-invoicing, UBL invoice Nigeria, tax compliance software, TaxMaster NG, Mono bank integration, automated tax filing Nigeria';
        ?>

        <meta name="description" content="<?php echo e($seoDescription); ?>">
        <meta name="keywords" content="<?php echo e($seoKeywords); ?>">
        <meta name="author" content="TaxMaster NG">
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

        <!-- Open Graph -->
        <meta property="og:site_name" content="<?php echo e(config('app.name', 'TaxMaster NG')); ?>">
        <meta property="og:locale" content="en_NG">
        <meta property="og:title" content="<?php echo e($seoTitle); ?>">
        <meta property="og:description" content="<?php echo e($seoDescription); ?>">
        <meta property="og:image" content="<?php echo e($seoImage); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:url" content="<?php echo e($seoUrl); ?>">
        <meta property="og:type" content="website">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo e($seoTitle); ?>">
        <meta name="twitter:description" content="<?php echo e($seoDescription); ?>">
        <meta name="twitter:image" content="<?php echo e($seoImage); ?>">
        <meta name="twitter:site" content="@TaxMasterNG">
        <meta name="twitter:creator" content="@TaxMasterNG">

        <!-- Canonical -->
        <link rel="canonical" href="<?php echo e($seoUrl); ?>">



        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Tighten\Ziggy\BladeRouteGenerator')->generate(); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"]); ?>
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
    </head>
    <body class="font-sans antialiased">
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } elseif (config('inertia.use_script_element_for_initial_page')) { ?><script data-page="app" type="application/json"><?php echo json_encode($page); ?></script><div id="app"></div><?php } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>
    </body>
</html>
<?php /**PATH C:\laragon\www\taxmaster\resources\views/app.blade.php ENDPATH**/ ?>