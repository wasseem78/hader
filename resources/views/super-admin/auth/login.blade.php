<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() == 'ar' ? 'تسجيل دخول المشرف العام' : 'Super Admin Login' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #111827;
            color: #f9fafb;
            font-family: {!! "'Tajawal', system-ui, -apple-system, sans-serif" !!};
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #1f2937;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #374151;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand h1 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        .brand p {
            color: #9ca3af;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #d1d5db;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            background: #374151;
            border: 1px solid #4b5563;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #dc2626;
            ring: 2px solid #dc2626;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #b91c1c;
        }
        .error {
            color: #f87171;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <h1>{{ app()->getLocale() == 'ar' ? 'إدارة النظام' : 'System Administration' }}</h1>
            <p>{{ app()->getLocale() == 'ar' ? 'وصول آمن للمشرفين العامين فقط' : 'Secure Access for Super Admins Only' }}</p>
        </div>

        <form method="POST" action="{{ route('super-admin.login.store') }}">
            @csrf
            <div class="form-group">
                <label for="email">{{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}</label>
                <input type="email" id="email" name="email" required autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">{{ app()->getLocale() == 'ar' ? 'كلمة المرور' : 'Password' }}</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Login to Console' }}</button>
        </form>
    </div>
</body>
</html>
