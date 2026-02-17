<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.login') }} - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: {!! "'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif" !!};
            background: #0f172a;
            background-image:
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 1) 0, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #f8fafc;
        }
        
        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }
        
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: contain;
            margin: 0 auto 16px;
            display: block;
        }
        
        .brand h1 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .brand p {
            font-size: 14px;
            color: #94a3b8;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            color: #fca5a5;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s;
        }
        
        .form-control::placeholder {
            color: #64748b;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        
        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        
        .remember-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #6366f1;
            cursor: pointer;
        }
        
        .remember-row label {
            font-size: 14px;
            color: #94a3b8;
            cursor: pointer;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(99, 102, 241, 0.35);
        }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #94a3b8;
        }
        
        .register-link a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .register-link a:hover {
            color: #a5b4fc;
        }
        
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="brand-logo">
            <h1>{{ app()->getLocale() == 'ar' ? 'نظام الحضور' : 'Attendance System' }}</h1>
            <p>{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول إلى حسابك' : 'Sign in to your account' }}</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control"
                       required autofocus placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل بريدك الإلكتروني' : 'Enter your email' }}">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('messages.password') }}</label>
                <input type="password" id="password" name="password" class="form-control" required
                       placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل كلمة المرور' : 'Enter your password' }}">
            </div>

            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">{{ __('messages.remember_me') }}</label>
            </div>

            <button type="submit" class="btn-login">{{ __('messages.login') }}</button>
        </form>
        
        <div class="register-link">
            {{ __('messages.dont_have_account') }} 
            <a href="{{ route('register') }}">{{ __('messages.start_free_trial') }}</a>
        </div>


    </div>
</body>
</html>
