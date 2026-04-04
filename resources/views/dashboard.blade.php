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
            <option value="0">Januari</option>
            <option value="1">Februari</option>
            <option value="2">Maret</option>
            <option value="3">April</option>
            <option value="4">Mei</option>
            <option value="5">Juni</option>
            <option value="6">Juli</option>
            <option value="7">Agustus</option>
            <option value="8">September</option>
            <option value="9">Oktober</option>
            <option value="10">November</option>
            <option value="11">Desember</option>
        </select>
    </div>
</header>

<main class="dashboard-grid">

    @include('partials.stats')
    @include('partials.BppChart')
    @include('partials.PenjualanChart')
 @include('partials.PenjualanKwh')

 
    <div class="card">
        <div class="card-title">Penjualan Per Golongan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Target vs Realisasi per golongan tarif</p>
        <a href="/penjualan-golongan" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Golongan Tarif</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Realisasi vs target per golongan</p>
        <a href="/golongan-tarif" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    {{-- PENDAPATAN --}}
    <div class="card">
        <div class="card-title">Kinerja Pendapatan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Rencana vs realisasi pendapatan</p>
        <a href="/kinerja-pendapatan" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Pendapatan Batam</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Rencana vs realisasi pendapatan Batam</p>
        <a href="/pendapatan-batam" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Pendapatan Tarif</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Target vs realisasi per tarif</p>
        <a href="/pendapatan-tarif" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    {{-- RPKWH --}}
    <div class="card">
        <div class="card-title">Kinerja RPKWH</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Rencana vs realisasi RPKWH</p>
        <a href="/kinerja-rpkwh" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Analisa RPKWH</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Growth & BPP HJR RPKWH</p>
        <a href="/analisa-rpkwh" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    {{-- PELANGGAN --}}
    <div class="card">
        <div class="card-title">Kinerja Pelanggan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Rencana vs realisasi pelanggan</p>
        <a href="/kinerja-pelanggan" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Penambahan KVA</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Rencana vs realisasi KVA</p>
        <a href="/kinerja-kva" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    {{-- LAINNYA --}}
    <div class="card">
        <div class="card-title">Losses & EMIN</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">02.Losses + EMIN vs - EMIN</p>
        <a href="/losses" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Kinerja Tunggakan</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Data tunggakan pelanggan</p>
        <a href="/tunggakan" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">P2TL</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Data P2TL</p>
        <a href="/p2tl" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

    <div class="card">
        <div class="card-title">Jam Nyala</div>
        <p style="color:var(--text-muted); font-size:0.8rem; margin:0 0 10px;">Data jam nyala</p>
        <a href="/jam-nyala" style="color:var(--accent-orange); font-size:0.8rem;">Lihat Detail →</a>
    </div>

</main>
@endsection

@section('scripts')
<script type="module" src="/js/Dashboard/pages/dashboard.js"></script>
@endsection