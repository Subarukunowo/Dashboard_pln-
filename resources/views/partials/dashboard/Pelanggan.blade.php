{{-- CARD 1: Rencana vs Realisasi Penambahan Pelanggan --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('pelanggan.title') }}</div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterTahunPelanggan" class="sr-only">Tahun</label>
                <select id="filterTahunPelanggan">
                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                </select>

                <label for="filterTarifPelanggan" class="sr-only">{{ __('pelanggan.filter.tarif') }}</label>
                <select id="filterTarifPelanggan"></select>

                <label for="filterBulanPelanggan" class="sr-only">{{ __('pelanggan.filter.bulan') }}</label>
                <select id="filterBulanPelanggan">
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatPelanggan" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatPelanggan" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusPelanggan" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartPelangganBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('pelanggan.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('pelanggan.table.rencana') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('pelanggan.table.realisasi') }}</th>
                    <th scope="col">{{ __('pelanggan.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="pelangganSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('pelanggan.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('pelanggan.table.bulan') }}</th>
                    <th scope="col">{{ __('pelanggan.table.delta') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganDelta"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Distribusi Golongan Pelanggan --}}
<div class="card col-12 preview-card">
    <div class="card-title">{{ __('pelanggan.distribusi_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganPie"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('pelanggan.table.golongan') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('pelanggan.table.jumlah') }}</th>
                    <th scope="col">%</th>
                </tr>
            </thead>
            <tbody id="pelangganPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('pelanggan.gol_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganGol"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('pelanggan.table.golongan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('pelanggan.table.target_ini') }}</th>
                    <th scope="col" style="color:var(--accent-blue);">{{ __('pelanggan.table.real_lalu') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganGol"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: RKAP Pelanggan --}}
<div class="card col-12">
    <div class="card-title">{{ __('pelanggan.rkap_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px; margin-bottom:16px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th scope="col" rowspan="2" style="border-right:1px solid var(--input-border);">{{ __('pelanggan.table.golongan') }}</th>
                    <th scope="colgroup" colspan="5" style="text-align:center; border-bottom:1px solid var(--input-border);">{{ __('pelanggan.table.target') }}</th>
                    <th scope="colgroup" colspan="5" style="text-align:center; border-bottom:1px solid var(--input-border); border-left:1px solid var(--input-border);">{{ __('pelanggan.table.realisasi') }}</th>
                    <th scope="col" rowspan="2" style="color:var(--accent-orange); border-left:1px solid var(--input-border);">{{ __('pelanggan.table.pct_capai') }}</th>
                </tr>
                <tr style="font-size:0.65rem;">
                    <th scope="col">{{ __('pelanggan.table.tw1') }}</th>
                    <th scope="col">{{ __('pelanggan.table.tw2') }}</th>
                    <th scope="col">{{ __('pelanggan.table.tw3') }}</th>
                    <th scope="col">{{ __('pelanggan.table.tw4') }}</th>
                    <th scope="col" style="border-right:1px solid var(--input-border);">{{ __('pelanggan.table.total') }}</th>
                    <th scope="col" style="border-left:1px solid var(--input-border);">{{ __('pelanggan.table.tw1') }}</th>
                    <th scope="col">{{ __('pelanggan.table.tw2') }}</th>
                    <th scope="col">{{ __('pelanggan.table.tw3') }}</th>
                    <th scope="col">{{ __('pelanggan.table.tw4') }}</th>
                    <th scope="col">{{ __('pelanggan.table.total') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRkapPelanggan"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; flex-wrap:wrap;">
        <div class="card-title" style="margin:0;">Edit Data</div>

        <label for="filterGolonganEditPelanggan" class="sr-only">{{ __('pelanggan.filter.tarif') }}</label>
        <select id="filterGolonganEditPelanggan" style="width:auto;"></select>

        <span id="pelangganEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange); margin-left:auto;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="pelangganEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatPelanggan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data Pelanggan</div>

        <div style="margin-bottom:12px;">
            <label for="muatTahunPelanggan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunPelanggan" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="muatTipePelanggan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipePelanggan" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatPelanggan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatPelanggan" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusPelanggan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Pelanggan</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunPelanggan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunPelanggan" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipePelanggan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipePelanggan" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusPelanggan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusPelanggan" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatPelanggan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data Pelanggan</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunPelanggan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunPelanggan" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="buatTipePelanggan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipePelanggan" style="width:100%;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatPelanggan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatPelanggan" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>