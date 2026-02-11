@extends('layouts.tenant')

@section('title', __('messages.invoice_history'))

@section('header')
    <h1>{{ __('messages.invoice_history') }}</h1>
    <p>{{ __('messages.invoice_history_desc') }}</p>
@endsection

@section('content')

{{-- Subscription Summary Strip --}}
<div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div style="flex: 1; min-width: 200px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 24px;">📦</span>
        <div>
            <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">{{ __('messages.current_plan') }}</div>
            <div style="color: var(--text-primary); font-weight: 700; font-size: 16px;">{{ $currentPlan->name ?? '—' }}</div>
        </div>
    </div>
    <div style="flex: 1; min-width: 200px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 24px;">💰</span>
        <div>
            <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">{{ __('messages.total_paid') }}</div>
            <div style="color: #34d399; font-weight: 700; font-size: 16px;">${{ number_format($invoices->where('status', 'paid')->sum('total'), 2) }}</div>
        </div>
    </div>
    <div style="flex: 1; min-width: 200px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 24px;">📄</span>
        <div>
            <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">{{ __('messages.total_invoices') }}</div>
            <div style="color: var(--text-primary); font-weight: 700; font-size: 16px;">{{ $invoices->total() }}</div>
        </div>
    </div>
</div>

{{-- Invoices Table --}}
<div class="card">
    <div class="card-header">
        <h3>📋 {{ __('messages.invoices') }}</h3>
        <div class="action-btns">
            <a href="{{ route('billing.index') }}" class="btn btn-secondary btn-sm">
                ← {{ __('messages.back_to_billing') }}
            </a>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.invoice_number') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.plan') }}</th>
                    <th>{{ __('messages.period') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.payment_method') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <span style="font-weight: 600; color: var(--text-primary); font-family: monospace;">
                                {{ $invoice->number ?? '#' . $invoice->id }}
                            </span>
                        </td>
                        <td style="color: var(--text-secondary);">
                            {{ $invoice->invoice_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            <span style="color: var(--text-primary); font-weight: 500;">
                                {{ $invoice->plan?->name ?? '—' }}
                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 13px;">
                            @if($invoice->period_start && $invoice->period_end)
                                {{ $invoice->period_start->format('d/m/Y') }} — {{ $invoice->period_end->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 700; color: var(--primary-light); font-size: 15px;">
                                ${{ number_format($invoice->total, 2) }}
                            </span>
                            <span style="color: var(--text-muted); font-size: 11px;">{{ strtoupper($invoice->currency) }}</span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 13px;">
                            {{ $invoice->payment_method ? ucfirst($invoice->payment_method) : '—' }}
                        </td>
                        <td>
                            @if($invoice->status === 'paid')
                                <span class="badge badge-success">{{ __('messages.paid') }}</span>
                            @elseif($invoice->status === 'pending')
                                <span class="badge badge-warning">{{ __('messages.pending') }}</span>
                            @elseif($invoice->status === 'overdue')
                                <span class="badge badge-danger">{{ __('messages.overdue') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 60px 20px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                            <h3 style="color: var(--text-primary); margin-bottom: 8px;">{{ __('messages.no_invoices') }}</h3>
                            <p style="color: var(--text-muted); font-size: 14px;">{{ __('messages.no_invoices_desc') }}</p>
                            <a href="{{ route('billing.index') }}" class="btn btn-primary btn-sm" style="margin-top: 16px;">
                                {{ __('messages.view_plans') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($invoices->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--glass-border); display: flex; justify-content: center;">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection

