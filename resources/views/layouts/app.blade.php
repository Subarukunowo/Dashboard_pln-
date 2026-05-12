<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="PLN Commercial Dashboard - Sistem monitoring real-time untuk performa penjualan, pendapatan, dan data statistik pelanggan PT PLN Batam.">
    <meta name="author" content="Sandy Mulia Kesuma">

    <title>@yield('title', 'PLN Commercial Dashboard')</title>

    @include('partials.layouts.MetaConfig')

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    {{-- Font Awesome 6.5.1 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Global CSS --}}
    <link rel="stylesheet" href="{{ asset('css/layouts/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/fact-entry.css') }}">

    @stack('styles')
    @yield('head')

    {{-- 🌓 Anti-Flash: Apply theme BEFORE render to prevent FOUC --}}
    <script>
    (function () {
        var theme = localStorage.getItem('pln_theme') || 'dark';

        // ⭐ Apply BOTH html class (anti-flash) dan body class (untuk CSS selector)
        if (theme === 'light') {
            document.documentElement.classList.add('light-mode-pre');
            // body belum tersedia di <head>, pakai DOMContentLoaded
            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.add('light-mode');
            });
        }

        // Apply accent color
        var accents = {
            orange:'#ff9800', green:'#00ff88', blue:'#3b82f6',
            cyan:'#06b6d4', violet:'#8b5cf6', rose:'#f43f5e'
        };
        var accent = localStorage.getItem('pln_accent') || 'orange';
        document.documentElement.style.setProperty('--accent-orange', accents[accent] || accents.orange);

        // Apply custom font size if saved
        var fontSize = localStorage.getItem('pln_font');
        if (fontSize) {
            document.documentElement.style.fontSize = fontSize + 'px';
        }

        // Disable animations if user prefers reduced motion
        if (localStorage.getItem('pln_anim') === '0') {
            var style = document.createElement('style');
            style.textContent = '*,*::before,*::after{animation:none!important;transition:none!important}';
            document.head.appendChild(style);
        }
    })();
    </script>

    {{-- ⭐ Pre-render styles for anti-flash — WARNA SIDEBAR HARUS SESUAI CSS --}}
    <style>
        /* Body background saat light mode pre-render */
        html.light-mode-pre body {
            background: #f0f2f5 !important;
            color: #1e293b !important;
        }

        /* Navbar — putih di light mode */
        html.light-mode-pre .navbar {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }

        /* ⭐ SIDEBAR — harus #1e293b (slate-800), BUKAN putih */
        html.light-mode-pre .dash-sidebar {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Header dashboard */
        html.light-mode-pre .dash-header {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        .visually-hidden {
            position: absolute; width: 1px; height: 1px; padding: 0;
            margin: -1px; overflow: hidden; clip: rect(0,0,0,0);
            white-space: nowrap; border: 0;
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
                <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                <span class="nav-label">Dashboard</span>
            </a>

            <a href="/historis"
               class="nav-item {{ Request::is('historis*') ? 'active' : '' }}"
               aria-current="{{ Request::is('historis*') ? 'page' : 'false' }}">
                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                <span class="nav-label">Historis</span>
            </a>
        </div>

        <div class="nav-right">
            {{-- 🌓 Theme Toggle Button --}}
            <button id="theme-toggle"
                    class="theme-toggle-btn"
                    aria-label="Ganti tema gelap/terang"
                    title="Toggle Dark/Light Mode (Ctrl+Shift+L)"
                    type="button">
                <i class="fa-solid fa-moon theme-icon-moon" aria-hidden="true"></i>
                <i class="fa-solid fa-sun theme-icon-sun" aria-hidden="true"></i>
            </button>

            <div class="profile-dropdown">
                <button class="profile-btn"
                        aria-label="Menu profil {{ auth()->user()->name }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        type="button">
                    <img src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
                         alt="" class="nav-avatar nav-avatar-img" aria-hidden="true">
                    <span class="nav-username">{{ auth()->user()->name }}</span>
                    <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
                </button>

                <div class="dropdown-content" role="menu">
                    <a href="{{ route('profile') }}" role="menuitem">
                        <i class="fa-solid fa-circle-user" aria-hidden="true"></i> Profile
                    </a>
                    <a href="{{ route('settings') }}" role="menuitem">
                        <i class="fa-solid fa-gear" aria-hidden="true"></i> Settings
                    </a>
                    <hr class="dropdown-divider">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-item" role="menuitem">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout
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

    {{-- Chatbot hanya di dashboard --}}
    @if(!Request::is('historis*') && !Request::is('profile*') && !Request::is('settings*'))
        @include('partials.widgets.chatbot')
    @endif

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    @yield('scripts')
    @stack('scripts')

    {{-- 🌓 Theme Toggle + UI Scripts --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ========== 🌓 THEME TOGGLE FUNCTIONALITY ==========
        const themeToggle = document.getElementById('theme-toggle');
        const htmlEl      = document.documentElement;

        function applyTheme(theme) {
            if (theme === 'light') {
                document.body.classList.add('light-mode');
                htmlEl.classList.add('light-mode-pre');
                localStorage.setItem('pln_theme', 'light');
            } else {
                document.body.classList.remove('light-mode');
                htmlEl.classList.remove('light-mode-pre');
                localStorage.setItem('pln_theme', 'dark');
            }
            // ⭐ TIDAK ada setTimeout — light-mode-pre tidak pernah dihapus saat light mode aktif
        }

        // Initialize theme on load
        const savedTheme = localStorage.getItem('pln_theme') || 'dark';
        applyTheme(savedTheme);

        // Theme toggle click handler
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                const currentTheme = localStorage.getItem('pln_theme') || 'dark';
                applyTheme(currentTheme === 'light' ? 'dark' : 'light');

                // Rotation animation feedback
                this.style.transition = 'transform 0.4s cubic-bezier(0.68,-0.55,0.265,1.55)';
                this.style.transform  = 'rotate(360deg)';
                setTimeout(() => { this.style.transform = ''; }, 400);
            });
        }

        // Keyboard shortcut: Ctrl+Shift+L
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'l') {
                e.preventDefault();
                if (themeToggle) themeToggle.click();
            }
        });

        // System theme change (only if no user preference saved)
        if (!localStorage.getItem('pln_theme')) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                applyTheme(e.matches ? 'dark' : 'light');
            });
        }

        // ========== 👤 NAVBAR DROPDOWN ==========
        const profileBtn  = document.querySelector('.profile-btn');
        const dropContent = document.querySelector('.dropdown-content');

        if (profileBtn && dropContent) {
            profileBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = dropContent.classList.toggle('show');
                profileBtn.setAttribute('aria-expanded', isOpen);
            });

            document.addEventListener('click', function () {
                dropContent.classList.remove('show');
                profileBtn.setAttribute('aria-expanded', 'false');
            });

            dropContent.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // ========== 🔔 TOAST NOTIFICATIONS ==========
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: 3000, timerProgressBar: true,
            didOpen: toast => {
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
        @if(session('pending'))
            Toast.fire({ icon: 'info', title: '{{ addslashes(session('pending')) }}' });
        @endif
    });
    </script>

    @stack('global-scripts')

</body>
</html>