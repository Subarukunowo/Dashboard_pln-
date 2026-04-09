<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Pendapatan Batam (Rp)</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPendapatan" style="font-size:0.7rem; padding:4px;">
                {{-- diisi dinamis oleh JS --}}
            </select>
            <select id="filterBulanPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option>
                <option value="1">Feb</option>
                <option value="2">Mar</option>
                <option value="3">Apr</option>
                <option value="4">Mei</option>
                <option value="5">Jun</option>
                <option value="6">Jul</option>
                <option value="7">Agu</option>
                <option value="8">Sep</option>
                <option value="9">Okt</option>
                <option value="10">Nov</option>
                <option value="11">Des</option>
            </select>
            <select id="filterViewPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:2; height:320px;">
            <canvas id="chartPendapatanBatam"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:320px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#ff9800; margin-left:auto;">Rencana</span>
                <span style="color:var(--accent-green);">Realisasi</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table>
                <tbody id="pendapatanSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Pendapatan (Rp)</div>
    <div class="chart-wrapper">
        <canvas id="chartDeltaPendapatan"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Pendapatan Batam</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Delta</th>
                <th>% Pencapaian</th>
            </tr>
        </thead>
        <tbody id="tabelPendapatan"></tbody>
    </table>
</div>