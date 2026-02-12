@extends('layouts.tenant')

@section('title', __('messages.billing_subscription'))

@section('header')
    <h1>{{ __('messages.billing_subscription') }}</h1>
    <p>{{ __('messages.manage_subscription_desc') }}</p>
@endsection

@section('content')

{{-- Success / Error Messages --}}
@if(session('success'))
<div class="alert alert-success" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 20px;">✅</span>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 20px;">❌</span>
    <span>{{ session('error') }}</span>
</div>
@endif
@if(session('info'))
<div class="alert alert-info" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); color: #818cf8; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 20px;">ℹ️</span>
    <span>{{ session('info') }}</span>
</div>
@endif

{{-- ====================================================================== --}}
{{-- Subscription Status Card                                               --}}
{{-- ====================================================================== --}}
<div class="card" style="margin-bottom: 24px; overflow: hidden;">
    {{-- Status Header Banner --}}
    <div style="padding: 24px 28px; background: linear-gradient(135deg,
        @if($subscriptionInfo['status_color'] === 'success') rgba(52,211,153,0.08), rgba(16,185,129,0.04)
        @elseif($subscriptionInfo['status_color'] === 'warning') rgba(251,191,36,0.08), rgba(245,158,11,0.04)
        @elseif($subscriptionInfo['status_color'] === 'info') rgba(99,102,241,0.08), rgba(79,70,229,0.04)
        @else rgba(239,68,68,0.08), rgba(220,38,38,0.04)
        @endif
    ); border-bottom: 1px solid var(--glass-border);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <h2 style="font-size: 22px; font-weight: 700; color: var(--text-primary); margin: 0;">
                        {{ __('messages.subscription_status') }}
                    </h2>
                    <span class="badge badge-{{ $subscriptionInfo['status_color'] }}" style="font-size: 12px; padding: 4px 12px;">
                        {{ $subscriptionInfo['status_label'] }}
                    </span>
                </div>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                    {{ __('messages.current_plan') }}: <strong style="color: var(--text-primary);">{{ $subscriptionInfo['plan_name'] }}</strong>
                </p>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @if($subscriptionInfo['can_renew'])
                    <button onclick="document.getElementById('renewModal').style.display='flex'" class="btn btn-primary btn-sm">
                        🔄 {{ __('messages.renew_subscription') }}
                    </button>
                @endif
                @if($subscriptionInfo['can_cancel'])
                    <button onclick="document.getElementById('cancelModal').style.display='flex'" class="btn btn-secondary btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3);">
                        {{ __('messages.cancel_subscription') }}
                    </button>
                @endif
                <a href="{{ route('billing.invoices') }}" class="btn btn-secondary btn-sm">
                    📋 {{ __('messages.view_invoices') }}
                </a>
                <a href="{{ route('billing.orders') }}" class="btn btn-secondary btn-sm">
                    📦 {{ __('messages.my_orders') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 28px;">
        {{-- Subscription Details Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 28px;">
            {{-- Plan Name --}}
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    {{ __('messages.plan') }}
                </div>
                <div style="font-size: 20px; font-weight: 700; color: var(--primary-light);">
                    {{ $currentPlan->name ?? '—' }}
                </div>
                @if($currentPlan && !$currentPlan->isFree())
                    <div style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
                        ${{ number_format($currentPlan->price_monthly, 2) }}/{{ __('messages.month') }}
                    </div>
                @endif
            </div>

            {{-- Subscription Status --}}
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    {{ __('messages.status') }}
                </div>
                <div style="font-size: 20px; font-weight: 700; color:
                    @if($subscriptionInfo['status_color'] === 'success') #34d399
                    @elseif($subscriptionInfo['status_color'] === 'warning') #fbbf24
                    @elseif($subscriptionInfo['status_color'] === 'info') #818cf8
                    @else #ef4444
                    @endif;">
                    @if($subscriptionInfo['is_trial'])
                        {{ __('messages.trial') }}
                    @elseif($subscriptionInfo['is_active'] && !$subscriptionInfo['is_cancelled'])
                        {{ __('messages.active') }}
                    @elseif($subscriptionInfo['is_cancelled'])
                        {{ __('messages.cancelled') }}
                    @elseif($subscriptionInfo['is_expired'])
                        {{ __('messages.expired') }}
                    @elseif($subscriptionInfo['is_free'])
                        {{ __('messages.free') }}
                    @else
                        {{ __('messages.inactive') }}
                    @endif
                </div>
            </div>

            {{-- Subscription End / Trial End --}}
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    @if($subscriptionInfo['is_trial'])
                        {{ __('messages.trial_ends') }}
                    @else
                        {{ __('messages.renewal_date') }}
                    @endif
                </div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary);">
                    @if($subscriptionInfo['subscription_end'])
                        {{ $subscriptionInfo['subscription_end']->format('d M Y') }}
                    @elseif($subscriptionInfo['is_free'])
                        ∞
                    @else
                        —
                    @endif
                </div>
                @if($subscriptionInfo['days_remaining'] > 0 || $subscriptionInfo['trial_days_remaining'] > 0)
                    <div style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
                        {{ $subscriptionInfo['is_trial'] ? $subscriptionInfo['trial_days_remaining'] : $subscriptionInfo['days_remaining'] }}
                        {{ __('messages.days_remaining') }}
                    </div>
                @endif
            </div>

            {{-- Days Remaining Visual --}}
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    {{ __('messages.days_left') }}
                </div>
                <div style="font-size: 20px; font-weight: 700; color:
                    @if($subscriptionInfo['is_free']) #818cf8
                    @elseif(($subscriptionInfo['days_remaining'] ?? 0) > 7 || ($subscriptionInfo['trial_days_remaining'] ?? 0) > 7) #34d399
                    @elseif(($subscriptionInfo['days_remaining'] ?? 0) > 0 || ($subscriptionInfo['trial_days_remaining'] ?? 0) > 0) #fbbf24
                    @else #ef4444
                    @endif;">
                    @if($subscriptionInfo['is_free'])
                        ∞
                    @else
                        {{ $subscriptionInfo['is_trial'] ? $subscriptionInfo['trial_days_remaining'] : $subscriptionInfo['days_remaining'] }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Pending Orders Banner --}}
        @if($pendingOrders->count() > 0)
        <div style="background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <span style="font-size: 24px;">⏳</span>
                <div>
                    <strong style="color: #fbbf24;">{{ __('messages.pending_orders_title') }}</strong>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 2px 0 0;">{{ __('messages.pending_orders_desc') }}</p>
                </div>
            </div>
            @foreach($pendingOrders as $pendingOrder)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: rgba(0,0,0,0.2); border-radius: 8px; margin-bottom: 6px;">
                <div>
                    <span style="color: var(--text-primary); font-weight: 600; font-size: 13px;">{{ $pendingOrder->plan->name ?? '—' }}</span>
                    <span style="color: var(--text-muted); font-size: 12px; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">{{ $pendingOrder->getTypeLabel() }}</span>
                    <span style="color: var(--primary-light); font-weight: 600; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">${{ number_format($pendingOrder->amount, 2) }}</span>
                </div>
                <a href="{{ route('billing.order.show', $pendingOrder) }}" class="btn btn-primary btn-sm">{{ __('messages.view_order') }}</a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Trial Warning --}}
        @if($subscriptionInfo['is_trial'] && $subscriptionInfo['trial_days_remaining'] <= 3)
        <div style="background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong style="color: #fbbf24;">{{ __('messages.trial_expiring_soon') }}</strong>
                <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0;">
                    {{ __('messages.trial_expiring_desc') }}
                </p>
            </div>
        </div>
        @endif

        {{-- Expired Warning --}}
        @if($subscriptionInfo['is_expired'])
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">🚨</span>
            <div>
                <strong style="color: #ef4444;">{{ __('messages.subscription_expired_title') }}</strong>
                <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0;">
                    {{ __('messages.subscription_expired_desc') }}
                </p>
            </div>
        </div>
        @endif

        {{-- Usage Statistics --}}
        <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px;">
            📊 {{ __('messages.usage_statistics') }}
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            {{-- Devices Usage --}}
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <div>
                        <span style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">{{ __('messages.devices') }}</span>
                    </div>
                    <span style="color: var(--text-primary); font-weight: 700; font-size: 16px;">
                        {{ $usageStats['devices_count'] }} / {{ $usageStats['max_devices'] }}
                    </span>
                </div>
                @php $devicePct = $usageStats['devices_percent']; @endphp
                <div class="progress-bar" style="height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; border-radius: 4px; transition: width 0.5s; width: {{ $devicePct }}%;
                        background: {{ $devicePct >= 90 ? '#ef4444' : ($devicePct >= 70 ? '#fbbf24' : '#34d399') }};"></div>
                </div>
                <div style="color: var(--text-muted); font-size: 12px; margin-top: 8px;">
                    {{ $devicePct }}% {{ __('messages.used') }}
                    @if($devicePct >= 90)
                        — <span style="color: #ef4444;">{{ __('messages.almost_full') }}</span>
                    @endif
                </div>
            </div>

            {{-- Employees Usage --}}
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <div>
                        <span style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">{{ __('messages.employees') }}</span>
                    </div>
                    <span style="color: var(--text-primary); font-weight: 700; font-size: 16px;">
                        {{ $usageStats['employees_count'] }} / {{ $usageStats['max_employees'] }}
                    </span>
                </div>
                @php $empPct = $usageStats['employees_percent']; @endphp
                <div class="progress-bar" style="height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; border-radius: 4px; transition: width 0.5s; width: {{ $empPct }}%;
                        background: {{ $empPct >= 90 ? '#ef4444' : ($empPct >= 70 ? '#fbbf24' : '#818cf8') }};"></div>
                </div>
                <div style="color: var(--text-muted); font-size: 12px; margin-top: 8px;">
                    {{ $empPct }}% {{ __('messages.used') }}
                    @if($empPct >= 90)
                        — <span style="color: #ef4444;">{{ __('messages.almost_full') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- Available Plans                                                        --}}
{{-- ====================================================================== --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0;">
        {{ __('messages.available_plans') }}
    </h2>
    {{-- Billing Cycle Toggle --}}
    <div style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); padding: 4px; border-radius: 8px; border: 1px solid var(--glass-border);">
        <button id="btn-monthly" onclick="toggleBillingCycle('monthly')" class="btn btn-sm" style="padding: 6px 16px; font-size: 13px; border-radius: 6px; background: var(--primary); color: white; border: none;">
            {{ __('messages.monthly') }}
        </button>
        <button id="btn-yearly" onclick="toggleBillingCycle('yearly')" class="btn btn-sm" style="padding: 6px 16px; font-size: 13px; border-radius: 6px; background: transparent; color: var(--text-secondary); border: none;">
            {{ __('messages.yearly') }}
            <span style="background: rgba(52,211,153,0.2); color: #34d399; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 4px;">-17%</span>
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 32px;">
    @foreach($plans as $plan)
    <div class="card" style="position: relative; transition: all 0.3s ease; overflow: hidden;
        {{ $plan->is_featured ? 'border: 2px solid rgba(99,102,241,0.5); box-shadow: 0 0 30px rgba(99,102,241,0.1);' : '' }}
        {{ $currentPlan && $currentPlan->id === $plan->id ? 'border: 2px solid rgba(52,211,153,0.5);' : '' }}"
        onmouseover="this.style.transform='translateY(-4px)'"
        onmouseout="this.style.transform='translateY(0)'">

        {{-- Featured Badge --}}
        @if($plan->is_featured)
        <div style="position: absolute; top: 0; {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 6px 16px; font-size: 11px; font-weight: 700; border-radius: 0 0 0 12px; text-transform: uppercase; letter-spacing: 0.5px;">
            ⭐ {{ __('messages.popular') }}
        </div>
        @endif

        {{-- Current Plan Badge --}}
        @if($currentPlan && $currentPlan->id === $plan->id)
        <div style="position: absolute; top: 0; {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0; background: linear-gradient(135deg, #10b981, #34d399); color: white; padding: 6px 16px; font-size: 11px; font-weight: 700; border-radius: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px;">
            ✓ {{ __('messages.current') }}
        </div>
        @endif

        <div class="card-body" style="display: flex; flex-direction: column; height: 100%; padding: 28px;">
            <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">{{ $plan->name }}</h3>
            <p style="color: var(--text-muted); font-size: 13px; min-height: 40px; line-height: 1.5;">{{ $plan->description }}</p>

            {{-- Price --}}
            <div style="margin: 24px 0; padding: 16px 0; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border);">
                <div class="price-monthly" style="display: flex; align-items: baseline; gap: 4px;">
                    <span style="font-size: 38px; font-weight: 800; color: var(--text-primary);">${{ number_format($plan->price_monthly, 0) }}</span>
                    <span style="color: var(--text-muted); font-size: 14px;">/{{ __('messages.month') }}</span>
                </div>
                <div class="price-yearly" style="display: none; align-items: baseline; gap: 4px;">
                    <span style="font-size: 38px; font-weight: 800; color: var(--text-primary);">${{ number_format($plan->price_yearly / 12, 0) }}</span>
                    <span style="color: var(--text-muted); font-size: 14px;">/{{ __('messages.month') }}</span>
                    @if($plan->price_yearly > 0)
                    <span style="color: #34d399; font-size: 12px; font-weight: 600; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">
                        {{ __('messages.save') }} ${{ number_format($plan->getYearlySavings(), 0) }}/{{ __('messages.year') }}
                    </span>
                    @endif
                </div>
                @if($plan->isFree())
                    <span style="color: #34d399; font-size: 13px; font-weight: 600;">{{ __('messages.free_forever') }}</span>
                @endif
            </div>

            {{-- Features --}}
            <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; flex-grow: 1;">
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span>
                    <strong>{{ $plan->max_devices }}</strong>&nbsp;{{ __('messages.devices_limit') }}
                </li>
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span>
                    <strong>{{ $plan->max_employees }}</strong>&nbsp;{{ __('messages.employees_limit') }}
                </li>
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span>
                    <strong>{{ $plan->max_users }}</strong>&nbsp;{{ __('messages.users_limit') }}
                </li>
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span>
                    <strong>{{ $plan->retention_days }}</strong>&nbsp;{{ __('messages.days_history') }}
                </li>
                @if($plan->api_access)
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span> {{ __('messages.api_access') }}
                </li>
                @endif
                @if($plan->advanced_reports)
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span> {{ __('messages.advanced_reports') }}
                </li>
                @endif
                @if($plan->custom_branding)
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span> {{ __('messages.custom_branding') }}
                </li>
                @endif
                @if($plan->priority_support)
                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: var(--text-secondary);">
                    <span style="color: #34d399; font-size: 14px;">✓</span> {{ __('messages.priority_support') }}
                </li>
                @endif
            </ul>

            {{-- CTA Button --}}
            @if($currentPlan && $currentPlan->id === $plan->id)
                <button class="btn btn-secondary" style="width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>
                    ✓ {{ __('messages.current_plan') }}
                </button>
            @elseif($plan->isFree())
                <form action="{{ route('billing.subscribe', ['plan' => $plan->uuid]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="billing_cycle" class="billing-cycle-input" value="monthly">
                    <button type="submit" class="btn btn-secondary" style="width: 100%; text-align: center;">
                        {{ $currentPlan && $currentPlan->price_monthly > $plan->price_monthly ? __('messages.downgrade') : __('messages.select_plan') }}
                    </button>
                </form>
            @else
                {{-- Paid plans go through checkout --}}
                <a href="{{ route('billing.checkout', ['plan' => $plan->uuid, 'billing_cycle' => 'monthly']) }}" class="btn btn-primary checkout-link" style="width: 100%; text-align: center;">
                    @if(!$currentPlan || $currentPlan->price_monthly < $plan->price_monthly)
                        🚀 {{ __('messages.upgrade_now') }}
                    @else
                        {{ __('messages.switch_plan') }}
                    @endif
                </a>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ====================================================================== --}}
{{-- Plan Comparison Table                                                  --}}
{{-- ====================================================================== --}}
@if($plans->count() > 1)
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3>📋 {{ __('messages.plan_comparison') }}</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="min-width: 180px;">{{ __('messages.feature') }}</th>
                    @foreach($plans as $plan)
                    <th style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.05);' : '' }}">
                        {{ $plan->name }}
                        @if($currentPlan && $currentPlan->id === $plan->id)
                            <br><span class="badge badge-success" style="font-size: 10px;">{{ __('messages.current') }}</span>
                        @endif
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.monthly_price') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">
                        <strong style="color: var(--primary-light);">${{ number_format($plan->price_monthly, 0) }}</strong>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.devices') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">{{ $plan->max_devices }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.employees') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">{{ $plan->max_employees }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.users_limit') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">{{ $plan->max_users }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.data_retention') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">{{ $plan->retention_days }} {{ __('messages.days') }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.api_access') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">
                        {!! $plan->api_access ? '<span style="color:#34d399;font-size:16px;">✓</span>' : '<span style="color:var(--text-muted);">—</span>' !!}
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.advanced_reports') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">
                        {!! $plan->advanced_reports ? '<span style="color:#34d399;font-size:16px;">✓</span>' : '<span style="color:var(--text-muted);">—</span>' !!}
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.custom_branding') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">
                        {!! $plan->custom_branding ? '<span style="color:#34d399;font-size:16px;">✓</span>' : '<span style="color:var(--text-muted);">—</span>' !!}
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight: 600;">{{ __('messages.priority_support') }}</td>
                    @foreach($plans as $plan)
                    <td style="text-align: center; {{ $currentPlan && $currentPlan->id === $plan->id ? 'background: rgba(99,102,241,0.03);' : '' }}">
                        {!! $plan->priority_support ? '<span style="color:#34d399;font-size:16px;">✓</span>' : '<span style="color:var(--text-muted);">—</span>' !!}
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ====================================================================== --}}
{{-- Renew Modal                                                            --}}
{{-- ====================================================================== --}}
<div id="renewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);" onclick="if(event.target===this)this.style.display='none'">
    <div class="card" style="max-width: 480px; width: 90%; margin: 20px;">
        <div class="card-header">
            <h3>🔄 {{ __('messages.renew_subscription') }}</h3>
            <button onclick="document.getElementById('renewModal').style.display='none'" style="background: none; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <div class="card-body">
            @if($currentPlan && !$currentPlan->isFree())
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                {{ __('messages.renew_description') }}
            </p>
            <form action="{{ route('billing.renew-checkout') }}" method="GET">
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 8px; border: 2px solid var(--glass-border); cursor: pointer; transition: border-color 0.2s;" onclick="this.querySelector('input').checked=true; this.parentElement.querySelectorAll('label').forEach(l=>l.style.borderColor='var(--glass-border)'); this.style.borderColor='rgba(99,102,241,0.5)';">
                        <input type="radio" name="billing_cycle" value="monthly" checked style="accent-color: #6366f1;">
                        <div>
                            <strong style="color: var(--text-primary);">{{ __('messages.monthly') }}</strong>
                            <span style="color: var(--primary-light); font-weight: 700; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">${{ number_format($currentPlan->price_monthly, 2) }}/{{ __('messages.month') }}</span>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 8px; border: 2px solid var(--glass-border); cursor: pointer; transition: border-color 0.2s;" onclick="this.querySelector('input').checked=true; this.parentElement.querySelectorAll('label').forEach(l=>l.style.borderColor='var(--glass-border)'); this.style.borderColor='rgba(99,102,241,0.5)';">
                        <input type="radio" name="billing_cycle" value="yearly" style="accent-color: #6366f1;">
                        <div>
                            <strong style="color: var(--text-primary);">{{ __('messages.yearly') }}</strong>
                            <span style="color: var(--primary-light); font-weight: 700; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">${{ number_format($currentPlan->price_yearly, 2) }}/{{ __('messages.year') }}</span>
                            @if($currentPlan->getYearlySavings() > 0)
                            <span style="background: rgba(52,211,153,0.2); color: #34d399; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                                {{ __('messages.save') }} ${{ number_format($currentPlan->getYearlySavings(), 0) }}
                            </span>
                            @endif
                        </div>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🔄 {{ __('messages.proceed_to_checkout') }}
                </button>
            </form>
            @else
            <p style="color: var(--text-muted);">{{ __('messages.select_paid_plan') }}</p>
            @endif
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- Cancel Modal                                                           --}}
{{-- ====================================================================== --}}
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);" onclick="if(event.target===this)this.style.display='none'">
    <div class="card" style="max-width: 480px; width: 90%; margin: 20px;">
        <div class="card-header">
            <h3 style="color: #ef4444;">⚠️ {{ __('messages.cancel_subscription') }}</h3>
            <button onclick="document.getElementById('cancelModal').style.display='none'" style="background: none; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <div class="card-body">
            <p style="color: var(--text-secondary); margin-bottom: 8px;">
                {{ __('messages.cancel_warning') }}
            </p>
            @if($company->subscription_ends_at)
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">
                {{ __('messages.cancel_access_until') }} <strong style="color: var(--text-primary);">{{ $company->subscription_ends_at->format('d M Y') }}</strong>
            </p>
            @endif
            <div style="display: flex; gap: 12px;">
                <button onclick="document.getElementById('cancelModal').style.display='none'" class="btn btn-secondary" style="flex: 1;">
                    {{ __('messages.keep_subscription') }}
                </button>
                <form action="{{ route('billing.cancel-subscription') }}" method="POST" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn" style="width: 100%; background: #ef4444; color: white; border: none; border-radius: 8px; padding: 10px 16px; cursor: pointer;">
                        {{ __('messages.confirm_cancel') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
function toggleBillingCycle(cycle) {
    const monthlyBtn = document.getElementById('btn-monthly');
    const yearlyBtn = document.getElementById('btn-yearly');
    const monthlyPrices = document.querySelectorAll('.price-monthly');
    const yearlyPrices = document.querySelectorAll('.price-yearly');
    const cycleInputs = document.querySelectorAll('.billing-cycle-input');

    if (cycle === 'yearly') {
        monthlyBtn.style.background = 'transparent';
        monthlyBtn.style.color = 'var(--text-secondary)';
        yearlyBtn.style.background = 'var(--primary)';
        yearlyBtn.style.color = 'white';
        monthlyPrices.forEach(el => el.style.display = 'none');
        yearlyPrices.forEach(el => el.style.display = 'flex');
    } else {
        monthlyBtn.style.background = 'var(--primary)';
        monthlyBtn.style.color = 'white';
        yearlyBtn.style.background = 'transparent';
        yearlyBtn.style.color = 'var(--text-secondary)';
        monthlyPrices.forEach(el => el.style.display = 'flex');
        yearlyPrices.forEach(el => el.style.display = 'none');
    }

    cycleInputs.forEach(input => input.value = cycle);

    // Update checkout links (for paid plans)
    document.querySelectorAll('.checkout-link').forEach(link => {
        const url = new URL(link.href);
        url.searchParams.set('billing_cycle', cycle);
        link.href = url.toString();
    });
}
</script>
@endsection

