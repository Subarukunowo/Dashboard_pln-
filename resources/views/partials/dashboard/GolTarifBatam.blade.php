{{-- SECTION 1: PIE CHART PER GOLONGAN --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPie">{{ __('goltarif.pie_title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTahunPie" style="font-size:0.7rem; padding:4px;">
                <option value="2024">{{ __('goltarif.filter.tahun_lalu') }}</option>
                <option value="2025" selected>{{ __('goltarif.filter.tahun_ini') }}</option>
            </select>
            <select id="filterViewPie" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('goltarif.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('goltarif.filter.akumulasi') }}</option>
            </select>
            <select id="filterBulanPie" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('goltarif.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:350px; margin-bottom:20px;">
        <canvas id="chartPieTarif"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('goltarif.table.golongan') }}</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">{{ __('goltarif.table.nilai') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="tabelPieTarif"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 2: REALISASI PENDAPATAN THN INI --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('goltarif.realisasi_title') }}</div>
        <select id="filterBulanRealisasiGol" style="font-size:0.7rem; padding:4px;">
            <option value="all">{{ __('goltarif.filter.semua_bulan') }}</option>
            @foreach(range(0, 11) as $i)
                <option value="{{ $i }}">
                    {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRealisasiBulanan"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('goltarif.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">{{ __('goltarif.table.target') }}</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">{{ __('goltarif.table.realisasi') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">{{ __('goltarif.table.selisih') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">{{ __('goltarif.table.pct_capai') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRealisasi"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('goltarif.delta_realisasi_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartDeltaRealisasi"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('goltarif.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">{{ __('goltarif.table.selisih_juta') }}</th>
                </tr>
            </thead>
            <tbody id="tabelDeltaRealisasi"></tbody>
        </table>
    </div>
</div>

{{-- SECTION 3: TOTAL THN LALU vs THN INI --}}
<div class="card col-12">
    <div class="card-title">{{ __('goltarif.total_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartTotal2024vs2025"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('goltarif.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:#555;">{{ __('goltarif.table.target_lalu') }}</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">{{ __('goltarif.table.real_lalu') }}</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">{{ __('goltarif.table.target_ini') }}</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">{{ __('goltarif.table.real_ini') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">{{ __('goltarif.table.selisih') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">{{ __('goltarif.table.selisih_pct') }}</th>
                </tr>
            </thead>
            <tbody id="tabelTotal"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('goltarif.delta_total_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTotalDelta"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('goltarif.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">{{ __('goltarif.table.selisih_triliun') }}</th>
                </tr>
            </thead>
            <tbody id="tabelDeltaTotal"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('goltarif.pertumbuhan_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTotalPertumbuhan"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('goltarif.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:#ff9800;">{{ __('goltarif.table.pct_target') }}</th>
                    <th style="padding:10px; text-align:center; color:#00ff88;">{{ __('goltarif.table.pct_realisasi') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPertumbuhan"></tbody>
        </table>
    </div>
</div>