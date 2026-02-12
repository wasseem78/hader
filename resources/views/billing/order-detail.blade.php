@extends('layouts.tenant')

@section('title', __('messages.order_details'))

@section('header')
    <h1>{{ __('messages.order_details') }}</h1>
@endsection

@section('content')

<a href="{{ route('billing.orders') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; margin-bottom: 24px; font-size: 14px;">
    ← {{ __('messages.back_to_orders') }}
</a>

{{-- Success / Info Messages --}}
@if(session('success'))
<div class="alert alert-success" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 20px;">✅</span>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('info'))
<div style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); color: #818cf8; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 20px;">ℹ️</span>
    <span>{{ session('info') }}</span>
</div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    {{-- Order Info --}}
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="padding: 20px 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700;">{{ __('messages.order') }} #{{ substr($order->uuid, 0, 8) }}</h3>
                    <span class="badge badge-{{ $order->getStatusColor() }}" style="font-size: 12px; padding: 5px 12px;">
                        {{ $order->getStatusLabel() }}
                    </span>
                </div>
            </div>
            <div class="card-body" style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">{{ __('messages.order_type') }}</div>
                        <div style="font-size: 16px; font-weight: 600; color: var(--text-primary);">{{ $order->getTypeLabel() }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">{{ __('messages.plan') }}</div>
                        <div style="font-size: 16px; font-weight: 600; color: var(--primary-light);">{{ $order->plan->name ?? '—' }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">{{ __('messages.billing_cycle_label') }}</div>
                        <div style="font-size: 16px; font-weight: 600; color: var(--text-primary);">{{ $order->billing_cycle === 'yearly' ? __('messages.yearly') : __('messages.monthly') }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">{{ __('messages.total_amount') }}</div>
                        <div style="font-size: 22px; font-weight: 800; color: #34d399;">${{ number_format($order->amount, 2) }}</div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div style="margin-bottom: 24px;">
                    <h4 style="font-size: 14px; color: var(--text-primary); margin-bottom: 16px;">{{ __('messages.order_timeline') }}</h4>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #34d399;"></div>
                            <div>
                                <span style="font-size: 13px; color: var(--text-primary);">{{ __('messages.order_created') }}</span>
                                <span style="font-size: 12px; color: var(--text-muted); margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                        @if($order->isApproved())
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #34d399;"></div>
                            <div>
                                <span style="font-size: 13px; color: var(--text-primary);">{{ __('messages.order_approved') }}</span>
                                <span style="font-size: 12px; color: var(--text-muted); margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">{{ $order->approved_at?->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                        @elseif($order->isRejected())
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></div>
                            <div>
                                <span style="font-size: 13px; color: #ef4444;">{{ __('messages.order_rejected') }}</span>
                                <span style="font-size: 12px; color: var(--text-muted); margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">{{ $order->updated_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                        @elseif($order->isPending())
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #fbbf24; animation: pulse 2s infinite;"></div>
                            <span style="font-size: 13px; color: #fbbf24;">{{ __('messages.awaiting_approval') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                @if($order->payment_reference)
                <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 10px; border: 1px solid var(--glass-border); margin-bottom: 16px;">
                    <div style="color: var(--text-muted); font-size: 12px; margin-bottom: 4px;">{{ __('messages.payment_reference_label') }}</div>
                    <div style="color: var(--text-primary); font-size: 14px;">{{ $order->payment_reference }}</div>
                </div>
                @endif

                @if($order->customer_notes)
                <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 10px; border: 1px solid var(--glass-border); margin-bottom: 16px;">
                    <div style="color: var(--text-muted); font-size: 12px; margin-bottom: 4px;">{{ __('messages.customer_notes_label') }}</div>
                    <div style="color: var(--text-primary); font-size: 14px;">{{ $order->customer_notes }}</div>
                </div>
                @endif

                @if($order->isRejected() && $order->rejection_reason)
                <div style="background: rgba(239, 68, 68, 0.1); padding: 16px; border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.3); margin-bottom: 16px;">
                    <div style="color: #ef4444; font-size: 12px; font-weight: 600; margin-bottom: 4px;">{{ __('messages.rejection_reason') }}</div>
                    <div style="color: var(--text-primary); font-size: 14px;">{{ $order->rejection_reason }}</div>
                </div>
                @endif

                @if($order->admin_notes)
                <div style="background: rgba(99,102,241,0.05); padding: 16px; border-radius: 10px; border: 1px solid rgba(99,102,241,0.2);">
                    <div style="color: #818cf8; font-size: 12px; font-weight: 600; margin-bottom: 4px;">{{ __('messages.admin_notes') }}</div>
                    <div style="color: var(--text-primary); font-size: 14px;">{{ $order->admin_notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        {{-- Actions --}}
        @if($order->isPending())
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-body" style="padding: 24px;">
                <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                    {{ __('messages.pending_order_info') }}
                </p>
                <form action="{{ route('billing.order.cancel', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn" style="width: 100%; background: #ef4444; color: white; border: none; border-radius: 8px; padding: 10px 16px; cursor: pointer;"
                        onclick="return confirm('{{ __('messages.confirm_cancel_order') }}')">
                        {{ __('messages.cancel_order') }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Bank Transfer Reminder --}}
        @if($order->isPending())
        <div class="card">
            <div class="card-header" style="padding: 16px 20px;">
                <h4 style="margin: 0; font-size: 14px;">🏦 {{ __('messages.payment_reminder') }}</h4>
            </div>
            <div class="card-body" style="padding: 20px;">
                <p style="color: var(--text-muted); font-size: 12px; line-height: 1.6;">
                    {{ __('messages.payment_reminder_text') }}
                </p>
                <div style="margin-top: 12px; font-size: 12px; color: var(--text-secondary);">
                    <div>📧 {{ __('messages.support_email_value') }}</div>
                    <div>📱 {{ __('messages.support_phone_value') }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>

@endsection
