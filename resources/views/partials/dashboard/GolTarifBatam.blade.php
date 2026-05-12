{{-- SECTION 1: PIE CHART PER GOLONGAN --}}
<div class="card col-12">
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
                <option value="all">Semua Bulan</option>
                <option value="0">Januari</option><option value="1">Februari</option><option value="2">Maret</option>
                <option value="3">April</option><option value="4">Mei</option><option value="5">Juni</option>
                <option value="6">Juli</option><option value="7">Agustus</option><option value="8">September</option>
                <option value="9">Oktober</option><option value="10">November</option><option value="11">Desember</option>
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:350px; margin-bottom:20px;">
        <canvas id="chartPieTarif"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Golongan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Nilai</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="tabelPieTarif"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 2: REALISASI PENDAPATAN 2025 --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Realisasi vs Target Pendapatan 2025 (Juta Rp)</div>
        <select id="filterBulanRealisasiGol" style="font-size:0.7rem; padding:4px;">
            <option value="all">Semua Bulan</option>
            <option value="0">Januari</option><option value="1">Februari</option><option value="2">Maret</option>
            <option value="3">April</option><option value="4">Mei</option><option value="5">Juni</option>
            <option value="6">Juli</option><option value="7">Agustus</option><option value="8">September</option>
            <option value="9">Oktober</option><option value="10">November</option><option value="11">Desember</option>
        </select>
    </div>

    {{-- Chart Utama --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRealisasiBulanan"></canvas>
    </div>
    {{-- Tabel Utama --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">Realisasi</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Capai</th>
                </tr>
            </thead>
            <tbody id="tabelRealisasi"></tbody>
        </table>
    </div>

    {{-- Chart Delta --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Selisih Realisasi 2025 (Juta Rp)</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartDeltaRealisasi"></canvas>
    </div>
    {{-- Tabel Delta --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih (Juta Rp)</th>
                </tr>
            </thead>
            <tbody id="tabelDeltaRealisasi"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 3: TOTAL 2024 vs 2025 --}}
<div class="card col-12">
    <div class="card-title">Total Realisasi Pendapatan 2024 vs 2025 (Triliun Rp)</div>
    
    {{-- Chart Total Comparison --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartTotal2024vs2025"></canvas>
    </div>
    {{-- Tabel Total --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#555;">Target '24</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Real '24</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target '25</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">Real '25</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih</th>
                </tr>
            </thead>
            <tbody id="tabelTotal"></tbody>
        </table>
    </div>

    {{-- Chart Delta Total --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Selisih Total Realisasi (Triliun Rp)</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTotalDelta"></canvas>
    </div>
    {{-- Tabel Delta Total --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih (Triliun Rp)</th>
                </tr>
            </thead>
            <tbody id="tabelDeltaTotal"></tbody>
        </table>
    </div>

    {{-- Chart Pertumbuhan --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">% Pertumbuhan Pendapatan</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTotalPertumbuhan"></canvas>
    </div>
    {{-- Tabel Pertumbuhan --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">% Target</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">% Realisasi</th>
                </tr>
            </thead>
            <tbody id="tabelPertumbuhan"></tbody>
        </table>
    </div>
</div>