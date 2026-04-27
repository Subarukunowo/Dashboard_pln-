<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Pendapatan Bulan Berjalan</div>
        <select id="filterBulanTunggakan" style="font-size:0.7rem; padding:4px;">
            <option value="all">Semua Bulan</option>
            <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
            <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
            <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
            <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
        </select>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:280px;">
        <div class="chart-wrapper" style="flex:2; height:280px;">
            <canvas id="chartTunggakanPendapatan"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:280px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Pendapatan</span>
                <span style="color:#aaa;">Ratio</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="tunggakanSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Ratio Tunggakan vs Target</div>
    <div class="chart-wrapper" style="height:250px;">
        <canvas id="chartTunggakanRatio"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Kinerja Tunggakan</div>
    <div class="custom-scroll" style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th><th>PAL Berjalan</th><th>PAL Tunggakan</th>
                    <th>TS Berjalan</th><th>TS Tunggakan</th><th>Jml Tunggakan</th>
                    <th>Pendapatan</th><th>Ratio Tunggakan</th><th>Target Ratio</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakan"></tbody>
        </table>
    </div>
</div>