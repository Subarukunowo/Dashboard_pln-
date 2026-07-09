@extends('layouts.app')

@section('title', 'Data Historis PLN Batam')

@push('styles')
    @include('partials.layouts.Favicons')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pages/historis.css') }}?v={{ filemtime(public_path('css/pages/historis.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/components/historis-components.css') }}?v={{ filemtime(public_path('css/components/historis-components.css')) }}">
@endpush

@section('content')

<div class="dash-header">
    <div class="dash-header__left">
        <p class="dash-header__title">DATA HISTORIS PLN BATAM</p>
        <p class="dash-header__sub">Visualisasi &amp; Input Data Kelistrikan</p>
    </div>
</div>

<main class="historis-container">

    {{-- SECTION 1: LIHAT DATA HISTORIS --}}
    <section class="card section-historis" aria-labelledby="section-historis-title">
        <div class="section-header">
            <i class="fas fa-chart-area icon-accent" aria-hidden="true"></i>
            <div class="section-info">
                <h2 id="section-historis-title" class="card-title">Lihat Data Historis</h2>
                <span class="text-muted">Periode 1997 — 2025 (Read-Only)</span>
            </div>
        </div>

        <fieldset class="filter-controls">
            <legend class="sr-only">Filter data historis</legend>

            <div class="filter-group filter-group--wide">
                <label class="hist-label" for="histMetrik">Metrik</label>
                <select id="histMetrik" class="hist-select">
                    @foreach($metrikList as $m)
                        <option value="{{ $m->kode }}"
                                data-satuan="{{ $m->satuan }}"
                                data-kategori="{{ $m->kategori }}">
                            {{ $m->nama }} ({{ $m->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>

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

            <div class="filter-group">
                <label class="hist-label" for="histMode">Mode</label>
                <select id="histMode" class="hist-select">
                    <option value="tahunan">Tahunan</option>
                    <option value="triwulan">Triwulan</option>
                    <option value="bulanan">Bulanan</option>
                </select>
            </div>

            <button id="histBtnGenerate" class="hist-btn-primary" type="button">
                <i class="fas fa-chart-line"></i> Generate
            </button>
        </fieldset>

        <div id="histWarning" class="hist-warning-box" role="alert" style="display:none;"></div>
    </section>

    {{-- SECTION 2: GOLONGAN TARIF --}}
    <section class="card section-golongan" aria-labelledby="section-golongan-title">
        <div class="section-actions">
            <span id="section-golongan-title" class="hist-label">Golongan Tarif</span>
            <span class="text-muted" style="font-size:0.72rem; margin-left:auto; color:#444;">
                Bisa pilih lebih dari satu
            </span>
        </div>

        <fieldset class="golongan-chips">
            <legend class="sr-only">Pilihan golongan tarif</legend>
            @php
                $golonganColors = [
                    'S'     => 'gol-sosial',
                    'R'     => 'gol-rumah',
                    'B'     => 'gol-bisnis',
                    'I'     => 'gol-industri',
                    'P'     => 'gol-publik',
                    'M'     => 'gol-multiguna',
                    'TOTAL' => 'gol-total',
                ];
            @endphp
            @foreach($golonganList as $g)
                @php
                    $colorClass = $golonganColors[$g->kode] ?? 'gol-default';
                    $isChecked  = $g->kode === 'TOTAL';
                @endphp
                <label class="hist-gol-chip {{ $colorClass }} {{ $isChecked ? 'checked' : '' }}"
                       data-golongan="{{ $g->kode }}">
                    <input type="checkbox" class="cb-golongan" name="golongan_tarif[]"
                           value="{{ $g->kode }}" @checked($isChecked)>
                    <span class="chip-text">{{ $g->nama }}</span>
                    <span class="chip-indicator"></span>
                </label>
            @endforeach
        </fieldset>
    </section>

    {{-- SECTION 3: CHART --}}
    <section class="card section-chart" id="histCardChart" style="display:none;" aria-labelledby="chart-title">
        <div class="chart-header">
            <div class="chart-info">
                <h3 id="chart-title" class="card-title">—</h3>
                <span class="text-muted" id="histChartSubtitle"></span>
            </div>
            <fieldset class="chart-controls">
                <legend class="sr-only">Tipe visualisasi</legend>
                <button class="hist-btn-sm active" data-chart-type="bar"  id="histBtnBar"  type="button">Bar</button>
                <button class="hist-btn-sm"        data-chart-type="line" id="histBtnLine" type="button">Line</button>
                <button class="hist-btn-sm" id="histBtnTable" type="button">
                    <i class="fas fa-table"></i> Tabel
                </button>
            </fieldset>
        </div>
        <figure id="histChartWrap" aria-label="Grafik data historis">
            <canvas id="histChart"></canvas>
            <figcaption class="sr-only">Grafik data historis berdasarkan filter yang dipilih</figcaption>
        </figure>
    </section>

    {{-- SECTION 4: TABLE --}}
    <section class="card section-table" id="histCardTable" style="display:none;" aria-labelledby="table-title">
        <div class="table-header">
            <h3 id="table-title" class="card-title">Tabel Data</h3>
            <button class="hist-btn-sm" id="histBtnHideTable" type="button">
                <i class="fas fa-xmark"></i> Sembunyikan
            </button>
        </div>
        <div class="table-wrapper custom-scroll">
            <table class="hist-table" aria-describedby="table-title">
                <thead id="histThead">
                    <tr>
                        <th scope="col">Periode</th>
                    </tr>
                </thead>
                <tbody id="histTbody"></tbody>
            </table>
        </div>
    </section>

    {{-- SECTION 5: INPUT DATA BARU --}}
    <section class="card section-entry"
             style="border-left: 3px solid var(--accent-green, #00ff88);"
             aria-labelledby="entry-title">

        <button type="button" id="entryToggleBtn" class="entry-toggle"
                aria-expanded="false" aria-controls="entryPanel">
            <div class="toggle-content">
                <i class="fas fa-edit icon-accent" aria-hidden="true"></i>
                <div class="toggle-info">
                    <h3 id="entry-title">Input Data Baru ({{ $currentYear }} — {{ $currentYear + 2 }})</h3>
                    <span class="text-muted">Tambah atau update data untuk periode berjalan dan masa depan</span>
                </div>
            </div>
            <i class="fas fa-chevron-down toggle-icon" id="entryChevron" aria-hidden="true"></i>
        </button>

        <div id="entryPanel" class="entry-panel" hidden>

            <div class="hist-info-box" role="note">
                <i class="fas fa-circle-info"></i>
                <strong>Aturan Input:</strong> Data hanya dapat diedit hingga
                <strong>tanggal 7 bulan berikutnya</strong>. Setelah itu dikunci untuk kalkulasi total.
            </div>

            {{-- CONTROLS ROW --}}
            <fieldset class="entry-controls">
                <legend class="sr-only">Kontrol input data</legend>

                <div class="filter-group">
                    <label class="hist-label" for="inputTahun">Tahun</label>
                    <select id="inputTahun" class="hist-select">
                        @php
                            $currentYear    = $currentYear ?? now()->year;
                            $tahunAvailable = ($tahunInputTersedia ?? collect())
                                ->filter(fn($t) => $t >= $currentYear);
                            $defaultTahun   = $tahunAvailable->first() ?? $currentYear;
                        @endphp
                        @forelse($tahunAvailable as $t)
                            <option value="{{ $t }}" @selected($t == $defaultTahun)>
                                {{ $t }}
                                @if($t == $currentYear)(Tahun Ini)
                                @elseif($t > $currentYear)(+{{ $t - $currentYear }})
                                @endif
                            </option>
                        @empty
                            <option value="{{ $currentYear }}" selected>{{ $currentYear }} (Tahun Ini)</option>
                        @endforelse
                    </select>
                </div>

                <div class="filter-group">
                    <label class="hist-label" for="inputBulan">Bulan</label>
                    <select id="inputBulan" class="hist-select">
                        @php
                            $bulanNames = [
                                1=>'Januari',  2=>'Februari', 3=>'Maret',    4=>'April',
                                5=>'Mei',      6=>'Juni',     7=>'Juli',     8=>'Agustus',
                                9=>'September',10=>'Oktober', 11=>'November',12=>'Desember'
                            ];
                        @endphp
                        @foreach($bulanNames as $num => $name)
                            <option value="{{ $num }}" @selected($num == now()->month)>
                                {{ $name }}@if($num == now()->month) (Bulan Ini)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <button id="btnLoadData" class="hist-btn-primary" type="button">
                    <i class="fas fa-rotate-right"></i> Muat Data
                </button>

                <a href="{{ route('fact-historis.download-template') }}"
                   class="hist-btn-primary hist-btn-template"
                   download title="Download template Excel">
                    <i class="fas fa-file-excel"></i> Template
                </a>

                <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv"
                       style="display:none;" aria-label="Pilih file Excel">
                <button id="btnPickExcel" class="hist-btn-primary hist-btn-import" type="button"
                        title="Import data dari file Excel">
                    <i class="fas fa-file-import"></i> Import Excel
                </button>

                <button id="entrySaveBtn" class="hist-btn-primary green" type="button" style="display:none;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </fieldset>

            {{-- Import status bar --}}
            <output id="importStatusBar" class="import-status-bar" style="display:none;">
                <div class="import-status-inner">
                    <i class="fas fa-file-excel" style="color:#28a745;"></i>
                    <span id="importStatusText"></span>
                    <button id="btnClearImport" class="import-clear-btn" type="button" title="Hapus">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="import-progress-track" id="importProgressTrack" style="display:none;">
                    <div class="import-progress-fill" id="importProgressFill" style="width:0%;"></div>
                </div>
            </output>

            <output id="entryStatus" class="entry-status" style="display:none;"></output>

            <div id="entryLocked" class="hist-error-box" style="display:none;" role="alert">
                <i class="fas fa-lock"></i>
                <strong>Periode Terkunci</strong><br>
                Data untuk periode ini sudah tidak dapat diedit karena sudah melewati batas waktu input.
            </div>

            {{-- TABEL ENTRY --}}
            <div id="entryTableWrap" style="display:none;">

                <div id="tableDragOverlay" class="table-drag-overlay" style="display:none;" aria-hidden="true">
                    <div class="table-drag-overlay-inner">
                        <i class="fas fa-file-excel"></i>
                        <span>Lepaskan untuk import data Excel</span>
                    </div>
                </div>

                <div class="table-wrapper custom-scroll">
                    <table class="entry-table" aria-label="Form input data per golongan">
                        <thead id="entryThead">
                            <tr>
                                <th scope="col">Golongan</th>
                            </tr>
                        </thead>
                        <tbody id="entryTbody"></tbody>
                    </table>
                </div>

                <div class="entry-actions">
                    <button class="hist-btn-sm" id="btnFillAll"  type="button">Isi Nol</button>
                    <button class="hist-btn-sm" id="btnClearAll" type="button">
                        <i class="fas fa-eraser"></i> Reset
                    </button>
                </div>
            </div>

        </div>
    </section>

</main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
            integrity="sha384-REPLACE_WITH_ACTUAL_SRI_HASH_FROM_JSDELIVR"
            crossorigin="anonymous" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"
            integrity="sha512-REPLACE_WITH_ACTUAL_SRI_HASH_FROM_CDNJS"
            crossorigin="anonymous"></script>
    @vite([
        'resources/js/Dashboard/charts/HistorisChart.js',
        'resources/js/Dashboard/pages/HistorisEntry.js',
        'resources/js/Dashboard/pages/HistorisImport.js',
    ])
@endpush