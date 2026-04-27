@extends('layouts.app')

@section('title', 'Data Historis PLN Batam')

@push('styles')
    @include('partials.Favicons')
    <link rel="stylesheet" href="{{ asset('css/pages/historis.css') }}?v={{ filemtime(public_path('css/pages/historis.css')) }}">
     <link rel="stylesheet" href="{{ asset('css/components/historis-components.css') }}?v={{ filemtime(public_path('css/components/historis-components.css')) }}">
    @endpush

@section('content')

{{-- ══════════════ HEADER ══════════════ --}}
<header class="dash-header">
    <div class="header-content">
        <a href="/dashboard" class="breadcrumb-link">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <div class="header-divider"></div>
        <div class="header-title">
            <h1>Data Historis PLN Batam</h1>
            <p>Visualisasi & Input Data Kelistrikan</p>
        </div>
    </div>
</header>

<main class="historis-container">

    {{-- ══════════════════════════════════════════ --}}
    {{-- SECTION 1: LIHAT DATA HISTORIS (1997–2025) --}}
    {{-- ══════════════════════════════════════════ --}}
    <section class="card section-historis">
        
        {{-- Section Header --}}
        <div class="section-header">
            <i class="fas fa-chart-area icon-accent"></i>
            <div class="section-info">
                <h2 class="card-title">📊 Lihat Data Historis</h2>
                <span class="text-muted">Periode 1997 — 2025 (Read-Only)</span>
            </div>
        </div>

        {{-- Filter Controls --}}
        <div class="filter-controls">
            
            {{-- Metrik Selector --}}
            <div class="filter-group filter-group--wide">
                <label class="hist-label" for="histMetrik">Metrik</label>
                <select id="histMetrik" class="hist-select" data-placeholder="Pilih metrik...">
                    @foreach($metrikList as $m)
                        <option value="{{ $m->kode }}" 
                                data-satuan="{{ $m->satuan }}" 
                                data-kategori="{{ $m->kategori }}">
                            {{ $m->nama }} ({{ $m->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
            <div class="filter-group">
                <label class="hist-label" for="histDari">Dari</label>
                <select id="histDari" class="hist-select">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" @selected($t == 1997)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="hist-label" for="histSampai">Sampai</label>
                <select id="histSampai" class="hist-select">
                    @foreach($tahunTersedia as $t)
                        <option value="{{ $t }}" @selected($t == 2025)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mode Selector --}}
            <div class="filter-group">
                <label class="hist-label" for="histMode">Mode</label>
                <select id="histMode" class="hist-select">
                    <option value="tahunan">Tahunan</option>
                    <option value="triwulan">Triwulan</option>
                    <option value="bulanan">Bulanan</option>
                </select>
            </div>

            {{-- Action Button --}}
            <button id="histBtnGenerate" class="hist-btn-primary">
                <i class="fas fa-chart-line"></i> Generate
            </button>
        </div>

        {{-- Warning Container --}}
        <div id="histWarning" class="hist-warning-box" role="alert" style="display:none;"></div>
    </section>

    {{-- Filter Golongan Tarif --}}
    <section class="card section-golongan">
        <div class="section-actions">
            <span class="hist-label">Golongan Tarif</span>
            <div class="action-buttons">
                <button class="hist-btn-sm" id="histSelectAll">Pilih Semua</button>
                <button class="hist-btn-sm" id="histClearAll">Hapus Semua</button>
            </div>
        </div>
        
        <div class="golongan-chips">
            @foreach($golonganList as $g)
                <label class="hist-gol-chip {{ $g->kode === 'TOTAL' ? 'checked' : '' }}">
                    <input type="checkbox" 
                           class="cb-golongan" 
                           value="{{ $g->kode }}"
                           @checked($g->kode === 'TOTAL')>
                    <span>{{ $g->nama }}</span>
                </label>
            @endforeach
        </div>
    </section>

    {{-- Chart Display --}}
    <section class="card section-chart" id="histCardChart" style="display:none;">
        <div class="chart-header">
            <div class="chart-info">
                <h3 class="card-title" id="histChartTitle">—</h3>
                <span class="text-muted" id="histChartSubtitle"></span>
            </div>
            <div class="chart-controls">
                <button class="hist-btn-sm active" data-chart-type="bar" id="histBtnBar">Bar</button>
                <button class="hist-btn-sm" data-chart-type="line" id="histBtnLine">Line</button>
                <button class="hist-btn-sm" id="histBtnTable">
                    <i class="fas fa-table"></i> Tabel
                </button>
            </div>
        </div>
        <div id="histChartWrap">
            <canvas id="histChart"></canvas>
        </div>
    </section>

    {{-- Table Display --}}
    <section class="card section-table" id="histCardTable" style="display:none;">
        <div class="table-header">
            <h3 class="card-title">Tabel Data</h3>
            <button class="hist-btn-sm" id="histBtnHideTable">
                <i class="fas fa-times"></i> Sembunyikan
            </button>
        </div>
        <div class="table-wrapper custom-scroll">
            <table class="hist-table">
                <thead id="histThead"></thead>
                <tbody id="histTbody"></tbody>
            </table>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- SECTION 2: INPUT DATA BARU (Tahun Berjalan) --}}
    {{-- ══════════════════════════════════════════════ --}}
    <section class="card section-entry" style="border-left: 3px solid var(--accent-green);">
        
        {{-- Toggle Header --}}
        <button type="button" 
                id="entryToggleBtn" 
                class="entry-toggle"
                aria-expanded="false"
                aria-controls="entryPanel">
            <div class="toggle-content">
                <i class="fas fa-pen-to-square icon-accent"></i>
                <div class="toggle-info">
                    <h3>✏️ Input Data Baru ({{ $currentYear }} — {{ $currentYear + 2 }})</h3>
                    <span class="text-muted">Tambah atau update data untuk periode berjalan dan masa depan</span>
                </div>
            </div>
            <i class="fas fa-chevron-down toggle-icon" id="entryChevron"></i>
        </button>

        {{-- Entry Panel (Collapsible) --}}
        <div id="entryPanel" class="entry-panel" hidden>
            
            {{-- Info Banner --}}
            <div class="hist-info-box" role="note">
                <i class="fas fa-circle-info"></i>
                <strong>Aturan Input:</strong> Data hanya dapat diedit hingga
                <strong>tanggal 7 bulan berikutnya</strong>. Setelah itu dikunci untuk kalkulasi total.
            </div>

            {{-- Entry Controls --}}
            <div class="entry-controls">
                
                {{-- Year Selector --}}
                <div class="filter-group">
                    <label class="hist-label" for="inputTahun">Tahun</label>
                    <select id="inputTahun" class="hist-select">
                        @php
                            $tahunAvailable = ($tahunInputTersedia ?? collect())
                                ->filter(fn($t) => $t >= $currentYear);
                            $defaultTahun = $tahunAvailable->first() ?? $currentYear;
                        @endphp
                        @forelse($tahunAvailable as $t)
                            <option value="{{ $t }}" @selected($t == $defaultTahun)>
                                {{ $t }}
                                @if($t == $currentYear)
                                    (Tahun Ini)
                                @elseif($t > $currentYear)
                                    (+{{ $t - $currentYear }})
                                @endif
                            </option>
                        @empty
                            <option value="{{ $currentYear }}" selected>{{ $currentYear }} (Tahun Ini)</option>
                        @endforelse
                    </select>
                </div>

                {{-- Month Selector --}}
                <div class="filter-group">
                    <label class="hist-label" for="inputBulan">Bulan</label>
                    <select id="inputBulan" class="hist-select">
                        @php
                            $bulanNames = [
                                1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                                5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                                9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                            ];
                        @endphp
                        @foreach($bulanNames as $num => $name)
                            <option value="{{ $num }}" @selected($num == now()->month)>
                                {{ $name }}@if($num == now()->month) (Bulan Ini)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <button id="btnLoadData" class="hist-btn-primary">
                    <i class="fas fa-download"></i> Muat Data
                </button>
                <button id="entrySaveBtn" class="hist-btn-primary green" style="display:none;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>

            {{-- Status Messages --}}
            <div id="entryStatus" class="entry-status" style="display:none;" role="status"></div>
            
            {{-- Locked State --}}
            <div id="entryLocked" class="hist-error-box" style="display:none;" role="alert">
                <i class="fas fa-lock"></i>
                <strong>Periode Terkunci</strong><br>
                Data untuk periode ini sudah tidak dapat diedit karena sudah melewati batas waktu input.
            </div>

            {{-- Entry Table --}}
            <div id="entryTableWrap" style="display:none;">
                <div class="table-wrapper custom-scroll">
                    <table class="entry-table">
                        <thead id="entryThead"></thead>
                        <tbody id="entryTbody"></tbody>
                    </table>
                </div>
                <div class="entry-actions">
                    <button class="hist-btn-sm" id="btnFillAll">
                        <i class="fas fa-zero"></i> Isi Nol
                    </button>
                    <button class="hist-btn-sm" id="btnClearAll">
                        <i class="fas fa-eraser"></i> Reset
                    </button>
                </div>
            </div>

        </div>
    </section>

</main>
@endsection

@push('scripts')
    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>

    {{-- Module Scripts with Cache Busting --}}
    <script src="{{ asset('js/Dashboard/charts/HistorisChart.js') }}?v={{ filemtime(public_path('js/Dashboard/charts/HistorisChart.js')) }}" defer></script>
    <script src="{{ asset('js/Dashboard/pages/HistorisEntry.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/HistorisEntry.js')) }}" defer></script>

    {{-- Page Initialization --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const HistorisPage = {
            elements: {
                entryToggle: document.getElementById('entryToggleBtn'),
                entryPanel: document.getElementById('entryPanel'),
                entryChevron: document.getElementById('entryChevron')
            },

            init() {
                this.bindEvents();
                this.debug();
            },

            bindEvents() {
                const { entryToggle, entryPanel, entryChevron } = this.elements;
                
                entryToggle?.addEventListener('click', () => {
                    const isOpen = !entryPanel.hidden;
                    entryPanel.hidden = isOpen;
                    entryToggle.setAttribute('aria-expanded', !isOpen);
                    entryChevron?.classList.toggle('open', !isOpen);
                    
                    if (typeof toggleEntryPanel === 'function') {
                        toggleEntryPanel();
                    }
                });
            },

            debug() {
                console.log('✅ Historis Page Loaded');
                console.log('📊 HistorisChart:', typeof window.HistorisChart !== 'undefined' ? 'Ready' : 'Not found');
            }
        };

        HistorisPage.init();
    });
    </script>
@endpush