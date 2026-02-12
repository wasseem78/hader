@extends('layouts.super-admin')

@section('title', app()->getLocale() == 'ar' ? 'تفاصيل الطلب' : 'Order Details')
@section('header', app()->getLocale() == 'ar' ? 'تفاصيل الطلب' : 'Order Details')

@section('header-actions')
    <a href="{{ route('super-admin.orders.index') }}" class="btn btn-secondary btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        {{ app()->getLocale() == 'ar' ? 'العودة للقائمة' : 'Back to List' }}
    </a>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Main Content -->
    <div>
        <!-- Order Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">
                    {{ app()->getLocale() == 'ar' ? 'معلومات الطلب' : 'Order Information' }}
                    <span style="font-family: monospace; font-size: 12px; color: var(--text-muted); margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">#{{ substr($order->uuid, 0, 8) }}</span>
                </div>
                @php
                    $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'info'];
                    $statusLabels = [
                        'pending' => app()->getLocale() == 'ar' ? 'معلق' : 'Pending',
                        'approved' => app()->getLocale() == 'ar' ? 'مقبول' : 'Approved',
                        'rejected' => app()->getLocale() == 'ar' ? 'مرفوض' : 'Rejected',
                        'cancelled' => app()->getLocale() == 'ar' ? 'ملغي' : 'Cancelled',
                    ];
                @endphp
                <span class="badge badge-{{ $statusColors[$order->status] ?? 'info' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <div class="form-label">{{ app()->getLocale() == 'ar' ? 'نوع الطلب' : 'Order Type' }}</div>
                        @php
                            $typeLabels = [
                                'new' => app()->getLocale() == 'ar' ? 'اشتراك جديد' : 'New Subscription',
                                'upgrade' => app()->getLocale() == 'ar' ? 'ترقية' : 'Upgrade',
                                'downgrade' => app()->getLocale() == 'ar' ? 'تخفيض' : 'Downgrade',
                                'renewal' => app()->getLocale() == 'ar' ? 'تجديد' : 'Renewal',
                            ];
                        @endphp
                        <span class="badge badge-info">{{ $typeLabels[$order->type] ?? $order->type }}</span>
                    </div>
                    <div>
                        <div class="form-label">{{ app()->getLocale() == 'ar' ? 'الخطة المطلوبة' : 'Requested Plan' }}</div>
                        <strong style="color: var(--text-primary);">{{ $order->plan->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <div class="form-label">{{ app()->getLocale() == 'ar' ? 'دورة الفوترة' : 'Billing Cycle' }}</div>
                        <span style="color: var(--text-primary);">{{ $order->billing_cycle === 'yearly' ? (app()->getLocale() == 'ar' ? 'سنوي' : 'Yearly') : (app()->getLocale() == 'ar' ? 'شهري' : 'Monthly') }}</span>
                    </div>
                    <div>
                        <div class="form-label">{{ app()->getLocale() == 'ar' ? 'المبلغ' : 'Amount' }}</div>
                        <strong style="color: var(--success); font-size: 18px;">${{ number_format($order->amount, 2) }}</strong>
                        <span style="color: var(--text-muted); font-size: 12px;">{{ strtoupper($order->currency) }}</span>
                    </div>
                    @if($order->previousPlan)
                    <div>
                        <div class="form-label">{{ app()->getLocale() == 'ar' ? 'الخطة السابقة' : 'Previous Plan' }}</div>
                        <span style="color: var(--text-secondary);">{{ $order->previousPlan->name }}</span>
                    </div>
                    @endif
                    <div>
                        <div class="form-label">{{ app()->getLocale() == 'ar' ? 'تاريخ الطلب' : 'Order Date' }}</div>
                        <span style="color: var(--text-secondary);">{{ $order->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>

                @if($order->payment_reference)
                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <div class="form-label">{{ app()->getLocale() == 'ar' ? 'مرجع الدفع' : 'Payment Reference' }}</div>
                    <div style="background: var(--bg-primary); padding: 10px 14px; border-radius: 8px; font-family: monospace; color: var(--text-primary);">
                        {{ $order->payment_reference }}
                    </div>
                </div>
                @endif

                @if($order->customer_notes)
                <div style="margin-top: 16px;">
                    <div class="form-label">{{ app()->getLocale() == 'ar' ? 'ملاحظات العميل' : 'Customer Notes' }}</div>
                    <div style="background: var(--bg-primary); padding: 10px 14px; border-radius: 8px; color: var(--text-secondary);">
                        {{ $order->customer_notes }}
                    </div>
                </div>
                @endif

                @if($order->rejection_reason)
                <div style="margin-top: 16px;">
                    <div class="form-label" style="color: var(--danger);">{{ app()->getLocale() == 'ar' ? 'سبب الرفض' : 'Rejection Reason' }}</div>
                    <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); padding: 10px 14px; border-radius: 8px; color: #f87171;">
                        {{ $order->rejection_reason }}
                    </div>
                </div>
                @endif

                @if($order->admin_notes)
                <div style="margin-top: 16px;">
                    <div class="form-label">{{ app()->getLocale() == 'ar' ? 'ملاحظات المشرف' : 'Admin Notes' }}</div>
                    <div style="background: var(--bg-primary); padding: 10px 14px; border-radius: 8px; color: var(--text-secondary);">
                        {{ $order->admin_notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Company Info -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">{{ app()->getLocale() == 'ar' ? 'معلومات الشركة' : 'Company Information' }}</div>
                <a href="{{ route('super-admin.tenants.edit', $order->company->id ?? 0) }}" class="btn btn-secondary btn-sm">
                    {{ app()->getLocale() == 'ar' ? 'عرض الشركة' : 'View Company' }}
                </a>
            </div>
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div class="company-avatar" style="width: 48px; height: 48px; font-size: 18px;">{{ substr($order->company->name ?? '?', 0, 1) }}</div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary);">{{ $order->company->name ?? '-' }}</h3>
                        <div style="font-size: 13px; color: var(--text-muted);">{{ $order->company->email ?? '-' }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            {{ app()->getLocale() == 'ar' ? 'الخطة الحالية:' : 'Current plan:' }}
                            <strong>{{ $order->company->plan->name ?? '-' }}</strong>
                            &middot;
                            {{ app()->getLocale() == 'ar' ? 'الحالة:' : 'Status:' }}
                            <strong>{{ $order->company->stripe_subscription_status ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Timeline -->
        @if($order->approved_at)
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{ app()->getLocale() == 'ar' ? 'السجل الزمني' : 'Timeline' }}</div>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(99,102,241,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="16" height="16" fill="none" stroke="var(--primary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ app()->getLocale() == 'ar' ? 'تم إنشاء الطلب' : 'Order Created' }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $order->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        @if($order->isApproved())
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16,185,129,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--success);">{{ app()->getLocale() == 'ar' ? 'تمت الموافقة' : 'Approved' }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $order->approved_at->format('M d, Y H:i') }}</div>
                        </div>
                        @elseif($order->isRejected())
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="16" height="16" fill="none" stroke="var(--danger)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--danger);">{{ app()->getLocale() == 'ar' ? 'تم الرفض' : 'Rejected' }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $order->approved_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar: Actions -->
    <div>
        @if($order->isPending())
        <!-- Approve Form -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title" style="color: var(--success);">
                    ✅ {{ app()->getLocale() == 'ar' ? 'الموافقة على الطلب' : 'Approve Order' }}
                </div>
            </div>
            <div class="card-body">
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
                    {{ app()->getLocale() == 'ar'
                        ? 'الموافقة ستفعّل الاشتراك فوراً وستنشئ فاتورة تلقائياً.'
                        : 'Approving will activate the subscription immediately and create an invoice.' }}
                </p>
                <form action="{{ route('super-admin.orders.approve', $order) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من الموافقة؟' : 'Are you sure you want to approve?' }}');">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'ملاحظات المشرف (اختياري)' : 'Admin Notes (optional)' }}</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="{{ app()->getLocale() == 'ar' ? 'أضف ملاحظات...' : 'Add notes...' }}"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--success);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ app()->getLocale() == 'ar' ? 'الموافقة وتفعيل الاشتراك' : 'Approve & Activate' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Reject Form -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title" style="color: var(--danger);">
                    ❌ {{ app()->getLocale() == 'ar' ? 'رفض الطلب' : 'Reject Order' }}
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('super-admin.orders.reject', $order) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من رفض الطلب؟' : 'Are you sure you want to reject?' }}');">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'سبب الرفض (مطلوب)' : 'Rejection Reason (required)' }}</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{ app()->getLocale() == 'ar' ? 'اذكر سبب الرفض...' : 'Explain why this order is being rejected...' }}"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'ملاحظات إضافية (اختياري)' : 'Additional Notes (optional)' }}</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="{{ app()->getLocale() == 'ar' ? 'ملاحظات...' : 'Notes...' }}"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        {{ app()->getLocale() == 'ar' ? 'رفض الطلب' : 'Reject Order' }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Plan Details -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{ app()->getLocale() == 'ar' ? 'تفاصيل الخطة' : 'Plan Details' }}</div>
            </div>
            <div class="card-body">
                @if($order->plan)
                <div style="margin-bottom: 12px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--primary);">{{ $order->plan->name }}</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">{{ app()->getLocale() == 'ar' ? 'السعر الشهري' : 'Monthly' }}</span>
                        <strong style="color: var(--text-primary);">${{ number_format($order->plan->price_monthly, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">{{ app()->getLocale() == 'ar' ? 'السعر السنوي' : 'Yearly' }}</span>
                        <strong style="color: var(--text-primary);">${{ number_format($order->plan->price_yearly, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">{{ app()->getLocale() == 'ar' ? 'الأجهزة' : 'Devices' }}</span>
                        <strong style="color: var(--text-primary);">{{ $order->plan->max_devices == -1 ? '∞' : $order->plan->max_devices }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">{{ app()->getLocale() == 'ar' ? 'الموظفون' : 'Employees' }}</span>
                        <strong style="color: var(--text-primary);">{{ $order->plan->max_employees == -1 ? '∞' : $order->plan->max_employees }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($order->isApproved() && $order->invoice)
<div class="card mt-4">
    <div class="card-header">
        <div class="card-title">{{ app()->getLocale() == 'ar' ? 'الفاتورة المرتبطة' : 'Related Invoice' }}</div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; font-size: 13px;">
            <div>
                <div class="form-label">{{ app()->getLocale() == 'ar' ? 'رقم الفاتورة' : 'Invoice #' }}</div>
                <strong style="color: var(--text-primary);">{{ $order->invoice->invoice_number }}</strong>
            </div>
            <div>
                <div class="form-label">{{ app()->getLocale() == 'ar' ? 'المبلغ' : 'Amount' }}</div>
                <strong style="color: var(--text-primary);">${{ number_format($order->invoice->amount, 2) }}</strong>
            </div>
            <div>
                <div class="form-label">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</div>
                <span class="badge badge-success">{{ app()->getLocale() == 'ar' ? 'مدفوعة' : 'Paid' }}</span>
            </div>
            <div>
                <div class="form-label">{{ app()->getLocale() == 'ar' ? 'الفترة' : 'Period' }}</div>
                <span style="color: var(--text-secondary);">{{ optional($order->invoice->period_start)->format('M d') }} - {{ optional($order->invoice->period_end)->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
</div>
@endif

<style>
    @media (max-width: 1024px) {
        .card-body [style*="grid-template-columns: 2fr 1fr"],
        [style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
