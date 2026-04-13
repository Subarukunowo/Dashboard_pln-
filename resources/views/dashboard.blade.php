@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
@endpush

@section('content')

<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <button class="sidebar-toggle" id="sidebarToggle">☰</button>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:#fff; letter-spacing:0.5px;">PLN COMMERCIAL DASHBOARD</div>
            <div id="clock" style="color:var(--text-muted); font-size:0.72rem;"></div>
        </div>
    </div>
    <div class="filters">
        <select id="filterYear">
            <option value="2025">2025</option>
        </select>
        <select id="filterMonth">
            <option value="all">Januari - Desember</option>
            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $index => $month)
                <option value="{{ $index }}">{{ $month }}</option>
            @endforeach
        </select>
    </div>
</header>

<div class="dashboard-layout">

    @include('partials.sidebar')

    <div class="dash-content">
        <div class="dashboard-grid">

            @include('partials.stats')

            <div class="dash-section active" data-section-content="bpp">
                @include('partials.BppChart')
            </div>

            <div class="dash-section active" data-section-content="penjualan">
                @include('partials.PenjualanChart')
            </div>

            <div class="dash-section active" data-section-content="kwh">
                @include('partials.PenjualanKwh')
            </div>

            <div class="dash-section active" data-section-content="goltarif">
                @include('partials.GolTarifBatam')
            </div>

            <div class="dash-section active" data-section-content="pendapatan">
                @include('partials.PendapatanBatam')
            </div>

            <div class="dash-section active" data-section-content="rpkwh">
                @include('partials.Rpkwh')
            </div>

            <div class="dash-section active" data-section-content="pelanggan">
                @include('partials.Pelanggan')
            </div>

            <div class="dash-section active" data-section-content="kva">
                @include('partials.Kva')
            </div>

            <div class="dash-section active" data-section-content="babin">
                @include('partials.Babin')
            </div>

            <div class="dash-section active" data-section-content="jamnyala">
                @include('partials.JamNyala')
            </div>

            <div class="dash-section active" data-section-content="losses">
                @include('partials.Losses')
            </div>

            <div class="dash-section active" data-section-content="tunggakan">
                @include('partials.Tunggakan')
            </div>

            <div class="dash-section active" data-section-content="p2tl">
                @include('partials.P2tl')
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script type="module" src="{{ asset('js/Dashboard/pages/dashboard.js') }}"></script>
    <script src="{{ asset('js/sidebar.js') }}"></script>
@endsection