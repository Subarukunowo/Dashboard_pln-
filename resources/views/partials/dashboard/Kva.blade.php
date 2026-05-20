{{-- CARD 1: Rencana vs Realisasi Penambahan KVA --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('kva.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifKva" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterViewKva" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('kva.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('kva.filter.akumulasi') }}</option>
            </select>
            <select id="filterBulanKva" style="font-size:0.7rem; padding:4px;">
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartKvaBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('kva.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('kva.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="kvaSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('kva.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartKvaDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.selisih_kva') }}</th>
                </tr>
            </thead>
            <tbody id="tabelKvaDelta"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Distribusi Golongan KVA --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.distribusi_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartKvaPie"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.golongan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('kva.table.nilai') }}</th>
                    <th style="padding:10px; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="kvaPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('kva.gol_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartKvaGol"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.golongan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('kva.table.target_ini') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('kva.table.real_lalu') }}</th>
                </tr>
            </thead>
            <tbody id="tabelKvaGol"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Perbandingan KVA Thn Lalu vs Thn Ini --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.perbandingan_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartKvaPerbandingan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.bulan') }}</th>
                    <th style="padding:10px; color:#555;">{{ __('kva.table.t_lalu') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('kva.table.r_lalu') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('kva.table.t_ini') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('kva.table.r_ini') }}</th>
                    <th style="padding:10px; color:#aaa;">Δ</th>
                </tr>
            </thead>
            <tbody id="kvaPerbandinganSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('kva.delta_perbandingan_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartKvaDeltaPerbandingan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.selisih_kva') }}</th>
                </tr>
            </thead>
            <tbody id="tabelKvaDeltaPerbandingan"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: RKAP KVA --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.rkap_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.golongan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.target_tw1') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.target_tw2') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.target_tw3') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.target_tw4') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.target_total') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.real_tw1') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.real_tw2') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.real_tw3') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.real_tw4') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.real_total') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.pct_capai') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRkapKva"></tbody>
        </table>
    </div>
</div>

{{-- CARD 5: Tabel Perbandingan KVA --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.tabel_perbandingan_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('kva.table.bulan') }}</th>
                    <th style="padding:10px; color:#555;">{{ __('kva.table.target_lalu') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('kva.table.real_lalu') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('kva.table.target_ini') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('kva.table.realisasi_ini') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.selisih') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.selisih_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.pct_capai_target') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('kva.table.pct_capai_real') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPerbandinganKva"></tbody>
        </table>
    </div>
</div>