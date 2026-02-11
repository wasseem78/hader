@extends('layouts.super-admin')

@section('title', 'Add Tenant')
@section('header', 'Add New Tenant')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Company Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('super-admin.tenants.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter company name" required autofocus>
                    @error('name')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="admin@company.com" required>
                    @error('email')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Subdomain</label>
                    <div class="input-group">
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="form-control" placeholder="company" required>
                        <span class="input-addon">.localhost:8000</span>
                    </div>
                    @error('subdomain')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Subscription Plan</label>
                    <select name="plan_id" class="form-control" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} (${{ number_format($plan->price_monthly, 2) }}/mo)</option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn btn-primary">Create Tenant</button>
                    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
