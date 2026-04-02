import SheetsService from "./services/SheetsService.js"
import BppService from "./services/BppService.js"
import BppChart from "./charts/BppChart.js"
import PenjualanService from "./services/PenjualanService.js"
import PenjualanChart from "./charts/PenjualanChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        this.sheets = new SheetsService(SCRIPT_URL)
        this.bppService = new BppService(this.sheets)
        this.penjualanService = new PenjualanService(this.sheets)

        this.bppChart = new BppChart("bppComparisonChart")
        this.penjualanChart = new PenjualanChart("penjualanChart")

        this.bppData = null
        this.penjualanData = null

        this.currentType = "BPP PEMBANGKIT"
        this.currentYear = "2025"
        this.currentMonth = "all"
        this.currentViewPenjualan = "bulanan"
        this.currentTarif = "JUMLAH"

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

        const tarif = this.currentTarif
        const view = this.currentViewPenjualan
        let target, realisasi

        if (tarif === "JUMLAH") {
            const data = this.penjualanData[view]
            if (!data) return
            const isPercent = view.includes('persen')
            target = isPercent ? [] : (data.target || [])
            realisasi = isPercent ? (data.realisasi || data) : (data.realisasi || [])
        } else {
            const data = this.penjualanData.perTarif?.[tarif]
            if (!data) return
            target = data.target
            realisasi = data.realisasi
        }

        if (this.currentMonth !== "all") {
            const idx = parseInt(this.currentMonth)
            this.penjualanChart.render(
                target.length ? [target[idx]] : [],
                [realisasi[idx]],
                [this.labels[idx]], view
            )
        } else {
            this.penjualanChart.render(target, realisasi, [...this.labels], view)
        }
    }

    initFilters() {
        const filterMonth = document.getElementById("filterMonth")
        const filterYear = document.getElementById("filterYear")
        const filterBpp = document.getElementById("filterBpp")
        const filterViewPenjualan = document.getElementById("filterViewPenjualan")
        const filterTarif = document.getElementById("filterTarif")

        filterMonth.addEventListener("change", (e) => {
            this.currentMonth = e.target.value
            this.renderBppChart()
            this.renderPenjualanChart()
        })

        filterYear.addEventListener("change", (e) => {
            this.currentYear = e.target.value
            this.renderBppChart()
            this.renderPenjualanChart()
        })

        filterBpp.addEventListener("change", (e) => {
            this.currentType = e.target.value
            this.renderBppChart()
        })

        if (filterViewPenjualan) {
            filterViewPenjualan.addEventListener("change", (e) => {
                this.currentViewPenjualan = e.target.value
                this.renderPenjualanChart()
            })
        }

        if (filterTarif) {
            filterTarif.addEventListener("change", (e) => {
                this.currentTarif = e.target.value
                this.renderPenjualanChart()
            })
        }

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