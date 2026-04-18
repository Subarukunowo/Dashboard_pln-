@extends('layouts.app')

@section('title', 'Analisis Metrik Historis 1997-2025')

@section('content')
<header class="header">
    <div>
        <h2>ANALISIS MULTI-METRIK HISTORIS</h2>
        <p style="color:var(--text-muted); margin:4px 0 0; font-size:0.85rem;">PLN BATAM | Periode 1997 — 2025</p>
    </div>
</header>

{{-- Filter Panel Utama --}}
<div class="card" style="margin-bottom:var(--gap);">
    <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
        
        {{-- Y-AXIS CONFIG: Filter Metrik --}}
        <div style="display:flex; flex-direction:column; gap:4px; flex: 1; min-width: 250px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight: bold;">Y-AXIS: PILIH METRIK</label>
            <select id="filterMetrik" style="width:100%; padding: 8px;">
                @foreach($metrikList as $m)
                    <option value="{{ $m->kode }}" data-satuan="{{ $m->satuan }}">
                        {{ $m->nama }} ({{ $m->satuan }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- RENTANG WAKTU --}}
        <div style="display:flex; gap:8px;">
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label style="font-size:0.72rem; color:var(--text-muted);">DARI</label>
                <select id="filterTahunDari">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" {{ $t == 1997 ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label style="font-size:0.72rem; color:var(--text-muted);">SAMPAI</label>
                <select id="filterTahunSampai">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" {{ $t == 2025 ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button id="btnLoadChart" style="background:var(--accent-orange); color:#000; border:none; padding:10px 25px; border-radius:6px; font-weight:700; cursor:pointer; height: 38px;">
            GENERATE ANALISIS
        </button>
    </div>
</div>

{{-- X-AXIS CONFIG: Filter Golongan --}}
<div class="card" style="margin-bottom:var(--gap); border-top: 2px solid #444;">
    <label style="font-size:0.72rem; color:var(--text-muted); font-weight: bold; display: block; margin-bottom: 10px;">
        X-AXIS: KATEGORI GOLONGAN
    </label>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        @foreach($golonganList as $g)
            <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; cursor:pointer; background: #252525; padding: 6px 12px; border-radius: 4px; border: 1px solid #333;">
                <input type="checkbox" class="cb-golongan" value="{{ $g->kode }}" 
                    {{ in_array($g->kode, ['S', 'R', 'B', 'I', 'P', 'M', 'TOTAL']) ? 'checked' : '' }}>
                {{ $g->nama }}
            </label>
        @endforeach
    </div>
</div>

{{-- Visualisasi Section --}}
<div class="card" id="cardChart" style="display:none; margin-bottom:var(--gap);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 id="chartTitle" style="margin:0; font-size:1.1rem; color: #fff;">—</h3>
            <small id="chartSubtitle" style="color:var(--text-muted);"></small>
        </div>
        <div style="display:flex; gap:8px;">
            <select id="filterMode" style="font-size: 0.75rem; background: #1a1a1a; color: #ccc;">
                <option value="tahunan">Agregasi Tahunan</option>
                <option value="triwulan">Breakdown Triwulan</option>
            </select>
            <button id="btnToggleType" style="background:#333; color:#fff; border:none; padding:5px 12px; border-radius:4px; font-size:0.75rem; cursor:pointer;">
                Switch to Line
            </button>
        </div>
    </div>
    
    <div class="chart-container" style="position: relative; height:450px; width:100%;">
        <canvas id="chartHistoris"></canvas>
    </div>
</div>

{{-- Data Table Section --}}
<div class="card" id="cardTable" style="display:none;">
    <div class="card-title">Detailed Matrix Data</div>
    <div class="custom-scroll" style="overflow-x:auto; margin-top: 15px;">
        <table id="tabelHistoris" style="width:100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead id="theadHistoris" style="background: #1a1a1a;"></thead>
            <tbody id="tbodyHistoris"></tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script type="module" src="{{ asset('js/Dashboard/pages/historis.js') }}"></script>
@endsection