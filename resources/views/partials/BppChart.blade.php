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
    <div style="display: flex; gap: 15px; flex: 1; min-height: 300px;">
        <div class="chart-wrapper" style="flex: 2;">
            <canvas id="bppComparisonChart"></canvas>
        </div>
        <div id="bppLegendPanel" style="flex: 1; overflow-y: auto; background: rgba(0,0,0,0.1); padding: 10px; border-radius: 8px; border-left: 1px solid #333;">
            <div style="font-size: 0.75rem; color: #888; margin-bottom: 8px; font-weight: bold;">DETAIL ANGKA</div>
            <table style="width: 100%; font-size: 0.8rem; color: white; border-collapse: collapse;">
                <tbody id="bppTableBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>