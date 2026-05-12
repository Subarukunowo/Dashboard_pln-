<div class="card col-12">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px;">
        <div class="card-title">Realisasi BPP Rp/kWh: Konsolidasi vs Internal</div>
        <select id="filterBpp" aria-label="Filter kategori BPP">
            <option value="BPP PEMBANGKIT">BPP Pembangkit</option>
            <option value="BPP S.D TRANSMISI">BPP s.d Transmisi</option>
            <option value="BPP S.D DIDTRIBUSI TM">BPP s.d Distribusi TM</option>
            <option value="BPP S.D DIDTRIBUSI TR">BPP s.d Distribusi TR</option>
        </select>
    </div>

    <div class="chart-wrapper" style="width:100%; min-height:300px; margin-bottom:20px;">
        <canvas id="bppComparisonChart"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px;">Bulan</th>
                    <th style="padding:10px; color:var(--accent-orange);">Konsolidasi</th>
                    <th style="padding:10px; color:var(--accent-green);">Internal</th>
                    <th style="padding:10px;">Selisih</th>
                </tr>
            </thead>
            <tbody id="bppTableBody">
                {{-- Diisi oleh BppModule.renderTable() --}}
            </tbody>
        </table>
    </div>
</div>