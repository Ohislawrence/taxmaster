@extends('errors.layout')

@section('title', '500 — Server Error')

@section('content')
    <div class="big-code">500</div>

    <div class="code-badge" style="background:#fef2f2; color:#dc2626; border-color:#fecaca;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        500 Server Error
    </div>

    <h1>Something went wrong</h1>
    <p class="sub">An unexpected error occurred on our end. Our team has been notified and is working on a fix. Your data is safe — please try again shortly.</p>

    <div class="actions">
        <a href="javascript:window.location.reload()" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Try Again
        </a>
        <a href="/business/dashboard" class="btn btn-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
    </div>

    <div class="divider"></div>
    <p style="font-size:13px; color:#9ca3af;">If the problem persists, contact <a href="/contact" style="color:#2563eb; text-decoration:none; font-weight:600;">support</a>.</p>
@endsection
