@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

@push('styles')
    <link rel="icon"             type="image/png"     href="{{ asset('assets/favicons/favicon-96x96.png') }}?v={{ time() }}" sizes="96x96">
    <link rel="icon"             type="image/svg+xml" href="{{ asset('assets/favicons/favicon.svg') }}?v={{ time() }}">
    <link rel="shortcut icon"                         href="{{ asset('assets/favicons/favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" sizes="180x180"      href="{{ asset('assets/favicons/apple-touch-icon.png') }}?v={{ time() }}">
    <link rel="manifest"                              href="{{ asset('assets/favicons/site.webmanifest') }}?v={{ time() }}">

    @vite([
        'resources/css/components/dashboard-components.css',
        'resources/css/pages/dashboard.css',
    ])

    <link rel="stylesheet" href="{{ Vite::asset('resources/css/components/chatbot.css') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/layouts/sidebar.css') }}"   media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/components/chatbot.css') }}">
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/layouts/sidebar.css') }}">
    </noscript>
@endpush

@section('content')

<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:var(--text-primary); letter-spacing:0.5px;">
                {{ __('dashboard.title') }}
            </div>
            <div id="clock" style="color:var(--text-muted); font-size:0.72rem;"></div>
        </div>
    </div>
    <div class="filters">
        <select id="filterYear" aria-label="{{ __('dashboard.filter.tahun') }}">
            <option value="2025">2025</option>
        </select>
        <select id="filterMonth" aria-label="{{ __('dashboard.filter.bulan') }}">
            <option value="all">{{ __('dashboard.filter.semua_bulan') }}</option>
            @foreach(range(0, 11) as $i)
                <option value="{{ $i }}">
                    {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>
</header>

<div class="dashboard-layout" id="dashboardLayout">

    @include('partials.layouts.Sidebar')

    <main class="dash-content" id="mainContent">
        <div class="dashboard-grid">

            @include('partials.widgets.Stats')

            @foreach([
                'bpp'        => 'Bpp',
                'penjualan'  => 'Penjualan',
                'kwh'        => 'PenjualanKwh',
                'goltarif'   => 'GolTarifBatam',
                'pendapatan' => 'PendapatanBatam',
                'rpkwh'      => 'Rpkwh',
                'pelanggan'  => 'Pelanggan',
                'kva'        => 'Kva',
                'babin'      => 'Babin',
                'jamnyala'   => 'JamNyala',
                'losses'     => 'Losses',
                'tunggakan'  => 'Tunggakan',
                'p2tl'       => 'P2tl',
            ] as $section => $partial)
                <div class="dash-section {{ $loop->first || $loop->index === 1 ? 'active' : '' }}"
                     data-section-content="{{ $section }}">
                    @include("partials.dashboard.{$partial}")
                </div>
            @endforeach

        </div>
    </main>
</div>

@endsection

@section('scripts')
    @vite('resources/js/Dashboard/pages/dashboard.js')
    <script src="{{ asset('js/sidebar.js') }}" defer></script>
@endsection