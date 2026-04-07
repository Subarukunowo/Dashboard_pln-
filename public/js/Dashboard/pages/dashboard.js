import SheetsService from "../services/SheetsService.js"
import BppPage from "./bpp.js"
import PenjualanPage from "./penjualan.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PenjualanKwhChart from "../charts/PenjualanKwhChart.js"
import PendapatanBatamService from "../services/PendapatanBatamService.js"
import PendapatanBatamChart from "../charts/PendapatanBatamChart.js"
import GolTarifBatamService from "../services/GolTarifBatamService.js"
import GolTarifBatamChart from "../charts/GolTarifBatamChart.js"
import RpkwhService from "../services/RpkwhService.js"
import RpkwhChart from "../charts/RpkwhChart.js"
import PelangganService from "../services/PelangganService.js"
import PelangganChart from "../charts/PelangganChart.js"
import KvaService from "../services/KvaService.js"
import KvaChart from "../charts/KvaChart.js"
import KwhModule from "../modules/KwhModule.js"
import PendapatanBatamModule from "../modules/PendapatanBatamModule.js"
import GolTarifBatamModule from "../modules/GolTarifBatamModule.js"
import RpkwhModule from "../modules/RpkwhModule.js"
import PelangganModule from "../modules/PelangganModule.js"
import KvaModule from "../modules/KvaModule.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

        this.bpp = new BppPage(sheets, labels)
        this.penjualan = new PenjualanPage(sheets, labels)

        // Services
        this.kwhService        = new RealisasiKWHService(sheets)
        this.pendapatanService = new PendapatanBatamService(sheets)
        this.golTarifService   = new GolTarifBatamService(sheets)
        this.rpkwhService      = new RpkwhService(sheets)
        this.pelangganService  = new PelangganService(sheets)
        this.kvaService        = new KvaService(sheets)

        // Modules
        this.kwhModule        = new KwhModule(new PenjualanKwhChart())
        this.pendapatanModule = new PendapatanBatamModule(new PendapatanBatamChart())
        this.golTarifModule   = new GolTarifBatamModule(new GolTarifBatamChart())
        this.rpkwhModule      = new RpkwhModule(new RpkwhChart())
        this.pelangganModule  = new PelangganModule(new PelangganChart())
        this.kvaModule        = new KvaModule(new KvaChart())

        // Data
        this.kwhData        = null
        this.pendapatanData = null
        this.golTarifData   = null
        this.rpkwhData      = null
        this.pelangganData  = null
        this.kvaData        = null

        // State
        this.state = {
            global:     { month: "all", year: "2025" },
            kwh:        { view: "bulanan" },
            pendapatan: { view: "bulanan", tarif: "JUMLAH" },
            golTarif:   { tahun: "2025", view: "bulanan", bulan: "11" },
            rpkwh:      { tarif: "JUMLAH", view: "bulanan", bulan: "11", tahun: "2025" },
            pelanggan:  { tarif: "JUMLAH", bulan: 11 },
            kva:        { tarif: "JUMLAH", bulan: 11, view: "bulanan" }
        }

        this.labels = labels
    }

    async init() {
        try {
            console.log("Loading Dashboard...")

            await Promise.all([this.bpp.load(), this.penjualan.load()])
            this.bpp.render(this.state.global.month)
            this.penjualan.render(this.state.global.month)
            this.bpp.initFilter(() => this.bpp.render(this.state.global.month))
            this.penjualan.initFilters(() => this.penjualan.render(this.state.global.month))

            this.kwhData = await this.kwhService.getData()
            this.kwhModule.render(this.kwhData, this.state.kwh)
            this.kwhModule.initFilters(this.state.kwh, () => this.kwhModule.render(this.kwhData, this.state.kwh))

            this.pendapatanData = await this.pendapatanService.getData()
            this.pendapatanModule.render(this.pendapatanData, this.state.pendapatan)
            this.pendapatanModule.initFilters(this.state.pendapatan, () => this.pendapatanModule.render(this.pendapatanData, this.state.pendapatan))

            this.golTarifData = await this.golTarifService.getData()
            this.golTarifModule.render(this.golTarifData, this.state.golTarif)
            this.golTarifModule.initFilters(this.state.golTarif, () => this.golTarifModule.render(this.golTarifData, this.state.golTarif))

            this.rpkwhData = await this.rpkwhService.getData()
            this.rpkwhModule.render(this.rpkwhData, this.state.rpkwh)
            this.rpkwhModule.initFilters(this.state.rpkwh, () => this.rpkwhModule.render(this.rpkwhData, this.state.rpkwh))

            this.pelangganData = await this.pelangganService.getData()
            this.pelangganModule.render(this.pelangganData, this.state.pelanggan)
            this.pelangganModule.initFilters(this.state.pelanggan, () => this.pelangganModule.render(this.pelangganData, this.state.pelanggan))

            this.kvaData = await this.kvaService.getData()
            this.kvaModule.render(this.kvaData, this.state.kva)
            this.kvaModule.initFilters(this.state.kva, () => this.kvaModule.render(this.kvaData, this.state.kva))

            this._initGlobalFilters()
            this.updateClock()
            setInterval(() => this.updateClock(), 1000)

        } catch (err) {
            console.error("Dashboard Init Error:", err)
        }
    }

    _initGlobalFilters() {
        document.getElementById("filterMonth")?.addEventListener("change", e => {
            this.state.global.month = e.target.value
            this.bpp.render(this.state.global.month)
            this.penjualan.render(this.state.global.month)
        })
        document.getElementById("filterYear")?.addEventListener("change", e => {
            this.state.global.year = e.target.value
            this.bpp.render(this.state.global.month)
            this.penjualan.render(this.state.global.month)
        })
    }

    updateClock() {
        const clockEl = document.getElementById("clock")
        if (!clockEl) return
        const now = new Date()
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
        clockEl.textContent = `${now.toLocaleDateString('id-ID', options)} | ${now.toLocaleTimeString()}`
    }
}

document.addEventListener("DOMContentLoaded", () => new Dashboard().init())