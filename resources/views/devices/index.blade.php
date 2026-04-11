<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perangkat Terdaftar</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#121212; color:#fff; font-family:'Inter','Segoe UI',sans-serif; padding:40px 20px; }
        .container { max-width:600px; margin:0 auto; }
        h1 { font-size:1.2rem; font-weight:700; border-left:4px solid #ff9800; padding-left:12px; margin-bottom:24px; }
        .device-card { background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:16px 20px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; }
        .device-card.current { border-color:#00ff88; }
        .device-info { font-size:0.85rem; }
        .device-name { font-weight:600; margin-bottom:4px; }
        .device-meta { color:#888; font-size:0.75rem; }
        .current-badge { font-size:0.7rem; color:#00ff88; margin-left:8px; }
        .btn-remove { background:rgba(244,67,54,0.1); border:1px solid #f44336; color:#f44336; border-radius:6px; padding:6px 14px; font-size:0.78rem; cursor:pointer; }
        .btn-remove:hover { background:rgba(244,67,54,0.2); }
        .btn-back { display:inline-block; margin-top:20px; color:#888; font-size:0.8rem; text-decoration:none; }
        .btn-back:hover { color:#fff; }
        .success { background:rgba(0,255,136,0.1); border:1px solid #00ff88; border-radius:8px; padding:10px 14px; font-size:0.8rem; color:#00ff88; margin-bottom:16px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Perangkat Terdaftar</h1>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @foreach($devices as $device)
        <div class="device-card {{ $device->device_token === $currentToken ? 'current' : '' }}">
            <div class="device-info">
                <div class="device-name">
                    {{ $device->device_name }}
                    @if($device->device_token === $currentToken)
                        <span class="current-badge">● Perangkat ini</span>
                    @endif
                </div>
                <div class="device-meta">
                    IP: {{ $device->ip_address }} · 
                    Terakhir aktif: {{ $device->last_seen_at->diffForHumans() }}
                </div>
            </div>
            <form method="POST" action="{{ route('devices.destroy', $device->id) }}">
                @csrf @method('DELETE')
                <button class="btn-remove" type="submit"
                    onclick="return confirm('Hapus perangkat ini?')">
                    Hapus
                </button>
            </form>
        </div>
    @endforeach

    <a href="{{ route('dashboard') }}" class="btn-back">← Kembali ke Dashboard</a>
</div>
</body>
</html>