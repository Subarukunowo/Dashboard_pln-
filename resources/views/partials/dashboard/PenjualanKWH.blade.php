{{-- CARD 1: Target vs Realisasi KWH --}}
    <div class="card col-12">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div class="card-title">{{ __('PenjualanKWH.title') }}</div>
            <div style="display:flex; gap:5px;">
                <select id="filterBulanKwh" style="font-size:0.7rem; padding:4px;">
                    <option value="all">{{ __('PenjualanKWH.filter.semua') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <select id="filterViewKwh" style="font-size:0.7rem; padding:4px;">
                    <option value="bulanan">{{ __('PenjualanKWH.filter.bulanan') }}</option>
                    <option value="akumulasi">{{ __('PenjualanKWH.filter.akumulasi') }}</option>
                </select>
            </div>
        </div>

        <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
            <canvas id="chartTargetRealisasi"></canvas>
        </div>

        <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.72rem; text-transform:uppercase;">
                        <th style="padding:10px; color:#888;">{{ __('PenjualanKWH.table.bulan') }}</th>
                        <th style="padding:10px; color:#555;">{{ __('PenjualanKWH.table.t_lalu') }}</th>
                        <th style="padding:10px; color:#2196f3;">{{ __('PenjualanKWH.table.r_lalu') }}</th>
                        <th style="padding:10px; color:#ff9800;">{{ __('PenjualanKWH.table.t_ini') }}</th>
                        <th style="padding:10px; color:var(--accent-green);">{{ __('PenjualanKWH.table.r_ini') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.selisih') }}</th>
                    </tr>
                </thead>
                <tbody id="kwhTableBody"></tbody>
            </table>
        </div>

        <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('PenjualanKWH.delta_title') }}</div>
        <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
            <canvas id="chartDelta"></canvas>
        </div>

        <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.72rem; text-transform:uppercase;">
                        <th style="padding:10px; color:#888;">{{ __('PenjualanKWH.table.bulan') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.selisih_gwh') }}</th>
                    </tr>
                </thead>
                <tbody id="tabelKwhDelta"></tbody>
            </table>
        </div>

        <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('PenjualanKWH.pencapaian_title') }}</div>
        <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
            <canvas id="chartPencapaian"></canvas>
        </div>

        <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.72rem; text-transform:uppercase;">
                        <th style="padding:10px; color:#888;">{{ __('PenjualanKWH.table.bulan') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.pct_pencapaian') }}</th>
                    </tr>
                </thead>
                <tbody id="tabelKwhPencapaian"></tbody>
            </table>
        </div>

        <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('PenjualanKWH.pertumbuhan_title') }}</div>
        <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
            <canvas id="chartPertumbuhan"></canvas>
        </div>

        <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.72rem; text-transform:uppercase;">
                        <th style="padding:10px; color:#888;">{{ __('PenjualanKWH.table.bulan') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.pct_pertumbuhan') }}</th>
                    </tr>
                </thead>
                <tbody id="tabelKwhPertumbuhan"></tbody>
            </table>
        </div>
    </div>

    {{-- CARD 2: Tabel Ringkasan KWH --}}
    <div class="card col-12">
        <div class="card-title">{{ __('PenjualanKWH.ringkasan_title') }}</div>
        <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
            <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.72rem; text-transform:uppercase;">
                        <th style="padding:10px; color:#888;">{{ __('PenjualanKWH.table.bulan') }}</th>
                        <th style="padding:10px; color:#555;">{{ __('PenjualanKWH.table.target_lalu') }}</th>
                        <th style="padding:10px; color:#2196f3;">{{ __('PenjualanKWH.table.real_lalu') }}</th>
                        <th style="padding:10px; color:#ff9800;">{{ __('PenjualanKWH.table.target_ini') }}</th>
                        <th style="padding:10px; color:var(--accent-green);">{{ __('PenjualanKWH.table.realisasi_ini') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.delta_gwh') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.selisih_pct') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.pct_pencapaian') }}</th>
                        <th style="padding:10px; color:#aaa;">{{ __('PenjualanKWH.table.pct_pertumbuhan') }}</th>
                    </tr>
                </thead>
                <tbody id="tabelRingkasan"></tbody>
            </table>
        </div>
    </div>