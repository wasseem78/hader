@extends('layouts.app')

@section('title', 'Invite User')

@section('content')
<div class="header">
    <div>
        <h1>Invite User</h1>
        <p class="header-date">Send an invitation to a new administrator</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            ← Back to List
        </a>
    </div>
</div>

<div class="form-card">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Jane Smith" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. jane@company.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
                <option value="">Select a role...</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
            <p style="font-size: 12px; color: #64748b; margin-top: 5px;">
                <strong>Company Admin:</strong> Full access to company settings and employees.<br>
                <strong>Manager:</strong> Can manage employees and attendance, but not company settings.
            </p>
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Send Invitation</button>
        </div>
    </form>
</div>
@endsection
