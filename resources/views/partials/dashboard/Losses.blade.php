{{-- CARD 1: Produksi --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('losses.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterEminLosses" style="font-size:0.7rem; padding:4px;">
                <option value="with">{{ __('losses.filter.dengan_emin') }}</option>
                <option value="without">{{ __('losses.filter.tanpa_emin') }}</option>
            </select>
            <select id="filterBulanLosses" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('losses.filter.semua') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartLossesProduksi"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('losses.table.prod_bruto') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('losses.table.prod_netto') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('losses.table.siap_salur') }}</th>
                </tr>
            </thead>
            <tbody id="lossesSideTable"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Losses % --}}
<div class="card col-12">
    <div class="card-title">{{ __('losses.pct_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartLossesPct"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('losses.table.losses_trans_bln') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('losses.table.losses_trans_akum') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('losses.table.losses_jar_bln') }}</th>
                    <th style="padding:10px; color:#9c27b0;">{{ __('losses.table.losses_jar_akum') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLossesPct"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Losses kWh --}}
<div class="card col-12">
    <div class="card-title">{{ __('losses.kwh_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartLossesKwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#e91e63;">{{ __('losses.table.losses_trans') }}</th>
                    <th style="padding:10px; color:#9c27b0;">{{ __('losses.table.losses_dist') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('losses.table.losses_jar') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLossesKwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Tabel Rincian Losses --}}
<div class="card col-12">
    <div class="card-title">{{ __('losses.detail_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('losses.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.prod_bruto') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.prod_netto') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.siap_salur') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_trans') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_bln') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_akum') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.interkoneksi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_dist') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_dist_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_dist_akum') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_jar') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_jar_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('losses.table.losses_jar_akum') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('losses.table.target') }}</th>
                </tr>
            </thead>
            <tbody id="tabelLosses"></tbody>
        </table>
    </div>
</div>