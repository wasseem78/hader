@extends('layouts.tenant')

@section('title', __('messages.employees'))

@section('header')
    <h1>{{ __('messages.employees') }}</h1>
    <p>{{ __('messages.manage_team_members') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <span>+</span> {{ __('messages.add_employee') }}
    </a>
@endsection

@section('content')
<div class="table-container">
    <!-- Filters -->
    <div class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
        <form action="{{ route('employees.index') }}" method="GET" style="display: flex; gap: 12px; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                   class="form-control" placeholder="{{ __('messages.search_employees') }}" 
                   style="max-width: 250px;">
            <select name="branch" class="form-control" style="max-width: 200px;">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ ($filters['branch'] ?? '') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="department" value="{{ $filters['department'] ?? '' }}" 
                   class="form-control" placeholder="{{ __('messages.department') }}" 
                   style="max-width: 150px;">
            <button type="submit" class="btn btn-secondary">{{ __('messages.filter') }}</button>
            @if(!empty($filters['search']) || !empty($filters['branch']) || !empty($filters['department']))
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
            @endif
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.email') }}</th>
                <th>{{ __('messages.employee_id') }}</th>
                <th>{{ __('messages.branch') }}</th>
                <th>{{ __('messages.department') }}</th>
                <th>{{ __('messages.position') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="avatar-sm">{{ substr($employee->name, 0, 1) }}</div>
                            <span class="user-cell-name">{{ $employee->name }}</span>
                        </div>
                    </td>
                    <td>{{ $employee->email }}</td>
                    <td>
                        @if($employee->employee_id)
                            <span class="badge badge-secondary">{{ $employee->employee_id }}</span>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>
                        @if($employee->branch)
                            <span class="badge badge-info">{{ $employee->branch->name }}</span>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>{{ $employee->department ?? '-' }}</td>
                    <td>{{ $employee->position ?? '-' }}</td>
                    <td>
                        <div class="action-btns">
                            <form action="{{ route('employees.sync-to-device', ['employee' => $employee->uuid]) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" title="{{ __('messages.sync_to_device') }}">
                                    🔄 {{ __('messages.sync') }}
                                </button>
                            </form>
                            <a href="{{ route('employees.edit', ['employee' => $employee->uuid]) }}" class="btn btn-secondary btn-sm">
                                {{ __('messages.edit') }}
                            </a>
                            <form action="{{ route('employees.destroy', ['employee' => $employee->uuid]) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <p>{{ __('messages.no_employees_found') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($employees->hasPages())
        <div class="pagination-wrapper">
            {{ $employees->links() }}
        </div>
    @endif
</div>
@endsection

