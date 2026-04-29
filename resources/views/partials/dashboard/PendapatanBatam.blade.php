<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Pendapatan Batam (Rp)</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPendapatan" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
                <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
                <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
            </select>
            <select id="filterViewPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>

   
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPendapatanBatam"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">Rencana</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Real</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Δ</th>
                </tr>
            </thead>
            <tbody id="pendapatanSideTable"></tbody>
        </table>
    </div>
</div>
<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">Delta Pendapatan (Rp)</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaPendapatan"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta (Rp)</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatanDelta"></tbody>
        </table>
    </div>
</div>
<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">Tabel Detail Pendapatan Batam</div>
    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">Rencana</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Realisasi</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">%</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatan"></tbody>
        </table>
    </div>
</div>