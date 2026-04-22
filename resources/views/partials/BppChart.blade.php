<div class="card col-12">
    <div style="displa21y:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
        <div class="card-title">Realisasi BPP Rp/kWh: Konsolidasi vs Internal</div>
        <select id="filterBpp" aria-label="Filter kategori BPP">
            <option value="BPP PEMBANGKIT">BPP PEMBANGKIT</option>
            <option value="BPP S.D TRANSMISI">BPP S.D TRANSMISI</option>
            <option value="BPP S.D DIDTRIBUSI TM">BPP S.D DISTRIBUSI TM</option>
            <option value="BPP S.D DIDTRIBUSI TR">BPP S.D DISTRIBUSI TR</option>
        </select>
    </div>

    <div style="display:flex; gap:var(--gap); flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2;">
            <canvas id="bppComparisonChart"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:340px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase; color:var(--text-muted);">
                        <th style="padding:10px; text-align:left;">Bulan</th>
                        <th style="padding:10px; text-align:right; color:var(--accent-orange);">Konsol</th>
                        <th style="padding:10px; text-align:right; color:var(--accent-green);">Inter</th>
                        <th style="padding:10px; text-align:right;">Δ Delta</th>
                    </tr>
                </thead>
                <tbody id="bppTableBody"></tbody>
            </table>
        </div>
    </div>
</div>