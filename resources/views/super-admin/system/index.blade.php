@extends('layouts.super-admin')

@section('title', 'System Status')

@section('header')
    System Status
@endsection

@section('content')
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
    }
    .info-card {
        background: var(--bg-secondary);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 20px;
    }
    .info-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--glass-border);
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-size: 13px;
        color: var(--text-secondary);
    }
    .info-value {
        font-size: 13px;
        color: var(--text-primary);
        font-family: monospace;
    }
    .env-badge {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(99, 102, 241, 0.2);
        color: #818cf8;
    }
    .status-enabled {
        color: #fbbf24;
    }
    .status-disabled {
        color: #34d399;
    }
</style>

<div class="info-grid">
    <!-- Environment Info -->
    <div class="info-card">
        <h3>
            <svg style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Environment
        </h3>
        <div class="info-row">
            <span class="info-label">PHP Version</span>
            <span class="info-value">{{ $php_version }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Laravel Version</span>
            <span class="info-value">{{ $laravel_version }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Server IP</span>
            <span class="info-value">{{ $server_ip }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Environment</span>
            <span class="env-badge">{{ app()->environment() }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Debug Mode</span>
            @if(config('app.debug'))
                <span class="info-value status-enabled">Enabled</span>
            @else
                <span class="info-value status-disabled">Disabled</span>
            @endif
        </div>
    </div>

    <!-- Database Info -->
    <div class="info-card">
        <h3>
            <svg style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            Database & Cache
        </h3>
        <div class="info-row">
            <span class="info-label">Database Connection</span>
            <span class="info-value">{{ config('database.default') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cache Driver</span>
            <span class="info-value">{{ config('cache.default') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Queue Connection</span>
            <span class="info-value">{{ config('queue.default') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Session Driver</span>
            <span class="info-value">{{ config('session.driver') }}</span>
        </div>
    </div>
</div>
@endsection
