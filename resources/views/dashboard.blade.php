@extends('layouts.app')

@section('title', 'PLN Commercial Dashboard')

@section('content')
<header class="header">
    <div>
        <h2>PLN COMMERCIAL DASHBOARD</h2>
        <div id="clock" style="color:var(--text-muted); font-size:0.9rem;"></div>
    </div>
    <div class="filters">
        <select id="filterYear">
            <option value="2025">2025</option>
        </select>
        <select id="filterMonth">
            <option value="all">Januari - Desember</option>
            <option value="0">Januari</option>
            <option value="1">Februari</option>
            <option value="2">Maret</option>
            <option value="3">April</option>
            <option value="4">Mei</option>
            <option value="5">Juni</option>
            <option value="6">Juli</option>
            <option value="7">Agustus</option>
            <option value="8">September</option>
            <option value="9">Oktober</option>
            <option value="10">November</option>
            <option value="11">Desember</option>
        </select>
    </div>
</header>

<main class="dashboard-grid">
    @include('partials.stats')
    @include('partials.BppChart')
    @include('partials.PenjualanChart')

    <div class="card">
        <div class="card-title">Customer Growth Trend</div>
        <div class="chart-wrapper" style="height:150px;">
            <canvas id="areaChart"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Area Distribution</div>
        <div class="chart-wrapper" style="height:150px;">
            <canvas id="pieChart1"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Rate Category</div>
        <div class="chart-wrapper" style="height:150px;">
            <canvas id="pieChart2"></canvas>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script type="module" src="/js/Dashboard/pages/dashboard.js"></script>
@endsection