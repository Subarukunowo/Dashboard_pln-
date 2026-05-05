@extends('layouts.app')

@section('title', 'Profile — PLN Commercial Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/profile.css') }}">
<link rel="stylesheet" href="{{ asset('css/layouts/account-sidebar.css') }}">
@endpush

@section('content')

{{-- Avatar Upload Modal --}}
<div class="avatar-modal-backdrop" id="avatarModal">
    <div class="avatar-modal">
        <div class="avatar-modal-title">Ganti Foto Profil</div>
        <div class="avatar-modal-sub">Upload foto baru untuk profil Anda.</div>
        <div class="avatar-preview-wrap">
            <img class="avatar-preview" id="avatarPreview"
                 src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
                 alt="Preview">
        </div>
        <div class="avatar-upload-zone" id="avatarUploadZone">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <p>Klik atau drag &amp; drop foto di sini</p>
            <p style="margin-top:.3rem;font-size:.68rem;color:var(--text-3);">PNG, JPG, WEBP — maks. 2MB</p>
            <input type="file" id="avatarFileInput" accept="image/png,image/jpeg,image/webp">
        </div>
        <div class="avatar-modal-actions">
            <button class="btn-modal-cancel" id="avatarClose">Batal</button>
            <button class="btn-modal-save" id="avatarModalSave">Simpan</button>
        </div>
    </div>
</div>

{{-- Account Layout --}}
<div class="account-layout">

    @include('partials.layouts.AccountSidebar')

    <div class="account-content">

        <button class="acc-sidebar-toggle" id="accSidebarToggle" aria-label="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="account-content-inner">

            <div class="acc-page-title">
                <i class="fa-solid fa-circle-user"></i> Profile Saya
            </div>

            {{-- Card 1: Identitas --}}
            <div class="profile-card">

                <div class="profile-hero-section">
                    <div class="profile-avatar-wrap" id="avatarWrap" title="Ganti foto profil">
                        <img class="profile-avatar" id="profileAvatarMain"
                             src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
                             alt="Avatar">
                        <div class="avatar-overlay"><i class="fa-solid fa-camera"></i></div>
                        <div class="profile-score-badge">
                            <i class="fa-solid fa-check" style="font-size:.45rem;"></i>
                        </div>
                    </div>

                    <div class="profile-name">{{ auth()->user()->name }}</div>
                    <div class="profile-role-line">
                        @foreach(auth()->user()->getRoleNames() as $role)
                            <span>{{ ucfirst($role) }}</span><span class="profile-role-dot">·</span>
                        @endforeach
                        <span>PLN Commercial</span>
                    </div>

                    <div class="profile-actions">
                        <a href="mailto:{{ auth()->user()->email }}" class="profile-action-btn" title="Email">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                        <a href="{{ route('2fa.setup') }}" class="profile-action-btn" title="2FA">
                            <i class="fa-solid fa-shield-halved"></i>
                        </a>
                        <a href="{{ route('settings') }}" class="profile-action-btn" title="Settings">
                            <i class="fa-solid fa-gear"></i>
                        </a>
                    </div>
                </div>

                <div class="profile-divider"></div>

                <div class="profile-info-table">
                    <div class="card-section-header">Informasi Akun</div>

                    <div class="info-row">
                        <div class="info-row-label">Nama Lengkap</div>
                        <div class="info-row-value">{{ auth()->user()->name }}</div>
                    </div>

                    <div class="info-row" @if(!auth()->user()->isGoogleUser()) id="emailEditRow" style="cursor:pointer;" @endif>
                        <div class="info-row-label">Email</div>
                        <div class="info-row-value @if(!auth()->user()->isGoogleUser()) editable @endif">
                            <span id="emailDisplay">{{ auth()->user()->email }}</span>
                            @if(!auth()->user()->isGoogleUser())
                                <input type="email" class="info-edit-input" id="emailInput"
                                       value="{{ auth()->user()->email }}"
                                       style="display:none;" placeholder="Masukkan email baru">
                                <span class="edit-hint"><i class="fa-solid fa-pen fa-xs"></i></span>
                            @else
                                <span class="google-managed-note">
                                    <i class="fa-brands fa-google" style="font-size:.65rem;"></i> Dikelola Google
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row-label">Bergabung</div>
                        <div class="info-row-value">
                            {{ auth()->user()->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row-label">Status</div>
                        <div class="info-row-value">
                            <span class="status-chip {{ auth()->user()->is_active ? 'active' : 'inactive' }}">
                                {{ auth()->user()->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="save-bar" id="profileSaveBar">
                    <button class="btn-cancel" id="btnCancelChanges">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button class="btn-save" id="btnSaveChanges">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>

                <div class="profile-divider"></div>

                <div class="card-section-header">Keamanan</div>
                <div class="twofa-row">
                    <div class="twofa-left">
                        <div class="twofa-icon-wrap {{ auth()->user()->two_factor_enabled ? 'on' : 'off' }}">
                            <i class="fa-solid fa-{{ auth()->user()->two_factor_enabled ? 'shield-check' : 'shield-xmark' }}"></i>
                        </div>
                        <div>
                            <div class="twofa-name">Two-Factor Authentication</div>
                            <div class="twofa-sub">
                                {{ auth()->user()->two_factor_enabled
                                    ? 'Akun dilindungi verifikasi dua langkah.'
                                    : 'Aktifkan 2FA untuk keamanan lebih.' }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('2fa.setup') }}" class="btn-2fa">
                        <i class="fa-solid fa-{{ auth()->user()->two_factor_enabled ? 'rotate' : 'plus' }}"></i>
                        {{ auth()->user()->two_factor_enabled ? 'Kelola' : 'Aktifkan' }}
                    </a>
                </div>
                <div style="height:1rem;"></div>

            </div>{{-- /card identitas --}}

            {{-- Card 2: Perangkat --}}
            <div class="profile-card" id="devices">
                <div class="card-section-header" style="padding-bottom:.75rem;">Perangkat Terdaftar</div>

                @php $devices = auth()->user()->devices()->orderByDesc('last_seen_at')->get(); @endphp

                @if($devices->isEmpty())
                    <div class="empty-state">
                        <i class="fa-solid fa-laptop-medical"></i>
                        Belum ada perangkat terdaftar.
                    </div>
                @else
                    @foreach($devices as $device)
                        @php
                            $mob = str_contains(strtolower($device->device_name ?? ''), 'mobile')
                                || str_contains(strtolower($device->device_name ?? ''), 'android')
                                || str_contains(strtolower($device->device_name ?? ''), 'iphone');
                        @endphp
                        <div class="device-item">
                            <div class="device-icon-wrap">
                                <i class="fa-solid fa-{{ $mob ? 'mobile-screen' : 'laptop' }}"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="device-name">{{ $device->device_name ?? 'Unknown Device' }}</div>
                                <div class="device-meta">
                                    <i class="fa-solid fa-location-dot"></i> {{ $device->ip_address ?? '-' }}
                                    &nbsp;·&nbsp;
                                    <i class="fa-regular fa-clock"></i> {{ $device->last_seen_at?->setTimezone('Asia/Jakarta')->diffForHumans() ?? '-' }}
                                </div>
                            </div>
                            <span class="device-status {{ $device->status }}">{{ ucfirst($device->status) }}</span>
                        </div>
                    @endforeach
                @endif
                <div style="height:.5rem;"></div>
            </div>{{-- /card perangkat --}}

        </div>{{-- /account-content-inner --}}
    </div>{{-- /account-content --}}

</div>{{-- /account-layout --}}

@endsection

@push('scripts')
<script src="{{ asset('js/Dashboard/pages/profile.js') }}"></script>
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