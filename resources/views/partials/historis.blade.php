@extends('layouts.app')

@section('title', 'Data Historis PLN Batam')

@push('styles')
{{-- 🔥 GLOBAL FAVICON (Embedded Direct - No Cache) --}}
<link rel="icon" type="image/png" href="{{ asset('assets/favicons/favicon-96x96.png') }}?v={{ time() }}" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicons/favicon.svg') }}?v={{ time() }}" />
<link rel="shortcut icon" href="{{ asset('assets/favicons/favicon.ico') }}?v={{ time() }}" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicons/apple-touch-icon.png') }}?v={{ time() }}" />
<link rel="manifest" href="{{ asset('assets/favicons/site.webmanifest') }}?v={{ time() }}" />

<link rel="stylesheet" href="{{ asset('css/historis-page.css') }}">
<style>
    /* ─── Filter & Button Components ─── */
    .hist-filter-group { flex: 1; min-width: 150px; }
    .hist-label { display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 4px; letter-spacing: 0.05em; text-transform: uppercase; }
    .hist-select {
        width: 100%; padding: 8px 12px; background: #1a1a1a; border: 1px solid #333;
        border-radius: 6px; color: #fff; font-size: 0.85rem; cursor: pointer; transition: border-color 0.2s;
    }
    .hist-select:focus { outline: none; border-color: var(--accent-orange); }
    .hist-btn-primary {
        padding: 8px 16px; background: var(--accent-orange); color: #000;
        border: none; border-radius: 6px; font-weight: 600; cursor: pointer;
        font-size: 0.82rem; transition: filter 0.2s, opacity 0.2s; white-space: nowrap;
    }
    .hist-btn-primary:hover { filter: brightness(1.1); }
    .hist-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; filter: none; }
    .hist-btn-primary.green { background: var(--accent-green); }
    .hist-btn-sm {
        padding: 5px 10px; background: #252525; color: #ccc; border: 1px solid #383838;
        border-radius: 4px; font-size: 0.75rem; cursor: pointer; transition: all 0.15s; white-space: nowrap;
    }
    .hist-btn-sm:hover { background: #333; color: #fff; }
    .hist-btn-sm.active { background: var(--accent-orange); color: #000; border-color: var(--accent-orange); font-weight: 600; }

    /* ─── Golongan Chips ─── */
    .hist-gol-chip {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px;
        background: #1a1a1a; border: 1px solid #333; border-radius: 20px;
        font-size: 0.74rem; color: #bbb; cursor: pointer; transition: all 0.15s; user-select: none;
    }
    .hist-gol-chip:hover { border-color: #555; color: #fff; }
    .hist-gol-chip.checked { background: rgba(0,255,136,0.08); border-color: var(--accent-green); color: var(--accent-green); }
    .hist-gol-chip input { display: none; }

    /* ─── Cards ── */
    .card {
        background: #141414; border: 1px solid #2a2a2a; border-radius: 12px;
        padding: 18px; margin-bottom: var(--gap);
    }
    .card-title { font-weight: 700; font-size: 0.92rem; color: #fff; margin-bottom: 4px; }

    /* ─── Entry Inputs ─── */
    .entry-input {
        width: 100%; padding: 6px 8px; background: #1a1a1a; border: 1px solid #333;
        border-radius: 4px; color: #fff; font-size: 0.8rem; text-align: center; transition: border-color 0.2s;
    }
    .entry-input:focus { outline: none; border-color: var(--accent-orange); background: #1f1f1f; }
    .entry-input.has-value { border-color: #2a6; }
    .entry-input:disabled { opacity: 0.4; cursor: not-allowed; }

    /* ─── Warning / Status Banners ─── */
    .hist-warning-box {
        padding: 8px 14px; background: rgba(255,152,0,0.07); border: 1px solid rgba(255,152,0,0.25);
        border-radius: 6px; font-size: 0.78rem; color: #ff9800;
    }
    .hist-info-box {
        padding: 10px 14px; background: rgba(0,255,136,0.05); border: 1px solid rgba(0,255,136,0.18);
        border-radius: 6px; font-size: 0.75rem; color: #00ff88;
    }
    .hist-error-box {
        padding: 10px 14px; background: rgba(244,67,54,0.05); border: 1px solid rgba(244,67,54,0.2);
        border-radius: 6px; font-size: 0.78rem; color: #f44336;
    }

    /* ─── Chart Container ─── */
    #histChartWrap { position: relative; height: 420px; }

    /* ─── Table ─── */
    .hist-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; white-space: nowrap; }
    .hist-table th { padding: 8px 10px; text-align: right; border-bottom: 1px solid #2a2a2a; color: #aaa; font-weight: 600; }
    .hist-table th:first-child { text-align: left; position: sticky; left: 0; background: #141414; z-index: 2; }
    .hist-table td { padding: 7px 10px; text-align: right; border-bottom: 1px solid #1e1e1e; color: #888; }
    .hist-table td:first-child { text-align: left; color: #ccc; position: sticky; left: 0; background: #141414; z-index: 1; }
    .hist-table tr:hover td { background: #1a1a1a; }

    /* ─── Entry Toggle Chevron ─── */
    #entryChevron { transition: transform 0.25s; }
    #entryChevron.open { transform: rotate(180deg); }
</style>
@endpush

@section('content')

{{-- ══════════════ HEADER ══════════════ --}}
<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <a href="/dashboard" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none; display:flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <div style="width:1px; height:24px; background:#333;"></div>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:#fff; letter-spacing:0.5px;">DATA HISTORIS PLN BATAM</div>
            <div style="color:var(--text-muted); font-size:0.72rem;">Visualisasi &amp; Input Data Kelistrikan</div>
        </div>
    </div>
</header>

<div style="padding: var(--gap); display:flex; flex-direction:column; gap:var(--gap);">

    {{-- ══════════════════════════════════════════ --}}
    {{-- SECTION 1 : LIHAT DATA HISTORIS (1997–2025) --}}
    {{-- ══════════════════════════════════════════ --}}

    {{-- Filter Utama --}}
    <div class="card">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #222;">
            <i class="fas fa-chart-area" style="color:var(--accent-orange);"></i>
            <div>
                <div class="card-title">📊 Lihat Data Historis</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">Periode 1997 — 2025 (Read-Only)</div>
            </div>
        </div>

        <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end;">

            <div class="hist-filter-group" style="min-width:200px;">
                <label class="hist-label">Metrik</label>
                <select id="histMetrik" class="hist-select">
                    @foreach($metrikList as $m)
                        <option value="{{ $m->kode }}" data-satuan="{{ $m->satuan }}" data-kategori="{{ $m->kategori }}">
                            {{ $m->nama }} ({{ $m->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="hist-filter-group">
                <label class="hist-label">Dari</label>
                <select id="histDari" class="hist-select">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" @selected($t == 1997)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="hist-filter-group">
                <label class="hist-label">Sampai</label>
                <select id="histSampai" class="hist-select">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" @selected($t == 2025)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="hist-filter-group">
                <label class="hist-label">Mode</label>
                <select id="histMode" class="hist-select">
                    <option value="tahunan">Tahunan</option>
                    <option value="triwulan">Triwulan</option>
                    <option value="bulanan">Bulanan</option>
                </select>
            </div>

            <button id="histBtnGenerate" class="hist-btn-primary">
                <i class="fas fa-chart-line"></i> Generate
            </button>

        </div>

        {{-- Warning box (hidden by default) --}}
        <div id="histWarning" class="hist-warning-box" style="display:none; margin-top:10px;"></div>
    </div>

    {{-- Filter Golongan --}}
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span class="hist-label">Golongan Tarif</span>
            <div style="display:flex; gap:6px;">
                <button class="hist-btn-sm" id="histSelectAll">Pilih Semua</button>
                <button class="hist-btn-sm" id="histClearAll">Hapus Semua</button>
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            @foreach($golonganList as $g)
                <label class="hist-gol-chip {{ $g->kode === 'TOTAL' ? 'checked' : '' }}">
                    <input type="checkbox" class="cb-golongan" value="{{ $g->kode }}"
                           @checked($g->kode === 'TOTAL')>
                    {{ $g->nama }}
                </label>
            @endforeach
        </div>
    </div>

    {{-- Chart Card --}}
    <div class="card" id="histCardChart" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <div class="card-title" id="histChartTitle">—</div>
                <div style="color:var(--text-muted); font-size:0.74rem;" id="histChartSubtitle"></div>
            </div>
            <div style="display:flex; gap:6px; align-items:center;">
                <button class="hist-btn-sm active" id="histBtnBar">Bar</button>
                <button class="hist-btn-sm" id="histBtnLine">Line</button>
                <button class="hist-btn-sm" id="histBtnTable">
                    <i class="fas fa-table"></i> Tabel
                </button>
            </div>
        </div>
        <div id="histChartWrap">
            <canvas id="histChart"></canvas>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card" id="histCardTable" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div class="card-title">Tabel Data</div>
            <button class="hist-btn-sm" id="histBtnHideTable">
                <i class="fas fa-times"></i> Sembunyikan
            </button>
        </div>
        <div class="custom-scroll" style="overflow-x:auto; max-height:400px; overflow-y:auto;">
            <table class="hist-table">
                <thead id="histThead" style="position:sticky; top:0; background:#141414; z-index:1;"></thead>
                <tbody id="histTbody"></tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- SECTION 2 : INPUT DATA BARU (Tahun Berjalan) --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="card" style="border-left: 3px solid var(--accent-green); margin-top:4px;">

        {{-- Toggle Header --}}
        <div id="entryToggleBtn"
             style="display:flex; justify-content:space-between; align-items:center; cursor:pointer; padding:2px 0;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-pen-to-square" style="color:var(--accent-green); font-size:1.05rem;"></i>
                <div>
                    <div style="font-weight:700; font-size:0.9rem; color:#fff;">
                        ✏️ Input Data Baru ({{ $currentYear }} — {{ $currentYear + 2 }})
                    </div>
                    <div style="font-size:0.72rem; color:var(--text-muted);">
                        Tambah atau update data untuk periode berjalan dan masa depan
                    </div>
                </div>
            </div>
            <i class="fas fa-chevron-down" id="entryChevron"
               style="color:var(--text-muted); font-size:0.85rem;"></i>
        </div>

        {{-- Entry Panel --}}
        <div id="entryPanel" style="display:none; margin-top:16px; border-top:1px solid #222; padding-top:16px;">

            {{-- Info Banner --}}
            <div class="hist-info-box" style="margin-bottom:14px;">
                <i class="fas fa-circle-info" style="margin-right:6px;"></i>
                <strong>Aturan Input:</strong> Data hanya dapat diedit hingga
                <strong>tanggal 7 bulan berikutnya</strong>. Setelah itu dikunci untuk kalkulasi total.
            </div>

            {{-- Selectors --}}
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:14px;">

                <div class="hist-filter-group">
                    <label class="hist-label">Tahun</label>
                    <select id="inputTahun" class="hist-select">
                        @php
                            $tahunAvailable = ($tahunInputTersedia ?? collect())->filter(fn($t) => $t >= $currentYear);
                            $defaultTahun   = $tahunAvailable->first() ?? $currentYear;
                        @endphp
                        @forelse($tahunAvailable as $t)
                            <option value="{{ $t }}" @selected($t == $defaultTahun)>
                                {{ $t }}{{ $t == $currentYear ? ' (Tahun Ini)' : ($t > $currentYear ? ' (+'.($t - $currentYear).')' : '') }}
                            </option>
                        @empty
                            <option value="{{ $currentYear }}" selected>{{ $currentYear }} (Tahun Ini)</option>
                        @endforelse
                    </select>
                </div>

                <div class="hist-filter-group">
                    <label class="hist-label">Bulan</label>
                    <select id="inputBulan" class="hist-select">
                        @php
                            $bulanNames = [
                                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                                9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
                            ];
                        @endphp
                        @foreach($bulanNames as $num => $name)
                            <option value="{{ $num }}" @selected($num == now()->month)>
                                {{ $name }}{{ $num == now()->month ? ' (Bulan Ini)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button id="btnLoadData" class="hist-btn-primary" style="align-self:flex-end;">
                    <i class="fas fa-download"></i> Muat Data
                </button>
                <button id="entrySaveBtn" class="hist-btn-primary green"
                        style="display:none; align-self:flex-end;">
                    <i class="fas fa-save"></i> Simpan
                </button>

            </div>

            {{-- Status / Locked Messages --}}
            <div id="entryStatus" style="display:none; padding:8px 14px; border-radius:6px; font-size:0.78rem; margin-bottom:12px;"></div>

            <div id="entryLocked" class="hist-error-box" style="display:none; text-align:center; padding:20px;">
                <i class="fas fa-lock" style="margin-right:8px; font-size:1.1rem;"></i>
                <strong>Periode Terkunci</strong><br>
                Data untuk periode ini sudah tidak dapat diedit karena sudah melewati batas waktu input.
            </div>

            {{-- Entry Table --}}
            <div id="entryTableWrap" style="display:none;">
                <div class="custom-scroll" style="overflow-x:auto; max-height:500px;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.78rem;">
                        <thead id="entryThead" style="position:sticky; top:0; background:#141414; z-index:1;"></thead>
                        <tbody id="entryTbody"></tbody>
                    </table>
                </div>
                <div style="display:flex; gap:8px; margin-top:12px;">
                    <button class="hist-btn-sm" id="btnFillAll">
                        <i class="fas fa-zero"></i> Isi Nol
                    </button>
                    <button class="hist-btn-sm" id="btnClearAll">
                        <i class="fas fa-eraser"></i> Reset
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Load Chart.js library FIRST --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

{{-- Load module scripts dengan cache busting --}}
<script src="{{ asset('js/Dashboard/charts/HistorisChart.js') }}?v={{ filemtime(public_path('js/Dashboard/charts/HistorisChart.js')) }}"></script>
<script src="{{ asset('js/Dashboard/pages/HistorisEntry.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/HistorisEntry.js')) }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Entry Panel Toggle ONLY ──────────────────────────────────────
    // Semua logic chart & golongan ditangani HistorisChart.js
    const entryToggleBtn = document.getElementById('entryToggleBtn');
    const entryPanel     = document.getElementById('entryPanel');
    const entryChevron   = document.getElementById('entryChevron');

    entryToggleBtn?.addEventListener('click', function () {
        const isOpen = entryPanel.style.display !== 'none';
        entryPanel.style.display = isOpen ? 'none' : 'block';
        entryChevron?.classList.toggle('open', !isOpen);
        if (typeof toggleEntryPanel === 'function') toggleEntryPanel();
    });

    // ── Debug: Pastikan module sudah loaded ─────────────────────────
    console.log('✅ DOM ready — HistorisChart available:', typeof window.HistorisChart !== 'undefined');
    
    // Auto-load chart dengan default filter jika diinginkan
    // window.HistorisChart?.load(); 
});
</script>
@endpush