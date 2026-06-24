{{-- CARD 1: Produksi --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('losses.title') }}</div>
            <span id="lossesWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>
        <div style="display:flex; gap:5px; align-items:center;">
            <select id="filterEminLosses" style="font-size:0.7rem; padding:4px;">
                <option value="with">{{ __('losses.filter.dengan_emin') }}</option>
                <option value="without">{{ __('losses.filter.tanpa_emin') }}</option>
            </select>
            <select id="filterBulanLosses" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('losses.filter.semua') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <button id="btnMuatLosses"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer;">
                ＋ Muat Data
            </button>
            <button id="btnBuatLosses"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusLosses"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartLossesProduksi"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('losses.table.prod_bruto') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('losses.table.prod_netto') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('losses.table.siap_salur') }}</th>
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

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('losses.table.losses_trans_bln') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('losses.table.losses_trans_akum') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('losses.table.losses_jar_bln') }}</th>
                    <th style="padding:10px; color:#9c27b0;">{{ __('losses.table.losses_jar_akum') }}</th>
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

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#e91e63;">{{ __('losses.table.losses_trans') }}</th>
                    <th style="padding:10px; color:#9c27b0;">{{ __('losses.table.losses_dist') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('losses.table.losses_jar') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLossesKwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Tabel Rincian Losses --}}
<div class="card col-12">
    <div class="card-title">{{ __('losses.detail_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.prod_bruto') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.prod_netto') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.siap_salur') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_trans') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_bln') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_akum') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.interkoneksi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_dist') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_dist_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_dist_akum') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_jar') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_jar_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_jar_akum') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('losses.table.target') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLosses"></tbody>
        </table>
    </div>

    {{-- Tabel Edit Produksi --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data Produksi</div>
        <span id="lossesEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="lossesEditProduksi" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px; margin-bottom:16px;"></div>

    {{-- Tabel Edit Losses --}}
    <div class="card-title" style="margin-bottom:8px;">Edit Data Losses</div>
    <div id="lossesEditLosses" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatLosses" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data Losses</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunLosses" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeLosses" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatLosses" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiMuatLosses" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusLosses" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Losses</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunLosses" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeLosses" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusLosses" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusLosses" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatLosses" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data Losses</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih. Mencakup data produksi dan losses per kategori.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunLosses" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe Losses</label>
            <select id="buatTipeLosses" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="dengan_emin">Dengan E-min</option>
                <option value="tanpa_emin">Tanpa E-min</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatLosses" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatLosses" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
        </div>
    </div>
</div>
