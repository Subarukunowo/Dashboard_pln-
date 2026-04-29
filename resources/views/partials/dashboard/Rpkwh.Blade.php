{{-- SECTION 1: RENCANA VS REALISASI --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi RPKWH</div>
        <select id="filterTarifRpkwh" style="font-size:0.7rem; padding:4px;"></select>
    </div>

    {{-- Chart Rencana vs Realisasi --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRencanaRealisasi"></canvas>
    </div>

    {{-- Tabel Rencana vs Realisasi --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Rencana</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Realisasi</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Δ</th>
                </tr>
            </thead>
            <tbody id="rpkwhSideTable"></tbody>
        </table>
    </div>

    {{-- Chart Delta --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Delta RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaRpkwh"></canvas>
    </div>

    {{-- Tabel Delta (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta (Rp/kWh)</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhDelta"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 2: PIE GOLONGAN --}}
<div class="card col-12">
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

    {{-- Chart Pie --}}
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPieRpkwh"></canvas>
    </div>

    {{-- Tabel Pie --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Golongan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Nilai</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="rpkwhPieTable"></tbody>
        </table>
    </div>

    {{-- Chart Growth --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Realisasi Growth RPKWH Per Golongan (%)</div>
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartGrowthRpkwh"></canvas>
    </div>

    {{-- Tabel Growth --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Sosial</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">R. Tangga</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Bisnis</th>
                    <th style="padding:10px; text-align:center; color:#e91e63;">Industri</th>
                    <th style="padding:10px; text-align:center; color:#9c27b0;">Publik</th>
                    <th style="padding:10px; text-align:center; color:#00bcd4;">Multiguna</th>
                </tr>
            </thead>
            <tbody id="tabelGrowthRpkwh"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 3: BPP vs HJR --}}
<div class="card col-12">
    <div class="card-title">Realisasi BPP HJR RPKWH (Rp/kWh)</div>
    
    {{-- Chart BPP vs HJR --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBppHjr"></canvas>
    </div>

    {{-- Tabel BPP vs HJR --}}
    <div class="custom-scroll" style="overflow-x:auto; overflow-y:auto; max-height:340px; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#222; text-align:center; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; border-bottom:1px solid #444; text-align:center; color:#888;">BULAN</th>
                    <th style="padding:10px; border-bottom:1px solid #444; text-align:center; color:#36a2eb;">HJR (Rp/kWh)</th>
                    <th style="padding:10px; border-bottom:1px solid #444; text-align:center; color:#ff9f40;">BPP (Rp/kWh)</th>
                    <th style="padding:10px; border-bottom:1px solid #444; text-align:center; color:#aaa;">SELISIH</th>
                </tr>
            </thead>
            <tbody id="tabelBppHjr"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 4: ANALISA --}}
<div class="card col-12">
    <div class="card-title">Analisa RPKWH 2024 vs 2025 (Rp/kWh)</div>

    {{-- Chart Analisa --}}
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartAnalisa2024vs2025"></canvas>
    </div>

    {{-- Tabel Analisa --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#555;">T'24</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">R'24</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">T'25</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">R'25</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Δ</th>
                </tr>
            </thead>
            <tbody id="rpkwhAnalisaSideTable"></tbody>
        </table>
    </div>

    {{-- Chart Delta Analisa --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Delta Analisa RPKWH (Rp/kWh)</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartAnalisaDelta"></canvas>
    </div>

    {{-- Tabel Delta Analisa (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta (Rp/kWh)</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhAnalisaDelta"></tbody>
        </table>
    </div>

    {{-- Chart % Realisasi --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">% Realisasi RPKWH</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPersenRpkwh"></canvas>
    </div>

    {{-- Tabel % Realisasi --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Realisasi</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta</th>
                </tr>
            </thead>
            <tbody id="tabelPersen"></tbody>
        </table>
    </div>
</div>

{{-- Tabel Analisa Detail --}}
<div class="card col-12">
    <div class="card-title">Tabel Analisa RPKWH</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#555;">Target 2024</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Realisasi 2024</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target 2025</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Realisasi 2025</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta %</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Capai Target</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Capai Real</th>
                </tr>
            </thead>
            <tbody id="tabelAnalisa"></tbody>
        </table>
    </div>
</div>