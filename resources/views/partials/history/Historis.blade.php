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
            <i class="fa-solid fa-chart-area icon-accent" aria-hidden="true"></i>
            <div class="section-info">
                <h2 id="section-historis-title" class="card-title">Lihat Data Historis</h2>
                <span class="text-muted">Periode 1997 — 2025 (Read-Only)</span>
            </div>
        </div>

        <div class="filter-controls" role="group" aria-label="Filter data historis">

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
                <i class="fa-solid fa-chart-line"></i> Generate
            </button>
        </div>

        <div id="histWarning" class="hist-warning-box" role="alert" style="display:none;"></div>
    </section>

    {{-- SECTION 2: GOLONGAN TARIF — multi-select, warna per golongan --}}
    <section class="card section-golongan" aria-labelledby="section-golongan-title">
        <div class="section-actions">
            <span id="section-golongan-title" class="hist-label">Golongan Tarif</span>
            <span class="text-muted" style="font-size:0.72rem; margin-left:auto; color:#444;">
                Bisa pilih lebih dari satu
            </span>
        </div>

        <div class="golongan-chips" role="group" aria-label="Pilihan golongan tarif">
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
                    <input type="checkbox"
                           class="cb-golongan"
                           name="golongan_tarif[]"
                           value="{{ $g->kode }}"
                           @checked($isChecked)>
                    <span class="chip-text">{{ $g->nama }}</span>
                    <span class="chip-indicator"></span>
                </label>
            @endforeach
        </div>
    </section>

    {{-- SECTION 3: CHART --}}
    <section class="card section-chart" id="histCardChart" style="display:none;" aria-labelledby="chart-title">
        <div class="chart-header">
            <div class="chart-info">
                <h3 id="chart-title" class="card-title">—</h3>
                <span class="text-muted" id="histChartSubtitle"></span>
            </div>
            <div class="chart-controls" role="group" aria-label="Tipe visualisasi">
                <button class="hist-btn-sm active" data-chart-type="bar"  id="histBtnBar"  type="button">Bar</button>
                <button class="hist-btn-sm"         data-chart-type="line" id="histBtnLine" type="button">Line</button>
                <button class="hist-btn-sm" id="histBtnTable" type="button">
                    <i class="fa-solid fa-table"></i> Tabel
                </button>
            </div>
        </div>
        <div id="histChartWrap" role="img" aria-label="Grafik data historis">
            <canvas id="histChart"></canvas>
        </div>
    </section>

    {{-- SECTION 4: TABLE — muncul di bawah chart --}}
    <section class="card section-table" id="histCardTable" style="display:none;" aria-labelledby="table-title">
        <div class="table-header">
            <h3 id="table-title" class="card-title">Tabel Data</h3>
            <button class="hist-btn-sm" id="histBtnHideTable" type="button">
                <i class="fa-solid fa-xmark"></i> Sembunyikan
            </button>
        </div>
        <div class="table-wrapper custom-scroll">
            <table class="hist-table" aria-describedby="table-title">
                <thead id="histThead"></thead>
                <tbody id="histTbody"></tbody>
            </table>
        </div>
    </section>

    {{-- SECTION 5: INPUT DATA BARU --}}
    <section class="card section-entry"
             style="border-left: 3px solid var(--accent-green, #00ff88);"
             aria-labelledby="entry-title">

        <button type="button"
                id="entryToggleBtn"
                class="entry-toggle"
                aria-expanded="false"
                aria-controls="entryPanel">
            <div class="toggle-content">
                <i class="fa-solid fa-pen-to-square icon-accent" aria-hidden="true"></i>
                <div class="toggle-info">
                    <h3 id="entry-title">Input Data Baru ({{ $currentYear }} — {{ $currentYear + 2 }})</h3>
                    <span class="text-muted">Tambah atau update data untuk periode berjalan dan masa depan</span>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down toggle-icon" id="entryChevron" aria-hidden="true"></i>
        </button>

        <div id="entryPanel" class="entry-panel" hidden>

            <div class="hist-info-box" role="note">
                <i class="fa-solid fa-circle-info"></i>
                <strong>Aturan Input:</strong> Data hanya dapat diedit hingga
                <strong>tanggal 7 bulan berikutnya</strong>. Setelah itu dikunci untuk kalkulasi total.
            </div>

            <div class="entry-controls" role="group" aria-label="Kontrol input data">

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
                                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                                9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
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
                    <i class="fa-solid fa-download"></i> Muat Data
                </button>
                <button id="entrySaveBtn" class="hist-btn-primary green" type="button" style="display:none;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>

            <div id="entryStatus" class="entry-status" style="display:none;" role="status" aria-live="polite"></div>

            <div id="entryLocked" class="hist-error-box" style="display:none;" role="alert">
                <i class="fa-solid fa-lock"></i>
                <strong>Periode Terkunci</strong><br>
                Data untuk periode ini sudah tidak dapat diedit karena sudah melewati batas waktu input.
            </div>

            <div id="entryTableWrap" style="display:none;">
                <div class="table-wrapper custom-scroll">
                    <table class="entry-table" aria-label="Form input data per golongan">
                        <thead id="entryThead"></thead>
                        <tbody id="entryTbody"></tbody>
                    </table>
                </div>
                <div class="entry-actions" role="group" aria-label="Aksi form input">
                    <button class="hist-btn-sm" id="btnFillAll" type="button">
                        Isi Nol
                    </button>
                    <button class="hist-btn-sm" id="btnClearAll" type="button">
                        <i class="fa-solid fa-eraser"></i> Reset
                    </button>
                </div>
            </div>

        </div>
    </section>

</main>
@endsection

@push('scripts')
    {{-- Chart.js sudah di-load di app.blade.php, tapi load lagi dengan versi spesifik untuk historis --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script src="{{ asset('js/Dashboard/charts/HistorisChart.js') }}?v={{ filemtime(public_path('js/Dashboard/charts/HistorisChart.js')) }}" defer></script>
    <script src="{{ asset('js/Dashboard/pages/HistorisEntry.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/HistorisEntry.js')) }}" defer></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Entry panel toggle ──────────────────────────────────────
        const entryToggle  = document.getElementById('entryToggleBtn');
        const entryPanel   = document.getElementById('entryPanel');
        const entryChevron = document.getElementById('entryChevron');

        entryToggle?.addEventListener('click', () => {
            const isOpen = !entryPanel.hidden;
            entryPanel.hidden = isOpen;
            entryToggle.setAttribute('aria-expanded', String(!isOpen));
            entryChevron?.classList.toggle('open', !isOpen);
            if (typeof toggleEntryPanel === 'function') toggleEntryPanel();
        });

        // ── Chip initial state sync ─────────────────────────────────
        // Hanya sinkronisasi state awal. Click handler dikelola oleh HistorisChart.js.
        document.querySelectorAll('.cb-golongan').forEach(cb => {
            const chip = cb.closest('.hist-gol-chip');
            if (chip) chip.classList.toggle('checked', cb.checked);
        });
    });
    </script>
@endpush