<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - Attendance System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            text-align: center;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 12px;
        }
        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background: #2563eb;
        }
        .logout-link {
            display: block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-link:hover {
            color: #334155;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✉️</div>
        <h1>Verify Your Email</h1>
        
        @if (session('message') == 'Verification link sent!')
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                A new verification link has been sent to your email address.
            </div>
        @endif

        <p>
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-link" style="background: none; border: none; cursor: pointer;">Log Out</button>
        </form>
    </div>
</body>
</html>
