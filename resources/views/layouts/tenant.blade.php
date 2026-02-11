<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #f59e0b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --border-color: rgba(255, 255, 255, 0.1);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --sidebar-width: 260px;
            --glass-bg: rgba(30, 41, 59, 0.8);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: {!! app()->getLocale() == 'ar' ? "'Tajawal'" : "'Plus Jakarta Sans'" !!}, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(139, 92, 246, 0.1) 0, transparent 50%),
                radial-gradient(at 50% 100%, rgba(245, 158, 11, 0.05) 0, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 70px;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }

        .logo-placeholder {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .company-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-section {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 12px;
            margin-bottom: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--text-primary);
            transform: translate{{ app()->getLocale() == 'ar' ? 'X(-4px)' : 'X(4px)' }};
        }

        .nav-item.active {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary-light);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .nav-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--glass-border);
            background: rgba(0,0,0,0.2);
        }

        .lang-switch {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .lang-btn {
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 6px;
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
        }

        .lang-btn:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
        }

        .lang-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: white;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Main Content */
        .main-wrapper {
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-bar {
            position: sticky;
            top: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 50;
        }

        .page-header-content h1 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .page-header-content p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .main-content {
            padding: 24px;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        /* Cards */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2,
        .card-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .card-body {
            padding: 22px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
        }

        .stat-icon.primary { background: rgba(99, 102, 241, 0.15); border-color: rgba(99, 102, 241, 0.3); }
        .stat-icon.success { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); }
        .stat-icon.warning { background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); }
        .stat-icon.danger { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .stat-value span {
            font-size: 16px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: var(--bg-hover);
            color: var(--text-secondary);
            border: 1px solid var(--glass-border);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text-primary);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Tables */
        .table-container {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            padding: 14px 18px;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--glass-border);
        }

        table td {
            padding: 14px 18px;
            font-size: 13px;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }

        table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 20px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-secondary {
            background: rgba(100, 116, 139, 0.15);
            color: #94a3b8;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        .badge-primary {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        /* Forms */
        .form-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            color: var(--text-primary);
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            transition: all 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        }

        .form-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .form-error {
            color: #f87171;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
        }

        /* Action Buttons Group */
        .action-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.4;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Avatar */
        .avatar-sm {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            color: var(--text-primary);
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-cell-info {
            display: flex;
            flex-direction: column;
        }

        .user-cell-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-cell-sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Status Badge */
        .status-online {
            color: #34d399;
        }

        .status-offline {
            color: #f87171;
        }

        /* Code Style */
        code {
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            background: rgba(0,0,0,0.3);
            padding: 3px 8px;
            border-radius: 6px;
            color: #fbbf24;
        }

        /* Filter Card */
        .filter-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 16px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-row .form-group {
            flex: 1;
            min-width: 180px;
            margin-bottom: 0;
        }

        /* Timeline */
        .timeline-item {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-item:hover {
            background: rgba(255,255,255,0.02);
            margin: 0 -22px;
            padding: 14px 22px;
            border-radius: 10px;
        }

        .timeline-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #475569, #1e293b);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 14px;
            border: 1px solid var(--glass-border);
            flex-shrink: 0;
        }

        .timeline-content {
            flex: 1;
            min-width: 0;
        }

        .timeline-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .timeline-meta {
            color: var(--text-muted);
            font-size: 12px;
        }

        .timeline-time {
            text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 14px;
        }

        .timeline-time-value {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        /* Quick Action */
        .quick-action {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s;
        }

        .quick-action:hover {
            background: rgba(255,255,255,0.06);
            transform: translate{{ app()->getLocale() == 'ar' ? 'X(-6px)' : 'X(6px)' }};
            border-color: rgba(99, 102, 241, 0.3);
        }

        .quick-action-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: transform 0.2s;
        }

        .quick-action:hover .quick-action-icon {
            transform: scale(1.1);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .progress-bar-fill.primary { background: var(--primary); }
        .progress-bar-fill.warning { background: var(--warning); }
        .progress-bar-fill.success { background: var(--success); }

        /* Link */
        a.link {
            color: var(--primary-light);
            text-decoration: none;
        }

        a.link:hover {
            text-decoration: underline;
        }

        /* Pagination */
        .pagination-wrapper {
            padding: 16px 20px;
            border-top: 1px solid var(--glass-border);
        }

        /* Checkbox Grid */
        .checkbox-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px;
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            transition: all 0.2s;
        }

        .checkbox-label:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .checkbox-label input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .checkbox-label input[type="checkbox"]:checked + .checkbox-text {
            color: var(--primary-light);
        }

        .checkbox-text {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Radio Group */
        .radio-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .radio-label {
            cursor: pointer;
        }

        .radio-label input[type="radio"] {
            display: none;
        }

        .radio-text {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .radio-label input[type="radio"]:checked + .radio-text {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            color: var(--primary-light);
        }

        .radio-icon {
            font-size: 16px;
        }

        /* Empty State Enhanced */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 56px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 8px;
        }

        .empty-state .btn {
            margin-top: 16px;
        }

        /* Quick Action Content */
        .quick-action-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .quick-action-content strong {
            font-size: 13px;
            color: var(--text-primary);
        }

        .quick-action-content span {
            font-size: 11px;
            color: var(--text-muted);
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            @if(auth()->user()->company && auth()->user()->company->logo)
                <img src="{{ route('tenant.storage', ['path' => auth()->user()->company->logo]) }}" alt="Logo" class="sidebar-logo">
            @else
                <div class="logo-placeholder">✨</div>
            @endif
            <span class="company-name">{{ auth()->user()->company?->name ?? __('messages.company_name') }}</span>
        </div>
        
        <nav class="nav-section">
            <div class="nav-label">{{ __('messages.main_menu') ?? 'القائمة الرئيسية' }}</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> {{ __('messages.dashboard') }}
            </a>
            <a href="{{ route('branches.index') }}" class="nav-item {{ request()->is('branches*') ? 'active' : '' }}">
                <span class="nav-icon">🏢</span> {{ __('messages.branches') }}
            </a>
            <a href="{{ route('departments.index') }}" class="nav-item {{ request()->is('departments*') ? 'active' : '' }}">
                <span class="nav-icon">🗂️</span> {{ __('messages.departments') }}
            </a>
            <a href="{{ route('devices.index') }}" class="nav-item {{ request()->is('devices*') ? 'active' : '' }}">
                <span class="nav-icon">📱</span> {{ __('messages.devices') }}
            </a>
            <a href="{{ route('employees.index') }}" class="nav-item {{ request()->is('employees*') ? 'active' : '' }}">
                <span class="nav-icon">👥</span> {{ __('messages.employees') }}
            </a>
            <a href="{{ route('attendance.index') }}" class="nav-item {{ request()->is('attendance*') ? 'active' : '' }}">
                <span class="nav-icon">⏰</span> {{ __('messages.attendance') }}
            </a>
            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->is('reports*') ? 'active' : '' }}">
                <span class="nav-icon">📈</span> {{ __('messages.reports') }}
            </a>
            <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->is('analytics*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> {{ __('messages.analytics') }}
            </a>
            <a href="{{ route('attendance-report.index') }}" class="nav-item {{ request()->is('attendance-report*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span> {{ __('messages.attendance_report') }}
            </a>
            
            <div class="nav-label" style="margin-top: 20px;">{{ __('messages.management') ?? 'الإدارة' }}</div>
            <a href="{{ route('admin.shifts.index') }}" class="nav-item {{ request()->is('admin/shifts*') ? 'active' : '' }}">
                <span class="nav-icon">📅</span> {{ __('messages.shifts') }}
            </a>
            <a href="{{ route('billing.index') }}" class="nav-item {{ request()->is('billing*') ? 'active' : '' }}">
                <span class="nav-icon">💳</span> {{ __('messages.billing') }}
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span> {{ __('messages.settings') }}
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', ['locale' => 'ar']) }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
                <a href="{{ route('lang.switch', ['locale' => 'en']) }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a>
            </div>

            <div class="user-card">
                <a href="{{ route('profile.edit') }}" style="text-decoration: none; display: flex; align-items: center; gap: 10px; flex: 1;">
                    <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-email">{{ auth()->user()->email }}</div>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn" title="{{ __('messages.logout') }}">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="top-bar">
            <div class="page-header-content">
                @yield('header')
            </div>
            <div class="header-actions">
                @yield('header-actions')
            </div>
        </header>

        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
