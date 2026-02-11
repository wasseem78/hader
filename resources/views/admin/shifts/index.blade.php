@extends('layouts.tenant')

@section('title', __('messages.shifts'))

@section('header')
    <h1>{{ __('messages.shifts') }}</h1>
    <p>{{ __('messages.manage_work_schedules') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">
        <span>+</span> {{ __('messages.add_shift') }}
    </a>
@endsection

@section('content')
<!-- Filters -->
@if(isset($branches) && $branches->count() > 0)
<div class="filter-card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.shifts.index') }}" class="filter-row">
        <div class="form-group">
            <label for="branch_id" class="form-label">{{ __('messages.branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
        @if(request('branch_id'))
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
        @endif
    </form>
</div>
@endif

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.code') }}</th>
                    @if(isset($branches) && $branches->count() > 0)
                    <th>{{ __('messages.branch') }}</th>
                    @endif
                    <th>{{ __('messages.time') }}</th>
                    <th>{{ __('messages.working_days') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td>
                            <span style="font-weight: 600; color: var(--text-primary);">{{ $shift->name }}</span>
                        </td>
                        <td>
                            <code style="background: rgba(99, 102, 241, 0.2); color: var(--primary-light); padding: 3px 8px; border-radius: 4px; font-size: 12px;">{{ $shift->code }}</code>
                        </td>
                        @if(isset($branches) && $branches->count() > 0)
                        <td>{{ $shift->branch->name ?? __('messages.all_branches') }}</td>
                        @endif
                        <td>
                            <span style="color: var(--text-secondary);">{{ $shift->start_time }} - {{ $shift->end_time }}</span>
                        </td>
                        <td>
                            @if($shift->working_days)
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach(array_slice($shift->working_days, 0, 5) as $day)
                                        <span class="badge badge-secondary" style="font-size: 10px;">{{ ucfirst(substr($day, 0, 3)) }}</span>
                                    @endforeach
                                    @if(count($shift->working_days) > 5)
                                        <span class="badge badge-secondary" style="font-size: 10px;">+{{ count($shift->working_days) - 5 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="badge badge-success">{{ __('messages.all_days') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.shifts.edit', ['shift' => $shift->uuid]) }}" class="btn btn-secondary btn-sm">
                                    {{ __('messages.edit') }}
                                </a>
                                <form action="{{ route('admin.shifts.destroy', ['shift' => $shift->uuid]) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        {{ __('messages.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>{{ isset($branches) && $branches->count() > 0 ? 6 : 5 }}
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <div class="empty-icon">📅</div>
                            <p>{{ __('messages.no_shifts_found') }}</p>
                            <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary" style="margin-top: 12px;">
                                {{ __('messages.add_shift') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
