@extends('layouts.tenant')

@section('title', __('messages.my_orders'))

@section('header')
    <h1>{{ __('messages.my_orders') }}</h1>
    <p>{{ __('messages.my_orders_desc') }}</p>
@endsection

@section('content')

<a href="{{ route('billing.index') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; margin-bottom: 24px; font-size: 14px;">
    ← {{ __('messages.back_to_billing') }}
</a>

<div class="card">
    @if($orders->count())
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.order') }}</th>
                    <th>{{ __('messages.plan') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <span style="font-weight: 600; color: var(--text-primary);">#{{ substr($order->uuid, 0, 8) }}</span>
                    </td>
                    <td>{{ $order->plan->name ?? '—' }}</td>
                    <td>
                        <span style="font-size: 12px;">{{ $order->getTypeLabel() }}</span>
                    </td>
                    <td>
                        <strong style="color: var(--primary-light);">${{ number_format($order->amount, 2) }}</strong>
                        <span style="color: var(--text-muted); font-size: 11px;">/{{ $order->billing_cycle === 'yearly' ? __('messages.year') : __('messages.month') }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $order->getStatusColor() }}">{{ $order->getStatusLabel() }}</span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 12px;">
                        {{ $order->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <a href="{{ route('billing.order.show', $order) }}" class="btn btn-secondary btn-sm">
                            {{ __('messages.view') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding: 16px 20px;">
        {{ $orders->links() }}
    </div>
    @else
    <div style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;">📋</div>
        <h3 style="color: var(--text-primary); margin-bottom: 8px;">{{ __('messages.no_orders') }}</h3>
        <p style="color: var(--text-muted); font-size: 13px;">{{ __('messages.no_orders_desc') }}</p>
    </div>
    @endif
</div>

@endsection
