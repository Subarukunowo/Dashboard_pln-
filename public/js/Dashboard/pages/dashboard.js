import SheetsService from "../services/SheetsService.js"
import BppPage from "./bpp.js"
import PenjualanPage from "./penjualan.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PenjualanKwhChart from "../charts/PenjualanKwhChart.js"
import PendapatanBatamService from "../services/PendapatanBatamService.js"
import PendapatanBatamChart from "../charts/PendapatanBatamChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"]

        // Page Components
        this.bpp = new BppPage(sheets, labels)
        this.penjualan = new PenjualanPage(sheets, labels)

        // kWh Services & Charts
        this.kwhService = new RealisasiKWHService(sheets)
        this.kwhChart = new PenjualanKwhChart()
        this.kwhData = null
        this.currentViewKwh = "bulanan"

        // Pendapatan Services & Charts
        this.pendapatanBatamService = new PendapatanBatamService(sheets)
        this.pendapatanBatamChart = new PendapatanBatamChart()
        this.pendapatanBatamData = null
        this.currentViewPendapatan = "bulanan"
        this.currentTarifPendapatan = "JUMLAH"

        // Global State
        this.currentMonth = "all"
        this.currentYear = "2025"
        this.labels = labels
    }

    async init() {
        try {
            console.log("Loading Dashboard...")

            // 1. Load BPP & Penjualan
            await Promise.all([
                this.bpp.load(),
                this.penjualan.load()
            ])

            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)

            // 2. Load kWh Data
            this.kwhData = await this.kwhService.getData()
            this.renderKwh()

            // 3. Load Pendapatan Data (Pindahkan ke dalam init)
            this.pendapatanBatamData = await this.pendapatanBatamService.getData()
            this.renderPendapatanBatam()

            // 4. Initialize All Listeners
            this.initEventListeners()
            
            // 5. Start Clock
            this.updateClock()
            setInterval(() => this.updateClock(), 1000)

        } catch (err) {
            console.error("Dashboard Init Error:", err)
        }
    }

    initEventListeners() {
        // Filter Global (Bulan/Tahun)
        document.getElementById("filterMonth")?.addEventListener("change", (e) => {
            this.currentMonth = e.target.value
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)
        })

        document.getElementById("filterYear")?.addEventListener("change", (e) => {
            this.currentYear = e.target.value
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)
        })

        // Filter Grafik kWh
        document.getElementById("filterViewKwh")?.addEventListener("change", e => {
            this.currentViewKwh = e.target.value
            this.renderKwh()
        })

        // Filter Grafik Pendapatan
        document.getElementById("filterViewPendapatan")?.addEventListener("change", e => {
            this.currentViewPendapatan = e.target.value
            this.renderPendapatanBatam()
        })

        document.getElementById("filterTarifPendapatan")?.addEventListener("change", e => {
            this.currentTarifPendapatan = e.target.value
            this.renderPendapatanBatam()
        })
    }

    renderKwh() {
        if (!this.kwhData) return
        const akum = this.currentViewKwh === "akumulasi"
        
        this.kwhChart.renderTargetRealisasi(this.kwhData, akum)
        this.kwhChart.renderDelta(this.kwhData, akum)
        this.kwhChart.renderPencapaian(this.kwhData)
        this.kwhChart.renderPertumbuhan(this.kwhData)

        this.renderKwhTable(akum)
    }

    renderKwhTable(akum) {
        const tbody = document.getElementById("tabelRingkasan")
        if (!tbody) return

        const fmt = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(2) + "%"

        tbody.innerHTML = this.kwhData.map(d => {
            const deltaValue = akum ? d.deltaAkumKwh : d.deltaKwh
            const deltaPersen = akum ? (d.deltaAkumPersen - 1) : (d.deltaPersen - 1)
            const color = deltaValue >= 0 ? '#00ff88' : '#f44336'

            return `
                <tr>
                    <td>${d.bulan}</td>
                    <td>${fmt(akum ? d.targetAkum2024 : d.target2024)}</td>
                    <td>${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                    <td>${fmt(akum ? d.targetAkum2025 : d.target2025)}</td>
                    <td>${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                    <td style="color:${color}">${fmt(deltaValue)}</td>
                    <td style="color:${color}">${fmtPct(deltaPersen)}</td>
                    <td>${fmtPct(d.pencapaianTarget)}</td>
                    <td>${fmtPct(d.pertumbuhanReal)}</td>
                </tr>
            `
        }).join("")
    }

    renderPendapatanBatam() {
        if (!this.pendapatanBatamData) return
        // Asumsi method render ada di PendapatanBatamChart.js
        this.pendapatanBatamChart.render(
            this.pendapatanBatamData, 
            this.currentViewPendapatan, 
            this.currentTarifPendapatan
        )
    }

    updateClock() {
        const clockEl = document.getElementById("clock")
        if (!clockEl) return
        const now = new Date()
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
        clockEl.textContent = `${now.toLocaleDateString('id-ID', options)} | ${now.toLocaleTimeString()}`
    }
}

// Inisialisasi
document.addEventListener("DOMContentLoaded", () => {
    const app = new Dashboard()
    app.init()
})