<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PLN Commercial Dashboard')</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/dashboard.css">

    @stack('styles')
    @yield('head')
</head>
<body>

<nav class="navbar">

    {{-- Kiri: navigasi halaman --}}
    <div class="nav-left">
        <a href="/dashboard" class="nav-item {{ Request::is('dashboard') || Request::is('/') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="/historis" class="nav-item {{ Request::is('historis*') ? 'active' : '' }}">
            <i class="fas fa-history"></i>
            <span>Historis</span>
        </a>
    </div>

    {{-- Kanan: profil --}}
    <div class="nav-right">
        <div class="profile-dropdown">
            <button class="profile-btn">
                <img src="{{ auth()->user()->avatar ?? '/img/default-avatar.png' }}"
                     alt="Profile" class="nav-avatar">
                <span class="nav-username">{{ auth()->user()->name }}</span>
                <i class="fas fa-caret-down" style="font-size:0.65rem; color:var(--text-muted);"></i>
            </button>
            <div class="dropdown-content">
                <a href="/profile"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="javascript:void(0)" onclick="openSettings()">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <hr class="dropdown-divider">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

</nav>

<div class="container">
    @yield('content')
</div>

@include('partials.chatbot')

@yield('scripts')
@stack('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

@if(session('success'))
    Toast.fire({ icon: 'success', title: '{{ addslashes(session('success')) }}' });
@endif
@if(session('error'))
    Toast.fire({ icon: 'error', title: '{{ addslashes(session('error')) }}' });
@endif
@if(session('pending'))
    Swal.fire({
        icon: 'info',
        title: 'Menunggu Persetujuan',
        text: '{{ addslashes(session('pending')) }}',
        confirmButtonColor: '#ff9800',
        confirmButtonText: 'OK',
    });
@endif
@if(session('warning'))
    Toast.fire({ icon: 'warning', title: '{{ addslashes(session('warning')) }}' });
@endif
</script>

</body>
</html>