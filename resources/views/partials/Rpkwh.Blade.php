{{-- SECTION 1: RENCANA VS REALISASI --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi RPKWH</div>
        <select id="filterTarifRpkwh" style="font-size:0.7rem; padding:4px;">{{-- diisi dinamis JS --}}</select>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartRencanaRealisasi"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#ff9800; margin-left:auto;">Rencana</span>
                <span style="color:var(--accent-green);">Realisasi</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="rpkwhSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Delta RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartDeltaRpkwh"></canvas>
    </div>
</div>

{{-- SECTION 2: PIE GOLONGAN --}}
<div class="card col-6">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPieRpkwh">RPKWH Per Golongan</div>
        <div style="display:flex; gap:5px; flex-wrap:wrap; justify-content:flex-end;">
            <select id="filterTahunRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="2024">2024</option>
                <option value="2025" selected>2025</option>
            </select>
            <select id="filterViewRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
            <select id="filterBulanRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
                <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
                <option value="9">Okt</option><option value="10">Nov</option><option value="11" selected>Des</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:1; height:320px;">
            <canvas id="chartPieRpkwh"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:320px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Gol</span>
                <span style="color:var(--accent-green); margin-left:auto;">Nilai</span>
                <span style="color:#aaa;">%</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="rpkwhPieTable"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION 3: GROWTH --}}
<div class="card col-6">
    <div class="card-title">Realisasi Growth RPKWH Per Golongan (%)</div>
    <div class="chart-wrapper" style="height:320px;">
        <canvas id="chartGrowthRpkwh"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Growth RPKWH (%)</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Sosial</th><th>R. Tangga</th><th>Bisnis</th>
                    <th>Industri</th><th>Publik</th><th>Multiguna</th>
                </tr>
            </thead>
            <tbody id="tabelGrowthRpkwh"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 4: BPP HJR --}}
<div class="card col-8">
    <div class="card-title">Realisasi BPP HJR RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper" style="height:300px;">
        <canvas id="chartBppHjr"></canvas>
    </div>
</div>

{{-- SECTION 4: BPP vs HJR --}}
<div class="card col-12">
    <div class="card-title">Tabel Detail HJR vs BPP (Tahunan)</div>
    <div class="custom-scroll" style="overflow-x:auto; overflow-y:auto; max-height:300px; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:10px; border-bottom:1px solid #444;">BULAN</th>
                    <th style="padding:10px; border-bottom:1px solid #444; color:#36a2eb;">HJR (Rp/kWh)</th>
                    <th style="padding:10px; border-bottom:1px solid #444; color:#ff9f40;">BPP (Rp/kWh)</th>
                    <th style="padding:10px; border-bottom:1px solid #444;">SELISIH</th>
                </tr>
            </thead>
            <tbody id="tabelBppHjr"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 5: ANALISA --}}
<div class="card col-12">
    <div class="card-title">Analisa RPKWH 2024 vs 2025 (Rp/kWh)</div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:2; height:320px;">
            <canvas id="chartAnalisa2024vs2025"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:320px;">
            <div style="display:flex; gap:6px; padding:8px 10px; background:#252525; border-radius:6px 6px 0 0; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#555; margin-left:auto;">T'24</span>
                <span style="color:#2196f3;">R'24</span>
                <span style="color:#ff9800;">T'25</span>
                <span style="color:var(--accent-green);">R'25</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <tbody id="rpkwhAnalisaSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">Delta Analisa RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartAnalisaDelta"></canvas>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">% Realisasi RPKWH</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartPersenRpkwh"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Analisa RPKWH</div>
    <div class="custom-scroll" style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th><th>Target 2024</th><th>Realisasi 2024</th>
                    <th>Target 2025</th><th>Realisasi 2025</th>
                    <th>Delta</th><th>Delta %</th>
                    <th>% Pencapaian Target</th><th>% Pencapaian Real</th>
                </tr>
            </thead>
            <tbody id="tabelAnalisa"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
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