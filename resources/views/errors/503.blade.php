@extends('errors.layout')

@section('title', '503 — Under Maintenance')

@section('content')
    <div style="margin-bottom: -8px;">
        <svg style="width:80px; height:80px; margin:0 auto; display:block; color:#2563eb; opacity:.15;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </div>

    <div class="code-badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Maintenance Mode
    </div>

    <h1>We'll be right back</h1>
    <p class="sub">TaxMaster is currently undergoing scheduled maintenance to improve your experience. We'll be back shortly — thank you for your patience.</p>

    <div class="actions">
        <a href="javascript:window.location.reload()" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Check Again
        </a>
    </div>

    @if(isset($exception) && $exception->getMessage() && app()->environment('local'))
        <div class="divider"></div>
        <p style="font-size:12px; color:#9ca3af; font-family:monospace; word-break:break-all;">
            {{ $exception->getMessage() }}
        </p>
    @endif
@endsection
