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
    <div style="display:flex; gap:var(--gap); flex:1; min-height:320px; align-items:flex-start;">
        <div class="chart-wrapper" style="flex:1; min-width:0; height:320px;">
            <canvas id="chartTargetRealisasi"></canvas>
        </div>
        <div class="custom-scroll" style="flex:0 0 380px; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:340px;">
            <table style="width:100%; border-collapse:collapse; table-layout:fixed; font-size:0.65rem;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="text-transform:uppercase; color:var(--text-muted); border-bottom:1px solid #444;">
                        <th style="padding:8px 4px; text-align:left; width:18%;">Bulan</th>
                        <th style="padding:8px 4px; text-align:right; color:#888;">T'24</th>
                        <th style="padding:8px 4px; text-align:right; color:#2196f3;">R'24</th>
                        <th style="padding:8px 4px; text-align:right; color:#ff9800;">T'25</th>
                        <th style="padding:8px 4px; text-align:right; color:var(--accent-green);">R'25</th>
                        <th style="padding:8px 4px; text-align:right; color:#aaa; width:12%;">Δ</th>
                    </tr>
                </thead>
                <tbody id="kwhTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-8">
    <div class="card-title">Delta Target (GWh)</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartDelta"></canvas>
    </div>
</div>

<div class="card col-4">
    <div class="card-title">% Pencapaian Terhadap Target</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartPencapaian"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">% Pertumbuhan 2024 vs 2025</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartPertumbuhan"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Ringkasan (GWh)</div>
    <div class="custom-scroll" style="overflow-x:auto; background:rgba(0,0,0,0.1); border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; font-size:0.75rem; min-width:1000px;">
            <thead style="background:#252525;">
                <tr style="color:var(--text-muted); text-transform:uppercase;">
                    <th style="padding:12px 8px; text-align:left;">Bulan</th>
                    <th style="padding:12px 8px; text-align:right;">Target 2024</th>
                    <th style="padding:12px 8px; text-align:right;">Realisasi 2024</th>
                    <th style="padding:12px 8px; text-align:right;">Target 2025</th>
                    <th style="padding:12px 8px; text-align:right;">Realisasi 2025</th>
                    <th style="padding:12px 8px; text-align:right;">Delta GWh</th>
                    <th style="padding:12px 8px; text-align:right;">Delta %</th>
                    <th style="padding:12px 8px; text-align:right;">% Pencapaian</th>
                    <th style="padding:12px 8px; text-align:right;">% Pertumbuhan</th>
                </tr>
            </thead>
            <tbody id="tabelRingkasan"></tbody>
        </table>
    </div>
</div>