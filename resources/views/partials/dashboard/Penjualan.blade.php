<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">{{ __('penjualan.title') }}</div>
        <div style="display:flex; gap:5px; flex-wrap:wrap;">
            <select id="filterTarif" aria-label="{{ __('penjualan.filter.tarif') }}" style="font-size:0.7rem; padding:4px;">
                <option value="JUMLAH">{{ __('penjualan.tarif.semua') }}</option>
                <optgroup label="{{ __('penjualan.tarif.sosial') }}">
                    <option value="Jumlah Sosial (S)">{{ __('penjualan.tarif.jumlah_sosial') }}</option>
                    <option value="S-2 / 450 VA">S-2 / 450 VA</option>
                    <option value="S-2 / 900 VA">S-2 / 900 VA</option>
                    <option value="S-2 / 1300 VA">S-2 / 1300 VA</option>
                    <option value="S-2 / 2200 VA">S-2 / 2200 VA</option>
                    <option value="S-2 / > 2200 VA s.d 200 kVA">S-2 / > 2200 VA</option>
                    <option value="S-3 / > 200 kVA">S-3 / > 200 kVA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.rumah_tangga') }}">
                    <option value="Jumlah Rumah Tangga (R)">{{ __('penjualan.tarif.jumlah_rt') }}</option>
                    <option value="R-1 / 450 VA">R-1 / 450 VA</option>
                    <option value="R-1 / 900 VA">R-1 / 900 VA</option>
                    <option value="R-1 / 1300 VA">R-1 / 1300 VA</option>
                    <option value="R-1 / 2200 VA">R-1 / 2200 VA</option>
                    <option value="R-2 / > 2200 s.d 6600 VA">R-2 / > 2200 VA</option>
                    <option value="R-3 / > 6600 VA">R-3 / > 6600 VA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.bisnis') }}">
                    <option value="Jumlah Bisnis (B)">{{ __('penjualan.tarif.jumlah_bisnis') }}</option>
                    <option value="B-1 / 450 VA">B-1 / 450 VA</option>
                    <option value="B-1 / 900 VA">B-1 / 900 VA</option>
                    <option value="B-1 / 1300 VA">B-1 / 1300 VA</option>
                    <option value="B-1 / 2200 VA">B-1 / 2200 VA</option>
                    <option value="B-2 / > 2200 VA s.d 200 kVA">B-2 / > 2200 VA</option>
                    <option value="B-3 / > 200 kVA">B-3 / > 200 kVA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.industri') }}">
                    <option value="Jumlah Industri (I)">{{ __('penjualan.tarif.jumlah_industri') }}</option>
                    <option value="I-1 / 450 VA">I-1 / 450 VA</option>
                    <option value="I-1 / 900 VA">I-1 / 900 VA</option>
                    <option value="I-1 / 1300 VA">I-1 / 1300 VA</option>
                    <option value="I-1 / 2200 VA">I-1 / 2200 VA</option>
                    <option value="I-1/2200 s/d 14000 VA">I-1/2200 s/d 14000 VA</option>
                    <option value="I-2/>14 s/d 200 kVA">I-2/>14 s/d 200 kVA</option>
                    <option value="I-3/>200 kVA s.d 5.000 kVA">I-3/>200 kVA s/d 5000 kVA</option>
                    <option value="I-3/>5.000 kVA s.d 30.000 kVA">I-3/>5000 kVA s/d 30000 kVA</option>
                    <option value="I-4/>30.000 kVA">I-4/>30.000 kVA</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.publik') }}">
                    <option value="Jumlah Publik (P)">{{ __('penjualan.tarif.jumlah_publik') }}</option>
                    <option value="P-1 / 450 VA">P-1 / 450 VA</option>
                    <option value="P-1 / 900 VA">P-1 / 900 VA</option>
                    <option value="P-1 / 1300 VA">P-1 / 1300 VA</option>
                    <option value="P-1 / 2200 VA">P-1 / 2200 VA</option>
                    <option value="P-1 / > 2200 VA s.d 200 kVA">P-1 / > 2200 VA</option>
                    <option value="P-2 / > 200 kVA">P-2 / > 200 kVA</option>
                    <option value="P3">P3</option>
                </optgroup>
                <optgroup label="{{ __('penjualan.tarif.multiguna') }}">
                    <option value="Jumlah Multiguna (M)">{{ __('penjualan.tarif.jumlah_multiguna') }}</option>
                    <option value="M / PS (Penerangan Sementara)">M / PS</option>
                    <option value="M / TS1 (Non Pelanggan)">M / TS1</option>
                    <option value="MB/ Multi Guna Prabayar">MB/ Multi Guna Prabayar</option>
                    <option value="T > 200 kVA">T > 200 kVA</option>
                    <option value="C > 200 kVA">C > 200 kVA</option>
                </optgroup>
            </select>
            <select id="filterBulanPenjualan" aria-label="{{ __('penjualan.filter.bulan') }}" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('penjualan.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select id="filterViewPenjualan" aria-label="{{ __('penjualan.filter.tampilan') }}" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('penjualan.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('penjualan.filter.akumulasi') }}</option>
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="penjualanChart"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('penjualan.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('penjualan.table.target') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('penjualan.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('penjualan.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="penjualanTableBody"></tbody>
        </table>
    </div>
</div>