{{-- resources/views/partials/fact-historis-entry.blade.php --}}
@extends('layouts.app')

@section('title', 'Input Data Historis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/fact-entry.css') }}">
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
                Entry Data Bulanan PLN Batam (2026+)
            </div>
        </div>
    </div>
</header>

<div class="fact-entry-container">
    {{-- INFO BANNER --}}
    <div class="card info-banner">
        <i class="fas fa-circle-info"></i>
        <div>
            <strong>Aturan Input Data</strong>
            <ul>
                <li>Hanya untuk tahun <strong>2026 ke atas</strong> yang belum terisi</li>
                <li>Edit hanya tersedia hingga <strong>tanggal 7 bulan berikutnya</strong></li>
                <li>Semua perubahan tercatat otomatis di audit log</li>
            </ul>
        </div>
    </div>

    {{-- FILTER & ACTIONS --}}
    <div class="card">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">TAHUN</label>
                <select id="inputTahun" class="form-select">
                    <option value="">Pilih Tahun</option>
                    @for($y = 2026; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                            {{ $y }}
                            @if($y == now()->year) <span class="badge-current">(Sekarang)</span> @endif
                            @if($y > now()->year) <span class="badge-future">(+{{ $y - now()->year }})</span> @endif
                        </option>
                    @endfor
                </select>
                <small class="hint" id="tahunHint"></small>
            </div>

            <div class="filter-group">
                <label class="filter-label">BULAN</label>
                <select id="inputBulan" class="form-select">
                    <option value="">Pilih Bulan</option>
                    @php
                        $bulanNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                                       7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                    @endphp
                    @foreach($bulanNames as $num => $name)
                        <option value="{{ $num }}" {{ $num == now()->month ? 'selected' : '' }}>
                            {{ $name }}
                            @if($num == now()->month) <span class="badge-current">(Ini)</span> @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="action-buttons">
                <button id="btnLoadData" class="btn btn-primary">
                    <i class="fas fa-download"></i> Muat Data
                </button>
                <button id="entrySaveBtn" class="btn btn-success" style="display:none;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>

        <div id="entryStatus" class="status-message" style="display:none;"></div>
        <div id="entryLocked" class="locked-message" style="display:none; margin-top:10px;"></div>
    </div>

    {{-- DATA ENTRY FORM --}}
    <div class="card" id="cardForm" style="display:none;">
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

        <div id="loadingData" class="loading-state" style="display:none;">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Memuat data...</span>
        </div>

        <div id="entryTableWrap" class="entry-form" style="display:none;">
            <div class="table-responsive">
                <table class="entry-table">
                    <thead id="entryThead"></thead>
                    <tbody id="entryTbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Hidden Golongan Chips --}}
    <div style="display:none;">
        @php
            $golonganDefault = [
                ['kode'=>'S','nama'=>'Sosial'], ['kode'=>'R','nama'=>'Rumah Tangga'],
                ['kode'=>'B','nama'=>'Bisnis'], ['kode'=>'I','nama'=>'Industri'],
                ['kode'=>'P','nama'=>'Pemerintah'], ['kode'=>'M','nama'=>'Meteran'],
                ['kode'=>'TOTAL','nama'=>'TOTAL'],
            ];
        @endphp
        @foreach($golonganDefault as $gol)
            <label class="cb-golongan">
                <input type="checkbox" value="{{ $gol['kode'] }}" checked>
                {{ $gol['nama'] }}
            </label>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- ✅ Hanya load HistorisEntry.js untuk halaman ini --}}
     <script src="{{ asset('js/Dashboard/pages/HistorisEntry.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/HistorisEntry.js')) }}"></script>
@endpush