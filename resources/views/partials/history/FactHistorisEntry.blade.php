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
                            @if($y == now()->year) (Sekarang) @endif
                            @if($y > now()->year) (+{{ $y - now()->year }}) @endif
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
                            @if($num == now()->month) (Ini) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="action-buttons">
                {{-- Muat Data --}}
                <button id="btnLoadData" class="btn btn-primary">
                    <i class="fas fa-download"></i> Muat Data
                </button>

                {{-- Download Template --}}
                <a href="{{ route('fact-historis.download-template') }}"
                   class="btn btn-template"
                   download
                   title="Download template Excel untuk import data">
                    <i class="fas fa-file-excel"></i> Template
                </a>

                {{-- Import Excel --}}
                <button id="btnToggleImport" class="btn btn-import" title="Import data dari file Excel">
                    <i class="fas fa-file-import"></i> Import
                </button>

                {{-- Simpan --}}
                <button id="entrySaveBtn" class="btn btn-success" style="display:none;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>

        <div id="entryStatus" class="status-message" style="display:none;"></div>
        <div id="entryLocked" class="locked-message" style="display:none; margin-top:10px;"></div>
    </div>

    {{-- EXCEL IMPORT PANEL (toggled by btnToggleImport) --}}
    <div class="card excel-import-card" id="excelImportCard" style="display:none;">
        <div class="import-card-header">
            <div>
                <div class="import-card-title">
                    <i class="fas fa-file-excel" style="color:#28a745;"></i>
                    Import Data dari Excel
                </div>
                <div class="import-card-subtitle">
                    Upload file Excel yang sudah diisi sesuai template
                </div>
            </div>
            <button id="btnCloseImport" class="btn-icon-close" title="Tutup panel import">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Upload Area --}}
        <div class="upload-area" id="uploadArea">
            <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" style="display:none;">
            <label for="excelFileInput" class="upload-label">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <span class="upload-text">Klik atau seret file ke sini</span>
                <small class="upload-hint">.xlsx, .xls, .csv &nbsp;•&nbsp; Maks. 10 MB</small>
            </label>
        </div>

        {{-- File Preview --}}
        <div id="filePreview" class="file-preview" style="display:none;">
            <i class="fas fa-file-excel" style="color:#28a745; font-size:1.1rem;"></i>
            <span id="fileName" class="file-name"></span>
            <button id="removeFile" class="btn-icon-remove" title="Hapus file">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Import Actions --}}
        <div id="importActions" class="import-actions" style="display:none;">
            <button id="btnCancelImport" class="btn btn-sm">
                <i class="fas fa-times"></i> Batal
            </button>
            <button id="btnProcessImport" class="btn btn-import-process">
                <i class="fas fa-play"></i> Proses Import
            </button>
        </div>

        {{-- Progress Bar --}}
        <div id="importProgress" style="display:none; margin-top:12px;">
            <div class="progress-track">
                <div id="progressFill" class="progress-fill" style="width:0%;"></div>
            </div>
            <small id="progressText" class="progress-text">Memproses...</small>
        </div>

        {{-- Result --}}
        <div id="importResult" style="display:none; margin-top:10px; font-size:0.8rem;"></div>
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

</div>
@endsection

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('js/Dashboard/pages/HistorisEntry.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/HistorisEntry.js')) }}"></script>
    <script src="{{ asset('js/Dashboard/pages/excel-import.js') }}?v={{ filemtime(public_path('js/Dashboard/pages/excel-import.js')) }}"></script>

    <script>
    // Toggle Import Panel
    document.getElementById('btnToggleImport').addEventListener('click', function () {
        const card = document.getElementById('excelImportCard');
        const isHidden = card.style.display === 'none' || card.style.display === '';
        card.style.display = isHidden ? 'block' : 'none';
        this.classList.toggle('active', isHidden);
    });

    document.getElementById('btnCloseImport').addEventListener('click', function () {
        document.getElementById('excelImportCard').style.display = 'none';
        document.getElementById('btnToggleImport').classList.remove('active');
    });

    // Drag & Drop support
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('excelFileInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }
    });
    </script>
@endpush