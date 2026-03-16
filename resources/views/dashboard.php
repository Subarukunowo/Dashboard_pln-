<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBU Monthly Dashboard</title>
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
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: calc(var(--gap) * 1.5);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 30px;
            border-left: 4px solid var(--accent-orange);
            padding-left: 15px;
        }

        .header h2 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Grid System 3x3 */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: minmax(220px, auto);
            gap: var(--gap);
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease;
            border: 1px solid #333;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #444;
        }

        .card-title {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* Stat Styles */
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent-orange);
            margin-top: auto;
            margin-bottom: auto;
        }

        .stat-change {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        /* Chart Wrapper */
        .chart-wrapper {
            flex-grow: 1;
            position: relative;
            min-height: 0; /* Penting agar chart.js tidak overflow */
        }

        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <header class="header">
        <h2>Commercial / SBU Monthly Dashboard</h2>
    </header>

    <main class="dashboard-grid">
        <section class="card">
            <div class="card-title">Total Customer</div>
            <div class="stat-value">402,737</div>
        </section>

        <section class="card">
            <div class="card-title">New Customer (MoM)</div>
            <div class="stat-change">+1,494</div>
        </section>

        <section class="card">
            <div class="card-title">New Customer (YoY)</div>
            <div class="stat-change">+16,107</div>
        </section>

        <section class="card">
            <div class="card-title">Growth Trend</div>
            <div class="chart-wrapper">
                <canvas id="areaChart"></canvas>
            </div>
        </section>

        <section class="card">
            <div class="card-title">Segment Distribution A</div>
            <div class="chart-wrapper">
                <canvas id="pieChart1"></canvas>
            </div>
        </section>

        <section class="card">
            <div class="card-title">Segment Distribution B</div>
            <div class="chart-wrapper">
                <canvas id="pieChart2"></canvas>
            </div>
        </section>

        <section class="card">
            <div class="card-title">Revenue Performance</div>
            <div style="color: #444; text-align: center; margin-top: 40px;">No data available</div>
        </section>

        <section class="card">
            <div class="card-title">Regional Stats</div>
            <div style="color: #444; text-align: center; margin-top: 40px;">No data available</div>
        </section>

        <section class="card">
            <div class="card-title">Customer Churn Rate</div>
            <div style="color: #444; text-align: center; margin-top: 40px;">No data available</div>
        </section>
    </main>
</div>

<script>
    // Konfigurasi standar untuk semua chart agar responsif
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                labels: { color: '#888', font: { size: 10 } }
            }
        }
    };

    // Inisialisasi Area Chart
    new Chart(document.getElementById('areaChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Growth',
                data: [1200, 1900, 1500, 2100, 2400, 1494],
                fill: true,
                backgroundColor: 'rgba(255, 152, 0, 0.1)',
                borderColor: '#ff9800',
                tension: 0.4
            }]
        },
        options: chartOptions
    });

    // Inisialisasi Pie Chart 1
    new Chart(document.getElementById('pieChart1'), {
        type: 'doughnut',
        data: {
            labels: ['Retail', 'Corporate', 'SME'],
            datasets: [{
                data: [300, 50, 100],
                backgroundColor: ['#ff9800', '#00ff88', '#2196f3'],
                borderWidth: 0
            }]
        },
        options: chartOptions
    });

    // Inisialisasi Pie Chart 2
    new Chart(document.getElementById('pieChart2'), {
        type: 'pie',
        data: {
            labels: ['Active', 'Idle', 'Churned'],
            datasets: [{
                data: [402000, 500, 237],
                backgroundColor: ['#00ff88', '#ffc107', '#f44336'],
                borderWidth: 0
            }]
        },
        options: chartOptions
    });
</script>

</body>
</html>