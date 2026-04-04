import SheetsService from "../services/SheetsService.js"
import BppPage from "./bpp.js"
import PenjualanPage from "./penjualan.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PenjualanKwhChart from "../charts/PenjualanKwhChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

        this.bpp = new BppPage(sheets, labels)
        this.penjualan = new PenjualanPage(sheets, labels)
        this.kwhService = new RealisasiKWHService(sheets)
        this.kwhChart = new PenjualanKwhChart()
        this.kwhData = null
        this.currentViewKwh = "bulanan"

        this.currentMonth = "all"
        this.currentYear = "2025"
        this.labels = labels
    }

    async init() {
        try {
            console.log("Loading Dashboard...")

            await this.bpp.load()
            this.bpp.render(this.currentMonth)
            this.bpp.initFilter(() => this.bpp.render(this.currentMonth))

            await this.penjualan.load()
            this.penjualan.render(this.currentMonth)
            this.penjualan.initFilters(() => this.penjualan.render(this.currentMonth))

            this.kwhData = await this.kwhService.getData()
            this.renderKwh()

            document.getElementById("filterViewKwh")?.addEventListener("change", e => {
                this.currentViewKwh = e.target.value
                this.renderKwh()
            })

            this.initFilters()
            this.updateClock()
            setInterval(() => this.updateClock(), 1000)

        } catch (err) {
            console.error("Dashboard Init Error:", err)
        }
    }

    renderKwh() {
        if (!this.kwhData) return
        const akum = this.currentViewKwh === "akumulasi"
        this.kwhChart.renderTargetRealisasi(this.kwhData, akum)
        this.kwhChart.renderDelta(this.kwhData, akum)
        this.kwhChart.renderPencapaian(this.kwhData)
        this.kwhChart.renderPertumbuhan(this.kwhData)

        const tbody = document.getElementById("tabelRingkasan")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(2) + "%"
        tbody.innerHTML = this.kwhData.map(d => `
            <tr>
                <td>${d.bulan}</td>
                <td>${fmt(akum ? d.targetAkum2024 : d.target2024)}</td>
                <td>${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td>${fmt(akum ? d.targetAkum2025 : d.target2025)}</td>
                <td>${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${(akum ? d.deltaAkumKwh : d.deltaKwh) >= 0 ? '#00ff88' : '#f44336'}">${fmt(akum ? d.deltaAkumKwh : d.deltaKwh)}</td>
                <td style="color:${(akum ? d.deltaAkumKwh : d.deltaKwh) >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(akum ? d.deltaAkumPersen - 1 : d.deltaPersen - 1)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pertumbuhanReal)}</td>
            </tr>
        `).join("")
    }

    initFilters() {
        const filterMonth = document.getElementById("filterMonth")
        const filterYear = document.getElementById("filterYear")

        filterMonth?.addEventListener("change", (e) => {
            this.currentMonth = e.target.value
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)
        })

        filterYear?.addEventListener("change", (e) => {
            this.currentYear = e.target.value
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)
        })

        if (filterMonth) filterMonth.value = this.currentMonth
        if (filterYear) filterYear.value = this.currentYear
    }

    updateClock() {
        const clockEl = document.getElementById("clock")
        if (!clockEl) return
        const now = new Date()
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
        clockEl.textContent = `${now.toLocaleDateString('id-ID', options)} | ${now.toLocaleTimeString()}`
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new Dashboard().init()
})