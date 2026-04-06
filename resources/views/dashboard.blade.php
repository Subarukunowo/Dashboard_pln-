@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

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
    {{-- ===== BELUM DIIMPLEMENTASI ===== --}}
    <div class="card">
        <div class="card-title">Penjualan Per Golongan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Target vs Realisasi per golongan tarif</p>
    </div>

    <div class="card">
        <div class="card-title">Golongan Tarif</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Realisasi vs target per golongan</p>
    </div>

    <div class="card">
        <div class="card-title">Kinerja Pendapatan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Rencana vs realisasi pendapatan</p>
    </div>

    <div class="card">
        <div class="card-title">Kinerja RPKWH</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Rencana vs realisasi RPKWH</p>
    </div>

    <div class="card">
        <div class="card-title">Analisa RPKWH</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Growth & BPP HJR RPKWH</p>
    </div>

    <div class="card">
        <div class="card-title">Kinerja Pelanggan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Rencana vs realisasi pelanggan</p>
    </div>

    <div class="card">
        <div class="card-title">Penambahan KVA</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Rencana vs realisasi KVA</p>
    </div>

    <div class="card">
        <div class="card-title">Losses & EMIN</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">02.Losses + EMIN vs - EMIN</p>
    </div>

    <div class="card">
        <div class="card-title">Kinerja Tunggakan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Data tunggakan pelanggan</p>
    </div>

    <div class="card">
        <div class="card-title">P2TL</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Data P2TL</p>
    </div>

    <div class="card">
        <div class="card-title">Jam Nyala</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0;">Data jam nyala</p>
    </div>

</main>
@endsection

@section('scripts')
<script type="module" src="{{ asset('js/Dashboard/pages/dashboard.js') }}"></script>
@endsection