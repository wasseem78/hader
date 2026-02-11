<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Attendance System</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --bg-dark: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh; 
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-main);
        }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 480px;
            padding: 40px;
        }

        h1 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 8px;
            text-align: center;
            font-weight: 700;
        }

        p {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            font-size: 14px;
            color: #fff;
            transition: all 0.2s;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 14px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover { text-decoration: underline; }
        
        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: contain;
            margin: 0 auto 20px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="brand-logo">
        <h1>{{ __('messages.start_free_trial') }}</h1>
        <p>{{ __('messages.create_account_desc') }}</p>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-group">
                <label>{{ __('messages.company_name_label') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required autofocus placeholder="e.g. Acme Corp">
            </div>

            <div class="form-group">
                <label>{{ __('messages.full_name_label') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe">
            </div>

            <div class="form-group">
                <label>{{ __('messages.email_label') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. john@acme.com">
            </div>

            <div class="form-group">
                <label>{{ __('messages.password') }}</label>
                <input type="password" name="password" required minlength="8" placeholder="{{ __('messages.password_placeholder') }}">
            </div>

            <div class="form-group">
                <label>{{ __('messages.confirm_password') }}</label>
                <input type="password" name="password_confirmation" required placeholder="{{ __('messages.confirm_password_placeholder') }}">
            </div>

            <button type="submit" class="btn">{{ __('messages.create_account_btn') }}</button>
        </form>

        <div class="login-link">
            {{ __('messages.already_have_account') }} <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
        </div>
    </div>
</body>
</html>
