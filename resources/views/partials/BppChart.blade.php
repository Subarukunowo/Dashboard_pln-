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
    <div class="chart-wrapper" style="flex:1; min-height:300px;">
        <canvas id="bppComparisonChart"></canvas>
    </div>
</div>