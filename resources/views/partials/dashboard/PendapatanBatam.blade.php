<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
        <div class="card-title">{{ __('PendapatanBatam.title') }}</div>

        <div class="filters">
            <label for="filterTarifPendapatan" class="sr-only">{{ __('PendapatanBatam.filter.tarif') }}</label>
            <select id="filterTarifPendapatan"></select>

            <label for="filterBulanPendapatan" class="sr-only">{{ __('PendapatanBatam.filter.bulan') }}</label>
            <select id="filterBulanPendapatan">
                <option value="all">{{ __('PendapatanBatam.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <label for="filterViewPendapatan" class="sr-only">{{ __('PendapatanBatam.filter.tampilan') }}</label>
            <select id="filterViewPendapatan">
                <option value="bulanan">{{ __('PendapatanBatam.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('PendapatanBatam.filter.akumulasi') }}</option>
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPendapatanBatam"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('PendapatanBatam.table.rencana') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('PendapatanBatam.table.realisasi') }}</th>
                    <th scope="col">{{ __('PendapatanBatam.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="pendapatanSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12 preview-card">
    <div class="card-title" style="margin-bottom:15px;">{{ __('PendapatanBatam.delta_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th scope="col">{{ __('PendapatanBatam.table.selisih_rp') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatanDelta"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div class="card-title">{{ __('PendapatanBatam.detail_title') }}</div>

        <div class="btn-action-group">
            <button id="btnMuatPendapatan" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatPendapatan" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusPendapatan" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid var(--border-color); border-radius:8px; margin-bottom:16px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th scope="col">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('PendapatanBatam.table.rencana') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('PendapatanBatam.table.realisasi') }}</th>
                    <th scope="col">{{ __('PendapatanBatam.table.delta') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">%</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatan"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="pendapatanEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="pendapatanEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatPendapatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data Pendapatan</div>

        <div style="margin-bottom:12px;">
            <label for="muatTahunPendapatan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunPendapatan" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="muatTipePendapatan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipePendapatan" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatPendapatan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatPendapatan" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusPendapatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data Pendapatan</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunPendapatan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunPendapatan" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipePendapatan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipePendapatan" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusPendapatan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusPendapatan" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatPendapatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data Pendapatan</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunPendapatan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunPendapatan" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="buatTipePendapatan" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipePendapatan" style="width:100%;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatPendapatan" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatPendapatan" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>