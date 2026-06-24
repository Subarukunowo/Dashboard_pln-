{{-- CARD 1: Rencana vs Realisasi Penambahan Pelanggan --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('pelanggan.title') }}</div>
        <div style="display:flex; gap:5px; align-items:center;">
            <select id="filterTarifPelanggan" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanPelanggan" style="font-size:0.7rem; padding:4px;">
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <button id="btnMuatPelanggan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer;">
                ＋ Muat Data
            </button>
            <button id="btnBuatPelanggan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusPelanggan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartPelangganBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('pelanggan.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('pelanggan.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('pelanggan.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="pelangganSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('pelanggan.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('pelanggan.table.delta') }}</th>
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

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.golongan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('pelanggan.table.jumlah') }}</th>
                    <th style="padding:10px; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="pelangganPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('pelanggan.gol_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganGol"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.golongan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('pelanggan.table.target_ini') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('pelanggan.table.real_lalu') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganGol"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: RKAP Pelanggan --}}
<div class="card col-12">
    <div class="card-title">{{ __('pelanggan.rkap_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px; margin-bottom:16px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:12px; color:#888; border-right:1px solid #444;">{{ __('pelanggan.table.golongan') }}</th>
                    <th colspan="5" style="padding:12px; color:#aaa; text-align:center; border-bottom:1px solid #444;">{{ __('pelanggan.table.target') }}</th>
                    <th colspan="5" style="padding:12px; color:#aaa; text-align:center; border-bottom:1px solid #444; border-left:1px solid #444;">{{ __('pelanggan.table.realisasi') }}</th>
                    <th style="padding:12px; color:var(--accent-orange); border-left:1px solid #444;">{{ __('pelanggan.table.pct_capai') }}</th>
                </tr>
                <tr style="font-size:0.65rem;">
                    <th style="padding:8px; border-right:1px solid #444;">&nbsp;</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw1') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw2') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw3') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw4') }}</th>
                    <th style="padding:8px; color:#aaa; border-right:1px solid #444;">{{ __('pelanggan.table.total') }}</th>
                    <th style="padding:8px; color:#aaa; border-left:1px solid #444;">{{ __('pelanggan.table.tw1') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw2') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw3') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw4') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.total') }}</th>
                    <th style="padding:8px;">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="tabelRkapPelanggan"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="pelangganEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="pelangganEditContainer" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatPelanggan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data Pelanggan</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunPelanggan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipePelanggan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatPelanggan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiMuatPelanggan" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusPelanggan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Pelanggan</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunPelanggan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipePelanggan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusPelanggan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusPelanggan" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatPelanggan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data Pelanggan</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunPelanggan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipePelanggan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatPelanggan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatPelanggan" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
        </div>
    </div>
</div>