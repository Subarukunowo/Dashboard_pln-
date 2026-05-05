{{--
    Partial: partials/layouts/AccountSidebar.blade.php
    Sidebar bersama untuk halaman /profile dan /settings
    Usage: @include('partials.layouts.AccountSidebar')
--}}
<aside class="account-sidebar" id="accountSidebar">

    {{-- Mini user card --}}
    <div class="sidebar-user-card">
        <img class="sidebar-user-avatar"
             src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
             alt="Avatar">
        <div>
            <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-user-role">
                @foreach(auth()->user()->getRoleNames() as $role)
                    {{ ucfirst($role) }}@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Profile group --}}
    <div class="acc-section-label">Profil</div>

    <a href="{{ route('profile') }}"
       class="acc-sidebar-item {{ Request::is('profile') ? 'active' : '' }}">
        <i class="fa-solid fa-circle-user"></i>
        Informasi Profil
    </a>

    <a href="{{ route('profile') }}#devices"
       class="acc-sidebar-item {{ Request::is('profile') && request()->query('tab') === 'devices' ? 'active' : '' }}">
        <i class="fa-solid fa-laptop"></i>
        Perangkat
    </a>

    <a href="{{ route('2fa.setup') }}"
       class="acc-sidebar-item {{ Request::is('2fa*') ? 'active' : '' }}">
        <i class="fa-solid fa-shield-halved"></i>
        Two-Factor Auth
    </a>

    {{-- Settings group --}}
    <div class="acc-section-label">Pengaturan</div>

    <a href="{{ route('settings') }}"
       class="acc-sidebar-item {{ Request::is('settings') ? 'active' : '' }}">
        <i class="fa-solid fa-palette"></i>
        Tampilan
    </a>

    <a href="{{ route('settings') }}#notifications"
       class="acc-sidebar-item">
        <i class="fa-solid fa-bell"></i>
        Notifikasi
    </a>

    <div class="acc-sidebar-divider"></div>

    {{-- Session --}}
    <div class="acc-section-label">Sesi</div>

    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
        @csrf
        <button type="submit" class="acc-sidebar-item danger" style="width:100%;background:none;border:none;font-family:inherit;cursor:pointer;text-align:left;">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </button>
    </form>

</aside>