@extends('layouts.tenant')

@section('title', __('messages.branches'))

@section('header')
    <h1>{{ __('messages.branches') }}</h1>
    <p>{{ __('messages.manage_company_branches') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('branches.create') }}" class="btn btn-primary">
        <span>+</span> {{ __('messages.add_branch') }}
    </a>
@endsection

@section('content')
<div class="table-container">
    <!-- Filters -->
    <div class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
        <form action="{{ route('branches.index') }}" method="GET" style="display: flex; gap: 12px; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                   class="form-control" placeholder="{{ __('messages.search_branches') }}" 
                   style="max-width: 300px;">
            <select name="status" class="form-control" style="max-width: 150px;">
                <option value="">{{ __('messages.all_statuses') }}</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('messages.filter') }}</button>
            @if(!empty($filters['search']) || !empty($filters['status']))
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
            @endif
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.branch_name') }}</th>
                <th>{{ __('messages.code') }}</th>
                <th>{{ __('messages.city') }}</th>
                <th>{{ __('messages.employees_count') }}</th>
                <th>{{ __('messages.devices_count') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branches as $branch)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="avatar-sm" style="background: {{ $branch->is_headquarters ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #6366f1, #8b5cf6)' }};">
                                {{ $branch->is_headquarters ? '🏢' : '🏬' }}
                            </div>
                            <div>
                                <span class="user-cell-name">{{ $branch->name }}</span>
                                @if($branch->is_headquarters)
                                    <span class="badge badge-warning" style="font-size: 10px; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;">{{ __('messages.headquarters') }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($branch->code)
                            <span class="badge badge-secondary">{{ $branch->code }}</span>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>{{ $branch->city ?? '-' }}</td>
                    <td>
                        <span class="badge badge-info">{{ $branch->employees_count }} {{ __('messages.employees') }}</span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $branch->devices_count }} {{ __('messages.devices') }}</span>
                    </td>
                    <td>
                        @if($branch->is_active)
                            <span class="badge badge-success">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-secondary btn-sm">
                                {{ __('messages.edit') }}
                            </a>
                            @if($branch->employees_count == 0 && $branch->devices_count == 0)
                                <form action="{{ route('branches.destroy', $branch) }}" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        {{ __('messages.delete') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">🏢</div>
                            <p>{{ __('messages.no_branches_found') }}</p>
                            <a href="{{ route('branches.create') }}" class="btn btn-primary" style="margin-top: 16px;">
                                {{ __('messages.add_first_branch') }}
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($branches->hasPages())
        <div class="pagination-wrapper">
            {{ $branches->links() }}
        </div>
    @endif
</div>
@endsection
