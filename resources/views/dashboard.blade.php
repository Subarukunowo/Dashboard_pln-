@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

@push('styles')
    {{-- Memastikan CSS chatbot dan dashboard spesifik dimuat ke layout utama --}}
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<header class="header">
    <div>
        <h2>PLN COMMERCIAL DASHBOARD</h2>
        <div id="clock" style="color:var(--text-muted); font-size:0.9rem;"></div>
    </div>
    <div class="filters">
        <select id="filterYear">
            <option value="2025">2025</option>
        </select>
        <select id="filterMonth">
            <option value="all">Januari - Desember</option>
            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                <option value="{{ $index }}">{{ $month }}</option>
            @endforeach
        </select>
    </div>
</header>

{{-- Kontainer utama dashboard --}}
<main class="dashboard-grid">
    {{-- ===== STATISTIK ===== --}}
    @include('partials.stats')

    {{-- ===== BIAYA POKOK PRODUKSI (BPP) ===== --}}
    @include('partials.BppChart')

    {{-- ===== KINERJA PENJUALAN ===== --}}
    @include('partials.PenjualanChart')
    @include('partials.PenjualanKwh')

    {{-- ===== PENDAPATAN PLN BATAM ===== --}}
    @include('partials.GolTarifBatam')
    @include('partials.PendapatanBatam')

    {{-- ===== RPKWH ===== --}}
    @include('partials.Rpkwh')

    {{-- ===== PELANGGAN ===== --}}
    @include('partials.Pelanggan')

    {{-- ===== KVA ===== --}}
    @include('partials.Kva')

    {{-- ===== BABIN & WRKR ===== --}}
    @include('partials.Babin')

    {{-- ===== JAM NYALA ===== --}}
    @include('partials.JamNyala')

    {{-- ===== LOSSES ===== --}}
    @include('partials.Losses')

    {{-- ===== KINERJA TUNGGAKAN ===== --}}
    @include('partials.Tunggakan')

    {{-- ===== P2TL ===== --}}
    @include('partials.P2tl')
</main>

{{-- 
    PENTING: Chatbot ditaruh di LUAR <main> agar tidak masuk ke hitungan grid.
    Ini yang membuat dashboard kamu muncul kembali dengan benar.
--}}

@endsection

@section('scripts')
{{-- Memanggil logika dashboard utama (termasuk inisialisasi chatbot) --}}
<script type="module" src="{{ asset('js/Dashboard/pages/dashboard.js') }}"></script>
@endsection