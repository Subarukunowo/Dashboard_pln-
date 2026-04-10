<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PLN Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #121212;
            color: #fff;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 16px;
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-card h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            border-left: 4px solid #ff9800;
            padding-left: 12px;
            text-align: left;
        }
        .login-card p {
            color: #888;
            font-size: 0.85rem;
            margin-bottom: 32px;
            text-align: left;
            padding-left: 16px;
        }
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px 20px;
            background: #fff;
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-google:hover { background: #f0f0f0; }
        .btn-google img { width: 20px; height: 20px; }
        .error {
            background: rgba(244,67,54,0.1);
            border: 1px solid #f44336;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.8rem;
            color: #f44336;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>PLN Commercial</h1>
        <p>Dashboard Monitoring & Analisis</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <a href="{{ route('auth.google') }}" class="btn-google">
            <img src="https://www.google.com/favicon.ico" alt="Google">
            Masuk dengan Google
        </a>
    </div>
</body>
</html>