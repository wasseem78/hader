@extends('layouts.tenant')

@section('title', __('messages.attendance_records'))

@section('header')
    <h1>{{ __('messages.attendance_records') }}</h1>
    <p>{{ __('messages.view_employee_attendance') ?? 'عرض سجلات حضور الموظفين' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('attendance.timeline') }}" class="btn btn-secondary">
        📊 {{ __('messages.timeline') ?? 'Timeline' }}
    </a>
    <a href="{{ route('attendance.export') }}" class="btn btn-secondary">
        ⬇ {{ __('messages.export_csv') }}
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('attendance.index') }}" class="filter-row">
        @if(isset($branches) && $branches->count() > 0)
        <div class="form-group">
            <label for="branch_id" class="form-label">{{ __('messages.branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-group">
            <label for="date" class="form-label">{{ __('messages.date') }}</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
        </div>

        <div class="form-group">
            <label for="employee" class="form-label">{{ __('messages.employee_name') }}</label>
            <input type="text" name="employee" id="employee" class="form-control" placeholder="{{ __('messages.search_placeholder') }}" value="{{ request('employee') }}">
        </div>

        <div class="form-group">
            <label for="type" class="form-label">{{ __('messages.type') }}</label>
            <select name="type" id="type" class="form-control">
                <option value="">{{ __('messages.all_types') }}</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>{{ __('messages.check_in') }}</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>{{ __('messages.check_out') }}</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
        @if(request()->anyFilled(['date', 'employee', 'type', 'branch_id']))
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
        @endif
    </form>
</div>

<!-- Data Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.employee_name') }}</th>
                @if(isset($branches) && $branches->count() > 0)
                <th>{{ __('messages.branch') }}</th>
                @endif
                <th>{{ __('messages.date') }}</th>
                <th>{{ __('messages.time') }}</th>
                <th>{{ __('messages.type') }}</th>
                <th>{{ __('messages.verification_method') }}</th>
                <th>{{ __('messages.device') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.duration') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="avatar-sm">{{ substr($record->user->name ?? 'U', 0, 1) }}</div>
                            <div class="user-cell-info">
                                <span class="user-cell-name">{{ $record->user->name ?? 'Unknown' }}</span>
                                <span class="user-cell-sub">{{ $record->user->employee_id ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                    @if(isset($branches) && $branches->count() > 0)
                    <td>{{ $record->branch->name ?? '-' }}</td>
                    @endif
                    <td>{{ $record->punch_date ? $record->punch_date->format('M d, Y') : '-' }}</td>
                    <td><strong style="color: var(--text-primary);">{{ $record->punch_time ? \Carbon\Carbon::parse($record->punch_time)->format('h:i A') : '-' }}</strong></td>
                    <td>
                        @php
                            $typeClass = match($record->type) {
                                'in' => 'badge-success',
                                'out' => 'badge-danger',
                                'break_start', 'break_end' => 'badge-info',
                                'overtime_start', 'overtime_end' => 'badge-primary',
                                default => 'badge-secondary'
                            };
                            $typeLabel = match($record->type) {
                                'in' => __('messages.punch_in'),
                                'out' => __('messages.punch_out'),
                                'break_start' => __('messages.break_start'),
                                'break_end' => __('messages.break_end'),
                                'overtime_start' => __('messages.overtime_start'),
                                'overtime_end' => __('messages.overtime_end'),
                                default => strtoupper($record->type)
                            };
                        @endphp
                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                    </td>
                    <td>
                        @php
                            $vKey = $record->verification_type;
                            $vLabel = match($vKey) {
                                'fingerprint' => __('messages.fingerprint'),
                                'face' => __('messages.face'),
                                'card' => __('messages.card'),
                                'password' => __('messages.password'),
                                'manual' => __('messages.manual'),
                                default => $vKey
                            };
                            
                            // If the translation key wasn't found (returns the key itself), show a formatted version or the key
                            if ($vLabel === "messages.$vKey") {
                                $vLabel = ucfirst($vKey);
                            }
                        @endphp
                        <span class="text-sm">{{ $vLabel }}</span>
                    </td>
                    <td>{{ $record->device->name ?? 'Manual' }}</td>
                    <td>
                        @if($record->is_late)
                            <span class="badge badge-warning">{{ __('messages.late') }} {{ $record->late_minutes }}m</span>
                        @elseif($record->is_early_departure)
                            <span class="badge badge-warning">{{ __('messages.early') }} {{ $record->early_minutes }}m</span>
                        @elseif($record->status === 'missing_punch_out')
                            <span class="badge badge-danger">{{ __('messages.missing_out') }}</span>
                        @else
                            <span class="badge badge-success">{{ __('messages.normal') }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $record->work_duration_minutes ? floor($record->work_duration_minutes / 60) . 'h ' . ($record->work_duration_minutes % 60) . 'm' : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">📅</div>
                            <p>{{ __('messages.no_attendance_records') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($records->hasPages())
        <div class="pagination-wrapper">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
