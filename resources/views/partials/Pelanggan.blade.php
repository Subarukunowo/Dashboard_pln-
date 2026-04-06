<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Penambahan Pelanggan</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPelanggan" style="font-size:0.7rem; padding:4px;">
                {{-- diisi dinamis JS --}}
            </select>
            <select id="filterBulanPelanggan" style="font-size:0.7rem; padding:4px;">
                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="chart-wrapper" style="height:300px;">
        <canvas id="chartPelangganBar"></canvas>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Penambahan Pelanggan</div>
    <div class="chart-wrapper">
        <canvas id="chartPelangganDelta"></canvas>
    </div>
</div>

<div class="card double-column">
    <div id="judulPiePelanggan" class="card-title">Distribusi Golongan Pelanggan</div>
    <div class="chart-wrapper">
        <canvas id="chartPelangganPie"></canvas>
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