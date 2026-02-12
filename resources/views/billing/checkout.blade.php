@extends('layouts.tenant')

@section('title', __('messages.checkout'))

@section('header')
    <h1>{{ __('messages.checkout') }}</h1>
    <p>{{ __('messages.checkout_desc') }}</p>
@endsection

@section('content')

{{-- Back Button --}}
<a href="{{ route('billing.index') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; margin-bottom: 24px; font-size: 14px;">
    ← {{ __('messages.back_to_billing') }}
</a>

{{-- Already Pending Warning --}}
@if($existingPending)
<div style="background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 24px;">⏳</span>
    <div>
        <strong style="color: #fbbf24;">{{ __('messages.order_already_pending') }}</strong>
        <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0;">
            {{ __('messages.order_already_pending_desc') }}
        </p>
        <a href="{{ route('billing.order.show', $existingPending) }}" class="btn btn-primary btn-sm" style="margin-top: 12px;">
            {{ __('messages.view_order') }}
        </a>
    </div>
</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    {{-- ================================================================ --}}
    {{-- LEFT: Order Summary                                              --}}
    {{-- ================================================================ --}}
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="padding: 20px 24px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;">📋 {{ __('messages.order_summary') }}</h3>
            </div>
            <div class="card-body" style="padding: 24px;">
                {{-- Order Type Badge --}}
                <div style="margin-bottom: 20px;">
                    <span class="badge badge-{{ $orderType === 'upgrade' ? 'success' : ($orderType === 'renewal' ? 'info' : 'warning') }}" style="font-size: 12px; padding: 6px 14px;">
                        @if($orderType === 'upgrade') 🚀 {{ __('messages.order_type_upgrade') }}
                        @elseif($orderType === 'downgrade') ⬇️ {{ __('messages.order_type_downgrade') }}
                        @elseif($orderType === 'renewal') 🔄 {{ __('messages.order_type_renewal') }}
                        @else 🆕 {{ __('messages.order_type_new') }}
                        @endif
                    </span>
                </div>

                {{-- Plan Details --}}
                <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border); margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.plan') }}</span>
                        <strong style="color: var(--primary-light); font-size: 16px;">{{ $plan->name }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.billing_cycle_label') }}</span>
                        <strong style="color: var(--text-primary);">{{ $billingCycle === 'yearly' ? __('messages.yearly') : __('messages.monthly') }}</strong>
                    </div>
                    @if($currentPlan && $orderType !== 'new')
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.current_plan') }}</span>
                        <span style="color: var(--text-secondary);">{{ $currentPlan->name }}</span>
                    </div>
                    @endif
                </div>

                {{-- Plan Features --}}
                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 14px; color: var(--text-primary); margin-bottom: 12px;">{{ __('messages.plan_includes') }}</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: #34d399;">✓</span> <strong>{{ $plan->max_devices }}</strong>&nbsp;{{ __('messages.devices_limit') }}
                        </li>
                        <li style="display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: #34d399;">✓</span> <strong>{{ $plan->max_employees }}</strong>&nbsp;{{ __('messages.employees_limit') }}
                        </li>
                        <li style="display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: #34d399;">✓</span> <strong>{{ $plan->retention_days }}</strong>&nbsp;{{ __('messages.days_history') }}
                        </li>
                        @if($plan->api_access)
                        <li style="display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: #34d399;">✓</span> {{ __('messages.api_access') }}
                        </li>
                        @endif
                        @if($plan->advanced_reports)
                        <li style="display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: #34d399;">✓</span> {{ __('messages.advanced_reports') }}
                        </li>
                        @endif
                        @if($plan->priority_support)
                        <li style="display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: #34d399;">✓</span> {{ __('messages.priority_support') }}
                        </li>
                        @endif
                    </ul>
                </div>

                {{-- Price Summary --}}
                <div style="border-top: 2px solid var(--glass-border); padding-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 16px; font-weight: 600; color: var(--text-primary);">{{ __('messages.total_amount') }}</span>
                        <span style="font-size: 28px; font-weight: 800; color: var(--primary-light);">
                            ${{ number_format($price, 2) }}
                            <span style="font-size: 14px; font-weight: 400; color: var(--text-muted);">/ {{ $billingCycle === 'yearly' ? __('messages.year') : __('messages.month') }}</span>
                        </span>
                    </div>
                    @if($billingCycle === 'yearly' && $plan->getYearlySavings() > 0)
                    <div style="text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}; margin-top: 4px;">
                        <span style="background: rgba(52,211,153,0.2); color: #34d399; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            {{ __('messages.save') }} ${{ number_format($plan->getYearlySavings(), 0) }} {{ __('messages.per_year') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- RIGHT: Payment Instructions + Submit Form                        --}}
    {{-- ================================================================ --}}
    <div>
        {{-- Bank Transfer Info --}}
        <div class="card" style="margin-bottom: 24px; border: 2px solid rgba(99,102,241,0.3);">
            <div class="card-header" style="padding: 20px 24px; background: rgba(99,102,241,0.05);">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;">🏦 {{ __('messages.payment_instructions') }}</h3>
            </div>
            <div class="card-body" style="padding: 24px;">
                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px; line-height: 1.7;">
                    {{ __('messages.bank_transfer_intro') }}
                </p>

                {{-- Bank Details --}}
                <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border); margin-bottom: 20px;">
                    <h4 style="font-size: 14px; color: var(--primary-light); margin-bottom: 16px;">{{ __('messages.bank_details') }}</h4>
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.bank_name') }}</span>
                            <strong style="color: var(--text-primary); font-size: 13px;">{{ __('messages.bank_name_value') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.account_holder') }}</span>
                            <strong style="color: var(--text-primary); font-size: 13px;">{{ __('messages.account_holder_value') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.iban') }}</span>
                            <strong style="color: var(--text-primary); font-size: 13px; direction: ltr;">{{ __('messages.iban_value') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.swift_code') }}</span>
                            <strong style="color: var(--text-primary); font-size: 13px; direction: ltr;">{{ __('messages.swift_code_value') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                            <span style="color: var(--text-muted); font-size: 13px;">{{ __('messages.transfer_reference') }}</span>
                            <strong style="color: #fbbf24; font-size: 13px;">{{ $company->subdomain ?? $company->name }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div style="background: rgba(52,211,153,0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(52,211,153,0.2); margin-bottom: 20px;">
                    <h4 style="font-size: 14px; color: #34d399; margin-bottom: 12px;">📞 {{ __('messages.contact_info') }}</h4>
                    <div style="display: grid; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary);">
                            <span>📧</span> {{ __('messages.support_email_value') }}
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary);">
                            <span>📱</span> {{ __('messages.support_phone_value') }}
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary);">
                            <span>💬</span> {{ __('messages.support_whatsapp_value') }}
                        </div>
                    </div>
                </div>

                {{-- How it works --}}
                <div style="background: rgba(255,255,255,0.03); padding: 16px 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                    <h4 style="font-size: 13px; color: var(--text-primary); margin-bottom: 12px;">{{ __('messages.how_it_works') }}</h4>
                    <ol style="list-style: none; padding: 0; margin: 0; counter-reset: step;">
                        <li style="display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 12px; color: var(--text-muted);">
                            <span style="background: var(--primary); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">1</span>
                            {{ __('messages.step_1_submit') }}
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 12px; color: var(--text-muted);">
                            <span style="background: var(--primary); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">2</span>
                            {{ __('messages.step_2_transfer') }}
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 12px; color: var(--text-muted);">
                            <span style="background: var(--primary); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">3</span>
                            {{ __('messages.step_3_confirm') }}
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 12px; color: var(--text-muted);">
                            <span style="background: #34d399; color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">✓</span>
                            {{ __('messages.step_4_activated') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Submit Order Form --}}
        @unless($existingPending)
        <div class="card">
            <div class="card-body" style="padding: 24px;">
                <form action="{{ $orderType === 'renewal' ? route('billing.renew') : route('billing.subscribe', ['plan' => $plan->uuid]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="billing_cycle" value="{{ $billingCycle }}">

                    {{-- Payment Reference --}}
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px;">
                            {{ __('messages.payment_reference_label') }}
                        </label>
                        <input type="text" name="payment_reference" placeholder="{{ __('messages.payment_reference_placeholder') }}"
                            style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                        <p style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">{{ __('messages.payment_reference_hint') }}</p>
                    </div>

                    {{-- Customer Notes --}}
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px;">
                            {{ __('messages.customer_notes_label') }}
                        </label>
                        <textarea name="customer_notes" rows="3" placeholder="{{ __('messages.customer_notes_placeholder') }}"
                            style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: var(--text-primary); font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 700;">
                        📤 {{ __('messages.submit_order') }}
                    </button>
                    <p style="text-align: center; color: var(--text-muted); font-size: 11px; margin-top: 8px;">
                        {{ __('messages.submit_order_note') }}
                    </p>
                </form>
            </div>
        </div>
        @endunless
    </div>
</div>

@endsection
