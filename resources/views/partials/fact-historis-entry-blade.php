{{-- resources/views/partials/fact-historis-entry.blade.php --}}
{{-- Standalone Page: Input Data Historis (2026+) --}}

@extends('layouts.app')

@section('title', 'Input Data Historis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fact-entry.css') }}">
@endpush

@section('content')

<header class="dash-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <a href="/dashboard" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <div>
            <div style="font-size:0.95rem; font-weight:700; color:#fff; letter-spacing:0.5px;">
                INPUT DATA HISTORIS
            </div>
            <div style="color:var(--text-muted); font-size:0.72rem;">
                Entry Data Bulanan PLN Batam ({{ $currentYear }} — {{ $currentYear + 2 }})
            </div>
        </div>
    </div>
</header>

<div class="fact-entry-container">

    {{-- ===== INFO BANNER ===== --}}
    <div class="card info-banner">
        <i class="fas fa-circle-info"></i>
        <div>
            <strong>Aturan Input Data</strong>
            <ul>
                <li>Hanya untuk tahun <strong>{{ $currentYear }} ke atas</strong> yang belum terisi</li>
                <li>Edit hanya tersedia hingga <strong>tanggal 7 bulan berikutnya</strong></li>
                <li>Semua perubahan tercatat otomatis di audit log</li>
            </ul>
        </div>
    </div>

    {{-- ===== FILTER & ACTIONS ===== --}}
    <div class="card">
        <div class="filter-row">
            
            {{-- Tahun Selector --}}
            <div class="filter-group">
                <label class="filter-label">TAHUN</label>
                <select id="inputTahun" class="form-select">
                    @php
                        $currentYear = now()->year;
                        $years = collect(range($currentYear, $currentYear + 2));
                        $tahunDenganData = \App\Models\FactHistoris::where('is_akumulasi', 0)
                            ->where('is_jumlah_tahunan', 0)
                            ->distinct()->pluck('tahun')->toArray();
                        $tahunTersedia = $years->filter(fn($t) => !in_array($t, $tahunDenganData));
                        $defaultTahun = $tahunTersedia->first() ?? $currentYear;
                    @endphp
                    
                    @forelse($tahunTersedia as $t)
                        <option value="{{ $t }}" {{ $t == $defaultTahun ? 'selected' : '' }}>
                            {{ $t }}
                            @if($t == $currentYear) <span class="badge-current">(Sekarang)</span> @endif
                            @if($t > $currentYear) <span class="badge-future">(+{{ $t - $currentYear }})</span> @endif
                        </option>
                    @empty
                        <option value="{{ $currentYear }}" selected>{{ $currentYear }} (Sekarang)</option>
                    @endforelse
                </select>
                <small class="hint">{{ $tahunTersedia->count() }} tahun tersedia</small>
            </div>

            {{-- Bulan Selector --}}
            <div class="filter-group">
                <label class="filter-label">BULAN</label>
                <select id="inputBulan" class="form-select">
                    @php
                        $bulanNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                                       7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                        $currentMonth = now()->month;
                    @endphp
                    @foreach($bulanNames as $num => $name)
                        <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                            {{ $name }}
                            @if($num == $currentMonth) <span class="badge-current">(Ini)</span> @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="action-buttons">
                <button id="btnLoadData" class="btn btn-primary">
                    <i class="fas fa-download"></i> Muat Data
                </button>
                <button id="btnSave" class="btn btn-success" style="display:none;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>

        {{-- Status Message --}}
        <div id="statusInfo" class="status-message" style="display:none;"></div>
    </div>

    {{-- ===== DATA ENTRY FORM ===== --}}
    <div class="card" id="cardForm" style="display:none;">
        
        {{-- Form Header --}}
        <div class="form-header">
            <div>
                <div class="form-title">Form Input Data</div>
                <div class="form-subtitle">Isi nilai untuk setiap metrik dan golongan</div>
            </div>
            <div class="form-actions">
                <button class="btn btn-sm" id="btnFillAll">
                    <i class="fas fa-zero"></i> Isi Nol
                </button>
                <button class="btn btn-sm" id="btnClearAll">
                    <i class="fas fa-eraser"></i> Reset
                </button>
            </div>
        </div>

        {{-- Loading State --}}
        <div id="loadingData" class="loading-state" style="display:none;">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Memuat data...</span>
        </div>

        {{-- Entry Table --}}
        <div id="entryForm" class="entry-form" style="display:none;">
            @php
                $kategoriGroups = ($metrikList ?? collect())->groupBy('kategori');
                $golonganList = $golonganList ?? collect();
            @endphp

            @foreach($kategoriGroups as $kategori => $metriks)
                <div class="kategori-section">
                    <div class="kategori-title">{{ strtoupper($kategori) }}</div>
                    
                    <div class="table-responsive">
                        <table class="entry-table">
                            <thead>
                                <tr>
                                    <th class="col-metrik">Metrik</th>
                                    <th class="col-satuan">Satuan</th>
                                    @foreach($golonganList as $gol)
                                        <th class="col-golongan">{{ $gol->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metriks as $metrik)
                                    <tr>
                                        <td class="cell-metrik">{{ $metrik->nama }}</td>
                                        <td class="cell-satuan">{{ $metrik->satuan }}</td>
                                        @foreach($golonganList as $gol)
                                            <td class="cell-input">
                                                <input type="number" 
                                                    step="any"
                                                    data-metrik="{{ $metrik->kode }}"
                                                    data-golongan="{{ $gol->kode }}"
                                                    class="data-input"
                                                    placeholder="0"
                                                    autocomplete="off">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/Dashboard/pages/fact-entry.js') }}"></script>
@endpush