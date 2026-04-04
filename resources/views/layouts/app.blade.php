<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PLN Commercial Dashboard')</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/css/dashboard.css">
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

@yield('scripts')
</body>
</html>