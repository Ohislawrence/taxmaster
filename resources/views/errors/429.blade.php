@extends('errors.layout')

@section('title', '429 — Too Many Requests')

@section('content')
    <div class="big-code">429</div>

    <div class="code-badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        429 Rate Limited
    </div>

    <h1>Slow down a little</h1>
    <p class="sub">You've made too many requests in a short time. Please wait a moment before trying again — your data is safe.</p>

    <div class="actions">
        <a href="javascript:history.back()" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Go Back
        </a>
        <a href="/business/dashboard" class="btn btn-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
    </div>
@endsection
