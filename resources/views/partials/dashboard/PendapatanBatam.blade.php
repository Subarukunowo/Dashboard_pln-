<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">{{ __('PendapatanBatam.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPendapatan" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('PendapatanBatam.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select id="filterViewPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('PendapatanBatam.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('PendapatanBatam.filter.akumulasi') }}</option>
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPendapatanBatam"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('PendapatanBatam.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('PendapatanBatam.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('PendapatanBatam.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="pendapatanSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">{{ __('PendapatanBatam.delta_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('PendapatanBatam.table.selisih_rp') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatanDelta"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div class="card-title">{{ __('PendapatanBatam.detail_title') }}</div>
        <div style="display:flex; gap:5px; align-items:center;">
            <button id="btnMuatPendapatan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer;">
                ＋ Muat Data
            </button>
            <button id="btnBuatPendapatan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusPendapatan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px; margin-bottom:16px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('PendapatanBatam.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('PendapatanBatam.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('PendapatanBatam.table.delta') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">%</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatan"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="pendapatanEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="pendapatanEditContainer" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatPendapatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data Pendapatan</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunPendapatan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipePendapatan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatPendapatan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiMuatPendapatan" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusPendapatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Pendapatan</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunPendapatan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipePendapatan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusPendapatan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusPendapatan" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatPendapatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data Pendapatan</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunPendapatan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipePendapatan" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatPendapatan" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatPendapatan" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
        </div>
    </div>
</div>
