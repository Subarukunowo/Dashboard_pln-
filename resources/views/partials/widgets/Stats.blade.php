{{-- Stats.blade.php --}}
{{-- Data diisi oleh initStats() dari dashboard.js via UpdateStats.js --}}

<div class="card col-4">
    <div class="card-title">TOTAL CUSTOMER</div>
    <div id="statTotalCustomer" class="stat-value">---</div>
</div>

<div class="card col-4">
    <div class="card-title">NEW CUSTOMER (MoM)</div>
    <div id="statNewCustomer" class="stat-change">---</div>
</div>

<div class="card col-4">
    <div class="card-title">GROWTH RATE (YoY)</div>
    <div id="statGrowthRate" class="stat-change">---</div>
</div>