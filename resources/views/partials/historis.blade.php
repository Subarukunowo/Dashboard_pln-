@extends('layouts.app')

@section('title', 'Data Historis PLN Batam 1997–2025')

@section('content')

<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <a href="/dashboard" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none;">
            ← Dashboard
        </a>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:#fff; letter-spacing:0.5px;">DATA HISTORIS PLN BATAM</div>
            <div style="color:var(--text-muted); font-size:0.72rem;">Periode 1997 — 2025</div>
        </div>
    </div>
</header>

<div style="padding: var(--gap); display:flex; flex-direction:column; gap:var(--gap);">

    {{-- ===== FILTER PANEL ===== --}}
    <div class="card">
        <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">

            {{-- Metrik --}}
            <div class="hist-filter-group" style="flex:1; min-width:220px;">
                <label class="hist-label">METRIK</label>
                <select id="histMetrik" class="hist-select">
                    @foreach($metrikList as $m)
                        <option value="{{ $m->kode }}" data-satuan="{{ $m->satuan }}" data-kategori="{{ $m->kategori }}">
                            {{ $m->nama }} ({{ $m->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun dari / sampai --}}
            <div class="hist-filter-group">
                <label class="hist-label">DARI</label>
                <select id="histDari" class="hist-select">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" {{ $t == 1997 ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="hist-filter-group">
                <label class="hist-label">SAMPAI</label>
                <select id="histSampai" class="hist-select">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" {{ $t == 2025 ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mode tampilan --}}
            <div class="hist-filter-group">
                <label class="hist-label">MODE</label>
                <select id="histMode" class="hist-select">
                    <option value="tahunan">Tahunan</option>
                    <option value="triwulan">Triwulan</option>
                    <option value="bulanan">Bulanan</option>
                </select>
            </div>

            {{-- Tombol --}}
            <button id="histBtnGenerate" class="hist-btn-primary">
                <i class="fas fa-chart-line"></i> Generate
            </button>

        </div>

        {{-- Warning mixed --}}
        <div id="histWarning" style="display:none; margin-top:10px; padding:8px 14px;
            background:rgba(255,152,0,0.08); border:1px solid rgba(255,152,0,0.25);
            border-radius:6px; font-size:0.78rem; color:#ff9800;">
        </div>
    </div>

    {{-- ===== FILTER GOLONGAN ===== --}}
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span class="hist-label" style="font-size:0.72rem;">GOLONGAN TARIF</span>
            <div style="display:flex; gap:8px;">
                <button class="hist-btn-sm" id="histSelectAll">Pilih Semua</button>
                <button class="hist-btn-sm" id="histClearAll">Hapus Semua</button>
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            @foreach($golonganList as $g)
                <label class="hist-gol-chip {{ in_array($g->kode, ['TOTAL']) ? 'checked' : '' }}">
                    <input type="checkbox" class="cb-golongan" value="{{ $g->kode }}"
                        {{ in_array($g->kode, ['TOTAL']) ? 'checked' : '' }}>
                    {{ $g->nama }}
                </label>
            @endforeach
        </div>
    </div>

    {{-- ===== CHART ===== --}}
    <div class="card" id="histCardChart" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <div class="card-title" id="histChartTitle">—</div>
                <div style="color:var(--text-muted); font-size:0.75rem;" id="histChartSubtitle"></div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button class="hist-btn-sm active" id="histBtnBar" data-type="bar">Bar</button>
                <button class="hist-btn-sm" id="histBtnLine" data-type="line">Line</button>
                <button class="hist-btn-sm" id="histBtnTable" onclick="toggleTable()">
                    <i class="fas fa-table"></i> Tabel
                </button>
            </div>
        </div>
        <div style="position:relative; height:420px;">
            <canvas id="histChart"></canvas>
        </div>
    </div>

    {{-- ===== TABEL DETAIL ===== --}}
    <div class="card" id="histCardTable" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div class="card-title">Tabel Data</div>
            <button class="hist-btn-sm" onclick="toggleTable()">Sembunyikan</button>
        </div>
        <div class="custom-scroll" style="overflow-x:auto; max-height:400px; overflow-y:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.78rem; white-space:nowrap;">
                <thead id="histThead" style="position:sticky; top:0; background:#1a1a1a; z-index:1;"></thead>
                <tbody id="histTbody"></tbody>
            </table>
        </div>
    </div>

</div>

{{-- ===== STYLES ===== --}}
<style>
.hist-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.hist-select {
    background: #1a1a1a;
    color: #ccc;
    border: 1px solid #333;
    border-radius: 5px;
    padding: 7px 10px;
    font-size: 0.8rem;
    outline: none;
    cursor: pointer;
    min-width: 120px;
}
.hist-select:focus { border-color: var(--accent-orange); }

.hist-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.hist-btn-primary {
    background: var(--accent-orange);
    color: #000;
    border: none;
    border-radius: 6px;
    padding: 9px 22px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
    align-self: flex-end;
}
.hist-btn-primary:hover { background: #ffb74d; }

.hist-btn-sm {
    background: #252525;
    color: #888;
    border: 1px solid #333;
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 0.72rem;
    cursor: pointer;
    transition: all 0.15s;
}
.hist-btn-sm:hover, .hist-btn-sm.active {
    background: var(--accent-orange);
    color: #000;
    border-color: var(--accent-orange);
}

.hist-gol-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 20px;
    font-size: 0.78rem;
    color: #888;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.hist-gol-chip:hover { border-color: #555; color: #ccc; }
.hist-gol-chip.checked {
    background: rgba(255,152,0,0.1);
    border-color: var(--accent-orange);
    color: var(--accent-orange);
}
.hist-gol-chip input { display: none; }

#histThead th {
    padding: 8px 10px;
    text-align: right;
    color: var(--text-muted);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    border-bottom: 1px solid #333;
}
#histThead th:first-child { text-align: left; }
#histTbody tr { border-bottom: 1px solid #1e1e1e; transition: background 0.1s; }
#histTbody tr:hover { background: #1e1e1e; }
#histTbody td {
    padding: 7px 10px;
    text-align: right;
    font-size: 0.78rem;
    font-variant-numeric: tabular-nums;
}
#histTbody td:first-child { text-align: left; color: #888; }
</style>

@endsection

@section('scripts')
<script src="{{ asset('js/Dashboard/pages/historis.js') }}"></script>
@endsection