{{-- CARD 1: Pemeriksaan & Temuan --}}
<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('p2tl.title') }}</div>
        <select id="filterBulanP2tl" style="font-size:0.7rem; padding:4px;">
            <option value="all">{{ __('p2tl.filter.semua') }}</option>
            @foreach(range(0, 11) as $i)
                <option value="{{ $i }}">
                    {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlTemuan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('p2tl.table.bulan') }}</th>
                    <th style="padding:10px; color:#2196f3;">{{ __('p2tl.table.pemeriksaan') }}</th>
                    <th style="padding:10px; color:#f44336;">{{ __('p2tl.table.temuan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('p2tl.table.temuan_kwh') }}</th>
                </tr>
            </thead>
            <tbody id="p2tlSideTable"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Temuan kWh --}}
<div class="card col-12">
    <div class="card-title">{{ __('p2tl.kwh_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlKwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('p2tl.table.bulan') }}</th>
                    <th style="padding:10px; color:#ff9800;">{{ __('p2tl.table.temuan_kwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelP2tlKwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Tagihan Susulan --}}
<div class="card col-12">
    <div class="card-title">{{ __('p2tl.tagsus_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlTagsus"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('p2tl.table.bulan') }}</th>
                    <th style="padding:10px; color:#00ff88;">{{ __('p2tl.table.tagsus') }}</th>
                </tr>
            </thead>
            <tbody id="tabelP2tlTagsus"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Tabel P2TL --}}
<div class="card col-12">
    <div class="card-title">{{ __('p2tl.detail_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem; text-transform:uppercase;">
                    <th style="padding:10px; color:#888;">{{ __('p2tl.table.no') }}</th>
                    <th style="padding:10px; color:#888;">{{ __('p2tl.table.bulan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('p2tl.table.jlh_pemeriksaan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('p2tl.table.jlh_temuan') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('p2tl.table.temuan_kwh') }}</th>
                    <th style="padding:10px; color:#aaa;">{{ __('p2tl.table.tagsus') }}</th>
                </tr>
            </thead>
            <tbody id="tabelP2tl"></tbody>
        </table>
    </div>
</div>