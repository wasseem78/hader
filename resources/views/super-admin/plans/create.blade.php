@extends('layouts.super-admin')

@section('title', 'Create Plan')
@section('header', 'Create New Plan')

@section('content')
<div style="max-width: 700px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Plan Details</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.plans.store') }}" method="POST">
                @csrf
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Professional" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. professional" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Monthly Price ($)</label>
                        <input type="number" step="0.01" name="price_monthly" class="form-control" placeholder="29.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Yearly Price ($)</label>
                        <input type="number" step="0.01" name="price_yearly" class="form-control" placeholder="290.00" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Stripe Monthly Price ID</label>
                        <input type="text" name="stripe_price_monthly_id" class="form-control" placeholder="price_xxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stripe Yearly Price ID</label>
                        <input type="text" name="stripe_price_yearly_id" class="form-control" placeholder="price_xxx">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Max Employees</label>
                        <input type="number" name="max_employees" class="form-control" placeholder="50" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Devices</label>
                        <input type="number" name="max_devices" class="form-control" placeholder="5" required>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Active
                    </label>
                    <label class="checkbox-label" style="margin-top: 10px;">
                        <input type="checkbox" name="is_featured" value="1">
                        Featured Plan
                    </label>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                    <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
