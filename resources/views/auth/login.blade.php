<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PLN Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrapper">
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

    <div class="right-panel">
        <h3>Selamat Datang</h3>
        <p class="subtitle">Masuk untuk mengakses dashboard</p>

        @if(session('error'))
            <div class="alert-error">
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

        <div class="footer">© PLN Batam · REVAS Division</div>
    </div>
</div>

@include('auth.partials.login-alerts')

</body>
</html>