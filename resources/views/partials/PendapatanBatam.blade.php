<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Pendapatan Batam (Rp)</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPendapatan" style="font-size:0.7rem; padding:4px;">
                {{-- diisi dinamis oleh JS --}}
            </select>
            <select id="filterViewPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>
    <div class="chart-wrapper" style="height:320px;">
        <canvas id="chartPendapatanBatam"></canvas>
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