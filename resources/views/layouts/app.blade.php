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
    
    {{-- Tempat untuk chatbot.css dan dashboard.css spesifik --}}
    @stack('styles')
    @yield('head')
</head>
<body>

<nav class="navbar">
    <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">Overview</a>
    <a href="/realisasi-penjualan" class="nav-link {{ request()->is('realisasi-penjualan') ? 'active' : '' }}">Realisasi Penjualan</a>
</nav>

<div class="container">
    @yield('content')
</div>

{{-- Chatbot diletakkan di dalam body agar CSS fixed-nya bekerja --}}
@include('partials.chatbot')

{{-- Tempat script --}}
@yield('scripts')
@stack('scripts')

</body>
</html>