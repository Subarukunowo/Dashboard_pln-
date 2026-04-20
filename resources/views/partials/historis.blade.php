@extends('layouts.app')

@section('title', 'Data Historis PLN Batam')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/historis-page.css') }}">
@endpush

@section('content')

<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <a href="/dashboard" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none;">← Dashboard</a>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:#fff; letter-spacing:0.5px;">DATA HISTORIS PLN BATAM</div>
            <div style="color:var(--text-muted); font-size:0.72rem;">Visualisasi & Input Data Kelistrikan</div>
        </div>
    </div>
</header>

<div style="padding: var(--gap); display:flex; flex-direction:column; gap:var(--gap);">

    {{-- SECTION 1: VIEW HISTORICAL DATA (1997 - 2025) --}}
    <div class="card">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #333;">
            <i class="fas fa-chart-area" style="color: var(--accent-orange);"></i>
            <div>
                <div style="font-weight:700; font-size:0.9rem; color:#fff;">📊 Lihat Data Historis</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">Periode 1997 — 2025 (Read-Only)</div>
            </div>
        </div>

        {{-- Filter Controls --}}
        <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
            <div class="hist-filter-group" style="flex:1; min-width:200px;">
                <label class="hist-label">METRIK</label>
                <select id="histMetrik" class="hist-select">
                    @foreach($metrikList as $m)
                        <option value="{{ $m->kode }}" data-satuan="{{ $m->satuan }}" data-kategori="{{ $m->kategori }}">
                            {{ $m->nama }} ({{ $m->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>

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

            <div class="hist-filter-group">
                <label class="hist-label">MODE</label>
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

        <div id="histWarning" style="display:none; margin-top:10px; padding:8px 14px;
            background:rgba(255,152,0,0.08); border:1px solid rgba(255,152,0,0.25);
            border-radius:6px; font-size:0.78rem; color:#ff9800;"></div>
    </div>

    {{-- Filter Golongan --}}
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

    {{-- Chart Container --}}
    <div class="card" id="histCardChart" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <div class="card-title" id="histChartTitle">—</div>
                <div style="color:var(--text-muted); font-size:0.75rem;" id="histChartSubtitle"></div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button class="hist-btn-sm active" id="histBtnBar" data-type="bar">Bar</button>
                <button class="hist-btn-sm" id="histBtnLine" data-type="line">Line</button>
                <button class="hist-btn-sm" onclick="toggleTable()">
                    <i class="fas fa-table"></i> Tabel
                </button>
            </div>
        </div>
        <div style="position:relative; height:420px;">
            <canvas id="histChart"></canvas>
        </div>
    </div>

    {{-- Detail Table --}}
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

    {{-- SECTION 2: INPUT NEW DATA (Current Year - Current Year + 2) --}}
    <div class="card" style="border-left: 4px solid var(--accent-green); margin-top: 8px;">
        <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer; padding:4px 0;"
             onclick="toggleEntryPanel()">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-pen-to-square" style="color:var(--accent-green); font-size:1.1rem;"></i>
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
               style="color:var(--text-muted); transition:transform 0.2s; font-size:0.9rem;"></i>
        </div>

        <div id="entryPanel" style="display:none; margin-top:16px; border-top:1px solid #2a2a2a; padding-top:16px;">

            <div style="padding:10px 14px; background:rgba(0,255,136,0.06);
                border:1px solid rgba(0,255,136,0.2); border-radius:6px;
                font-size:0.75rem; color:#00ff88; margin-bottom:14px;">
                <i class="fas fa-circle-info" style="margin-right:6px;"></i>
                <strong>Aturan Input:</strong> Data hanya dapat diedit hingga <strong>tanggal 7 bulan berikutnya</strong>.
                Setelah itu, data dikunci untuk kalkulasi total. Semua perubahan tercatat di audit log.
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:14px;">
                
                <div class="hist-filter-group">
                    <label class="hist-label">TAHUN</label>
                    <select id="entryTahun" class="hist-select">
                        @php
                            $tahunDenganData = \App\Models\FactHistoris::where('is_akumulasi', 0)
                                ->where('is_jumlah_tahunan', 0)
                                ->distinct()
                                ->pluck('tahun')
                                ->toArray();
                            
                            $tahunAvailable = ($tahunInputTersedia ?? collect())->filter(fn($t) => !in_array($t, $tahunDenganData));
                            $defaultEntryTahun = $tahunAvailable->first() ?? $currentYear;
                        @endphp
                        
                        @forelse($tahunAvailable as $t)
                            <option value="{{ $t }}" {{ $t == $defaultEntryTahun ? 'selected' : '' }}>
                                {{ $t }}
                                @if($t == $currentYear) <span style="color:var(--accent-green);">(Tahun Ini)</span> @endif
                                @if($t > $currentYear) <span style="color:#2196f3;">(+{{ $t - $currentYear }})</span> @endif
                            </option>
                        @empty
                            <option value="{{ $currentYear }}" selected>{{ $currentYear }} (Tahun Ini)</option>
                        @endforelse
                    </select>
                    <small style="color: var(--text-muted); font-size: 0.7rem; margin-top: 4px; display: block;">
                        {{ $tahunAvailable->count() }} tahun tersedia untuk input
                    </small>
                </div>

                <div class="hist-filter-group">
                    <label class="hist-label">BULAN</label>
                    <select id="entryBulan" class="hist-select">
                        @php
                            $bulanNames = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            $currentMonth = now()->month;
                        @endphp
                        @foreach($bulanNames as $num => $name)
                            <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                                {{ $name }}
                                @if($num == $currentMonth) <span style="color:var(--accent-green);">(Bulan Ini)</span> @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <button class="hist-btn-primary" onclick="loadEntryData()" style="align-self:flex-end;">
                    <i class="fas fa-download"></i> Muat Data
                </button>
                <button class="hist-btn-primary" id="entrySaveBtn"
                        onclick="saveEntryData()"
                        style="background:#00ff88; color:#000; display:none; align-self:flex-end;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>

            <div id="entryStatus" style="display:none; padding:8px 14px; border-radius:6px;
                font-size:0.78rem; margin-bottom:12px;"></div>

            <div id="entryTableWrap" style="display:none;">
                <div class="custom-scroll" style="overflow-x:auto; max-height:500px;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.78rem;">
                        <thead id="entryThead" style="position:sticky; top:0; background:#1a1a1a; z-index:1;"></thead>
                        <tbody id="entryTbody"></tbody>
                    </table>
                </div>
            </div>

            <div id="entryLocked" style="display:none; padding:20px; text-align:center;
                color:#f44336; font-size:0.85rem; background:rgba(244,67,54,0.05);
                border:1px solid rgba(244,67,54,0.2); border-radius:6px;">
                <i class="fas fa-lock" style="margin-right:8px; font-size:1.1rem;"></i>
                <strong>Periode Terkunci</strong><br>
                Data untuk periode ini sudah tidak dapat diedit karena sudah melewati batas waktu input.
            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/Dashboard/pages/historis-inline.js') }}"></script>
@endpush