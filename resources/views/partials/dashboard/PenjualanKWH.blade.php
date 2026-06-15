{{-- CARD 1: Rencana vs Realisasi RPKWH --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('rpkwh.title') }}</div>
        <select id="filterTarifRpkwh" style="font-size:0.7rem; padding:4px;"></select>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRencanaRealisasi"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.rencana') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="rpkwhSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih_rpkwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhDelta"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Distribusi Golongan RPKWH --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPieRpkwh">{{ __('rpkwh.pie_title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTahunRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
            </select>
            <select id="filterViewRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="bulanan">{{ __('rpkwh.filter.bulanan') }}</option>
                <option value="akumulasi">{{ __('rpkwh.filter.akumulasi') }}</option>
            </select>
            <select id="filterBulanRpkwh" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('rpkwh.filter.semua') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPieRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.golongan') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.nilai') }}</th>
                    <th style="padding:10px; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="rpkwhPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.growth_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartGrowthRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.sosial') }}</th>
                    <th style="padding:10px; color:#00ff88;">{{ __('rpkwh.table.rtangga') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('rpkwh.table.bisnis') }}</th>
                    <th style="padding:10px; color:#e91e63;">{{ __('rpkwh.table.industri') }}</th>
                    <th style="padding:10px; color:#9c27b0;">{{ __('rpkwh.table.publik') }}</th>
                    <th style="padding:10px; color:#00bcd4;">{{ __('rpkwh.table.multiguna') }}</th>
                </tr>
            </thead>
            <tbody id="tabelGrowthRpkwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: BPP vs HJR --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.bpp_hjr_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBppHjr"></canvas>
    </div>

    <div class="custom-scroll" style="overflow-x:auto; overflow-y:auto; max-height:340px; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('rpkwh.table.hjr') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.bpp') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="tabelBppHjr"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Analisa RPKWH --}}
<div class="card col-12">
    <div class="card-title">
        {{ __('rpkwh.analisa_title') }}
        <span style="font-size:0.75rem; color:#555; font-weight:normal; margin-left:8px;">
            {{ date('Y') - 1 }} vs {{ date('Y') }}
        </span>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartAnalisa2024vs2025"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#555;">T'{{ date('Y') - 1 }}</th>
                    <th style="padding:10px; color:#2196f3;">R'{{ date('Y') - 1 }}</th>
                    <th style="padding:10px; color:#ff9800;">T'{{ date('Y') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">R'{{ date('Y') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="rpkwhAnalisaSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.delta_analisa_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartAnalisaDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih_rpkwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhAnalisaDelta"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.persen_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPersenRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('rpkwh.table.target') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('rpkwh.table.realisasi') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPersen"></tbody>
        </table>
    </div>
</div>

{{-- CARD 5: Tabel Analisa Detail RPKWH --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.tabel_analisa_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('rpkwh.table.bulan') }}</th>
                    <th style="padding:10px; color:#555;">T'{{ date('Y') - 1 }}</th>
                    <th style="padding:10px; color:#2196f3;">R'{{ date('Y') - 1 }}</th>
                    <th style="padding:10px; color:#ff9800;">T'{{ date('Y') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">R'{{ date('Y') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.selisih_pct') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.pct_capai_target') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('rpkwh.table.pct_capai_real') }}</th>
                </tr>
            </thead>
            <tbody id="tabelAnalisa"></tbody>
        </table>
    </div>
</div>