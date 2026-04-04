<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Penjualan KWH — Target vs Realisasi 2024 & 2025</div>
        <select id="filterViewKwh" style="font-size:0.7rem; padding:4px;">
            <option value="bulanan">Bulanan</option>
            <option value="akumulasi">Akumulasi</option>
        </select>
    </div>
    <div class="chart-wrapper" style="height:320px;">
        <canvas id="chartTargetRealisasi"></canvas>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Target (GWh)</div>
    <div class="chart-wrapper">
        <canvas id="chartDelta"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-title">% Pencapaian Terhadap Target</div>
    <div class="chart-wrapper">
        <canvas id="chartPencapaian"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">% Pertumbuhan 2024 vs 2025</div>
    <div class="chart-wrapper">
        <canvas id="chartPertumbuhan"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Ringkasan (GWh)</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Target 2024</th>
                <th>Realisasi 2024</th>
                <th>Target 2025</th>
                <th>Realisasi 2025</th>
                <th>Delta GWh</th>
                <th>Delta %</th>
                <th>% Pencapaian</th>
                <th>% Pertumbuhan</th>
            </tr>
        </thead>
        <tbody id="tabelRingkasan"></tbody>
    </table>
</div>