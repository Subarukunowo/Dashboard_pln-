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

    {{-- Preconnect & Preload --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">

    {{-- Stylesheets --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fact-entry.css') }}">
    
    @stack('styles')
    @yield('head')

    {{-- Accessibility: Utility classes --}}
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
        /* Fallback jika navbar.css belum load */
        :root {
            --text-muted: #b8b8b8;
            --accent-orange: #ff9800;
        }
    </style>
</head>
<body>

{{-- ✅ NAVBAR: Aksesibilitas Optimized --}}
<nav class="navbar" role="navigation" aria-label="Navigasi utama">
    <div class="nav-left">
        {{-- Link Dashboard --}}
        <a href="/dashboard" 
           class="nav-item {{ Request::is('dashboard') || Request::is('/') ? 'active' : '' }}"
           aria-current="{{ Request::is('dashboard') || Request::is('/') ? 'page' : 'false' }}">
            <i class="fas fa-chart-line" aria-hidden="true"></i>
            <span class="nav-label">Dashboard</span>
        </a>
        
        {{-- Link Historis --}}
        <a href="/historis" 
           class="nav-item {{ Request::is('historis*') ? 'active' : '' }}"
           aria-current="{{ Request::is('historis*') ? 'page' : 'false' }}">
            <i class="fas fa-history" aria-hidden="true"></i>
            <span class="nav-label">Historis</span>
        </a>
    </div>

    <div class="nav-right">
        <div class="profile-dropdown">
            {{-- Tombol Profile dengan accessible name --}}
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
            
            {{-- Dropdown Menu --}}
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

{{-- ✅ MAIN CONTENT --}}
<main class="app-main" id="main-content" role="main">
    @yield('content')
</main>

{{-- ✅ CHATBOT PARTIAL --}}
@include('partials.chatbot')

{{-- ✅ SCRIPTS: Non-blocking loading --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

@yield('scripts')
@stack('scripts')

{{-- ✅ Toast Notifications --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
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

{{-- ✅ Navbar Dropdown Toggle (klik, bukan hanya hover) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileBtn = document.querySelector('.profile-btn');
    const dropdown = document.querySelector('.dropdown-content');
    
    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            dropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            dropdown.classList.remove('show');
            profileBtn.setAttribute('aria-expanded', 'false');
        });
        
        // Prevent closing when clicking inside dropdown
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>

</body>
</html>