<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">P2TL — Pemeriksaan & Temuan Pelanggan</div>
        <select id="filterBulanP2tl" style="font-size:0.7rem; padding:4px;">
            <option value="all">Semua Bulan</option>
            <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
            <option value="3">Apr</option><option value="4">Mei</option><option value="5">Jun</option>
            <option value="6">Jul</option><option value="7">Agu</option><option value="8">Sep</option>
            <option value="9">Okt</option><option value="10">Nov</option><option value="11">Des</option>
        </select>
    </div>

    {{-- Chart Temuan --}}
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlTemuan"></canvas>
    </div>

    {{-- Tabel Temuan --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Periksa</th>
                    <th style="padding:10px; text-align:center; color:#f44336;">Temuan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">kWh</th>
                </tr>
            </thead>
            <tbody id="p2tlSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Temuan kWh</div>
    
    {{-- Chart Temuan kWh --}}
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlKwh"></canvas>
    </div>

    {{-- Tabel Temuan kWh (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Temuan kWh</th>
                </tr>
            </thead>
            <tbody id="tabelP2tlKwh"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tagihan Susulan (Rp)</div>
    
    {{-- Chart Tagsus --}}
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlTagsus"></canvas>
    </div>

    {{-- Tabel Tagsus (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">Tagsus (Rp)</th>
                </tr>
            </thead>
            <tbody id="tabelP2tlTagsus"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">Tabel P2TL</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap; font-size:0.75rem;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">No</th>
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Jlh Pemeriksaan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Jlh Temuan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Temuan kWh</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Tagsus (Rp)</th>
                </tr>
            </thead>
            <tbody id="tabelP2tl"></tbody>
        </table>
    </div>
</div>