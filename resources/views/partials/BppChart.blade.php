<div class="card double-column">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Realisasi BPP Rp/kWh: Konsolidasi vs Internal</div>
        <select id="filterBpp" style="width:auto; min-width:200px;">
            <option value="BPP PEMBANGKIT">BPP PEMBANGKIT</option>
            <option value="BPP S.D TRANSMISI">BPP S.D TRANSMISI</option>
            <option value="BPP S.D DIDTRIBUSI TM">BPP S.D DIDTRIBUSI TM</option>
            <option value="BPP S.D DIDTRIBUSI TR">BPP S.D DIDTRIBUSI TR</option>
        </select>
    </div>

    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2;">
            <canvas id="bppComparisonChart"></canvas>
        </div>

        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:340px;">
            <div style="display:flex; gap:12px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:var(--accent-orange); margin-left:auto;">● Konsolidasi</span>
                <span style="color:var(--accent-green);">● Internal</span>
                <span style="color:#aaa;">Δ Delta</span>
            </div>
            <table>
                <tbody id="bppTableBody"></tbody>
            </table>
        </div>
    </div>
</div>