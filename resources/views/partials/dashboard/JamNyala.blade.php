<div class="card col-12">
    {{-- Header + Filters --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('jamnyala.title') }}</div>
            <span id="jamNyalaWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>
        <div style="display:flex; gap:5px; align-items:center;">
            <select id="filterTarifJamNyala" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanJamNyala" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('jamnyala.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <button id="btnMuatJamNyala"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer;">
                ＋ Muat Data
            </button>
            <button id="btnBuatJamNyala"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusJamNyala"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    {{-- Chart Line --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartJamNyalaLine"></canvas>
    </div>

    {{-- Tabel Line --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;" id="jamNyalaLineHead">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('jamnyala.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">{{ __('jamnyala.table.jam_nyala') }}</th>
                </tr>
            </thead>
            <tbody id="tabelJamNyalaLine"></tbody>
        </table>
    </div>

    {{-- Bar Chart --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('jamnyala.bar_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartJamNyalaBar"></canvas>
    </div>

    {{-- Tabel editable per golongan --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:400px;">
        <table style="width:100%; border-collapse:collapse;" id="jamNyalaEditTable">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888; min-width:180px; text-align:left;">Golongan</th>
                    @foreach(range(1, 12) as $m)
                        <th style="padding:10px; color:var(--accent-green); min-width:60px; text-align:center;">
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                        </th>
                    @endforeach
                    <th style="padding:10px; color:#ff9800; min-width:70px; text-align:center;">Simpan</th>
                </tr>
            </thead>
            <tbody id="jamNyalaSideTable"></tbody>
        </table>
    </div>

    {{-- Modal Muat Data --}}
    <div id="modalMuatJamNyala" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
        <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
            <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data Jam Nyala</div>
            <div style="margin-bottom:12px;">
                <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
                <select id="muatTahunJamNyala" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                    @foreach(range(date('Y'), 2020) as $y)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
                <select id="muatTipeJamNyala" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                    <option value="realisasi">Realisasi</option>
                    <option value="target">Target</option>
                </select>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="btnBatalMuat" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
                <button id="btnKonfirmasiMuat" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
            </div>
        </div>
    </div>

    {{-- Modal Hapus Data --}}
    <div id="modalHapusJamNyala" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
        <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
            <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Jam Nyala</div>
            <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
            <div style="margin-bottom:12px;">
                <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
                <select id="hapusTahunJamNyala" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                    @foreach(range(date('Y'), 2020) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
                <select id="hapusTipeJamNyala" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                    <option value="realisasi">Realisasi</option>
                    <option value="target">Target</option>
                    <option value="semua">Semua</option>
                </select>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="btnBatalHapus" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
                <button id="btnKonfirmasiHapus" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
            </div>
        </div>
    </div>

    {{-- Modal Buat Data --}}
    <div id="modalBuatJamNyala" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
        <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
            <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data Jam Nyala</div>
            <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
            <div style="margin-bottom:12px;">
                <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
                <select id="buatTahunJamNyala" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                    @foreach(range(date('Y') + 3, 2020) as $y)
                        <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
                <select id="buatTipeJamNyala" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                    <option value="target">Target</option>
                    <option value="realisasi">Realisasi</option>
                </select>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="btnBatalBuatJamNyala" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
                <button id="btnKonfirmasiBuatJamNyala" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
            </div>
        </div>
    </div>
</div>