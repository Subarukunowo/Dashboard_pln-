@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

@push('styles')
    {{-- ===== FAVICON ===== --}}
    <link rel="icon"             type="image/png"  href="{{ asset('assets/favicons/favicon-96x96.png') }}?v={{ time() }}" sizes="96x96">
    <link rel="icon"             type="image/svg+xml" href="{{ asset('assets/favicons/favicon.svg') }}?v={{ time() }}">
    <link rel="shortcut icon"                       href="{{ asset('assets/favicons/favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" sizes="180x180"    href="{{ asset('assets/favicons/apple-touch-icon.png') }}?v={{ time() }}">
    <link rel="manifest"                            href="{{ asset('assets/favicons/site.webmanifest') }}?v={{ time() }}">

    {{-- ===== CSS DASHBOARD (urutan penting) ===== --}}

    {{-- 1. Critical: design tokens + skeleton loader (above-the-fold) --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard-critical.css') }}">

    {{-- 2. Layout: sidebar, header, main content, section visibility --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard-layout.css') }}">

    {{-- 3. Non-critical: chatbot & sidebar style (async) --}}
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}"  media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}"  media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
        <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    </noscript>
@endpush

@section('content')

{{-- ===== HEADER ===== --}}
<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:#fff; letter-spacing:0.5px;">
                PLN COMMERCIAL DASHBOARD
            </div>
            <div id="clock" style="color:var(--text-muted); font-size:0.72rem;"></div>
        </div>
    </div>
    <div class="filters">
        <select id="filterYear" aria-label="Filter tahun">
            <option value="2025">2025</option>
        </select>
        <select id="filterMonth" aria-label="Filter bulan">
            <option value="all">Januari - Desember</option>
            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $index => $month)
                <option value="{{ $index }}">{{ $month }}</option>
            @endforeach
        </select>
    </div>
</header>

{{-- ===== DASHBOARD BODY ===== --}}
<div class="dashboard-layout" id="dashboardLayout">

    @include('partials.sidebar')

    <main class="dash-content" id="mainContent">
        <div class="dashboard-grid">

            @include('partials.stats')

            <div class="dash-section active" data-section-content="bpp">
                @include('partials.BppChart')
            </div>

            <div class="dash-section active" data-section-content="penjualan">
                @include('partials.PenjualanChart')
            </div>

            <div class="dash-section" data-section-content="kwh">
                @include('partials.PenjualanKwh')
            </div>

            <div class="dash-section" data-section-content="goltarif">
                @include('partials.GolTarifBatam')
            </div>

            <div class="dash-section" data-section-content="pendapatan">
                @include('partials.PendapatanBatam')
            </div>

            <div class="dash-section" data-section-content="rpkwh">
                @include('partials.Rpkwh')
            </div>

            <div class="dash-section" data-section-content="pelanggan">
                @include('partials.Pelanggan')
            </div>

            <div class="dash-section" data-section-content="kva">
                @include('partials.Kva')
            </div>

            <div class="dash-section" data-section-content="babin">
                @include('partials.Babin')
            </div>

            <div class="dash-section" data-section-content="jamnyala">
                @include('partials.JamNyala')
            </div>

            <div class="dash-section" data-section-content="losses">
                @include('partials.Losses')
            </div>

            <div class="dash-section" data-section-content="tunggakan">
                @include('partials.Tunggakan')
            </div>

            <div class="dash-section" data-section-content="p2tl">
                @include('partials.P2tl')
            </div>

        </div>
    </main>
</div>

@endsection

@section('scripts')
    {{-- Modulepreload: hint browser untuk fetch modul JS lebih awal --}}
    <link rel="modulepreload" href="{{ asset('js/Dashboard/charts/BaseChart.js') }}">
    <link rel="modulepreload" href="{{ asset('js/Dashboard/services/SheetsService.js') }}">

    <script type="module" src="{{ asset('js/Dashboard/pages/dashboard.js') }}"></script>
    <script src="{{ asset('js/sidebar.js') }}" defer></script>
@endsection