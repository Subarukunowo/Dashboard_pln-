<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Pendapatan Bulan Berjalan</div>
        <select id="filterBulanTunggakan" style="font-size:0.7rem; padding:4px;">
            <option value="all">Semua Bulan</option>
            <option value="0">Januari</option><option value="1">Februari</option><option value="2">Maret</option>
            <option value="3">April</option><option value="4">Mei</option><option value="5">Juni</option>
            <option value="6">Juli</option><option value="7">Agustus</option><option value="8">September</option>
            <option value="9">Oktober</option><option value="10">November</option><option value="11">Desember</option>
        </select>
    </div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartTunggakanPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Pendapatan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Ratio</th>
                </tr>
            </thead>
            <tbody id="tunggakanSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Ratio Tunggakan vs Target</div>
    

    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTunggakanRatio"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#f44336;">Ratio Tunggakan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target Ratio</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakanRatio"></tbody>
        </table>
    </div>
</div>
<div class="card col-12">
    <div class="card-title">Tabel Kinerja Tunggakan</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">PAL Berjalan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">PAL Tunggakan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">TS Berjalan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">TS Tunggakan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Jml Tunggakan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Pendapatan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Ratio Tunggakan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Target Ratio</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakan"></tbody>
        </table>
    </div>
</div>