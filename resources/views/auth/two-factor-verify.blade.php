<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA — PLN Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#121212; color:#fff; font-family:'Inter','Segoe UI',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .card { background:#1e1e1e; border:1px solid #333; border-radius:16px; padding:48px 40px; width:100%; max-width:380px; text-align:center; }
        .icon { font-size:3rem; margin-bottom:16px; }
        h1 { font-size:1.2rem; font-weight:700; margin-bottom:8px; }
        p { color:#888; font-size:0.85rem; margin-bottom:32px; }
        input { width:100%; background:#161616; border:1px solid #444; border-radius:8px; padding:14px 16px; color:#fff; font-size:1.8rem; text-align:center; letter-spacing:10px; outline:none; margin-bottom:16px; }
        input:focus { border-color:#ff9800; }
        button { width:100%; background:#ff9800; color:#000; border:none; border-radius:8px; padding:13px; font-size:0.95rem; font-weight:700; cursor:pointer; }
        button:hover { background:#ffb74d; }
        .error { background:rgba(244,67,54,0.1); border:1px solid #f44336; border-radius:8px; padding:10px 14px; font-size:0.8rem; color:#f44336; margin-bottom:16px; text-align:left; }
        .logout-link { display:block; margin-top:16px; color:#555; font-size:0.8rem; text-decoration:none; }
        .logout-link:hover { color:#888; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🔐</div>
    <h1>Verifikasi 2FA</h1>
    <p>Masukkan kode 6 digit dari Google Authenticator</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('2fa.check') }}">
        @csrf
        <input type="text" name="otp" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" autofocus>
        <button type="submit">Verifikasi</button>
    </form>

    <a href="{{ route('logout') }}" class="logout-link"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Keluar & Login ulang
    </a>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</div>
</body>
</html>