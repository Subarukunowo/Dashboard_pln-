<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBU Monthly Dashboard - PLN</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
            --accent-orange: #ff9800;
            --accent-green: #00ff88;
            --text-main: #ffffff;
            --text-muted: #888888;
            --gap: 20px;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 30px;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-left: 4px solid var(--accent-orange);
            padding-left: 15px;
        }

        .filters { display: flex; gap: 10px; }
        select {
            background: var(--card-bg);
            color: white;
            border: 1px solid #444;
            padding: 8px 15px;
            border-radius: 5px;
            outline: none;
            cursor: pointer;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: minmax(180px, auto);
            gap: var(--gap);
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #333;
            display: flex;
            flex-direction: column;
        }

        .double-column { grid-column: span 2; }

        .card-title {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-value { font-size: 2.5rem; font-weight: 800; color: var(--accent-orange); }
        .stat-change { font-size: 1.5rem; font-weight: 700; color: var(--accent-green); }

        .chart-wrapper { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

<div class="container">
    <header class="header">
        <div>
            <h2>PLN COMMERCIAL DASHBOARD</h2>
            <div id="clock" style="color: var(--text-muted); font-size: 0.9rem;"></div>
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

        <div class="card">
            <div class="card-title">Total Customer</div>
            <div id="statTotalCustomer" class="stat-value">---</div>
        </div>

        <div class="card">
            <div class="card-title">New Customer (MoM)</div>
            <div id="statNewCustomer" class="stat-change">---</div>
        </div>

        <div class="card">
            <div class="card-title">Growth Rate (YoY)</div>
            <div id="statGrowthRate" class="stat-change" style="color:#2196f3;">---</div>
        </div>

        <div class="card double-column">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div class="card-title">Realisasi BPP Rp/kWh: Konsolidasi vs Internal</div>
                <select id="filterBpp" style="width: auto; min-width: 200px;">
                    <option value="BPP PEMBANGKIT">BPP PEMBANGKIT</option>
                    <option value="BPP S.D TRANSMISI">BPP S.D TRANSMISI</option>
                    <option value="BPP S.D DIDTRIBUSI TM">BPP S.D DIDTRIBUSI TM</option>
                    <option value="BPP S.D DIDTRIBUSI TR">BPP S.D DIDTRIBUSI TR</option>
                </select>
            </div>
            <div class="chart-wrapper" style="flex: 1; min-height: 300px;">
                <canvas id="bppComparisonChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div class="card-title">Kinerja Penjualan (GWh)</div>
                <select id="filterViewPenjualan" style="font-size: 0.7rem; padding: 4px;">
                    <option value="bulanan">Bulanan</option>
                    <option value="akumulasi">Akumulasi</option>
                </select>
            </div>
            <div class="chart-wrapper" style="flex: 1;">
                <canvas id="penjualanChart"></canvas>
            </div>
        </div>

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
</div>

<script type="module" src="/js/dashboard.js"></script>

</body>
</html>