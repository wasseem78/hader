@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="header">
    <div>
        <h1>User Management</h1>
        <p class="header-date">Manage system administrators and managers</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.users.invite') }}" class="btn btn-primary">
            <span>+</span> Invite User
        </a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="status-badge status-neutral">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($user->email_verified_at)
                            <span class="status-badge status-success">Active</span>
                        @else
                            <span class="status-badge status-warning">Pending Invite</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; margin-bottom: 10px;">👥</div>
                        <p style="color: #64748b;">No admin users found.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($users->hasPages())
        <div style="padding: 20px;">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
