@extends('layouts.app')

@section('title', 'Settings — PLN Commercial Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/settings.css') }}">
<link rel="stylesheet" href="{{ asset('css/layouts/account-sidebar.css') }}">
@endpush

@section('content')

<div class="account-layout">

    @include('partials.layouts.AccountSidebar')

    <div class="account-content">

        <button class="acc-sidebar-toggle" id="accSidebarToggle" aria-label="Toggle menu">
            <i class="fa-solid fa-bars">
                
            </i>
        </button>

        <div class="account-content-inner">

            <div class="acc-page-title">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </div>

            {{-- ══════════════════════════════════════
                 CARD 1 — Profil
            ══════════════════════════════════════ --}}
            <div class="settings-card">
                <div class="settings-card-header"><i class="fa-solid fa-circle-user"></i> Informasi Profil</div>
                <div class="settings-card-body">

                    <div class="avatar-setting-row">
                        <img class="settings-avatar"
                             src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
                             alt="Avatar">
                        <div>
                            <div class="av-name">{{ auth()->user()->name }}</div>
                            <div class="av-note">{{ auth()->user()->email }}</div>
                            @if(auth()->user()->isGoogleUser())
                                <span class="badge-google-sm">
                                    <i class="fa-brands fa-google"></i> Dikelola oleh Google
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-user"></i> Nama Tampilan</div>
                            <div class="setting-desc">Nama yang ditampilkan di seluruh sistem.</div>
                        </div>
                        <div style="flex-shrink:0;min-width:200px;">
                            <input type="text" class="settings-input" id="inputName"
                                   value="{{ auth()->user()->name }}"
                                   @if(auth()->user()->isGoogleUser()) readonly title="Nama dikelola Google" @endif>
                        </div>
                    </div>

                    <div class="settings-footer">
                        @if(!auth()->user()->isGoogleUser())
                            <button class="btn-primary" onclick="saveName()">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan
                            </button>
                        @else
                            <small style="color:var(--text-muted);font-size:.77rem;">
                                <i class="fa-solid fa-circle-info"></i>
                                Ubah profil melalui akun Google Anda.
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 CARD 2 — Tampilan
            ══════════════════════════════════════ --}}
            <div class="settings-card" id="tampilan">
                <div class="settings-card-header"><i class="fa-solid fa-palette"></i> Tampilan</div>
                <div class="settings-card-body">

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-circle-half-stroke"></i> Mode Tampilan</div>
                            <div class="setting-desc">Pilih tema gelap atau terang.</div>
                        </div>
                        <div class="theme-toggle-group">
                            <button class="theme-btn active" id="btnDark" onclick="setTheme('dark')">
                                <i class="fa-solid fa-moon"></i> Gelap
                            </button>
                            <button class="theme-btn" id="btnLight" onclick="setTheme('light')">
                                <i class="fa-solid fa-sun"></i> Terang
                            </button>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-droplet"></i> Warna Aksen</div>
                            <div class="setting-desc">Warna utama untuk tombol dan highlight.</div>
                        </div>
                        <div class="accent-picker" id="accentPicker">
                            <div class="accent-swatch selected" data-color="orange" style="background:#ff9800;" title="Orange" onclick="setAccent('orange','#ff9800')"></div>
                            <div class="accent-swatch" data-color="green"  style="background:#00ff88;" title="Green"  onclick="setAccent('green','#00ff88')"></div>
                            <div class="accent-swatch" data-color="blue"   style="background:#3b82f6;" title="Blue"   onclick="setAccent('blue','#3b82f6')"></div>
                            <div class="accent-swatch" data-color="cyan"   style="background:#06b6d4;" title="Cyan"   onclick="setAccent('cyan','#06b6d4')"></div>
                            <div class="accent-swatch" data-color="violet" style="background:#8b5cf6;" title="Violet" onclick="setAccent('violet','#8b5cf6')"></div>
                            <div class="accent-swatch" data-color="rose"   style="background:#f43f5e;" title="Rose"   onclick="setAccent('rose','#f43f5e')"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-text-height"></i> Ukuran Teks</div>
                            <div class="setting-desc">Ukuran font konten halaman.</div>
                        </div>
                        <select class="settings-select" id="fontSizeSelect" onchange="setFontSize(this.value)">
                            <option value="13">Kecil (13px)</option>
                            <option value="14" selected>Normal (14px)</option>
                            <option value="16">Besar (16px)</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-wand-magic-sparkles"></i> Animasi UI</div>
                            <div class="setting-desc">Nonaktifkan jika perangkat terasa lambat.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="animToggle" checked onchange="setAnimation(this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                </div>
            </div>

            {{-- ══════════════════════════════════════
                 CARD 3 — Notifikasi
            ══════════════════════════════════════ --}}
            <div class="settings-card" id="notifications">
                <div class="settings-card-header"><i class="fa-solid fa-bell"></i> Notifikasi</div>
                <div class="settings-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-envelope"></i> Email Perangkat Baru</div>
                            <div class="setting-desc">Kirim email saat ada login dari perangkat baru.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-shield"></i> Peringatan Keamanan</div>
                            <div class="setting-desc">Notifikasi perubahan status perangkat.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 CARD 4 — Sesi & Keamanan
            ══════════════════════════════════════ --}}
            <div class="settings-card">
                <div class="settings-card-header"><i class="fa-solid fa-lock"></i> Sesi &amp; Keamanan</div>
                <div class="settings-card-body">

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-shield-check"></i> Two-Factor Authentication</div>
                            <div class="setting-desc">
                                Status:
                                <strong style="color:{{ auth()->user()->two_factor_enabled ? 'var(--accent-green)' : '#ef4444' }}">
                                    {{ auth()->user()->two_factor_enabled ? 'Aktif ✓' : 'Nonaktif' }}
                                </strong>
                            </div>
                        </div>
                        <a href="{{ route('2fa.setup') }}" class="btn-outline">
                            <i class="fa-solid fa-{{ auth()->user()->two_factor_enabled ? 'rotate' : 'plus' }}"></i>
                            {{ auth()->user()->two_factor_enabled ? 'Kelola 2FA' : 'Aktifkan 2FA' }}
                        </a>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label"><i class="fa-solid fa-right-from-bracket"></i> Logout Semua Sesi</div>
                            <div class="setting-desc">Keluar dari seluruh perangkat yang sedang aktif.</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-danger">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>{{-- /account-content-inner --}}
    </div>{{-- /account-content --}}

</div>{{-- /account-layout --}}

@endsection

@push('scripts')
<script src="{{ asset('js/Dashboard/pages/settings.js') }}"></script>
<script>
document.getElementById('accSidebarToggle')?.addEventListener('click', function () {
    document.getElementById('accountSidebar')?.classList.toggle('open');
});
document.addEventListener('click', function (e) {
    const s = document.getElementById('accountSidebar');
    const t = document.getElementById('accSidebarToggle');
    if (window.innerWidth <= 900 && s && !s.contains(e.target) && e.target !== t) {
        s.classList.remove('open');
    }
});
</script>
@endpush