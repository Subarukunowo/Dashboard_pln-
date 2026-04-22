@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

@push('styles')
    {{-- 🔥 CRITICAL CSS: Inline langsung di HTML (di atas fold) --}}
    <style>
        /* Critical styles untuk layout dasar - render instan */
        :root{--bg-dark:#121212;--card-bg:#1e1e1e;--accent-orange:#ff9800;--accent-green:#00ff88;--text-main:#fff;--text-muted:#888;--gap:20px}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg-dark);color:var(--text-main);font-family:'Inter','Segoe UI',sans-serif;line-height:1.5}
        .dash-header{display:flex;align-items:center;justify-content:space-between;padding:0 30px;height:56px;background:#161616;border-bottom:1px solid #222;position:sticky;top:0;z-index:100}
        .dashboard-layout{display:flex;min-height:calc(100vh - 104px)}
        .dash-content{flex:1;padding:30px;overflow-y:auto}
        .dashboard-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:var(--gap)}
        .card{background:var(--card-bg);border-radius:12px;padding:20px;border:1px solid #333}
        .chart-wrapper{position:relative;height:300px;width:100%}
        .filters{display:flex;gap:10px}
        select{background:var(--card-bg);color:#fff;border:1px solid #444;padding:8px 15px;border-radius:5px}
        .sidebar-toggle{display:none;background:var(--accent-orange);border:none;border-radius:6px;width:32px;height:32px;color:#000;cursor:pointer}
        @media(max-width:900px){.sidebar-toggle{display:flex}}
        
        /* Loading state yang halus */
        .chart-wrapper{background:linear-gradient(90deg,var(--card-bg) 25%,#2a2a2a 50%,var(--card-bg) 75%);background-size:200% 100%;animation:loading 1.5s infinite}
        @keyframes loading{0%{background-position:200% 0}100%{background-position:-200% 0}}
        
        /* Hide scrollbar untuk cleaner look saat loading */
        .dash-content{scrollbar-width:thin;scrollbar-color:var(--accent-orange) #1a1a1a}
    </style>

    {{-- 📦 Full CSS: load setelah critical CSS --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    {{-- ⚡ Non-critical CSS: tetap async --}}
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}" media="print" onload="this.media='all'">
    
    <noscript>
        <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
        <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    </noscript>
@endpush

@section('content')

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

{{-- ✅ HAPUS opacity trick - biarkan render natural --}}
<div class="dashboard-layout" id="dashboardLayout">

    @include('partials.sidebar')

    <main class="dash-content" id="mainContent">
        <div class="dashboard-grid">

            @include('partials.stats')

            {{-- Semua section tetap ada, chart akan di-init progresif --}}
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
    <link rel="modulepreload" href="{{ asset('js/Dashboard/charts/BaseChart.js') }}">
    <link rel="modulepreload" href="{{ asset('js/Dashboard/services/SheetsService.js') }}">
    
    <script type="module" src="{{ asset('js/Dashboard/pages/dashboard.js') }}"></script>
    <script src="{{ asset('js/sidebar.js') }}" defer></script>
@endsection