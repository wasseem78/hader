@extends('layouts.super-admin')

@section('title', 'Tenant Details')

@section('header')
    <div style="display: flex; align-items: center; gap: 12px;">
        {{ $tenant->name }}
        @if($tenant->is_active)
            <span class="badge badge-success">Active</span>
        @else
            <span class="badge badge-danger">Inactive</span>
        @endif
    </div>
@endsection

@section('header-actions')
    <a href="{{ route('super-admin.tenants.impersonate', $tenant->id) }}" target="_blank" class="btn" style="background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3);">
        <svg style="width: 16px; height: 16px; margin-right: 6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
        </svg>
        Login as Admin
    </a>
    <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="btn btn-primary">
        Edit Tenant
    </a>
@endsection

@section('content')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
    .detail-card {
        background: var(--bg-secondary);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 20px;
    }
    .detail-card h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--glass-border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-card h3 svg {
        width: 18px;
        height: 18px;
        color: var(--primary);
    }
    .detail-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .detail-label {
        font-size: 12px;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-value {
        font-size: 14px;
        color: var(--text-primary);
    }
    .detail-value a {
        color: var(--primary);
        text-decoration: none;
    }
    .detail-value a:hover {
        text-decoration: underline;
    }
    .trial-expired {
        color: #f87171;
        font-size: 11px;
    }
    .trial-active {
        color: #34d399;
        font-size: 11px;
    }
    .email-text {
        color: var(--text-secondary);
        font-size: 13px;
        margin-top: -10px;
        margin-bottom: 20px;
    }
</style>

<p class="email-text">{{ $tenant->email }}</p>

<div class="detail-grid">
    <!-- General Info -->
    <div class="detail-card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            General Information
        </h3>
        <div class="detail-list">
            <div class="detail-item">
                <span class="detail-label">Tenant ID</span>
                <span class="detail-value">
                    {{ $tenant->subdomain }}
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Created At</span>
                <span class="detail-value">{{ $tenant->created_at->format('M d, Y H:i') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Database Name</span>
                <span class="detail-value" style="font-family: monospace; font-size: 12px;">{{ $tenant->tenancy_db_name ?? 'N/A' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Timezone</span>
                <span class="detail-value">{{ $tenant->timezone ?? 'UTC' }}</span>
            </div>
        </div>
    </div>

    <!-- Subscription Info -->
    <div class="detail-card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            Subscription & Limits
        </h3>
        <div class="detail-list">
            <div class="detail-item">
                <span class="detail-label">Current Plan</span>
                <span class="detail-value" style="font-weight: 600;">{{ $tenant->plan->name ?? 'No Plan' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Trial Ends At</span>
                <span class="detail-value">
                    @if($tenant->trial_ends_at)
                        {{ $tenant->trial_ends_at->format('M d, Y') }}
                        @if($tenant->trial_ends_at->isPast())
                            <span class="trial-expired">(Expired)</span>
                        @else
                            <span class="trial-active">({{ $tenant->trial_ends_at->diffForHumans() }})</span>
                        @endif
                    @else
                        N/A
                    @endif
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Max Employees</span>
                <span class="detail-value">{{ $tenant->max_employees ?? 'Unlimited' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Max Devices</span>
                <span class="detail-value">{{ $tenant->max_devices ?? 'Unlimited' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
