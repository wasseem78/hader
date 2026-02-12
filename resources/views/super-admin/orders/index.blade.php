@extends('layouts.super-admin')

@section('title', app()->getLocale() == 'ar' ? 'إدارة الطلبات' : 'Orders Management')
@section('header', app()->getLocale() == 'ar' ? 'إدارة الطلبات' : 'Orders Management')

@section('content')
<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-content">
            <h3>{{ app()->getLocale() == 'ar' ? 'طلبات معلقة' : 'Pending Orders' }}</h3>
            <div class="stat-value">{{ $pendingCount }}</div>
        </div>
        <div class="stat-icon yellow">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <h3>{{ app()->getLocale() == 'ar' ? 'طلبات مقبولة' : 'Approved Orders' }}</h3>
            <div class="stat-value">{{ $approvedCount }}</div>
        </div>
        <div class="stat-icon green">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <h3>{{ app()->getLocale() == 'ar' ? 'إجمالي الإيرادات' : 'Total Revenue' }}</h3>
            <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="stat-icon purple">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: 12px 16px;">
        <form action="{{ route('super-admin.orders.index') }}" method="GET">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ app()->getLocale() == 'ar' ? 'بحث بالاسم أو البريد...' : 'Search by name or email...' }}" style="flex: 1; min-width: 200px;">
                <select name="status" class="form-control" style="width: 160px;">
                    <option value="">{{ app()->getLocale() == 'ar' ? 'كل الحالات' : 'All Statuses' }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'معلق' : 'Pending' }}</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'مقبول' : 'Approved' }}</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'مرفوض' : 'Rejected' }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'ملغي' : 'Cancelled' }}</option>
                </select>
                <select name="type" class="form-control" style="width: 160px;">
                    <option value="">{{ app()->getLocale() == 'ar' ? 'كل الأنواع' : 'All Types' }}</option>
                    <option value="new" {{ request('type') == 'new' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'اشتراك جديد' : 'New' }}</option>
                    <option value="upgrade" {{ request('type') == 'upgrade' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'ترقية' : 'Upgrade' }}</option>
                    <option value="downgrade" {{ request('type') == 'downgrade' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'تخفيض' : 'Downgrade' }}</option>
                    <option value="renewal" {{ request('type') == 'renewal' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'تجديد' : 'Renewal' }}</option>
                </select>
                <button type="submit" class="btn btn-primary">{{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}</button>
                @if(request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('super-admin.orders.index') }}" class="btn btn-secondary">{{ app()->getLocale() == 'ar' ? 'إعادة تعيين' : 'Reset' }}</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ app()->getLocale() == 'ar' ? 'رقم الطلب' : 'Order ID' }}</th>
                    <th>{{ app()->getLocale() == 'ar' ? 'الشركة' : 'Company' }}</th>
                    <th>{{ app()->getLocale() == 'ar' ? 'الخطة' : 'Plan' }}</th>
                    <th>{{ app()->getLocale() == 'ar' ? 'النوع' : 'Type' }}</th>
                    <th>{{ app()->getLocale() == 'ar' ? 'المبلغ' : 'Amount' }}</th>
                    <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th>{{ app()->getLocale() == 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th style="text-align: right;">{{ app()->getLocale() == 'ar' ? 'إجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-size: 12px; color: var(--text-muted);">#{{ substr($order->uuid, 0, 8) }}</span>
                    </td>
                    <td>
                        <div class="company-info">
                            <div class="company-avatar">{{ substr($order->company->name ?? '?', 0, 1) }}</div>
                            <div class="company-details">
                                <h4>{{ $order->company->name ?? '-' }}</h4>
                                <p>{{ $order->company->email ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $order->plan->name ?? '-' }}</span>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ ucfirst($order->billing_cycle) }}</div>
                    </td>
                    <td>
                        @php
                            $typeColors = ['new' => 'info', 'upgrade' => 'success', 'downgrade' => 'warning', 'renewal' => 'info'];
                            $typeLabels = [
                                'new' => app()->getLocale() == 'ar' ? 'جديد' : 'New',
                                'upgrade' => app()->getLocale() == 'ar' ? 'ترقية' : 'Upgrade',
                                'downgrade' => app()->getLocale() == 'ar' ? 'تخفيض' : 'Downgrade',
                                'renewal' => app()->getLocale() == 'ar' ? 'تجديد' : 'Renewal',
                            ];
                        @endphp
                        <span class="badge badge-{{ $typeColors[$order->type] ?? 'info' }}">{{ $typeLabels[$order->type] ?? $order->type }}</span>
                    </td>
                    <td>
                        <strong style="color: var(--text-primary);">${{ number_format($order->amount, 2) }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ strtoupper($order->currency) }}</div>
                    </td>
                    <td>
                        @php
                            $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'info', 'expired' => 'danger'];
                            $statusLabels = [
                                'pending' => app()->getLocale() == 'ar' ? 'معلق' : 'Pending',
                                'approved' => app()->getLocale() == 'ar' ? 'مقبول' : 'Approved',
                                'rejected' => app()->getLocale() == 'ar' ? 'مرفوض' : 'Rejected',
                                'cancelled' => app()->getLocale() == 'ar' ? 'ملغي' : 'Cancelled',
                                'expired' => app()->getLocale() == 'ar' ? 'منتهي' : 'Expired',
                            ];
                        @endphp
                        <span class="badge badge-{{ $statusColors[$order->status] ?? 'info' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                    </td>
                    <td>
                        <div style="font-size: 13px;">{{ $order->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $order->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: flex-end;">
                            <a href="{{ route('super-admin.orders.show', $order) }}" class="action-btn" title="{{ app()->getLocale() == 'ar' ? 'عرض' : 'View' }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            @if($order->isPending())
                            <form action="{{ route('super-admin.orders.approve', $order) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل تريد الموافقة على هذا الطلب؟' : 'Approve this order?' }}');">
                                @csrf
                                <button type="submit" class="action-btn" title="{{ app()->getLocale() == 'ar' ? 'موافقة' : 'Approve' }}" style="color: var(--success);">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <p>{{ app()->getLocale() == 'ar' ? 'لا توجد طلبات' : 'No orders found' }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div style="padding: 16px; border-top: 1px solid var(--border-color);">
        {{ $orders->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
