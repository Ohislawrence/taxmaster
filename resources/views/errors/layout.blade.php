<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <title>@yield('title') — TaxMaster</title>
    <link rel="icon" type="image/png" href="/taxmaster-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --blue:   #2563eb;
            --blue-d: #1d4ed8;
            --blue-l: #eff6ff;
            --gray-1: #111827;
            --gray-2: #374151;
            --gray-3: #6b7280;
            --gray-4: #d1d5db;
            --gray-5: #f9fafb;
        }

        html, body {
            min-height: 100dvh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: radial-gradient(ellipse 80% 60% at 50% -10%, #dbeafe 0%, #f0f9ff 40%, #f9fafb 100%);
            color: var(--gray-1);
            -webkit-font-smoothing: antialiased;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            padding-bottom: max(32px, env(safe-area-inset-bottom));
            text-align: center;
            min-height: 100dvh;
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.04), 0 20px 40px -10px rgba(37,99,235,.08);
            padding: 48px 40px;
            max-width: 520px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37,99,235,.03) 0%, transparent 60%);
            pointer-events: none;
        }

        /* ── Logo ── */
        .logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 36px;
        }

        .logo-wrap img {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }

        .logo-wrap span {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--gray-1);
        }

        .logo-wrap span em {
            font-style: normal;
            color: var(--blue);
        }

        /* ── Code badge ── */
        .code-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--blue-l);
            color: var(--blue);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #bfdbfe;
            margin-bottom: 20px;
        }

        .code-badge svg { width: 13px; height: 13px; flex-shrink: 0; }

        /* ── Headline ── */
        h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.6px;
            color: var(--gray-1);
            line-height: 1.2;
            margin-bottom: 12px;
        }

        p.sub {
            font-size: 15px;
            color: var(--gray-3);
            line-height: 1.65;
            max-width: 380px;
            margin: 0 auto 32px;
        }

        /* ── Big code number (background decoration) ── */
        .big-code {
            font-size: 120px;
            font-weight: 900;
            letter-spacing: -6px;
            line-height: 1;
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: -8px;
            user-select: none;
        }

        /* ── Actions ── */
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 12px 22px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: transform .12s, box-shadow .12s, background .12s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn:active { transform: scale(.97); }

        .btn-primary {
            background: var(--blue);
            color: #fff;
            box-shadow: 0 1px 3px rgba(37,99,235,.3), 0 4px 12px rgba(37,99,235,.2);
        }

        .btn-primary:hover { background: var(--blue-d); box-shadow: 0 2px 6px rgba(37,99,235,.35), 0 6px 16px rgba(37,99,235,.25); }

        .btn-ghost {
            background: #f3f4f6;
            color: var(--gray-2);
            border: 1px solid #e5e7eb;
        }

        .btn-ghost:hover { background: #e9eaec; }

        .btn svg { width: 15px; height: 15px; }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: #f3f4f6;
            margin: 28px 0;
        }

        /* ── Footer note ── */
        .footer-note {
            font-size: 12px;
            color: var(--gray-4);
            margin-top: 28px;
        }

        @media (max-width: 480px) {
            .card { padding: 36px 24px; border-radius: 20px; }
            h1 { font-size: 22px; }
            .big-code { font-size: 88px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap">
            <img src="/taxmaster-icon.png" alt="TaxMaster">
            <span>Tax<em>Master</em></span>
        </div>

        @yield('content')
    </div>

    <p class="footer-note">TaxMaster &mdash; Nigerian Tax Compliance &amp; Management</p>
</body>
</html>
