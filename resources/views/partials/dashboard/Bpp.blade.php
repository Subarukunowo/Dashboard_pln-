<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">{{ __('bpp.title') }}</div>
        <div style="display:flex; gap:5px;">
            <select id="filterBpp" aria-label="{{ __('bpp.filter.kategori') }}">
                <option value="BPP PEMBANGKIT">{{ __('bpp.kategori.pembangkit') }}</option>
                <option value="BPP S.D TRANSMISI">{{ __('bpp.kategori.transmisi') }}</option>
                <option value="BPP S.D DISTRIBUSI TM">{{ __('bpp.kategori.distribusi_tm') }}</option>
                <option value="BPP S.D DISTRIBUSI TR">{{ __('bpp.kategori.distribusi_tr') }}</option>
            </select>
            <select id="filterBulanBpp" aria-label="{{ __('bpp.filter.bulan') }}">
                <option value="all">{{ __('bpp.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; min-height:300px; margin-bottom:20px;">
        <canvas id="bppComparisonChart"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px;">{{ __('bpp.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('bpp.table.konsolidasi') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('bpp.table.internal') }}</th>
                    <th style="padding:10px;">{{ __('bpp.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="bppTableBody"></tbody>
        </table>
    </div>
</div>