{{-- JAM NYALA --}}
<div class="card full-width">
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
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartJamNyalaLine"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Jam Nyala</span>
            </div>
            <table><tbody id="jamNyalaSideTable"></tbody></table>
        </div>
    </div>
</div>
<div class="card full-width">
    <div class="card-title">Jam Nyala per Bulan</div>
    <div class="chart-wrapper" style="height:280px;"><canvas id="chartJamNyalaBar"></canvas></div>
</div>