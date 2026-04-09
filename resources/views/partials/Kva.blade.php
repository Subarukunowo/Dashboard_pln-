<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Penambahan KVA</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifKva" style="font-size:0.7rem; padding:4px;">
                {{-- diisi dinamis JS --}}
            </select>
            <select id="filterViewKva" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">Bulanan</option>
                <option value="akumulasi">Akumulasi</option>
            </select>
            <select id="filterBulanKva" style="font-size:0.7rem; padding:4px;">
                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $bln)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartKvaBar"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#ff9800; margin-left:auto;">Rencana</span>
                <span style="color:var(--accent-green);">Realisasi</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table>
                <tbody id="kvaSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Penambahan KVA</div>
    <div class="chart-wrapper">
        <canvas id="chartKvaDelta"></canvas>
    </div>
</div>

<div class="card double-column">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Distribusi Golongan KVA</div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:280px;">
        <div class="chart-wrapper" style="flex:2; height:280px;">
            <canvas id="chartKvaPie"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:280px;">
            <div style="display:flex; gap:8px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Golongan</span>
                <span style="color:var(--accent-green); margin-left:auto;">Nilai</span>
                <span style="color:#aaa;">%</span>
            </div>
            <table>
                <tbody id="kvaPieTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">Target vs Realisasi Akumulasi per Golongan</div>
    <div class="chart-wrapper" style="height:280px;">
        <canvas id="chartKvaGol"></canvas>
    </div>
</div>

<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Perbandingan KVA 2024 vs 2025</div>
    </div>
    <div style="display:flex; gap:15px; flex:1; min-height:300px;">
        <div class="chart-wrapper" style="flex:2; height:300px;">
            <canvas id="chartKvaPerbandingan"></canvas>
        </div>
        <div class="custom-scroll" style="flex:1; display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:300px;">
            <div style="display:flex; gap:6px; padding:8px 10px; background:#252525; border-radius:6px; font-size:0.72rem; position:sticky; top:0; z-index:1;">
                <span style="color:#888;">Bulan</span>
                <span style="color:#555; margin-left:auto;">T'24</span>
                <span style="color:#2196f3;">R'24</span>
                <span style="color:#ff9800;">T'25</span>
                <span style="color:var(--accent-green);">R'25</span>
                <span style="color:#aaa;">Δ</span>
            </div>
            <table>
                <tbody id="kvaPerbandinganSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card double-column">
    <div class="card-title">Delta Perbandingan KVA</div>
    <div class="chart-wrapper">
        <canvas id="chartKvaDeltaPerbandingan"></canvas>
    </div>
</div>

<div class="card full-width">
    <div class="card-title">RKAP KVA</div>
    <table>
        <thead>
            <tr>
                <th>Golongan</th>
                <th>Target TW I</th><th>Target TW II</th><th>Target TW III</th><th>Target TW IV</th><th>Target Total</th>
                <th>Real TW I</th><th>Real TW II</th><th>Real TW III</th><th>Real TW IV</th><th>Real Total</th>
                <th>% Pencapaian</th>
            </tr>
        </thead>
        <tbody id="tabelRkapKva"></tbody>
    </table>
</div>

<div class="card full-width">
    <div class="card-title">Tabel Perbandingan KVA</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Target 2024</th><th>Realisasi 2024</th>
                <th>Target 2025</th><th>Realisasi 2025</th>
                <th>Delta</th><th>% Delta</th>
                <th>% Pencapaian Target</th><th>% Pencapaian Realisasi</th>
            </tr>
        </thead>
        <tbody id="tabelPerbandinganKva"></tbody>
    </table>
</div>