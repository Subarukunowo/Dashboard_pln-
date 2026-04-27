<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">P2TL — Pemeriksaan & Temuan Pelanggan</div>
        <select id="filterBulanP2tl" style="font-size:0.7rem; padding:4px;">
            <option value="all">Semua Bulan</option>
            <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
            <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
            <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
            <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
        </select>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:280px;">
        <div class="chart-wrapper" style="flex:2; height:280px;">
            <canvas id="chartP2tlTemuan"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:280px;">
            <div style="display:flex; gap:6px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#2196f3; margin-left:auto;">Periksa</span>
                <span style="color:#f44336;">Temuan</span>
                <span style="color:#ff9800;">kWh</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="p2tlSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">Temuan kWh</div>
    <div class="chart-wrapper"><canvas id="chartP2tlKwh"></canvas></div>
</div>

<div class="card col-6">
    <div class="card-title">Tagihan Susulan (Rp)</div>
    <div class="chart-wrapper"><canvas id="chartP2tlTagsus"></canvas></div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel P2TL</div>
    <table>
        <thead>
            <tr>
                <th>No</th><th>Bulan</th><th>Jlh Pemeriksaan</th>
                <th>Jlh Temuan Pelanggan</th><th>Jlh Temuan kWh</th><th>Jlh Tagsus (Rp)</th>
            </tr>
        </thead>
        <tbody id="tabelP2tl"></tbody>
    </table>
</div>