@extends('layouts.super-admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard Overview')

@section('content')
<!-- Stats Grid -->
<div class="stats-grid">
    <!-- Total Tenants -->
    <div class="stat-card">
        <div class="stat-content">
            <h3>Total Companies</h3>
            <div class="stat-value">{{ $totalTenants }}</div>
            <div class="stat-change positive">+{{ $recentTenants->count() }} this month</div>
        </div>
        <div class="stat-icon purple">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="stat-card">
        <div class="stat-content">
            <h3>Active Subscriptions</h3>
            <div class="stat-value">{{ $activeTenants }}</div>
            <div class="stat-change positive">{{ $totalTenants > 0 ? round(($activeTenants / $totalTenants) * 100) : 0 }}% of total</div>
        </div>
        <div class="stat-icon green">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="stat-card">
        <div class="stat-content">
            <h3>Monthly Revenue</h3>
            <div class="stat-value">${{ number_format($monthlyRevenue, 0) }}</div>
            <div class="stat-change positive">Recurring</div>
        </div>
        <div class="stat-icon blue">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <!-- Trial Users -->
    <div class="stat-card">
        <div class="stat-content">
            <h3>Trial Accounts</h3>
            <div class="stat-value">{{ $trialTenants }}</div>
            <div class="stat-change" style="color: var(--warning);">Potential leads</div>
        </div>
        <div class="stat-icon yellow">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Recent Tenants Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Registrations</h3>
            <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTenants as $tenant)
                    <tr>
                        <td>
                            <div class="company-info">
                                <div class="company-avatar">{{ substr($tenant->name, 0, 1) }}</div>
                                <div class="company-details">
                                    <h4>{{ $tenant->name }}</h4>
                                    <p>{{ $tenant->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-info">{{ $tenant->plan->name ?? 'No Plan' }}</span></td>
                        <td>
                            @if($tenant->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="action-btn" title="Edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <p>No tenants registered yet</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Plan Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Plan Distribution</h3>
        </div>
        <div class="card-body">
            @forelse($planDistribution as $dist)
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: 13px; color: var(--text-secondary);">{{ $dist->plan->name ?? 'Unknown' }}</span>
                    <span style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $dist->total }}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $totalTenants > 0 ? ($dist->total / $totalTenants) * 100 : 0 }}%"></div>
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; text-align: right;">
                    {{ $totalTenants > 0 ? round(($dist->total / $totalTenants) * 100) : 0 }}% of total
                </div>
            </div>
            @empty
            <div class="empty-state">
                <p>No plan data available</p>
            </div>
            @endforelse

            <div style="border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted);">Total Monthly Revenue</span>
                    <span style="font-size: 16px; font-weight: 700; color: var(--text-primary);">${{ number_format($monthlyRevenue, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Tenant
        </a>
        <a href="{{ route('super-admin.plans.create') }}" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Create Plan
        </a>
        <a href="{{ route('super-admin.system.index') }}" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            System Status
        </a>
    </div>
</div>
@endsection
