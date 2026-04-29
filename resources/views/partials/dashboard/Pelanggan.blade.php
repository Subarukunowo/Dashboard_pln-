<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
        <div class="card-title">Rencana vs Realisasi Penambahan Pelanggan</div>
        <div style="display:flex; gap:5px;">
            <select id="filterTarifPelanggan" style="font-size:0.7rem; padding:4px;"></select>
            <select id="filterBulanPelanggan" style="font-size:0.7rem; padding:4px;">
                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $bln)
                    <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Chart Bar — Rencana vs Realisasi --}}
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartPelangganBar"></canvas>
    </div>

   
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">Rencana</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Real</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Δ</th>
                </tr>
            </thead>
            <tbody id="pelangganSideTable"></tbody>
        </table>
    </div>

 
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Delta Penambahan Pelanggan</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganDelta"></canvas>
    </div>

   
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">Delta</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganDelta"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;" id="judulPiePelanggan">Distribusi Golongan Pelanggan</div>


    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganPie"></canvas>
    </div>

    
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.1); border-radius:6px; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Gol</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Jml</th>
                    <th style="padding:10px; text-align:center; color:#aaa;">%</th>
                </tr>
            </thead>
            <tbody id="pelangganPieTable"></tbody>
        </table>
    </div>

  
    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">Target vs Realisasi Akumulasi per Golongan</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPelangganGol"></canvas>
    </div>

  
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="padding:10px; text-align:center; color:#888;">Golongan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-orange);">Target '25</th>
                    <th style="padding:10px; text-align:center; color:#2196f3;">Real '24</th>
                </tr>
            </thead>
            <tbody id="tabelPelangganGol"></tbody>
        </table>
    </div>
</div>

<div class="card col-12">
    <div class="card-title" style="margin-bottom:15px;">RKAP Pelanggan</div>
    <div class="custom-scroll" style="width:100%; overflow-x:auto; border:1px solid #333; border-radius:8px;">
        <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
            <thead style="position:sticky; top:0; background:#252535; z-index:1;">
                <tr style="font-size:0.72rem;">
                    <th style="text-align:center; border-right:1px solid #444; padding:12px;">Golongan</th>
                    <th colspan="5" style="text-align:center; border-bottom:1px solid #444;">Target</th>
                    <th colspan="5" style="text-align:center; border-bottom:1px solid #444; border-left:1px solid #444;">Realisasi</th>
                    <th style="text-align:center; border-left:1px solid #444; color:var(--accent-orange);">% Pencapaian</th>
                </tr>
                <tr style="background:#252535; font-size:0.65rem;">
                    <th style="padding:8px; text-align:center; border-right:1px solid #444;">&nbsp;</th>
                    <th style="padding:8px; text-align:center;">TW I</th><th style="padding:8px; text-align:center;">TW II</th><th style="padding:8px; text-align:center;">TW III</th><th style="padding:8px; text-align:center;">TW IV</th>
                    <th style="padding:8px; text-align:center; border-right:1px solid #444;">Total</th>
                    <th style="padding:8px; text-align:center; border-left:1px solid #444;">TW I</th><th style="padding:8px; text-align:center;">TW II</th><th style="padding:8px; text-align:center;">TW III</th><th style="padding:8px; text-align:center;">TW IV</th><th style="padding:8px; text-align:center;">Total</th>
                    <th style="padding:8px; text-align:center;">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="tabelRkapPelanggan"></tbody>
        </table>
    </div>
</div>