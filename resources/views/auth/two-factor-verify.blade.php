<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA — PLN Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background:#121212;
            color:#fff;
            font-family:'Inter','Segoe UI',sans-serif;
            display:flex;
            align-items:center;
            justify-content:center;
            min-height:100vh;
        }

        .card {
            background:#1a1a1a;
            border:1px solid rgba(255,255,255,0.07);
            border-radius:20px;
            padding:52px 44px 44px;
            width:100%;
            max-width:380px;
            text-align:center;
        }

        .lock-icon {
            width:48px;
            height:48px;
            margin:0 auto 24px;
            display:block;
        }

        .label-brand {
            display:inline-block;
            font-size:11px;
            font-weight:600;
            color:#ff9800;
            letter-spacing:0.12em;
            text-transform:uppercase;
            margin-bottom:10px;
        }

        h1 {
            font-size:1.25rem;
            font-weight:700;
            color:#fff;
            margin-bottom:6px;
        }

        .sub {
            color:#555;
            font-size:0.82rem;
            line-height:1.5;
            margin-bottom:28px;
        }

        .divider {
            border:none;
            border-top:1px solid #222;
            margin:0 0 28px;
        }

        /* OTP 6 kotak terpisah */
        .otp-group {
            display:flex;
            gap:10px;
            justify-content:center;
            margin-bottom:28px;
        }

        .otp-group input {
            width:44px;
            height:52px;
            background:#111;
            border:1px solid #2e2e2e;
            border-radius:10px;
            color:#fff;
            font-size:1.4rem;
            font-weight:600;
            text-align:center;
            outline:none;
            transition:border-color .15s, background .15s;
        }

        .otp-group input:focus {
            border-color:#ff9800;
            background:#1a1400;
        }

        /* Hidden real input untuk submit form */
        #otp-hidden { display:none; }

        .error {
            background:rgba(244,67,54,0.08);
            border:1px solid rgba(244,67,54,0.3);
            border-radius:8px;
            padding:10px 14px;
            font-size:0.8rem;
            color:#f44336;
            margin-bottom:20px;
            text-align:left;
        }

        button[type="submit"] {
            width:100%;
            background:#ff9800;
            color:#0a0a0a;
            border:none;
            border-radius:10px;
            padding:13px;
            font-size:0.9rem;
            font-weight:700;
            cursor:pointer;
            letter-spacing:0.03em;
            transition:background .15s;
        }

        button[type="submit"]:hover { background:#ffa726; }

        .logout-link {
            display:block;
            margin-top:20px;
            color:#3a3a3a;
            font-size:0.78rem;
            text-decoration:none;
            transition:color .15s;
        }

        .logout-link:hover { color:#666; }
    </style>
</head>
<body>
<div class="card">

    <svg class="lock-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="8" y="22" width="32" height="22" rx="5" fill="#ff9800" opacity="0.15"/>
        <rect x="8" y="22" width="32" height="22" rx="5" stroke="#ff9800" stroke-width="1.5"/>
        <path d="M16 22V16a8 8 0 1 1 16 0v6" stroke="#ff9800" stroke-width="1.5" stroke-linecap="round"/>
        <circle cx="24" cy="33" r="2.5" fill="#ff9800"/>
        <line x1="24" y1="35.5" x2="24" y2="39" stroke="#ff9800" stroke-width="1.5" stroke-linecap="round"/>
    </svg>

    <span class="label-brand">Keamanan Akun</span>
    <h1>Verifikasi 2FA</h1>
    <p class="sub">Masukkan kode 6 digit dari<br>Google Authenticator</p>

    <hr class="divider">

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('2fa.check') }}" id="otpForm">
        @csrf
        <input type="hidden" name="otp" id="otp-hidden">

        <div class="otp-group" id="otpGroup">
            <input type="text" maxlength="1" inputmode="numeric" class="otp-box" autocomplete="off">
            <input type="text" maxlength="1" inputmode="numeric" class="otp-box" autocomplete="off">
            <input type="text" maxlength="1" inputmode="numeric" class="otp-box" autocomplete="off">
            <input type="text" maxlength="1" inputmode="numeric" class="otp-box" autocomplete="off">
            <input type="text" maxlength="1" inputmode="numeric" class="otp-box" autocomplete="off">
            <input type="text" maxlength="1" inputmode="numeric" class="otp-box" autocomplete="off">
        </div>

        <button type="submit">Verifikasi</button>
    </form>

    <a href="{{ route('logout') }}" class="logout-link"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Keluar &amp; Login ulang
    </a>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</div>

<script>
    const boxes = document.querySelectorAll('.otp-box');
    const hidden = document.getElementById('otp-hidden');
    const form   = document.getElementById('otpForm');

    boxes.forEach((box, i) => {
        box.addEventListener('input', e => {
            const v = e.target.value.replace(/\D/g, '');
            e.target.value = v;
            if (v && i < 5) boxes[i + 1].focus();
        });

        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
        });

        // Paste support — tempel semua digit sekaligus
        box.addEventListener('paste', e => {
            e.preventDefault();
            const digits = (e.clipboardData.getData('text').replace(/\D/g, '')).slice(0, 6);
            digits.split('').forEach((d, j) => {
                if (boxes[j]) boxes[j].value = d;
            });
            if (boxes[digits.length - 1]) boxes[digits.length - 1].focus();
        });
    });

    form.addEventListener('submit', e => {
        hidden.value = Array.from(boxes).map(b => b.value).join('');
    });

    boxes[0].focus();
</script>
</body>
</html>