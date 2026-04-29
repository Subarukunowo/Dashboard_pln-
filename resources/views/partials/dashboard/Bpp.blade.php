<div class="card col-12">
    <!-- Header Card -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
        <div class="card-title">Realisasi BPP Rp/kWh: Konsolidasi vs Internal</div>
        <select id="filterBpp" aria-label="Filter kategori BPP">
            <option value="BPP PEMBANGKIT">BPP PEMBANGKIT</option>
            <option value="BPP S.D TRANSMISI">BPP S.D TRANSMISI</option>
            <option value="BPP S.D DIDTRIBUSI TM">BPP S.D DISTRIBUSI TM</option>
            <option value="BPP S.D DIDTRIBUSI TR">BPP S.D DISTRIBUSI TR</option>
        </select>
    </div>

    <!-- Chart Section (Full Width) -->
    <div class="chart-wrapper" style="width:100%; min-height:300px; margin-bottom:20px;">
        <canvas id="bppComparisonChart"></canvas>
    </div>

    <!-- Table Section (Below Chart, Centered) -->
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse; margin:0 auto;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase; color:var(--text-muted);">
                    <!-- Label diperjelas & centered -->
                    <th style="padding:10px; text-align:center;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">Konsolidasi</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Internal</th>
                    <th style="padding:10px; text-align:center;">Selisih</th>
                </tr>
            </thead>
            <tbody id="bppTableBody"></tbody>
        </table>
    </div>
</div>