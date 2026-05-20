<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('babin.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterSectionBabin" style="font-size:0.7rem; padding:4px;">
                <option value="babin">{{ __('babin.section.babin') }}</option>
                <option value="sem">{{ __('babin.section.sem') }}</option>
            </select>
            <select id="filterTahunBabin" style="font-size:0.7rem; padding:4px;">
                <option value="2025" selected>{{ __('babin.filter.tahun_ini') }}</option>
                <option value="2024">{{ __('babin.filter.tahun_lalu') }}</option>
            </select>
            <select id="filterViewBabin" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('babin.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('babin.filter.akumulasi') }}</option>
            </select>
            <select id="filterBulanBabin" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('babin.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBabinBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('babin.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('babin.table.real_thn_lalu') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('babin.table.target_thn_ini') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('babin.table.real_thn_ini') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('babin.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="babinSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-6">
    <div class="card-title">{{ __('babin.delta_title') }}</div>
    <div class="chart-wrapper"><canvas id="chartBabinDelta"></canvas></div>
</div>

<div class="card col-6">
    <div class="card-title">{{ __('babin.pie_title') }}</div>
    <div class="chart-wrapper"><canvas id="chartBabinPie"></canvas></div>
</div>

<div class="card col-12">
    <div class="card-title">{{ __('babin.all_jual_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartAllJualBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('babin.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('babin.table.kwh_batam') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('babin.table.batam_bintan') }}</th>
                </tr>
            </thead>
            <tbody id="allJualSideTable"></tbody>
        </table>
    </div>
</div>