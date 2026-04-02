import SheetsService from "./services/SheetsService.js"
import BppService from "./services/BppService.js"
import BppChart from "./charts/BppChart.js"
import PenjualanService from "./services/PenjualanService.js"
import PenjualanChart from "./charts/PenjualanChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        // Inisialisasi Service
        this.sheets = new SheetsService(SCRIPT_URL)
        this.bppService = new BppService(this.sheets)
        this.penjualanService = new PenjualanService(this.sheets)

        // Inisialisasi Chart
        this.bppChart = new BppChart("bppComparisonChart")
        this.penjualanChart = new PenjualanChart("penjualanChart")

        // Data Store
        this.bppData = null
        this.penjualanData = null

        // Default State
        this.currentType = "BPP PEMBANGKIT"
        this.currentYear = "2025"
        this.currentMonth = "all"
        this.currentViewPenjualan = "bulanan"

        this.labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"]
    }

   async init() {
    try {
        console.log("Loading Dashboard...");

        this.bppData = await this.bppService.getAllBpp()
        this.renderBppChart()

        this.penjualanData = await this.penjualanService.getKinerjaData()
        this.renderPenjualanChart()

        this.initFilters()
        this.updateClock()
        setInterval(() => this.updateClock(), 1000)

    } catch (err) {
        console.error("Dashboard Init Error:", err)
    }
}
    renderBppChart() {
        if (!this.bppData) return
        const data = this.bppData[this.currentType]
        if (!data) return

        let kons = [...data.konsolidasi]
        let intl = [...data.internal]
        let displayLabels = [...this.labels]
        let mode = "trend"

        if (this.currentMonth !== "all") {
            const index = parseInt(this.currentMonth)
            kons = [kons[index]]
            intl = [intl[index]]
            displayLabels = [this.labels[index]]
            mode = "single"
        }

        this.bppChart.render(kons, intl, displayLabels, mode)
    }

    renderPenjualanChart() {
        if (!this.penjualanData) return

        const view = this.currentViewPenjualan // 'bulanan', 'persenBulanan', 'akumulasi', 'persenAkumulasi'
        const data = this.penjualanData[view]

        if (!data) {
            console.warn(`Data untuk view ${view} tidak ditemukan.`);
            return
        }

        // Logic pemisahan Target vs Realisasi
        // Jika mode persen, biasanya hanya mengirimkan satu array (realisasi/hasil kalkulasi)
        const isPercent = view.toLowerCase().includes('persen')
        const target = isPercent ? [] : (data.target || [])
        const realisasi = isPercent ? (data.realisasi || data) : (data.realisasi || [])
        
        let displayLabels = [...this.labels]

        // Handle filter bulan untuk Kinerja Penjualan
        if (this.currentMonth !== "all") {
            const idx = parseInt(this.currentMonth)
            const filteredTarget = target.length > 0 ? [target[idx]] : []
            const filteredReal = Array.isArray(realisasi) ? [realisasi[idx]] : [realisasi]
            this.penjualanChart.render(filteredTarget, filteredReal, [this.labels[idx]], view)
        } else {
            this.penjualanChart.render(target, realisasi, displayLabels, view)
        }
    }

    initFilters() {
        const filterMonth = document.getElementById("filterMonth")
        const filterYear = document.getElementById("filterYear")
        const filterBpp = document.getElementById("filterBpp")
        const filterViewPenjualan = document.getElementById("filterViewPenjualan")

        // FILTER BULAN (Berlaku untuk kedua grafik)
        filterMonth.addEventListener("change", (e) => {
            this.currentMonth = e.target.value
            this.renderBppChart()
            this.renderPenjualanChart()
        })

        // FILTER TAHUN
        filterYear.addEventListener("change", (e) => {
            this.currentYear = e.target.value
            // Re-fetch data jika diperlukan, atau sekadar re-render
            this.renderBppChart()
            this.renderPenjualanChart()
        })

        // FILTER JENIS BPP
        filterBpp.addEventListener("change", (e) => {
            this.currentType = e.target.value
            this.renderBppChart()
        })

        // FILTER MODE PENJUALAN (Bulanan / Akumulasi / Persen)
        if (filterViewPenjualan) {
            filterViewPenjualan.addEventListener("change", (e) => {
                this.currentViewPenjualan = e.target.value
                this.renderPenjualanChart()
            })
        }

        // SYNC DEFAULT UI
        filterMonth.value = this.currentMonth
        filterYear.value = this.currentYear
        filterBpp.value = this.currentType
        if (filterViewPenjualan) filterViewPenjualan.value = this.currentViewPenjualan
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
    const dashboard = new Dashboard()
    dashboard.init()
})
