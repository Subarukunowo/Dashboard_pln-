{{-- SECTION 1: PIE CHART PER GOLONGAN --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title" id="judulPie">{{ __('GolTarifBatam.pie_title') }}</div>
        </div>

        <div class="btn-action-group" style="display:flex; gap:5px; align-items:center;">
            <div class="filters">
                <label for="filterTahunPie" class="sr-only">{{ __('GolTarifBatam.filter.tahun') }}</label>
                <select id="filterTahunPie">
                    <option value="{{ now()->year - 1 }}">{{ __('GolTarifBatam.filter.tahun_lalu') }}</option>
                    <option value="{{ now()->year }}" selected>{{ __('GolTarifBatam.filter.tahun_ini') }}</option>
                </select>

                <label for="filterViewPie" class="sr-only">{{ __('GolTarifBatam.filter.tampilan') }}</label>
                <select id="filterViewPie">
                    <option value="bulanan">{{ __('GolTarifBatam.filter.bulanan') }}</option>
                    <option value="akumulasi">{{ __('GolTarifBatam.filter.akumulasi') }}</option>
                </select>

                <label for="filterBulanPie" class="sr-only">{{ __('GolTarifBatam.filter.bulan') }}</label>
                <select id="filterBulanPie">
                    <option value="all">{{ __('GolTarifBatam.filter.semua_bulan') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatGolTarif" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatGolTarif" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusGolTarif" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:350px; margin-bottom:20px;">
        <canvas id="chartPieTarif"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('GolTarifBatam.table.golongan') }}</th>
                    <th style="color:var(--accent-green);">{{ __('GolTarifBatam.table.nilai') }}</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody id="tabelPieTarif"></tbody>
        </table>
    </div>

    {{-- Edit Data per Golongan --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data Golongan</div>
    </div>
    <div id="golTarifGolonganEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- SECTION 2: REALISASI PENDAPATAN THN INI --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('GolTarifBatam.realisasi_title') }}</div>

        <div class="filters">
            <label for="filterBulanRealisasiGol" class="sr-only">{{ __('GolTarifBatam.filter.bulan') }}</label>
            <select id="filterBulanRealisasiGol">
                <option value="all">{{ __('GolTarifBatam.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRealisasiBulanan"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('GolTarifBatam.table.bulan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('GolTarifBatam.table.target') }}</th>
                    <th style="color:var(--accent-green);">{{ __('GolTarifBatam.table.realisasi') }}</th>
                    <th>{{ __('GolTarifBatam.table.selisih') }}</th>
                    <th>{{ __('GolTarifBatam.table.pct_capai') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRealisasi"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('GolTarifBatam.delta_realisasi_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartDeltaRealisasi"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('GolTarifBatam.table.bulan') }}</th>
                    <th>{{ __('GolTarifBatam.table.selisih_juta') }}</th>
                </tr>
            </thead>
            <tbody id="tabelDeltaRealisasi"></tbody>
        </table>
    </div>

    {{-- Edit Data Realisasi Bulanan --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="golTarifEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="golTarifRealisasiEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- SECTION 3: TOTAL THN LALU vs THN INI --}}
<div class="card col-12">
    <div class="card-title">{{ __('GolTarifBatam.total_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartTotal2024vs2025"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('GolTarifBatam.table.bulan') }}</th>
                    <th style="color:var(--text-dim);">{{ __('GolTarifBatam.table.target_lalu') }}</th>
                    <th style="color:var(--accent-blue);">{{ __('GolTarifBatam.table.real_lalu') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('GolTarifBatam.table.target_ini') }}</th>
                    <th style="color:var(--accent-green);">{{ __('GolTarifBatam.table.real_ini') }}</th>
                    <th>{{ __('GolTarifBatam.table.selisih') }}</th>
                    <th>{{ __('GolTarifBatam.table.selisih_pct') }}</th>
                </tr>
            </thead>
            <tbody id="tabelTotal"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('GolTarifBatam.delta_total_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTotalDelta"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('GolTarifBatam.table.bulan') }}</th>
                    <th>{{ __('GolTarifBatam.table.selisih_triliun') }}</th>
                </tr>
            </thead>
            <tbody id="tabelDeltaTotal"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('GolTarifBatam.pertumbuhan_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTotalPertumbuhan"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('GolTarifBatam.table.bulan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('GolTarifBatam.table.pct_target') }}</th>
                    <th style="color:var(--accent-green);">{{ __('GolTarifBatam.table.pct_realisasi') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPertumbuhan"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data Perbandingan Thn Lalu vs Thn Ini</div>
    </div>
    <div id="golTarifTotalEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatGolTarif" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data Golongan Tarif</div>

        <div style="margin-bottom:12px;">
            <label for="muatTahunGolTarif" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunGolTarif" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="muatTipeGolTarif" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeGolTarif" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatGolTarif" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatGolTarif" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusGolTarif" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Golongan Tarif</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunGolTarif" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunGolTarif" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipeGolTarif" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeGolTarif" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusGolTarif" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusGolTarif" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatGolTarif" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data Golongan Tarif</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunGolTarif" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunGolTarif" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="buatTipeGolTarif" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipeGolTarif" style="width:100%;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatGolTarif" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatGolTarif" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>