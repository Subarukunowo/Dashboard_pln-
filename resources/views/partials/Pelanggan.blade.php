<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Penambahan Pelanggan</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPelanggan" style="font-size:0.7rem; padding:4px;">
                {{-- diisi dinamis JS --}}
            </select>
            <select id="filterBulanPelanggan" style="font-size:0.7rem; padding:4px;">
                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $bln)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartPelangganBar"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#ff9800; margin-left:auto;">Rencana</span>
                <span style="color:var(--accent-green);">Realisasi</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table>
                <tbody id="pelangganSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Penambahan Pelanggan</div>
    <div class="chart-wrapper">
        <canvas id="chartPelangganDelta"></canvas>
    </div>
</div>

<div class="card double-column">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div id="judulPiePelanggan" class="card-title">Distribusi Golongan Pelanggan</div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:280px;">
        <div class="chart-wrapper" style="flex:2; height:280px;">
            <canvas id="chartPelangganPie"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:280px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Golongan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Jumlah</span>
                <span style="color:#aaa;">%</span>
            </div>
            <table>
                <tbody id="pelangganPieTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Target vs Realisasi Akumulasi per Golongan</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartPelangganGol"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">RKAP Pelanggan</div>
    <table>
        <thead>
            <tr>
                <th>Golongan</th>
                <th>Target TW I</th><th>Target TW II</th><th>Target TW III</th><th>Target TW IV</th><th>Target Total</th>
                <th>Real TW I</th><th>Real TW II</th><th>Real TW III</th><th>Real TW IV</th><th>Real Total</th>
                <th>% Pencapaian</th>
            </tr>
        </thead>
        <tbody id="tabelRkapPelanggan"></tbody>
    </table>
</div>