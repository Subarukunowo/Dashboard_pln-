<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA — PLN Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#121212; color:#fff; font-family:'Inter','Segoe UI',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .card { background:#1e1e1e; border:1px solid #333; border-radius:16px; padding:40px; width:100%; max-width:440px; }
        h1 { font-size:1.2rem; font-weight:700; border-left:4px solid #ff9800; padding-left:12px; margin-bottom:8px; }
        p { color:#888; font-size:0.85rem; margin-bottom:24px; padding-left:16px; }
        .qr-box { background:#fff; border-radius:12px; padding:16px; text-align:center; margin-bottom:20px; }
        .qr-box img { width:180px; height:180px; }
        .secret-box { background:#161616; border:1px solid #333; border-radius:8px; padding:10px 14px; font-family:monospace; font-size:0.85rem; color:#ff9800; text-align:center; margin-bottom:20px; letter-spacing:2px; }
        .steps { color:#888; font-size:0.8rem; margin-bottom:24px; line-height:1.8; }
        .steps span { color:#fff; }
        input { width:100%; background:#161616; border:1px solid #444; border-radius:8px; padding:12px 16px; color:#fff; font-size:1.2rem; text-align:center; letter-spacing:8px; outline:none; margin-bottom:12px; }
        input:focus { border-color:#ff9800; }
        button { width:100%; background:#ff9800; color:#000; border:none; border-radius:8px; padding:13px; font-size:0.95rem; font-weight:700; cursor:pointer; }
        button:hover { background:#ffb74d; }
        .error { background:rgba(244,67,54,0.1); border:1px solid #f44336; border-radius:8px; padding:10px 14px; font-size:0.8rem; color:#f44336; margin-bottom:16px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Aktifkan 2FA</h1>
    <p>Scan QR code dengan Google Authenticator</p>

    <div class="qr-box">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrCodeUrl) }}" alt="QR Code">
    </div>

    <div class="steps">
        <span>1.</span> Buka Google Authenticator di HP<br>
        <span>2.</span> Tap + → Scan QR code<br>
        <span>3.</span> Masukkan 6 digit kode di bawah
    </div>

    <div class="secret-box">{{ $secret }}</div>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('2fa.enable') }}">
        @csrf
        <label for="otp">Kode OTP</label>
        <input id="otp" type="text" name="otp" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
        <button type="submit">Verifikasi & Aktifkan</button>
    </form>
</div>
</body>
</html>