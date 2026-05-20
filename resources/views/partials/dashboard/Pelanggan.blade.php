{{-- CARD 1: Rencana vs Realisasi Penambahan Pelanggan --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('pelanggan.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPelanggan" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanPelanggan" style="font-size:0.7rem; padding:4px;">
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartPelangganBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('pelanggan.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('pelanggan.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('pelanggan.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="pelangganSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('pelanggan.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('pelanggan.table.delta') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganDelta"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Distribusi Golongan Pelanggan --}}
<div class="card col-12">
    <div class="card-title">{{ __('pelanggan.distribusi_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganPie"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.golongan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('pelanggan.table.jumlah') }}</th>
                    <th style="padding:10px; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="pelangganPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('pelanggan.gol_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganGol"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('pelanggan.table.golongan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('pelanggan.table.target_ini') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('pelanggan.table.real_lalu') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganGol"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: RKAP Pelanggan --}}
<div class="card col-12">
    <div class="card-title">{{ __('pelanggan.rkap_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:12px; color:#888; border-right:1px solid #444;">{{ __('pelanggan.table.golongan') }}</th>
                    <th colspan="5" style="padding:12px; color:#aaa; text-align:center; border-bottom:1px solid #444;">{{ __('pelanggan.table.target') }}</th>
                    <th colspan="5" style="padding:12px; color:#aaa; text-align:center; border-bottom:1px solid #444; border-left:1px solid #444;">{{ __('pelanggan.table.realisasi') }}</th>
                    <th style="padding:12px; color:var(--accent-orange); border-left:1px solid #444;">{{ __('pelanggan.table.pct_capai') }}</th>
                </tr>
                <tr style="font-size:0.65rem;">
                    <th style="padding:8px; border-right:1px solid #444;">&nbsp;</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw1') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw2') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw3') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw4') }}</th>
                    <th style="padding:8px; color:#aaa; border-right:1px solid #444;">{{ __('pelanggan.table.total') }}</th>
                    <th style="padding:8px; color:#aaa; border-left:1px solid #444;">{{ __('pelanggan.table.tw1') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw2') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw3') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.tw4') }}</th>
                    <th style="padding:8px; color:#aaa;">{{ __('pelanggan.table.total') }}</th>
                    <th style="padding:8px;">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="tabelRkapPelanggan"></tbody>
        </table>
    </div>
</div>