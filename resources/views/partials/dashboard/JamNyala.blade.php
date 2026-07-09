<div class="card col-12 preview-card">
    {{-- Header + Filters --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('jamnyala.title') }}</div>
            <span id="jamNyalaWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterTarifJamNyala" class="sr-only">{{ __('jamnyala.filter.tarif') }}</label>
                <select id="filterTarifJamNyala"></select>

                <label for="filterBulanJamNyala" class="sr-only">{{ __('jamnyala.filter.bulan') }}</label>
                <select id="filterBulanJamNyala">
                    <option value="all">{{ __('jamnyala.filter.semua_bulan') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatJamNyala" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatJamNyala" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusJamNyala" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    {{-- Chart Line --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartJamNyalaLine"></canvas>
    </div>

    {{-- Tabel Line --}}
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr id="jamNyalaLineHead">
                    <th>{{ __('jamnyala.table.bulan') }}</th>
                    <th style="color:var(--accent-green);">{{ __('jamnyala.table.jam_nyala') }}</th>
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

    {{-- Edit Data per Golongan — selalu tahun berjalan, tanpa filter tahun --}}
    <div style="display:flex; align-items:center; gap:14px; margin-top:10px; margin-bottom:15px; flex-wrap:wrap;">
        <label for="filterGolonganEdit" class="sr-only">Golongan Tarif</label>
        <select id="filterGolonganEdit" style="min-width:220px;"></select>

        <div class="card-title" style="margin:0;">Edit Data</div>
        <span style="font-size:0.75rem; color:var(--text-secondary);">Edit per baris, klik Simpan untuk menyimpan</span>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:400px;">
        <table id="jamNyalaEditTable">
            <thead>
                <tr>
                    <th style="min-width:120px; text-align:left;">Bulan</th>
                    <th style="color:var(--accent-orange); min-width:120px;">Target (KWH)</th>
                    <th style="color:var(--accent-green); min-width:120px;">Realisasi (KWH)</th>
                    <th style="min-width:80px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="jamNyalaSideTable"></tbody>
        </table>
    </div>

    {{-- Modal Muat Data --}}
    <div id="modalMuatJamNyala" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
            <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data Jam Nyala</div>

            <div style="margin-bottom:12px;">
                <label for="muatTahunJamNyala" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
                <select id="muatTahunJamNyala" style="width:100%;"></select>
            </div>

            <div style="margin-bottom:16px;">
                <label for="muatTipeJamNyala" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
                <select id="muatTipeJamNyala" style="width:100%;">
                    <option value="realisasi">Realisasi</option>
                    <option value="target">Target</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="btnBatalMuat" class="btn-action btn-action--ghost">Batal</button>
                <button id="btnKonfirmasiMuat" class="btn-action btn-action--solid btn-action--load">Muat</button>
            </div>
        </div>
    </div>

    {{-- Modal Hapus Data --}}
    <div id="modalHapusJamNyala" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
            <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Jam Nyala</div>
            <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

            <div style="margin-bottom:12px;">
                <label for="hapusTahunJamNyala" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
                <select id="hapusTahunJamNyala" style="width:100%;"></select>
            </div>

            <div style="margin-bottom:16px;">
                <label for="hapusTipeJamNyala" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
                <select id="hapusTipeJamNyala" style="width:100%;">
                    <option value="realisasi">Realisasi</option>
                    <option value="target">Target</option>
                    <option value="semua">Semua</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="btnBatalHapus" class="btn-action btn-action--ghost">Batal</button>
                <button id="btnKonfirmasiHapus" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
            </div>
        </div>
    </div>

    {{-- Modal Buat Data --}}
    <div id="modalBuatJamNyala" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
            <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data Jam Nyala</div>
            <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat data kosong (nilai = 0) untuk SEMUA golongan tarif yang terdaftar, 12 bulan penuh, untuk tahun yang dipilih.</div>

            <div style="margin-bottom:12px;">
                <label for="buatTahunJamNyala" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
                <select id="buatTahunJamNyala" style="width:100%;">
                    @foreach(range(date('Y') + 3, 2020) as $y)
                        <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:16px;">
                <label for="buatTipeJamNyala" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
                <select id="buatTipeJamNyala" style="width:100%;">
                    <option value="target">Target</option>
                    <option value="realisasi">Realisasi</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="btnBatalBuatJamNyala" class="btn-action btn-action--ghost">Batal</button>
                <button id="btnKonfirmasiBuatJamNyala" class="btn-action btn-action--solid btn-action--create">Buat</button>
            </div>
        </div>
    </div>
</div>