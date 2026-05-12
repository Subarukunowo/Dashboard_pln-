{{-- CARD 1: Rencana vs Realisasi Penambahan KVA --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">Rencana vs Realisasi Penambahan KVA</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifKva" style="font-size:0.7rem; padding:4px;"></select>
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

    {{-- Chart Bar --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartKvaBar"></canvas>
    </div>

    {{-- Tabel Bar --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Rencana</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Realisasi</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih</th>
                </tr>
            </thead>
            <tbody id="kvaSideTable"></tbody>
        </table>
    </div>

    {{-- Chart Delta --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Selisih Penambahan KVA</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartKvaDelta"></canvas>
    </div>

    {{-- Tabel Delta (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih (kVA)</th>
                </tr>
            </thead>
            <tbody id="tabelKvaDelta"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Distribusi Golongan KVA --}}
<div class="card col-12">
    <div class="card-title">Distribusi Golongan KVA</div>

    {{-- Chart Pie --}}
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartKvaPie"></canvas>
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
            <tbody id="kvaPieTable"></tbody>
        </table>
    </div>

    {{-- Chart Golongan Bar --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Target vs Realisasi Akumulasi per Golongan</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartKvaGol"></canvas>
    </div>

    {{-- Tabel Golongan (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Golongan</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target '25</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Real '24</th>
                </tr>
            </thead>
            <tbody id="tabelKvaGol"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Perbandingan KVA 2024 vs 2025 --}}
<div class="card col-12">
    <div class="card-title">Perbandingan KVA 2024 vs 2025</div>

    {{-- Chart Perbandingan --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartKvaPerbandingan"></canvas>
    </div>

    {{-- Tabel Perbandingan --}}
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
            <tbody id="kvaPerbandinganSideTable"></tbody>
        </table>
    </div>

    {{-- Chart Delta Perbandingan --}}
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Selisih Perbandingan KVA</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartKvaDeltaPerbandingan"></canvas>
    </div>

    {{-- Tabel Delta Perbandingan (NEW) --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih (kVA)</th>
                </tr>
            </thead>
            <tbody id="tabelKvaDeltaPerbandingan"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: RKAP KVA (Tabel Only) --}}
<div class="card col-12">
    <div class="card-title">RKAP KVA</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Golongan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Target TW I</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Target TW II</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Target TW III</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Target TW IV</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Target Total</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Real TW I</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Real TW II</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Real TW III</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Real TW IV</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Real Total</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Pencapaian</th>
                </tr>
            </thead>
            <tbody id="tabelRkapKva"></tbody>
        </table>
    </div>
</div>

{{-- CARD 5: Tabel Perbandingan KVA (Tabel Only) --}}
<div class="card col-12">
    <div class="card-title">Tabel Perbandingan KVA</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#555;">Target 2024</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Realisasi 2024</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">Target 2025</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Realisasi 2025</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Selisih</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Selisih</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Capai Target</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">% Capai Real</th>
                </tr>
            </thead>
            <tbody id="tabelPerbandinganKva"></tbody>
        </table>
    </div>
</div>