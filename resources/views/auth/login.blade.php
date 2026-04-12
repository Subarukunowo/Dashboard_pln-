<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PLN Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #0a0a0a;
            color: #fff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Background grid effect */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,152,0,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,152,0,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Glow blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.12;
            pointer-events: none;
        }
        .blob-1 {
            width: 400px; height: 400px;
            background: #ff9800;
            top: -100px; left: -100px;
        }
        .blob-2 {
            width: 300px; height: 300px;
            background: #00ff88;
            bottom: -80px; right: -80px;
        }

        .wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #222;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6);
            position: relative;
            z-index: 1;
        }

        /* Left Panel */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1a1200 0%, #0f0f0f 60%, #001a0a 100%);
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid #222;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #ff9800, #ff5722);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 800; color: #000;
        }
        .brand-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            font-size: 0.7rem;
            color: #555;
            margin-top: 1px;
        }

        .left-content h2 {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 16px;
        }
        .left-content h2 span {
            background: linear-gradient(90deg, #ff9800, #ffcc02);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .left-content p {
            color: #666;
            font-size: 0.85rem;
            line-height: 1.7;
        }

        .stats {
            display: flex;
            gap: 20px;
        }
        .stat {
            background: rgba(255,152,0,0.05);
            border: 1px solid rgba(255,152,0,0.1);
            border-radius: 10px;
            padding: 12px 16px;
        }
        .stat-val {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ff9800;
        }
        .stat-lbl {
            font-size: 0.68rem;
            color: #555;
            margin-top: 2px;
        }

        /* Right Panel */
        .right-panel {
            width: 380px;
            background: #111;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .right-panel .subtitle {
            color: #555;
            font-size: 0.82rem;
            margin-bottom: 36px;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .divider-line {
            flex: 1;
            height: 1px;
            background: #222;
        }
        .divider-text {
            color: #444;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px 20px;
            background: #fff;
            color: #1a1a1a;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            letter-spacing: 0.2px;
        }
        .btn-google:hover {
            background: #f5f5f5;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(255,255,255,0.1);
        }
        .btn-google:active { transform: translateY(0); }
        .btn-google svg { flex-shrink: 0; }

        .error {
            background: rgba(244,67,54,0.08);
            border: 1px solid rgba(244,67,54,0.3);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.8rem;
            color: #f44336;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notice {
            margin-top: 24px;
            padding: 12px 14px;
            background: rgba(255,152,0,0.05);
            border: 1px solid rgba(255,152,0,0.1);
            border-radius: 8px;
            font-size: 0.75rem;
            color: #666;
            line-height: 1.6;
        }
        .notice span { color: #ff9800; }

        .footer {
            margin-top: 32px;
            font-size: 0.72rem;
            color: #333;
            text-align: center;
        }

        @media (max-width: 700px) {
            .left-panel { display: none; }
            .wrapper { max-width: 400px; border-radius: 16px; }
            .right-panel { width: 100%; padding: 40px 28px; }
        }
    </style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrapper">

    {{-- Left Panel --}}
    <div class="left-panel">
        <div class="brand">
            <div class="brand-icon">P</div>
            <div>
                <div class="brand-name">PLN Batam</div>
                <div class="brand-sub">Commercial Division</div>
            </div>
        </div>

        <div class="left-content">
            <h2>Dashboard<br>Monitoring<br><span>& Analisis</span></h2>
            <p>Pantau kinerja penjualan, pendapatan,<br>dan RPKWH secara real-time.</p>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-val">47</div>
                <div class="stat-lbl">Sheet Aktif</div>
            </div>
            <div class="stat">
                <div class="stat-val">2025</div>
                <div class="stat-lbl">Tahun Data</div>
            </div>
            <div class="stat">
                <div class="stat-val">Live</div>
                <div class="stat-lbl">Status Data</div>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="right-panel">
        <h3>Selamat Datang</h3>
        <p class="subtitle">Masuk untuk mengakses dashboard</p>

        @if(session('error'))
            <div class="error">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <circle cx="7" cy="7" r="6" stroke="#f44336" stroke-width="1.5"/>
                    <path d="M7 4v3M7 9.5v.5" stroke="#f44336" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">Login dengan</span>
            <div class="divider-line"></div>
        </div>

        <a href="{{ route('auth.google') }}" class="btn-google">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Masuk dengan Google
        </a>

        <div class="notice">
            <span>⚠ Akses terbatas.</span> Hanya akun yang terdaftar dan diizinkan yang dapat mengakses dashboard ini.
        </div>

        <div class="footer">© 2025 PLN Batam · Commercial Division</div>
    </div>

</div>

</body>
</html>