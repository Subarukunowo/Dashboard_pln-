<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('tunggakan.pendapatan_title') }}</div>
        <select id="filterBulanTunggakan" style="font-size:0.7rem; padding:4px;">
            <option value="all">{{ __('tunggakan.filter.semua_bulan') }}</option>
            @foreach(range(0, 11) as $i)
                <option value="{{ $i }}">
                    {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartTunggakanPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('tunggakan.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('tunggakan.table.pendapatan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ratio') }}</th>
                </tr>
            </thead>
            <tbody id="tunggakanSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">{{ __('tunggakan.ratio_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartTunggakanRatio"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('tunggakan.table.bulan') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('tunggakan.table.ratio_tunggakan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('tunggakan.table.target_ratio') }}</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakanRatio"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title">{{ __('tunggakan.detail_title') }}</div>

    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('tunggakan.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.pal_berjalan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.pal_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ts_berjalan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ts_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.jml_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.pendapatan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.ratio_tunggakan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('tunggakan.table.target_ratio') }}</th>
                </tr>
            </thead>
            <tbody id="tabelTunggakan"></tbody>
        </table>
    </div>
</div>