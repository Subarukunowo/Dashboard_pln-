<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Losses Jaringan PLN Batam 2025</div>
        <select id="filterEminLosses" style="font-size:0.7rem; padding:4px;">
            <option value="with">Dengan EMIN</option>
            <option value="without">Tanpa EMIN</option>
        </select>
    </div>
    <div class="chart-wrapper" style="height:300px;"><canvas id="chartLossesProduksi"></canvas></div>
</div>
<div class="card double-column">
    <div class="card-title">Losses % (Bulanan & Akumulasi)</div>
    <div class="chart-wrapper"><canvas id="chartLossesPct"></canvas></div>
</div>
<div class="card double-column">
    <div class="card-title">Losses Transmisi (kWh)</div>
    <div class="chart-wrapper"><canvas id="chartLossesKwh"></canvas></div>
</div>
<div class="card full-width">
    <div class="card-title">Tabel Losses</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th><th>Prod Bruto (jt kWh)</th><th>Prod Netto (jt kWh)</th>
                <th>Siap Salur (jt kWh)</th><th>Losses kWh</th>
                <th>Losses Bulanan (%)</th><th>Losses Akum (%)</th><th>Interkoneksi (jt kWh)</th>
            </tr>
        </thead>
        <tbody id="tabelLosses"></tbody>
    </table>
</div>