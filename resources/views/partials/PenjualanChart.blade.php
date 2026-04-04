<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Kinerja Penjualan</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarif" style="font-size:0.7rem; padding:4px;">
                <option value="JUMLAH">Semua</option>
                <optgroup label="Sosial">
                    <option value="Jumlah Sosial (S)">Jumlah Sosial</option>
                    <option value="S-2 / 450 VA">S-2 / 450 VA</option>
                    <option value="S-2 / 900 VA">S-2 / 900 VA</option>
                    <option value="S-2 / 1300 VA">S-2 / 1300 VA</option>
                    <option value="S-2 / 2200 VA">S-2 / 2200 VA</option>
                    <option value="S-2 / > 2200 VA s.d 200 kVA">S-2 / > 2200 VA</option>
                    <option value="S-3 / > 200 kVA">S-3 / > 200 kVA</option>
                </optgroup>
                <optgroup label="Rumah Tangga">
                    <option value="Jumlah Rumah Tangga (R)">Jumlah Rumah Tangga</option>
                    <option value="R-1 / 450 VA">R-1 / 450 VA</option>
                    <option value="R-1 / 900 VA">R-1 / 900 VA</option>
                    <option value="R-1 / 1300 VA">R-1 / 1300 VA</option>
                    <option value="R-1 / 2200 VA">R-1 / 2200 VA</option>
                    <option value="R-2 / > 2200 s.d 6600 VA">R-2 / > 2200 VA</option>
                    <option value="R-3 / > 6600 VA">R-3 / > 6600 VA</option>
                </optgroup>
                <optgroup label="Bisnis">
                    <option value="Jumlah Bisnis (B)">Jumlah Bisnis</option>
                    <option value="B-1 / 450 VA">B-1 / 450 VA</option>
                    <option value="B-1 / 900 VA">B-1 / 900 VA</option>
                    <option value="B-1 / 1300 VA">B-1 / 1300 VA</option>
                    <option value="B-1 / 2200 VA">B-1 / 2200 VA</option>
                    <option value="B-2 / > 2200 VA s.d 200 kVA">B-2 / > 2200 VA</option>
                    <option value="B-3 / > 200 kVA">B-3 / > 200 kVA</option>
                </optgroup>
                <optgroup label="Industri">
                    <option value="Jumlah Industri (I)">Jumlah Industri</option>
                    <option value="I-1 / 450 VA">I-1 / 450 VA</option>
                    <option value="I-1 / 900 VA">I-1 / 900 VA</option>
                    <option value="I-1 / 1300 VA">I-1 / 1300 VA</option>
                    <option value="I-1 / 2200 VA">I-1 / 2200 VA</option>
                    <option value="I-1/2200 s/d 14000 VA">I-1/2200 s/d 14000 VA</option>
                    <option value="I-2/>14 s/d 200 kVA">I-2/>14 s/d 200 kVA</option>
                    <option value="I-3/>200 kVA s/d 5.000 kVA">I-3/>200 kVA s/d 5000 kVA</option>
                    <option value="I-3/>5.000 kVA s/d 30.000 kVA">I-3/>5000 kVA s/d 30000 kVA</option>
                    <option value="I-4/>30.000 kVA">I-4/>30.000 kVA</option>
                </optgroup>
                <optgroup label="Publik">
                    <option value="Jumlah Publik (P)">Jumlah Publik</option>
                    <option value="P-1 / 450 VA">P-1 / 450 VA</option>
                    <option value="P-1 / 900 VA">P-1 / 900 VA</option>
                    <option value="P-1 / 1300 VA">P-1 / 1300 VA</option>
                    <option value="P-1 / 2200 VA">P-1 / 2200 VA</option>
                    <option value="P-1 / > 2200 VA s.d 200 kVA">P-1 / > 2200 VA</option>
                    <option value="P-2 / > 200 kVA">P-2 / > 200 kVA</option>
                    <option value="P3">P3</option>
                </optgroup>
                <optgroup label="Multiguna">
                    <option value="Jumlah Multiguna (M)">Jumlah Multiguna</option>
                    <option value="M / PS (Penerangan Sementara)">M / PS</option>
                    <option value="M / TS1 (Non Pelanggan)">M / TS1</option>
                    <option value="MB/ Multi Guna Prabayar">MB/ Multi Guna Prabayar</option>
                    <option value="T > 200 kVA">T > 200 kVA</option>
                    <option value="C > 200 kVA">C > 200 kVA</option>
                </optgroup>
            </select>
            <select id="filterViewPenjualan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>
    <div class="chart-wrapper" style="flex:1;">
        <canvas id="penjualanChart"></canvas>
    </div>
</div>