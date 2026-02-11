@extends('layouts.super-admin')

@section('title', 'Edit Tenant')
@section('header', 'Edit Tenant')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit: {{ $tenant->name }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('super-admin.tenants.update', $tenant->uuid) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="form-control" required>
                    @error('name')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="form-control" required>
                    @error('email')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Subscription Plan</label>
                    <select name="plan_id" class="form-control">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ $tenant->plan_id == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (${{ number_format($plan->price_monthly, 2) }}/mo)
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Custom Domain (Optional)</label>
                    <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}" class="form-control" placeholder="company.example.com">
                    @error('domain')
                        <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn btn-primary">Update Tenant</button>
                    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
