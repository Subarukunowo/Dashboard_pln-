<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('babin.title') }}</div>
            <span id="babinWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterSectionBabin" class="sr-only">{{ __('babin.section.label') }}</label>
                <select id="filterSectionBabin">
                    <option value="babin">{{ __('babin.section.babin') }}</option>
                    <option value="sem">{{ __('babin.section.sem') }}</option>
                </select>

                <label for="filterTahunBabin" class="sr-only">{{ __('babin.filter.tahun') }}</label>
                <select id="filterTahunBabin">
                    @foreach(range(date('Y'), 2018) as $y)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <label for="filterViewBabin" class="sr-only">{{ __('babin.filter.tampilan') }}</label>
                <select id="filterViewBabin">
                    <option value="bulanan">{{ __('babin.filter.bulanan') }}</option>
                    <option value="akumulasi">{{ __('babin.filter.akumulasi') }}</option>
                </select>

                <label for="filterBulanBabin" class="sr-only">{{ __('babin.filter.bulan') }}</label>
                <select id="filterBulanBabin">
                    <option value="all">{{ __('babin.filter.semua_bulan') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatBabin" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatBabin" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusBabin" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBabinBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th style="color:var(--accent-blue);">Real Thn Lalu</th>
                    <th style="color:var(--accent-orange);">Target Thn Ini</th>
                    <th style="color:var(--accent-green);">Real Thn Ini</th>
                    <th>Selisih</th>
                    <th style="color:#ce93d8;">% Capaian</th>
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

<div class="card col-12 preview-card">
    <div class="card-title">Pertumbuhan YoY (Year-over-Year)</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartAllJualBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th style="color:var(--accent-blue);">Realisasi Thn Lalu</th>
                    <th style="color:var(--accent-orange);">Realisasi Thn Ini</th>
                    <th style="color:#ce93d8;">Growth YoY</th>
                </tr>
            </thead>
            <tbody id="allJualSideTable"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; flex-wrap:wrap;">
        <div class="card-title" style="margin:0;">Edit Data</div>

        <label for="filterTahunEditBabin" class="sr-only">Tahun Edit</label>
        <select id="filterTahunEditBabin">
            @foreach(range(date('Y'), 2018) as $y)
                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        <label for="filterAreaEditBabin" class="sr-only">Area Edit</label>
        <select id="filterAreaEditBabin">
            <option value="interkoneksi">{{ __('babin.section.babin') }}</option>
            <option value="sembulang">{{ __('babin.section.sem') }}</option>
        </select>

        <span id="babinEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="babinEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatBabin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data Babin</div>

        <div style="margin-bottom:12px;">
            <label for="muatTahunBabin" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunBabin" style="width:100%;">
                @foreach(range(date('Y'), 2018) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="muatTipeBabin" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeBabin" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatBabin" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatBabin" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusBabin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Babin</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunBabin" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunBabin" style="width:100%;">
                @foreach(range(date('Y'), 2018) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipeBabin" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeBabin" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusBabin" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusBabin" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatBabin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data Babin</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunBabin" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunBabin" style="width:100%;">
                @foreach(range(date('Y') + 3, 2018) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:12px;">
            <label for="buatAreaBabin" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Area</label>
            <select id="buatAreaBabin" style="width:100%;">
                <option value="semua">Semua (Interkoneksi + Sembulang)</option>
                <option value="interkoneksi">Interkoneksi</option>
                <option value="sembulang">Sembulang</option>
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="buatTipeBabin2" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipeBabin2" style="width:100%;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatBabin" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatBabin" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>