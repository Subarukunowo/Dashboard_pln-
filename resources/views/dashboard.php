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

        .full-width { grid-column: span 3; }

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
            <option value="all">Januari - Juni</option>
        </select>
    </div>
</header>

<main class="dashboard-grid">

<div class="card">
<div class="card-title">Total Customer</div>
<div class="stat-value">402,737</div>
</div>

<div class="card">
<div class="card-title">New Customer (MoM)</div>
<div class="stat-change">+1,494</div>
</div>

<div class="card">
<div class="card-title">Growth Rate (YoY)</div>
<div class="stat-change" style="color:#2196f3;">+4.2%</div>
</div>

<div class="card full-width">

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">

<div class="card-title">
Realisasi BPP Rp/kWh: Konsolidasi vs Internal
</div>

<div style="display:flex; gap:10px;">

<select id="filterYear">
<option value="2024">2024</option>
<option value="2025" selected>2025</option>
<option value="2026">2026</option>
</select>

<select id="filterBpp">
<option value="BPP PEMBANGKIT">BPP PEMBANGKIT</option>
<option value="BPP S.D TRANSMISI">BPP S.D TRANSMISI</option>
<option value="BPP S.D DIDTRIBUSI TM">BPP S.D DIDTRIBUSI TM</option>
<option value="BPP S.D DIDTRIBUSI TR">BPP S.D DIDTRIBUSI TR</option>
</select>

<select id="filterMonth">

<option value="all">-- Semua Bulan --</option>

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

</div>

<div class="chart-wrapper">
<canvas id="bppComparisonChart"></canvas>
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

<script>

/* =====================
   CLOCK
===================== */

function updateClock(){
    const now = new Date();

    document.getElementById("clock").innerHTML =
        now.toLocaleDateString('id-ID',{
            weekday:'long',
            day:'numeric',
            month:'long'
        })
        + " | " +
        now.toLocaleTimeString();
}

setInterval(updateClock,1000);
updateClock();


/* =====================
   SHEETS SERVICE CLASS
===================== */

class SheetsService{

    constructor(url){
        this.url = url
        this.cache = null
    }

    async fetch(){

        if(this.cache) return this.cache

        const res = await fetch(this.url)

        if(!res.ok){
            throw new Error("Gagal mengambil data sheets")
        }

        this.cache = await res.json()

        return this.cache
    }

    async getSheet(name){

        const data = await this.fetch()

        return data[name] || []
    }

}


/* =====================
   BPP CHART CLASS
===================== */

class BppChart{

    constructor(canvasId){
        this.canvasId = canvasId
        this.chart = null
    }

    render(konsolidasi, internal){

        const ctx = document.getElementById(this.canvasId)

        this.chart = new Chart(ctx,{

            type:'bar',

            data:{
                labels:["Jan","Feb","Mar","Apr","Mei","Jun"],

                datasets:[
                    {
                        label:'Versi Konsolidasi (POJK)',
                        data:konsolidasi,
                        backgroundColor:'#ff9800',
                        borderRadius:4
                    },

                    {
                        label:'Versi Internal (PLN Batam)',
                        data:internal,
                        backgroundColor:'#00ff88',
                        borderRadius:4
                    }
                ]
            },

            options:{
                responsive:true,
                maintainAspectRatio:false,

                plugins:{
                    legend:{
                        position:'top',
                        labels:{ color:'#888' }
                    }
                },

                scales:{
                    y:{
                        beginAtZero:false,
                        grid:{ color:'#333' },
                        ticks:{ color:'#888' }
                    },

                    x:{
                        grid:{ display:false },
                        ticks:{ color:'#888' }
                    }
                }
            }

        })

    }

}


/* =====================
   DASHBOARD CONTROLLER
===================== */

class Dashboard{

    constructor(){

        this.SCRIPT_URL = "https://script.google.com/macros/s/AKfycbweFQ59S0HfRkd6gT_D2becqDM9PiFBcnVLSTDiwuJPucFL4Oc3gdGCVY3xfNC1c7uEVg/exec"

        this.sheets = new SheetsService(this.SCRIPT_URL)

        this.bppChart = new BppChart("bppComparisonChart")

    }

    async init(){

        try{

            const bppSheet = await this.sheets.getSheet("BPP")

            const dataKons =
                bppSheet[7]
                .slice(6,12)
                .map(v => parseFloat(v))

            const dataInt =
                bppSheet[19]
                .slice(6,12)
                .map(v => parseFloat(v))

            this.bppChart.render(dataKons,dataInt)

            this.initOtherCharts()

        }

        catch(err){

            console.error("Dashboard Error:",err)

        }

    }


/* =====================
   STATIC CHARTS
===================== */

    initOtherCharts(){

        const commonOptions={
            responsive:true,
            maintainAspectRatio:false,
            plugins:{ legend:{ display:false } }
        }

        new Chart(document.getElementById('areaChart'),{
            type:'line',
            data:{
                labels:['Jan','Feb','Mar','Apr','Mei','Jun'],
                datasets:[
                    {
                        data:[1200,1900,1500,2100,2400,1494],
                        borderColor:'#ff9800',
                        fill:true,
                        backgroundColor:'rgba(255,152,0,0.1)',
                        tension:0.4
                    }
                ]
            },
            options:commonOptions
        })

        new Chart(document.getElementById('pieChart1'),{
            type:'doughnut',
            data:{
                labels:['Batam Center','Batu Aji','Tiban','Nagoya'],
                datasets:[
                    {
                        data:[32,25,22,21],
                        backgroundColor:['#ff9800','#ffeb3b','#4caf50','#2196f3'],
                        borderWidth:0
                    }
                ]
            },
            options:commonOptions
        })

        new Chart(document.getElementById('pieChart2'),{
            type:'pie',
            data:{
                labels:['Rumah Tangga','Bisnis','Sosial','Industri'],
                datasets:[
                    {
                        data:[83,10,5,2],
                        backgroundColor:['#ff9800','#ffeb3b','#4caf50','#2196f3'],
                        borderWidth:0
                    }
                ]
            },
            options:commonOptions
        })

    }

}


/* =====================
   INIT DASHBOARD
===================== */

document.addEventListener("DOMContentLoaded",()=>{

    const dashboard = new Dashboard()

    dashboard.init()

})

</script>

</body>
</html>
<script type="module" src="/js/dashboard.js"></script>