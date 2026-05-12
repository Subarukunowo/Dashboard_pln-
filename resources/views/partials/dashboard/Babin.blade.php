<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Kinerja Penjualan BABIN & WRKR</div>
        <div style="display:flex; gap:5px;">
            <select id="filterSectionBabin" style="font-size:0.7rem; padding:4px;">
                <option value="babin">Interkoneksi Batam-Bintan</option>
                <option value="sem">Sembulang, Blk Pdg, Buluh & Nguan</option>
            </select>
            <select id="filterTahunBabin" style="font-size:0.7rem; padding:4px;">
                <option value="2025" selected>2025</option>
                <option value="2024">2024</option>
            </select>
            <select id="filterViewBabin" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
            {{-- Opsi bulan di-inject oleh BabinModule.initFilters() --}}
            <select id="filterBulanBabin" style="font-size:0.7rem; padding:4px;"></select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBabinBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">Bulan</th>
                    <th style="padding:10px; color:#2196f3;">Real '24</th>
                    <th style="padding:10px; color:#ff9800;">Target '25</th>
                    <th style="padding:10px; color:var(--accent-green);">Real '25</th>
                    <th style="padding:10px; color:#aaa;">Selisih</th>
                </tr>
            </thead>
            <tbody id="babinSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">Delta BABIN</div>
    <div class="chart-wrapper"><canvas id="chartBabinDelta"></canvas></div>
</div>

<div class="card col-6">
    <div class="card-title">Distribusi KWH Jual (Bulan Terakhir)</div>
    <div class="chart-wrapper"><canvas id="chartBabinPie"></canvas></div>
</div>

<div class="card col-12">
    <div class="card-title">KWH Penjualan PLN Batam Gabungan</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartAllJualBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">Bulan</th>
                    <th style="padding:10px; color:#2196f3;">KWH Batam</th>
                    <th style="padding:10px; color:#ff9800;">Batam-Bintan</th>
                </tr>
            </thead>
            <tbody id="allJualSideTable"></tbody>
        </table>
    </div>
</div>