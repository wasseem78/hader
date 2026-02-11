@extends('layouts.super-admin')

@section('title', 'Edit Plan')

@section('header')
    Edit Plan: {{ $plan->name }}
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('super-admin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Plan Name</label>
                    <input type="text" name="name" value="{{ $plan->name }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" value="{{ $plan->slug }}" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="form-group">
                    <label>Monthly Price ($)</label>
                    <input type="number" step="0.01" name="price_monthly" value="{{ $plan->price_monthly }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Yearly Price ($)</label>
                    <input type="number" step="0.01" name="price_yearly" value="{{ $plan->price_yearly }}" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="form-group">
                    <label>Stripe Monthly Price ID</label>
                    <input type="text" name="stripe_price_monthly_id" value="{{ $plan->stripe_price_monthly_id }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>Stripe Yearly Price ID</label>
                    <input type="text" name="stripe_price_yearly_id" value="{{ $plan->stripe_price_yearly_id }}" class="form-control">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="form-group">
                    <label>Max Employees</label>
                    <input type="number" name="max_employees" value="{{ $plan->max_employees }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Max Devices</label>
                    <input type="number" name="max_devices" value="{{ $plan->max_devices }}" class="form-control" required>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
                    Active
                </label>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 10px;">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" {{ $plan->is_featured ? 'checked' : '' }}>
                    Featured Plan
                </label>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Update Plan</button>
                <a href="{{ route('super-admin.plans.index') }}" class="btn" style="background: transparent; border: 1px solid var(--glass-border);">Cancel</a>
            </div>
        </form>

        <form action="{{ route('super-admin.plans.destroy', $plan) }}" method="POST" style="margin-top: 40px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);" onclick="return confirm('Are you sure you want to delete this plan?')">
                Delete Plan
            </button>
        </form>
    </div>
</div>
@endsection
