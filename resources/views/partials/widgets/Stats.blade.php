{{-- Stats.blade.php --}}
{{-- Data diisi oleh initStats() dari dashboard.js via UpdateStats.js --}}
{{-- Sinkron dengan tabel: rt_fact_pelanggan, rt_fact_penjualan, rt_fact_tunggakan --}}

{{-- ROW 1: Pelanggan --}}
<div class="card col-4">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">
        Total Pelanggan
    </div>
    <div id="statTotalCustomer" class="stat-value">
        <span style="color:#555; font-size:0.8rem;">Memuat...</span>
    </div>
    <div style="font-size:0.65rem; color:#555; margin-top:4px;" id="statTotalCustomerPeriod"></div>
</div>

<div class="card col-4">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">
        Pelanggan Baru (MoM)
    </div>
    <div id="statNewCustomer" class="stat-change">
        <span style="color:#555; font-size:0.8rem;">Memuat...</span>
    </div>
    <div style="font-size:0.65rem; color:#555; margin-top:4px;">Month-over-Month</div>
</div>

<div class="card col-4">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">
        Pertumbuhan Pelanggan (YoY)
    </div>
    <div id="statGrowthRate" class="stat-change">
        <span style="color:#555; font-size:0.8rem;">Memuat...</span>
    </div>
    <div style="font-size:0.65rem; color:#555; margin-top:4px;">Year-over-Year</div>
</div>

{{-- ROW 2: Penjualan KWH --}}
<div class="card col-4">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">
        Realisasi Penjualan KWH
    </div>
    <div id="statRealisasiPenjualan" class="stat-value">
        <span style="color:#555; font-size:0.8rem;">Memuat...</span>
    </div>
    <div style="font-size:0.65rem; color:#555; margin-top:4px;" id="statPenjualanPeriod">Akumulasi YTD</div>
</div>

<div class="card col-4">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">
        Target Penjualan KWH
    </div>
    <div id="statTargetPenjualan" class="stat-value">
        <span style="color:#555; font-size:0.8rem;">Memuat...</span>
    </div>
    <div style="font-size:0.65rem; color:#555; margin-top:4px;" id="statPenjualanCapai"></div>
</div>

<div class="card col-4">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">
        Ratio Tunggakan
    </div>
    <div id="statRatioTunggakan" class="stat-change">
        <span style="color:#555; font-size:0.8rem;">Memuat...</span>
    </div>
    <div style="font-size:0.65rem; color:#555; margin-top:4px;" id="statTunggakanPeriod">vs Target 0.01%</div>
</div>
