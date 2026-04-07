<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Kinerja Penjualan BABIN & WRKR</div>
        <div style="display:flex; gap:5px;">
            <select id="filterSectionBabin" style="font-size:0.7rem; padding:4px;">
                <option value="babin">Interkoneksi Batam-Bintan</option>
                <option value="sem">Sembulang, Blk Pdg, Buluh & Nguan</option>
            </select>
            <select id="filterViewBabin" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>
    <div class="chart-wrapper" style="height:300px;"><canvas id="chartBabinBar"></canvas></div>
</div>
<div class="card double-column">
    <div class="card-title">Delta BABIN</div>
    <div class="chart-wrapper"><canvas id="chartBabinDelta"></canvas></div>
</div>
<div class="card double-column">
    <div class="card-title">Distribusi KWH Jual (Bulan Terakhir)</div>
    <div class="chart-wrapper"><canvas id="chartBabinPie"></canvas></div>
</div>
<div class="card full-width">
    <div class="card-title">KWH Penjualan PLN Batam Gabungan</div>
    <div class="chart-wrapper" style="height:300px;"><canvas id="chartAllJualBar"></canvas></div>
</div>