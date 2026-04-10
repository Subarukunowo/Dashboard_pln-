<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Penjualan KWH — Target vs Realisasi 2024 & 2025</div>
        <div style="display:flex; gap:5px;">
            <select id="filterBulanKwh" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
                <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
                <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
            </select>
            <select id="filterViewKwh" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:2; height:320px;">
            <canvas id="chartTargetRealisasi"></canvas>
        </div>
      <div class="custom-scroll" style="flex:0 0 200px; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:340px;">
            <div style="display:flex; gap:6px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#555; margin-left:auto;">T'24</span>
                <span style="color:#2196f3;">R'24</span>
                <span style="color:#ff9800;">T'25</span>
                <span style="color:var(--accent-green);">R'25</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="kwhTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-8">
    <div class="card-title">Delta Target (GWh)</div>
    <div class="chart-wrapper">
        <canvas id="chartDelta"></canvas>
    </div>
</div>

<div class="card col-4">
    <div class="card-title">% Pencapaian Terhadap Target</div>
    <div class="chart-wrapper">
        <canvas id="chartPencapaian"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">% Pertumbuhan 2024 vs 2025</div>
    <div class="chart-wrapper">
        <canvas id="chartPertumbuhan"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Ringkasan (GWh)</div>
    <div class="custom-scroll" style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Target 2024</th><th>Realisasi 2024</th>
                    <th>Target 2025</th><th>Realisasi 2025</th>
                    <th>Delta GWh</th><th>Delta %</th>
                    <th>% Pencapaian</th><th>% Pertumbuhan</th>
                </tr>
            </thead>
            <tbody id="tabelRingkasan"></tbody>
        </table>
    </div>
</div>