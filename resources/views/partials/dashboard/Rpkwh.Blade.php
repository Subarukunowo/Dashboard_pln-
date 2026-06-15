{{-- CARD 1: Rencana vs Realisasi RPKWH --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
        <div class="card-title">{{ __('rpkwh.title') }}</div>
        <div style="display:flex; gap:5px; flex-wrap:wrap; align-items:center;">
            <select id="filterTarifRpkwh" style="font-size:0.7rem; padding:4px;"></select>
            <button id="btnMuatRpkwh"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer; white-space:nowrap;">
                ＋ Muat Data
            </button>
            <button id="btnBuatRpkwh"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusRpkwh"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer; white-space:nowrap;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRencanaRealisasi"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="rpkwhSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih_rpkwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhDelta"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="rpkwhEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="rpkwhEditContainer" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- CARD 2: Distribusi Golongan RPKWH --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPieRpkwh">{{ __('rpkwh.pie_title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTahunRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="2024">2024</option>
                <option value="2025" selected>2025</option>
            </select>
            <select id="filterViewRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('rpkwh.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('rpkwh.filter.akumulasi') }}</option>
            </select>
            <select id="filterBulanRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('rpkwh.filter.semua') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPieRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.golongan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.nilai') }}</th>
                    <th style="padding:10px; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="rpkwhPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.growth_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartGrowthRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.sosial') }}</th>
                    <th style="padding:10px; color:#00ff88;">{{ __('rpkwh.table.rtangga') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('rpkwh.table.bisnis') }}</th>
                    <th style="padding:10px; color:#e91e63;">{{ __('rpkwh.table.industri') }}</th>
                    <th style="padding:10px; color:#9c27b0;">{{ __('rpkwh.table.publik') }}</th>
                    <th style="padding:10px; color:#00bcd4;">{{ __('rpkwh.table.multiguna') }}</th>
                </tr>
            </thead>
            <tbody id="tabelGrowthRpkwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: BPP vs HJR --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.bpp_hjr_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBppHjr"></canvas>
    </div>

    <div class="custom-scroll" style="overflow-x:auto; overflow-y:auto; max-height:340px; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('rpkwh.table.hjr') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.bpp') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="tabelBppHjr"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Analisa RPKWH --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.analisa_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartAnalisa2024vs2025"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#555;">{{ __('rpkwh.table.t_lalu') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('rpkwh.table.r_lalu') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.t_ini') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.r_ini') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="rpkwhAnalisaSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.delta_analisa_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartAnalisaDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih_rpkwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhAnalisaDelta"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.persen_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPersenRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.target') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPersen"></tbody>
        </table>
    </div>
</div>

{{-- CARD 5: Tabel Analisa Detail RPKWH --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.tabel_analisa_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#555;">{{ __('rpkwh.table.target_lalu') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('rpkwh.table.real_lalu') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.target_ini') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.realisasi_ini') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.pct_capai_target') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.pct_capai_real') }}</th>
                </tr>
            </thead>
            <tbody id="tabelAnalisa"></tbody>
        </table>
    </div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatRpkwh" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data RPKWH</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunRpkwh" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeRpkwh" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatRpkwh" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiMuatRpkwh" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatRpkwh" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data RPKWH</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunRpkwh" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipeRpkwh" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatRpkwh" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatRpkwh" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusRpkwh" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data RPKWH</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunRpkwh" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeRpkwh" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusRpkwh" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusRpkwh" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>