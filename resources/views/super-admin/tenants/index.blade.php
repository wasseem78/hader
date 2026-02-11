@extends('layouts.super-admin')

@section('title', 'Tenants Management')
@section('header', 'Tenants Management')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-title">
        <h1>All Companies</h1>
        <p>Manage registered companies and their subscriptions</p>
    </div>
    <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Tenant
    </a>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body" style="padding: 12px 16px;">
        <form action="{{ route('super-admin.tenants.index') }}" method="GET">
            <div style="display: flex; gap: 12px;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, email, or subdomain..." style="flex: 1;">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Tenants Table -->
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Subdomain</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
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
                    <td>
                        <span class="text-muted" style="font-size: 12px;">
                            {{ $tenant->subdomain }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $tenant->plan->name ?? 'No Plan' }}</span>
                        @if($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
                            <div style="font-size: 11px; color: var(--warning); margin-top: 4px;">
                                Trial ends {{ $tenant->trial_ends_at->diffForHumans() }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($tenant->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 13px;">{{ $tenant->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $tenant->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: flex-end;">
                            <a href="{{ route('super-admin.tenants.impersonate', $tenant->id) }}" target="_blank" class="action-btn" title="Login as Admin">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            </a>
                            <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="action-btn" title="Edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('super-admin.tenants.destroy', $tenant->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? This will delete the tenant database permanently.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn danger" title="Delete">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <p>No tenants found</p>
                            <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary btn-sm" style="margin-top: 12px;">Add First Tenant</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
    <div style="padding: 16px; border-top: 1px solid var(--border-color);">
        {{ $tenants->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
