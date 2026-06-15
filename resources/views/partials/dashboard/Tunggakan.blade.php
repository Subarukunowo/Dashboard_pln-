{{-- CARD 1: Pendapatan --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('tunggakan.pendapatan_title') }}</div>
            <span id="tunggakanWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>
        <div style="display:flex; gap:5px; align-items:center;">
            <select id="filterBulanTunggakan" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('tunggakan.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <button id="btnBuatTahunTunggakan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer;">
                ＋ Buat Tahun Baru
            </button>
            <button id="btnHapusTunggakan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartTunggakanPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('tunggakan.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('tunggakan.table.pendapatan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ratio') }}</th>
                </tr>
            </thead>
            <tbody id="tunggakanSideTable"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Ratio --}}
<div class="card col-12">
    <div class="card-title">{{ __('tunggakan.ratio_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTunggakanRatio"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('tunggakan.table.bulan') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('tunggakan.table.ratio_tunggakan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('tunggakan.table.target_ratio') }}</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakanRatio"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Detail --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('tunggakan.detail_title') }}</div>
        <span id="tunggakanEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>

    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('tunggakan.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.pal_berjalan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.pal_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ts_berjalan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ts_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.jml_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.pendapatan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ratio_tunggakan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('tunggakan.table.target_ratio') }}</th>
                    <th style="padding:10px; color:#aaa;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakan"></tbody>
        </table>
    </div>
</div>

{{-- Modal Buat Tahun Baru --}}
<div id="modalBuatTunggakan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Buat Data Tunggakan Tahun Baru</div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="tahunBuatTunggakan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 1, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatTunggakan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatTunggakan" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusTunggakan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Tunggakan</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunTunggakan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusTunggakan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusTunggakan" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>