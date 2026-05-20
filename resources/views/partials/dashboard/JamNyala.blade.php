<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('jamnyala.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifJamNyala" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanJamNyala" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('jamnyala.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartJamNyalaLine"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('jamnyala.table.bulan') }}</th>
                    <th id="headerJamNyalaLine" style="padding:10px; text-align:center; color:var(--accent-green);">{{ __('jamnyala.table.jam_nyala') }}</th>
                </tr>
            </thead>
            <tbody id="tabelJamNyalaLine"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('jamnyala.bar_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartJamNyalaBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">{{ __('jamnyala.table.bulan') }}</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">{{ __('jamnyala.table.jam_nyala') }}</th>
                </tr>
            </thead>
            <tbody id="jamNyalaSideTable"></tbody>
        </table>
    </div>
</div>