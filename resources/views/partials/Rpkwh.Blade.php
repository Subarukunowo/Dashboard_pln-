{{-- SECTION 1: RENCANA VS REALISASI PER TARIF --}}
<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi RPKWH</div>
        <select id="filterTarifRpkwh" style="font-size:0.7rem; padding:4px;">
            {{-- diisi dinamis JS --}}
        </select>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartRencanaRealisasi"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#ff9800; margin-left:auto;">Rencana</span>
                <span style="color:var(--accent-green);">Realisasi</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table>
                <tbody id="rpkwhSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper">
        <canvas id="chartDeltaRpkwh"></canvas>
    </div>
</div>

{{-- SECTION 2: PIE GOLONGAN --}}
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPieRpkwh">RPKWH Per Golongan</div>
        <div style="display:flex; gap:5px; flex-wrap:wrap;">
            <select id="filterTahunRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="2024">2024</option>
                <option value="2025" selected>2025</option>
            </select>
            <select id="filterViewRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
            <select id="filterBulanRpkwh" style="font-size:0.7rem; padding:4px;">
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
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartPieRpkwh"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Golongan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Nilai</span>
                <span style="color:#aaa;">%</span>
            </div>
            <table>
                <tbody id="rpkwhPieTable"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION 3: GROWTH --}}
<div class="card full-width">
    <div class="card-title">Realisasi Growth RPKWH Per Golongan (%)</div>
    <div class="chart-wrapper" style="height:300px;">
        <canvas id="chartGrowthRpkwh"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Growth RPKWH (%)</div>
    <div style="overflow-x:auto;" class="custom-scroll">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Sosial</th><th>Rumah Tangga</th><th>Bisnis</th>
                    <th>Industri</th><th>Publik</th><th>Multiguna</th>
                </tr>
            </thead>
            <tbody id="tabelGrowthRpkwh"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 4: BPP HJR --}}
<div class="card full-width">
    <div class="card-title">Realisasi BPP HJR RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper" style="height:300px;">
        <canvas id="chartBppHjr"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel BPP HJR RPKWH (Rp/kWh)</div>
    <div style="overflow-x:auto;" class="custom-scroll">
        <table>
            <thead id="theadBppHjr"></thead>
            <tbody id="tabelBppHjr"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 5: ANALISA 2024 vs 2025 --}}
<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Analisa RPKWH 2024 vs 2025 (Rp/kWh)</div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:2; height:320px;">
            <canvas id="chartAnalisa2024vs2025"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:320px;">
            <div style="display:flex; gap:6px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#555; margin-left:auto;">T'24</span>
                <span style="color:#2196f3;">R'24</span>
                <span style="color:#ff9800;">T'25</span>
                <span style="color:var(--accent-green);">R'25</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table>
                <tbody id="rpkwhAnalisaSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Analisa RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper">
        <canvas id="chartAnalisaDelta"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-title">% Realisasi RPKWH</div>
    <div class="chart-wrapper">
        <canvas id="chartPersenRpkwh"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Analisa RPKWH</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th><th>Target 2024</th><th>Realisasi 2024</th>
                <th>Target 2025</th><th>Realisasi 2025</th>
                <th>Delta</th><th>Delta %</th><th>% Pencapaian Target</th><th>% Pencapaian Real</th>
            </tr>
        </thead>
        <tbody id="tabelAnalisa"></tbody>
    </table>
</div>

<div class="card full-width">
    <div class="card-title">Tabel % Realisasi RPKWH</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th><th>Target</th><th>Realisasi</th><th>Delta</th>
            </tr>
        </thead>
        <tbody id="tabelPersen"></tbody>
    </table>
</div>