<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO & Metadata --}}
    <meta name="description" content="PLN Commercial Dashboard - Sistem monitoring real-time untuk performa penjualan, pendapatan, dan data statistik pelanggan PT PLN Batam.">
    <meta name="author" content="Sandy Mulia Kesuma">

    <title>@yield('title', 'PLN Commercial Dashboard')</title>

    @include('partials.layouts.MetaConfig')

    {{-- Preconnect --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    {{-- Preload: font-awesome (above the fold icon) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- ===== GLOBAL CSS ===== --}}
    {{-- 1. Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- 2. Komponen global --}}
    <link rel="stylesheet" href="{{ asset('css/layouts/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/fact-entry.css') }}">

    {{-- ===== PAGE-LEVEL CSS: diisi tiap halaman via @push('styles') ===== --}}
    @stack('styles')
    @yield('head')

    {{-- ===== UTILITY: visually-hidden & design tokens global ===== --}}
    {{-- Catatan: token --text-muted, --accent-orange, dll. sudah ada di
         navbar.css & dashboard.css. Style ini HANYA untuk utilitas aksesibilitas
         yang belum tercakup di file manapun. --}}
    <style>
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>

    {{-- NAVBAR --}}
    <nav class="navbar" role="navigation" aria-label="Navigasi utama">
        <div class="nav-left">
            <a href="/dashboard"
               class="nav-item {{ Request::is('dashboard') || Request::is('/') ? 'active' : '' }}"
               aria-current="{{ Request::is('dashboard') || Request::is('/') ? 'page' : 'false' }}">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                <span class="nav-label">Dashboard</span>
            </a>

            <a href="/historis"
               class="nav-item {{ Request::is('historis*') ? 'active' : '' }}"
               aria-current="{{ Request::is('historis*') ? 'page' : 'false' }}">
                <i class="fas fa-history" aria-hidden="true"></i>
                <span class="nav-label">Historis</span>
            </a>
        </div>

        <div class="nav-right">
            <div class="profile-dropdown">
                <button class="profile-btn"
                        aria-label="Menu profil {{ auth()->user()->name }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        type="button">
                    <img src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
                         alt=""
                         class="nav-avatar"
                         aria-hidden="true">
                    <span class="nav-username">{{ auth()->user()->name }}</span>
                    <i class="fas fa-caret-down" aria-hidden="true"></i>
                </button>

                <div class="dropdown-content" role="menu">
                    <a href="/profile" role="menuitem">
                        <i class="fas fa-user-circle" aria-hidden="true"></i> Profile
                    </a>
                    <a href="javascript:void(0)" onclick="openSettings()" role="menuitem">
                        <i class="fas fa-cog" aria-hidden="true"></i> Settings
                    </a>
                    <hr class="dropdown-divider">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-item" role="menuitem">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="app-main" id="main-content" role="main">
        @yield('content')
    </main>

   {{-- CHATBOT: Hanya tampil di dashboard, bukan di historis --}}
@if(!Request::is('historis*'))
    @include('partials.widgets.Chatbot')
@endif

    {{-- SCRIPTS: non-blocking --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    @yield('scripts')
    @stack('scripts')

    {{-- Toast Notifications --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        @if(session('success'))
            Toast.fire({ icon: 'success', title: '{{ addslashes(session('success')) }}' });
        @endif
        @if(session('error'))
            Toast.fire({ icon: 'error', title: '{{ addslashes(session('error')) }}' });
        @endif
        @if(session('warning'))
            Toast.fire({ icon: 'warning', title: '{{ addslashes(session('warning')) }}' });
        @endif
    });
    </script>

    @stack('global-scripts')

</body>
</html>