<div class="card col-12">
    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div class="card-title">{{ __('penjualan.title') }}</div>
            <span id="penjualanWarning"
                style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px;
                       background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>
        <div style="display:flex; gap:5px; flex-wrap:wrap; align-items:center;">
            <select id="filterTarif" aria-label="{{ __('penjualan.filter.tarif') }}" style="font-size:0.7rem; padding:4px;">
                <option value="JUMLAH">{{ __('penjualan.tarif.semua') }}</option>
                <optgroup label="{{ __('penjualan.tarif.sosial') }}">
                    <option value="Jumlah Sosial (S)">{{ __('penjualan.tarif.jumlah_sosial') }}</option>
                    <option value="S-2/450 VA">S-2 / 450 VA</option>
                    <option value="S-2/900 VA">S-2 / 900 VA</option>
                    <option value="S-2/1300 VA">S-2 / 1300 VA</option>
                    <option value="S-2/2200 VA">S-2 / 2200 VA</option>
                    <option value="S-2/>2200 VA s.d 200 kVA">S-2 / > 2200 VA</option>
                    <option value="S-3/>200 kVA">S-3 / > 200 kVA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.rumah_tangga') }}">
                    <option value="Jumlah Rumah Tangga (R)">{{ __('penjualan.tarif.jumlah_rt') }}</option>
                    <option value="R-1/450 VA">R-1 / 450 VA</option>
                    <option value="R-1/900 VA">R-1 / 900 VA</option>
                    <option value="R-1/1300 VA">R-1 / 1300 VA</option>
                    <option value="R-1/2200 VA">R-1 / 2200 VA</option>
                    <option value="R-2/>2200 s.d 6600 VA">R-2 / > 2200 VA</option>
                    <option value="R-3/>6600 VA">R-3 / > 6600 VA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.bisnis') }}">
                    <option value="Jumlah Bisnis (B)">{{ __('penjualan.tarif.jumlah_bisnis') }}</option>
                    <option value="B-1/450 VA">B-1 / 450 VA</option>
                    <option value="B-1/900 VA">B-1 / 900 VA</option>
                    <option value="B-1/1300 VA">B-1 / 1300 VA</option>
                    <option value="B-1/2200 VA">B-1 / 2200 VA</option>
                    <option value="B-2/>2200 VA s.d 200 kVA">B-2 / > 2200 VA</option>
                    <option value="B-3/>200 kVA">B-3 / > 200 kVA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.industri') }}">
                    <option value="Jumlah Industri (I)">{{ __('penjualan.tarif.jumlah_industri') }}</option>
                    <option value="I-1/450 VA">I-1 / 450 VA</option>
                    <option value="I-1/900 VA">I-1 / 900 VA</option>
                    <option value="I-1/1300 VA">I-1 / 1300 VA</option>
                    <option value="I-1/2200 VA">I-1 / 2200 VA</option>
                    <option value="I-1/2200 s/d 14000 VA">I-1/2200 s/d 14000 VA</option>
                    <option value="I-2/>14 s/d 200 kVA">I-2/>14 s/d 200 kVA</option>
                    <option value="I-3/>200 kVA s/d 5.000 kVA">I-3/>200 kVA s/d 5000 kVA</option>
                    <option value="I-3/>5.000 kVA s/d 30.000 kVA">I-3/>5000 kVA s/d 30000 kVA</option>
                    <option value="I-4/>30.000 kVA">I-4/>30.000 kVA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.publik') }}">
                    <option value="Jumlah Publik (P)">{{ __('penjualan.tarif.jumlah_publik') }}</option>
                    <option value="P-1/450 VA">P-1 / 450 VA</option>
                    <option value="P-1/900 VA">P-1 / 900 VA</option>
                    <option value="P-1/1300 VA">P-1 / 1300 VA</option>
                    <option value="P-1/2200 VA">P-1 / 2200 VA</option>
                    <option value="P-1/>2200 VA s.d 200 kVA">P-1 / > 2200 VA</option>
                    <option value="P-2/>200 kVA">P-2 / > 200 kVA</option>
                    <option value="P3">P3</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.multiguna') }}">
                    <option value="Jumlah Multiguna (M)">{{ __('penjualan.tarif.jumlah_multiguna') }}</option>
                    <option value="M/PS">M / PS</option>
                    <option value="M/TS1">M / TS1</option>
                    <option value="MB/Multi Guna Prabayar">MB/ Multi Guna Prabayar</option>
                    <option value="T/>200 kVA">T > 200 kVA</option>
                    <option value="C/>200 kVA">C > 200 kVA</option>
                </optgroup>
            </select>
            <select id="filterBulanPenjualan" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('penjualan.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select id="filterViewPenjualan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('penjualan.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('penjualan.filter.akumulasi') }}</option>
            </select>
            <button id="btnMuatPenjualan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3;
                       background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer; white-space:nowrap;">
                ＋ Muat Data
            </button>
            <button id="btnBuatPenjualan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88;
                       background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusPenjualan"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336;
                       background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer; white-space:nowrap;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    {{-- Chart --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="penjualanChart"></canvas>
    </div>

    {{-- Tabel ringkasan --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('penjualan.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange); text-align:right;">{{ __('penjualan.table.target') }}</th>
                    <th style="padding:10px; color:var(--accent-green); text-align:right;">{{ __('penjualan.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa; text-align:right;">{{ __('penjualan.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="penjualanTableBody"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="penjualanEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
        <span style="font-size:0.7rem; color:#555;">Edit per baris, klik Simpan untuk menyimpan</span>
    </div>
    <div id="penjualanEditContainer"
        class="custom-scroll"
        style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333;
               overflow-x:auto; overflow-y:auto; max-height:400px;">
        {{-- Diisi JS --}}
    </div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatPenjualan"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:300px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data Penjualan</div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunPenjualan"
                style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatPenjualan"
                style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">
                Batal
            </button>
            <button id="btnKonfirmasiMuatPenjualan"
                style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">
                Muat
            </button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusPenjualan"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:300px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data Penjualan</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunPenjualan"
                style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipePenjualan"
                style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusPenjualan"
                style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">
                Batal
            </button>
            <button id="btnKonfirmasiHapusPenjualan"
                style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">
                Hapus
            </button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatPenjualan"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:300px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data Penjualan</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunPenjualan"
                style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipePenjualan"
                style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatPenjualan"
                style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">
                Batal
            </button>
            <button id="btnKonfirmasiBuatPenjualan"
                style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">
                Buat
            </button>
        </div>
    </div>
</div>