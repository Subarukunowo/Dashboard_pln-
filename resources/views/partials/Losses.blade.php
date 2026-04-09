<div class="card full-width">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
        <div class="card-title">Losses Jaringan PLN Batam 2025</div>
        <div style="display:flex; gap:8px;">
            <select id="filterEminLosses" class="form-select-dark">
                <option value="with">Dengan EMIN</option>
                <option value="without">Tanpa EMIN</option>
            </select>
            <select id="filterBulanLosses" class="form-select-dark">
                <option value="all">Semua Bulan</option>
                <option value="0">Jan</option>
                <option value="1">Feb</option>
                <option value="2">Mar</option>
                <option value="3">Apr</option>
                <option value="4">Mei</option>
                <option value="5">Jun</option>
                <option value="6">Jul</option>
                <option value="7">Agu</option>
                <option value="8">Sep</option>
                <option value="9">Okt</option>
                <option value="10">Nov</option>
                <option value="11">Des</option>
            </select>
        </div>
    </div>
    
    <div style="display:flex; gap:20px; flex:1; min-height:320px;">
        <div class="chart-wrapper" style="flex:2.5; min-width:0;">
            <canvas id="chartLossesProduksi"></canvas>
        </div>
        
        <div class="custom-scroll" style="flex:1; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid #333; overflow-y:auto; max-height:320px;">
            <table style="width:100%; border-collapse: collapse; font-size:0.75rem;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="color:#888; text-transform: uppercase; font-size: 0.65rem;">
                        <th style="padding:10px; text-align:left;">Bulan</th>
                        <th style="padding:10px; text-align:right; color:#2196f3;">Bruto</th>
                        <th style="padding:10px; text-align:right; color:#00ff88;">Netto</th>
                        <th style="padding:10px; text-align:right; color:#ff9800;">Salur</th>
                    </tr>
                </thead>
                <tbody id="lossesSideTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
    <div class="card">
        <div class="card-title" style="margin-bottom: 10px;">Losses % (Bulanan & Akumulasi)</div>
        <div class="chart-wrapper"><canvas id="chartLossesPct"></canvas></div>
    </div>
    <div class="card">
        <div class="card-title" style="margin-bottom: 10px;">Losses Transmisi (kWh)</div>
        <div class="chart-wrapper"><canvas id="chartLossesKwh"></canvas></div>
    </div>
</div>

<div class="card full-width">
    <div class="card-title" style="margin-bottom: 15px;">Tabel Rincian Losses (jt kWh)</div>
    <div class="table-container custom-scroll" style="width:100%; overflow-x:auto; border: 1px solid #333; border-radius: 8px;">
        <table class="main-losses-table">
            <thead>
                <tr>
                    <th class="sticky-col">Bulan</th>
                    <th>Prod Bruto</th>
                    <th>Prod Netto</th>
                    <th>Siap Salur</th>
                    <th>Losses Trans</th>
                    <th>Losses Bln (%)</th>
                    <th>Losses Akum (%)</th>
                    <th>Interkoneksi</th>
                    <th>Losses Dist</th>
                    <th>Losses Dist (%)</th>
                    <th>Losses Dist Akum</th>
                    <th>Losses Jar</th>
                    <th>Losses Jar (%)</th>
                    <th>Losses Jar Akum</th>
                    <th style="color: #ff9800;">Target (%)</th>
                </tr>
            </thead>
            <tbody id="tabelLosses"></tbody>
        </table>
    </div>
</div>

<style>
    /* CSS Tambahan untuk kenyamanan User */
    .form-select-dark {
        background-color: #2b2b3b;
        color: #fff;
        border: 1px solid #444;
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 4px;
        outline: none;
    }

    .main-losses-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.7rem;
        white-space: nowrap; /* Menghindari teks terpotong ke bawah */
    }

    .main-losses-table th {
        background: #252535;
        color: #888;
        padding: 12px 15px;
        text-align: center;
        border-bottom: 2px solid #333;
    }

    .main-losses-table td {
        padding: 10px 15px;
        border-bottom: 1px solid #222;
        text-align: right;
        color: #eee;
    }

    /* Efek Sticky untuk Kolom Bulan agar tidak hilang saat di-scroll ke kanan */
    .main-losses-table .sticky-col {
        position: sticky;
        left: 0;
        background: #1e1e2d !important;
        z-index: 2;
        border-right: 2px solid #333;
        text-align: left !important;
        font-weight: bold;
    }

    .main-losses-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    /* Styling scrollbar minimalis */
    .custom-scroll::-webkit-scrollbar { height: 6px; width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #444; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
</style>