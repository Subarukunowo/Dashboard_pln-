{{-- CARD 1: Pendapatan --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('tunggakan.pendapatan_title') }}</div>
            <span id="tunggakanWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterBulanTunggakan" class="sr-only">{{ __('tunggakan.filter.bulan') }}</label>
                <select id="filterBulanTunggakan">
                    <option value="all">{{ __('tunggakan.filter.semua_bulan') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnBuatTahunTunggakan" class="btn-action btn-action--load">
                ＋ Buat Tahun Baru
            </button>
            <button id="btnHapusTunggakan" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartTunggakanPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('tunggakan.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('tunggakan.table.pendapatan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.ratio') }}</th>
                </tr>
            </thead>
            <tbody id="tunggakanSideTable"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Ratio --}}
<div class="card col-12 preview-card">
    <div class="card-title">{{ __('tunggakan.ratio_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTunggakanRatio"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('tunggakan.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-red);">{{ __('tunggakan.table.ratio_tunggakan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('tunggakan.table.target_ratio') }}</th>
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
        <span id="tunggakanEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>

    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th scope="col">{{ __('tunggakan.table.bulan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.pal_berjalan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.pal_tunggakan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.ts_berjalan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.ts_tunggakan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.jml_tunggakan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.pendapatan') }}</th>
                    <th scope="col">{{ __('tunggakan.table.ratio_tunggakan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('tunggakan.table.target_ratio') }}</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakan"></tbody>
        </table>
    </div>
</div>

{{-- Modal Buat Tahun Baru --}}
<div id="modalBuatTunggakan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Buat Data Tunggakan Tahun Baru</div>

        <div style="margin-bottom:16px;">
            <label for="tahunBuatTunggakan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="tahunBuatTunggakan" style="width:100%;">
                @foreach(range(date('Y') + 1, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatTunggakan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatTunggakan" class="btn-action btn-action--solid btn-action--load">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusTunggakan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Tunggakan</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:16px;">
            <label for="hapusTahunTunggakan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunTunggakan" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusTunggakan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusTunggakan" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>