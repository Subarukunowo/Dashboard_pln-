<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
        <div class="card-title">Rencana vs Realisasi Penambahan Pelanggan</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPelanggan" style="font-size:0.7rem; padding:4px;">{{-- Diisi dinamis JS --}}</select>
            <select id="filterBulanPelanggan" style="font-size:0.7rem; padding:4px;">
                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $bln)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2;">
            <canvas id="chartPelangganBar"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-y:auto; max-height:300px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase; color:var(--text-muted);">
                        <th style="padding:10px; text-align:left;">Bulan</th>
                        <th style="padding:10px; text-align:right; color:var(--accent-orange);">Rencana</th>
                        <th style="padding:10px; text-align:right; color:var(--accent-green);">Real</th>
                        <th style="padding:10px; text-align:right;">Δ</th>
                    </tr>
                </thead>
                <tbody id="pelangganSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-6">
    <div class="card-title" style="margin-bottom:15px;">Delta Penambahan Pelanggan</div>
    <div class="chart-wrapper">
        <canvas id="chartPelangganDelta"></canvas>
    </div>
</div>

<div class="card col-6">
    <div class="card-title" style="margin-bottom:15px;" id="judulPiePelanggan">Distribusi Golongan Pelanggan</div>
    <div style="display:flex; gap:var(--gap); flex:1; min-height:280px;">
        <div class="chart-wrapper" style="flex:1.2;">
            <canvas id="chartPelangganPie"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; background:rgba(0,0,0,0.1); border-radius:6px; overflow-y:auto; max-height:280px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase; color:var(--text-muted);">
                        <th style="padding:8px; text-align:left;">Gol</th>
                        <th style="padding:8px; text-align:right;">Jml</th>
                        <th style="padding:8px; text-align:right;">%</th>
                    </tr>
                </thead>
                <tbody id="pelangganPieTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">Target vs Realisasi Akumulasi per Golongan</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartPelangganGol"></canvas>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">RKAP Pelanggan</div>
    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead>
                <tr style="background:#252535;">
                    <th rowspan="2" style="text-align:left; border-right:1px solid #444; padding:12px;">Golongan</th>
                    <th colspan="5" style="text-align:center; border-bottom:1px solid #444;">Target</th>
                    <th colspan="5" style="text-align:center; border-bottom:1px solid #444; border-left:1px solid #444;">Realisasi</th>
                    <th rowspan="2" style="text-align:center; border-left:1px solid #444; color:var(--accent-orange);">% Pencapaian</th>
                </tr>
                <tr style="background:#252535; font-size:0.65rem;">
                    <th>TW I</th><th>TW II</th><th>TW III</th><th>TW IV</th>
                    <th style="border-right:1px solid #444;">Total</th>
                    <th style="border-left:1px solid #444;">TW I</th><th>TW II</th><th>TW III</th><th>TW IV</th><th>Total</th>
                </tr>
            </thead>
            <tbody id="tabelRkapPelanggan"></tbody>
        </table>
    </div>
</div>