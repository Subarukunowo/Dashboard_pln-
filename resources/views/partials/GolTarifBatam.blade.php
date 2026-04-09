{{-- SECTION 1: PIE CHART PER GOLONGAN --}}
<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPie">Pendapatan Per Golongan Tarif</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTahunPie" style="font-size:0.7rem; padding:4px;">
                <option value="2024">2024</option>
                <option value="2025" selected>2025</option>
            </select>
            <select id="filterViewPie" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
            <select id="filterBulanPie" style="font-size:0.7rem; padding:4px;">
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
                <option value="11" selected>Des</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:350px;">
        <div class="chart-wrapper" style="flex:2; height:350px;">
            <canvas id="chartPieTarif"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:350px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Golongan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Nilai</span>
                <span style="color:#aaa;">%</span>
            </div>
            <table>
                <tbody id="tabelPieTarif"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION 2: REALISASI PENDAPATAN 2025 --}}
<div class="card double-column">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Realisasi vs Target Pendapatan 2025 (Juta Rp)</div>
        <select id="filterBulanRealisasiGol" style="font-size:0.7rem; padding:4px;">
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
    </div>
    <div class="chart-wrapper">
        <canvas id="chartRealisasiBulanan"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-title">Delta Realisasi 2025 (Juta Rp)</div>
    <div class="chart-wrapper">
        <canvas id="chartDeltaRealisasi"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Realisasi Pendapatan 2025</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th><th>Target</th><th>Realisasi</th><th>Delta</th><th>% Pencapaian</th>
            </tr>
        </thead>
        <tbody id="tabelRealisasi"></tbody>
    </table>
</div>

{{-- SECTION 3: TOTAL REALISASI 2024 vs 2025 --}}
<div class="card full-width">
    <div class="card-title">Total Realisasi Pendapatan 2024 vs 2025 (Triliun Rp)</div>
    <div class="chart-wrapper" style="height:320px;">
        <canvas id="chartTotal2024vs2025"></canvas>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Total Realisasi (Triliun Rp)</div>
    <div class="chart-wrapper">
        <canvas id="chartTotalDelta"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-title">% Pertumbuhan Pendapatan</div>
    <div class="chart-wrapper">
        <canvas id="chartTotalPertumbuhan"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Total Realisasi 2024 vs 2025</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th><th>Target 2024</th><th>Realisasi 2024</th>
                <th>Target 2025</th><th>Realisasi 2025</th><th>Delta</th><th>Delta %</th>
            </tr>
        </thead>
        <tbody id="tabelTotal"></tbody>
    </table>
</div>