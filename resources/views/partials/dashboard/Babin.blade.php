<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('babin.title') }}</div>
            <span id="babinWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>
        <div style="display:flex; gap:5px; align-items:center;">
            <select id="filterSectionBabin" style="font-size:0.7rem; padding:4px;">
                <option value="babin">{{ __('babin.section.babin') }}</option>
                <option value="sem">{{ __('babin.section.sem') }}</option>
            </select>
            <select id="filterTahunBabin" style="font-size:0.7rem; padding:4px;">
                @foreach(range(date('Y'), 2018) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <select id="filterViewBabin" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('babin.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('babin.filter.akumulasi') }}</option>
            </select>
            <select id="filterBulanBabin" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('babin.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <button id="btnMuatBabin"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer;">
                ＋ Muat Data
            </button>
            <button id="btnBuatBabin"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusBabin"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBabinBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">Bulan</th>
                    <th style="padding:10px; color:#2196f3;">Real Thn Lalu</th>
                    <th style="padding:10px; color:#ff9800;">Target Thn Ini</th>
                    <th style="padding:10px; color:var(--accent-green);">Real Thn Ini</th>
                    <th style="padding:10px; color:#aaa;">Selisih</th>
                    <th style="padding:10px; color:#ce93d8;">% Capaian</th>
                </tr>
            </thead>
            <tbody id="babinSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">Delta Target vs Realisasi</div>
    <div class="chart-wrapper" style="height:260px;">
        <canvas id="chartBabinDelta"></canvas>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">% Capaian Target (YTD)</div>
    <div class="chart-wrapper" style="height:260px;">
        <canvas id="chartBabinPie"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Pertumbuhan YoY (Year-over-Year)</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartAllJualBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">Bulan</th>
                    <th style="padding:10px; color:#2196f3;">Realisasi Thn Lalu</th>
                    <th style="padding:10px; color:#ff9800;">Realisasi Thn Ini</th>
                    <th style="padding:10px; color:#ce93d8;">Growth YoY</th>
                </tr>
            </thead>
            <tbody id="allJualSideTable"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="babinEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="babinEditContainer" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatBabin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data Babin</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunBabin" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2018) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeBabin" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatBabin" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiMuatBabin" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusBabin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Babin</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunBabin" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2018) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeBabin" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusBabin" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusBabin" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatBabin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data Babin</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunBabin" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 3, 2018) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Area</label>
            <select id="buatAreaBabin" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="semua">Semua (Interkoneksi + Sembulang)</option>
                <option value="interkoneksi">Interkoneksi</option>
                <option value="sembulang">Sembulang</option>
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipeBabin2" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatBabin" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatBabin" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
        </div>
    </div>
</div>
