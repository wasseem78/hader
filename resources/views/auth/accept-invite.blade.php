<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accept Invitation - Attendance System</title>
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
            max-width: 420px;
            padding: 40px;
        }
        h1 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn {
            display: block;
            width: 100%;
            background: #3b82f6;
            color: white;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Set Your Password</h1>
        <p style="text-align: center; color: #64748b; margin-bottom: 30px;">
            Welcome, {{ $user->name }}! Please set a password to activate your account.
        </p>

        <form method="POST" action="{{ route('admin.users.store-password', ['user' => $user]) }}">
            @csrf
            
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" required minlength="8">
            </div>

            <button type="submit" class="btn">Activate Account</button>
        </form>
    </div>
</body>
</html>
