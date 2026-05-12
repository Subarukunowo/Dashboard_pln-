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
                <option value="0">Januari</option><option value="1">Februari</option><option value="2">Maret</option>
                <option value="3">April</option><option value="4">Mei</option><option value="5">Juni</option>
                <option value="6">Juli</option><option value="7">Agustus</option><option value="8">September</option>
                <option value="9">Oktober</option><option value="10">November</option><option value="11">Desember</option>
            </select>
        </div>
    </div>

    {{-- Chart Produksi --}}
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartLossesProduksi"></canvas>
    </div>

    {{-- Tabel Produksi --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Prod Bruto</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Prod Netto</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Siap Salur</th>
                </tr>
            </thead>
            <tbody id="lossesSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Losses % (Bulanan & Akumulasi)</div>
    
    {{-- Chart Losses % --}}
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartLossesPct"></canvas>
    </div>

    {{-- Tabel Losses % (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#f44336;">Losses Trans Bln (%)</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Losses Trans Akum (%)</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Losses Jar Bln (%)</th>
                    <th style="padding:10px; text-align:center; color:#9c27b0;">Losses Jar Akum (%)</th>
                </tr>
            </thead>
            <tbody id="tabelLossesPct"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Losses Transmisi, Distribusi & Jaringan (kWh)</div>
    
    {{-- Chart Losses kWh --}}
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartLossesKwh"></canvas>
    </div>

    {{-- Tabel Losses kWh (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#e91e63;">Losses Trans</th>
                    <th style="padding:10px; text-align:center; color:#9c27b0;">Losses Dist</th>
                    <th style="padding:10px; text-align:center; color:#f44336;">Losses Jar</th>
                </tr>
            </thead>
            <tbody id="tabelLossesKwh"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Rincian Losses (jt kWh)</div>
    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap; font-size:0.75rem;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Prod Bruto</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Prod Netto</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Siap Salur</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Trans</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Bln (%)</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Akum (%)</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Interkoneksi</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Dist</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Dist (%)</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Dist Akum</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Jar</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Jar (%)</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Losses Jar Akum</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">Target (%)</th>
                </tr>
            </thead>
            <tbody id="tabelLosses"></tbody>
        </table>
    </div>
</div>