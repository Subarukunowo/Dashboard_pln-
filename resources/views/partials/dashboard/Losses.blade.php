{{-- CARD 1: Produksi --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('losses.title') }}</div>
            <span id="lossesWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterEminLosses" class="sr-only">{{ __('losses.filter.emin') }}</label>
                <select id="filterEminLosses">
                    <option value="with">{{ __('losses.filter.dengan_emin') }}</option>
                    <option value="without">{{ __('losses.filter.tanpa_emin') }}</option>
                </select>

                <label for="filterBulanLosses" class="sr-only">{{ __('losses.filter.bulan') }}</label>
                <select id="filterBulanLosses">
                    <option value="all">{{ __('losses.filter.semua') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatLosses" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatLosses" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusLosses" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartLossesProduksi"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('losses.table.bulan') }}</th>
                    <th style="color:var(--accent-blue);">{{ __('losses.table.prod_bruto') }}</th>
                    <th style="color:var(--accent-green);">{{ __('losses.table.prod_netto') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('losses.table.siap_salur') }}</th>
                </tr>
            </thead>
            <tbody id="lossesSideTable"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Losses % --}}
<div class="card col-12 preview-card">
    <div class="card-title">{{ __('losses.pct_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartLossesPct"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('losses.table.bulan') }}</th>
                    <th style="color:var(--accent-red);">{{ __('losses.table.losses_trans_bln') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('losses.table.losses_trans_akum') }}</th>
                    <th style="color:var(--accent-green);">{{ __('losses.table.losses_jar_bln') }}</th>
                    <th style="color:#9c27b0;">{{ __('losses.table.losses_jar_akum') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLossesPct"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Losses kWh --}}
<div class="card col-12">
    <div class="card-title">{{ __('losses.kwh_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartLossesKwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('losses.table.bulan') }}</th>
                    <th style="color:#e91e63;">{{ __('losses.table.losses_trans') }}</th>
                    <th style="color:#9c27b0;">{{ __('losses.table.losses_dist') }}</th>
                    <th style="color:var(--accent-red);">{{ __('losses.table.losses_jar') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLossesKwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Tabel Rincian Losses --}}
<div class="card col-12">
    <div class="card-title">{{ __('losses.detail_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th>{{ __('losses.table.bulan') }}</th>
                    <th>{{ __('losses.table.prod_bruto') }}</th>
                    <th>{{ __('losses.table.prod_netto') }}</th>
                    <th>{{ __('losses.table.siap_salur') }}</th>
                    <th>{{ __('losses.table.losses_trans') }}</th>
                    <th>{{ __('losses.table.losses_bln') }}</th>
                    <th>{{ __('losses.table.losses_akum') }}</th>
                    <th>{{ __('losses.table.interkoneksi') }}</th>
                    <th>{{ __('losses.table.losses_dist') }}</th>
                    <th>{{ __('losses.table.losses_dist_pct') }}</th>
                    <th>{{ __('losses.table.losses_dist_akum') }}</th>
                    <th>{{ __('losses.table.losses_jar') }}</th>
                    <th>{{ __('losses.table.losses_jar_pct') }}</th>
                    <th>{{ __('losses.table.losses_jar_akum') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('losses.table.target') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLosses"></tbody>
        </table>
    </div>

    {{-- Tabel Edit Produksi --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data Produksi</div>
        <span id="lossesEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="lossesEditProduksi" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px; margin-bottom:16px;"></div>

    {{-- Tabel Edit Losses --}}
    <div class="card-title" style="margin-bottom:8px;">Edit Data Losses</div>
    <div id="lossesEditLosses" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatLosses" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data Losses</div>

        <div style="margin-bottom:12px;">
            <label for="muatTahunLosses" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunLosses" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="muatTipeLosses" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeLosses" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatLosses" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatLosses" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusLosses" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Losses</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunLosses" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunLosses" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipeLosses" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeLosses" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusLosses" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusLosses" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatLosses" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data Losses</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih. Mencakup data produksi dan losses per kategori.</div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunLosses" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunLosses" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="buatTipeLosses" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe Losses</label>
            <select id="buatTipeLosses" style="width:100%;">
                <option value="dengan_emin">Dengan E-min</option>
                <option value="tanpa_emin">Tanpa E-min</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatLosses" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatLosses" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>