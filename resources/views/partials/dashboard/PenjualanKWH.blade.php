<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Penjualan KWH — Target vs Realisasi 2024 & 2025</div>
        <div style="display:flex; gap:5px;">
            <select id="filterBulanKwh" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Januari</option><option value="1">Februari</option><option value="2">Maret</option>
                <option value="3">April</option><option value="4">Mei</option><option value="5">Juni</option>
                <option value="6">Juli</option><option value="7">Agustus</option><option value="8">September</option>
                <option value="9">Oktober</option><option value="10">November</option><option value="11">Desember</option>
            </select>
            <select id="filterViewKwh" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
        </div>
    </div>

    {{-- Chart Target vs Realisasi --}}
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartTargetRealisasi"></canvas>
    </div>

    {{-- Tabel Target vs Realisasi --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#555;">T'24</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">R'24</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">T'25</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">R'25</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih</th>
                </tr>
            </thead>
            <tbody id="kwhTableBody"></tbody>
        </table>
    </div>

    {{-- Chart Delta --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Selisih Target (GWh)</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDelta"></canvas>
    </div>

    {{-- Tabel Delta (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih (GWh)</th>
                </tr>
            </thead>
            <tbody id="tabelKwhDelta"></tbody>
        </table>
    </div>

    {{-- Chart Pencapaian --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">% Pencapaian Terhadap Target</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPencapaian"></canvas>
    </div>

    {{-- Tabel Pencapaian (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Pencapaian</th>
                </tr>
            </thead>
            <tbody id="tabelKwhPencapaian"></tbody>
        </table>
    </div>

    {{-- Chart Pertumbuhan --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">% Pertumbuhan 2024 vs 2025</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPertumbuhan"></canvas>
    </div>

    {{-- Tabel Pertumbuhan (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Pertumbuhan</th>
                </tr>
            </thead>
            <tbody id="tabelKwhPertumbuhan"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel Ringkasan (GWh)</div>
    <div class="custom-scroll" style="overflow-x:auto; background:rgba(0,0,0,0.1); border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; font-size:0.75rem; min-width:1000px;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="color:var(--text-muted); text-transform:uppercase;">
                    <th style="padding:12px 8px; text-align:center;">Bulan</th>
                    <th style="padding:12px 8px; text-align:center;">Target 2024</th>
                    <th style="padding:12px 8px; text-align:center;">Realisasi 2024</th>
                    <th style="padding:12px 8px; text-align:center;">Target 2025</th>
                    <th style="padding:12px 8px; text-align:center;">Realisasi 2025</th>
                    <th style="padding:12px 8px; text-align:center;">Delta GWh</th>
                    <th style="padding:12px 8px; text-align:center;">% Selisih</th>
                    <th style="padding:12px 8px; text-align:center;">% Pencapaian</th>
                    <th style="padding:12px 8px; text-align:center;">% Pertumbuhan</th>
                </tr>
            </thead>
            <tbody id="tabelRingkasan"></tbody>
        </table>
    </div>
</div>