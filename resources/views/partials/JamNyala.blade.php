<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Realisasi Rata-Rata Jam Nyala Pelanggan</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifJamNyala" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanJamNyala" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
                <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
                <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartJamNyalaLine"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Jam Nyala</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="jamNyalaSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Jam Nyala per Bulan</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartJamNyalaBar"></canvas>
    </div>
</div>