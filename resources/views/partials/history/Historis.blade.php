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

{{-- ═══════════════════════════════════════════════════════
     DASH-HEADER — persis seperti di halaman Dashboard
     Sticky di bawah navbar, judul rata kiri
     ═══════════════════════════════════════════════════════ --}}
<div class="dash-header">
    <div class="dash-header__left">
        <p class="dash-header__title">DATA HISTORIS PLN BATAM</p>
        <p class="dash-header__sub">Visualisasi &amp; Input Data Kelistrikan</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MAIN CONTENT
     ═══════════════════════════════════════════════════════ --}}
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
                <i class="fas fa-chart-line" aria-hidden="true"></i> Generate
            </button>
        </div>

        <div id="histWarning" class="hist-warning-box" role="alert" style="display:none;"></div>
    </section>

{{-- ═══════════════════════════════════════════════════════
     SECTION 2: GOLONGAN TARIF — MULTI-SELECT + WARNA UNIK
     ═══════════════════════════════════════════════════════ --}}
<section class="card section-golongan" aria-labelledby="section-golongan-title">
    <div class="section-actions">
        <span id="section-golongan-title" class="hist-label">Golongan Tarif</span>
        <span class="text-muted" style="font-size:0.85rem; margin-left:auto;">
            (Bisa pilih lebih dari satu)
        </span>
    </div>
    <div class="golongan-chips" role="group" aria-label="Pilihan golongan tarif">
        @php
            $golonganColors = [
                'S' => 'gol-sosial',
                'R' => 'gol-rumah',
                'B' => 'gol-bisnis',
                'I' => 'gol-industri',
                'P' => 'gol-publik',
                'M' => 'gol-multiguna',
                'TOTAL' => 'gol-total',
            ];
        @endphp
        
        @foreach($golonganList as $g)
            @php
                $colorClass = $golonganColors[$g->kode] ?? 'gol-default';
                $isChecked = $g->kode === 'TOTAL';
            @endphp
            <label class="hist-gol-chip {{ $colorClass }} {{ $isChecked ? 'checked' : '' }}" 
                   data-golongan="{{ $g->kode }}"
                   style="cursor: pointer; border-radius: 50px;">
                <input type="checkbox"
                       class="cb-golongan"
                       name="golongan_tarif[]"
                       value="{{ $g->kode }}"
                       data-kode="{{ $g->kode }}"
                       data-nama="{{ $g->nama }}"
                       @checked($isChecked)
                       style="display: none;">
                <span class="chip-text" style="pointer-events: none;">{{ $g->nama }}</span>
                <span class="chip-indicator" style="pointer-events: none;"></span>
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
                <button class="hist-btn-sm active" data-chart-type="bar" id="histBtnBar" type="button" aria-pressed="true">Bar</button>
                <button class="hist-btn-sm" data-chart-type="line" id="histBtnLine" type="button" aria-pressed="false">Line</button>
                <button class="hist-btn-sm" id="histBtnTable" type="button">
                    <i class="fas fa-table" aria-hidden="true"></i> Tabel
                </button>
            </div>
        </div>
        <div id="histChartWrap" role="img" aria-label="Grafik data historis">
            <canvas id="histChart"></canvas>
        </div>
    </section>

    {{-- SECTION 4: TABLE --}}
    <section class="card section-table" id="histCardTable" style="display:none;" aria-labelledby="table-title">
        <div class="table-header">
            <h3 id="table-title" class="card-title">Tabel Data</h3>
            <button class="hist-btn-sm" id="histBtnHideTable" type="button">
                <i class="fas fa-times" aria-hidden="true"></i> Sembunyikan
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
             style="border-left: 3px solid var(--accent-green);"
             aria-labelledby="entry-title">

        <button type="button"
                id="entryToggleBtn"
                class="entry-toggle"
                aria-expanded="false"
                aria-controls="entryPanel">
            <div class="toggle-content">
                <i class="fas fa-pen-to-square icon-accent" aria-hidden="true"></i>
                <div class="toggle-info">
                    <h3 id="entry-title">Input Data Baru ({{ $currentYear }} — {{ $currentYear + 2 }})</h3>
                    <span class="text-muted">Tambah atau update data untuk periode berjalan dan masa depan</span>
                </div>
            </div>
            <i class="fas fa-chevron-down toggle-icon" id="entryChevron" aria-hidden="true"></i>
        </button>

        <div id="entryPanel" class="entry-panel" hidden>

            <div class="hist-info-box" role="note">
                <i class="fas fa-circle-info" aria-hidden="true"></i>
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
                    <i class="fas fa-download" aria-hidden="true"></i> Muat Data
                </button>
                <button id="entrySaveBtn" class="hist-btn-primary green" type="button" style="display:none;">
                    <i class="fas fa-save" aria-hidden="true"></i> Simpan
                </button>
            </div>

            <div id="entryStatus" class="entry-status" style="display:none;" role="status" aria-live="polite"></div>

            <div id="entryLocked" class="hist-error-box" style="display:none;" role="alert">
                <i class="fas fa-lock" aria-hidden="true"></i>
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
                        <i class="fas fa-zero" aria-hidden="true"></i> Isi Nol
                    </button>
                    <button class="hist-btn-sm" id="btnClearAll" type="button">
                        <i class="fas fa-eraser" aria-hidden="true"></i> Reset
                    </button>
                </div>
            </div>

        </div>
    </section>

</main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script src="{{ asset('js/Dashboard/charts/HistorisChart.js') }}?v={{ filemtime(public_path('js/Dashboard/charts/HistorisChart.js')) }}" defer></script>
    <script src="{{ asset('js/Dashboard/pages/HistorisEntry.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/HistorisEntry.js')) }}" defer></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const HistorisPage = {
            elements: {
                entryToggle:  document.getElementById('entryToggleBtn'),
                entryPanel:   document.getElementById('entryPanel'),
                entryChevron: document.getElementById('entryChevron'),
            },
            init() {
                this.bindToggle();
                this.initChips();
            },
            bindToggle() {
                const { entryToggle, entryPanel, entryChevron } = this.elements;
                entryToggle?.addEventListener('click', () => {
                    const isOpen = !entryPanel.hidden;
                    entryPanel.hidden = isOpen;
                    entryToggle.setAttribute('aria-expanded', String(!isOpen));
                    entryChevron?.classList.toggle('open', !isOpen);
                    if (typeof toggleEntryPanel === 'function') toggleEntryPanel();
                });
            },
          initChips() {
    // Setup chips agar kompatibel dengan HistorisChart.js
    document.querySelectorAll('.hist-gol-chip').forEach(chip => {
        const checkbox = chip.querySelector('.cb-golongan');
        
        // Set initial state
        if (checkbox && checkbox.checked) {
            chip.classList.add('checked');
        }
        
        // Click handler — TANPA preventDefault yang konflik
        chip.addEventListener('click', function(e) {
            // Jangan toggle jika yang diklik adalah checkbox langsung
            if (e.target === checkbox) {
                return;
            }
            
            // Toggle checkbox
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                chip.classList.toggle('checked', checkbox.checked);
                
                // Dispatch change event untuk trigger listener lain
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                
                console.log(`🏷️ Golongan "${checkbox.value}": ${checkbox.checked ? '✓' : '✗'}`);
            }
        });
    });
    
    // Change handler untuk semua checkbox
    document.querySelectorAll('.cb-golongan').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const chip = this.closest('.hist-gol-chip');
            if (chip) {
                chip.classList.toggle('checked', this.checked);
            }
            
            // Log selected golongan
            const selected = [...document.querySelectorAll('.cb-golongan:checked')]
                .map(cb => cb.value);
            
            console.log('✅ Golongan terpilih:', selected);
            
            // Dispatch custom event untuk listener lain
            window.dispatchEvent(new CustomEvent('golonganChanged', {
                detail: { 
                    selected: selected,
                    count: selected.length
                }
            }));
        });
    });
    
    console.log('✅ Chips initialized:', document.querySelectorAll('.cb-golongan').length, 'golongan');
},
        };
        HistorisPage.init();
    });
    </script>
@endpush