<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">{{ __('PendapatanBatam.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPendapatan" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('PendapatanBatam.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select id="filterViewPendapatan" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('PendapatanBatam.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('PendapatanBatam.filter.akumulasi') }}</option>
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPendapatanBatam"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('PendapatanBatam.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('PendapatanBatam.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('PendapatanBatam.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="pendapatanSideTable"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">{{ __('PendapatanBatam.delta_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaPendapatan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('PendapatanBatam.table.selisih_rp') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatanDelta"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">{{ __('PendapatanBatam.detail_title') }}</div>

    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('PendapatanBatam.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('PendapatanBatam.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('PendapatanBatam.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('PendapatanBatam.table.delta') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">%</th>
                </tr>
            </thead>
            <tbody id="tabelPendapatan"></tbody>
        </table>
    </div>
</div>