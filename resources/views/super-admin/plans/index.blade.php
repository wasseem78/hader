@extends('layouts.super-admin')

@section('title', 'Plans Management')
@section('header', 'Plans Management')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-title">
        <h1>Subscription Plans</h1>
        <p>Manage pricing tiers and feature limits</p>
    </div>
    <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Plan
    </a>
</div>

<!-- Plans Table -->
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Monthly Price</th>
                    <th>Yearly Price</th>
                    <th>Limits</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr>
                    <td>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $plan->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $plan->slug }}</div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: var(--text-primary);">${{ number_format($plan->price_monthly, 2) }}</span>
                    </td>
                    <td>
                        <span style="color: var(--text-secondary);">${{ number_format($plan->price_yearly, 2) }}</span>
                    </td>
                    <td>
                        <div style="font-size: 12px;">
                            <div>Employees: <span style="color: var(--text-primary); font-weight: 500;">{{ $plan->max_employees }}</span></div>
                            <div>Devices: <span style="color: var(--text-primary); font-weight: 500;">{{ $plan->max_devices }}</span></div>
                        </div>
                    </td>
                    <td>
                        @if($plan->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                        @if($plan->is_featured)
                            <span class="badge badge-warning" style="margin-left: 4px;">Featured</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: flex-end;">
                            <a href="{{ route('super-admin.plans.edit', $plan->id) }}" class="action-btn" title="Edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('super-admin.plans.destroy', $plan->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
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
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <p>No plans found</p>
                            <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary btn-sm" style="margin-top: 12px;">Create First Plan</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
