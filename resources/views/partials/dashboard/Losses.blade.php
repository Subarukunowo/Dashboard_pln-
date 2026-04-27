<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">Losses Jaringan PLN Batam 2025</div>
        <div style="display:flex; gap:5px;">
            <select id="filterEminLosses" style="font-size:0.7rem; padding:4px;">
                <option value="with">Dengan EMIN</option>
                <option value="without">Tanpa EMIN</option>
            </select>
            <select id="filterBulanLosses" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
                <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
                <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:2.5; height:320px;">
            <canvas id="chartLossesProduksi"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:320px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase; color:#888;">
                        <th style="padding:10px; text-align:left;">Bulan</th>
                        <th style="padding:10px; text-align:right; color:#2196f3;">Bruto</th>
                        <th style="padding:10px; text-align:right; color:#00ff88;">Netto</th>
                        <th style="padding:10px; text-align:right; color:#ff9800;">Salur</th>
                    </tr>
                </thead>
                <tbody id="lossesSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">Losses % (Bulanan & Akumulasi)</div>
    <div class="chart-wrapper"><canvas id="chartLossesPct"></canvas></div>
</div>

<div class="card col-6">
    <div class="card-title">Losses Transmisi, Distribusi & Jaringan (kWh)</div>
    <div class="chart-wrapper"><canvas id="chartLossesKwh"></canvas></div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Rincian Losses (jt kWh)</div>
    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap; font-size:0.75rem;">
            <thead>
                <tr>
                    <th style="text-align:left; position:sticky; left:0; background:#252525; z-index:2; border-right:1px solid #444;">Bulan</th>
                    <th>Prod Bruto</th><th>Prod Netto</th><th>Siap Salur</th>
                    <th>Losses Trans</th><th>Losses Bln (%)</th><th>Losses Akum (%)</th>
                    <th>Interkoneksi</th>
                    <th>Losses Dist</th><th>Losses Dist (%)</th><th>Losses Dist Akum</th>
                    <th>Losses Jar</th><th>Losses Jar (%)</th><th>Losses Jar Akum</th>
                    <th style="color:var(--accent-orange);">Target (%)</th>
                </tr>
            </thead>
            <tbody id="tabelLosses"></tbody>
        </table>
    </div>
</div>