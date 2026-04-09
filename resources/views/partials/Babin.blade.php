{{-- BABIN --}}
<div class="card full-width">
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
            <select id="filterBulanBabin" style="font-size:0.7rem; padding:4px;">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
                <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
                <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartBabinBar"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:6px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#2196f3; margin-left:auto;">R'24</span>
                <span style="color:#ff9800;">T'25</span>
                <span style="color:var(--accent-green);">R'25</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table><tbody id="babinSideTable"></tbody></table>
        </div>
    </div>
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
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartAllJualBar"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#2196f3; margin-left:auto;">KWH Batam</span>
                <span style="color:#ff9800;">Batam-Bintan</span>
            </div>
            <table><tbody id="allJualSideTable"></tbody></table>
        </div>
    </div>
</div>