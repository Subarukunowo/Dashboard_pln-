import SheetsService from "../services/SheetsService.js"
import BppModule from "../modules/BppModule.js"
import PenjualanModule from "../modules/PenjualanModule.js"
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
import BabinService from "../services/BabinService.js"
import BabinChart from "../charts/BabinChart.js"
import JamNyalaService from "../services/JamNyalaService.js"
import JamNyalaChart from "../charts/JamNyalaChart.js"
import LossesService from "../services/LossesService.js"
import LossesChart from "../charts/LossesChart.js"
import TunggakanService from "../services/TunggakanService.js"
import TunggakanChart from "../charts/TunggakanChart.js"
import P2tlService from "../services/P2tlService.js"
import P2tlChart from "../charts/P2tlChart.js"
import KwhModule from "../modules/KwhModule.js"
import PendapatanBatamModule from "../modules/PendapatanBatamModule.js"
import GolTarifBatamModule from "../modules/GolTarifBatamModule.js"
import RpkwhModule from "../modules/RpkwhModule.js"
import PelangganModule from "../modules/PelangganModule.js"
import KvaModule from "../modules/KvaModule.js"
import BabinModule from "../modules/BabinModule.js"
import JamNyalaModule from "../modules/JamNyalaModule.js"
import LossesModule from "../modules/LossesModule.js"
import TunggakanModule from "../modules/TunggakanModule.js"
import P2tlModule from "../modules/P2tlModule.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

        // Modules (menggantikan BppPage & PenjualanPage)
        this.bppModule      = new BppModule(sheets, labels)
        this.penjualanModule = new PenjualanModule(sheets, labels)

        // Services
        this.kwhService       = new RealisasiKWHService(sheets)
        this.pendapatanService = new PendapatanBatamService(sheets)
        this.golTarifService  = new GolTarifBatamService(sheets)
        this.rpkwhService     = new RpkwhService(sheets)
        this.pelangganService = new PelangganService(sheets)
        this.kvaService       = new KvaService(sheets)
        this.babinService     = new BabinService(sheets)
        this.jamNyalaService  = new JamNyalaService(sheets)
        this.lossesService    = new LossesService(sheets)
        this.tunggakanService = new TunggakanService(sheets)
        this.p2tlService      = new P2tlService(sheets)

        // Modules
        this.kwhModule        = new KwhModule(new PenjualanKwhChart())
        this.pendapatanModule = new PendapatanBatamModule(new PendapatanBatamChart())
        this.golTarifModule   = new GolTarifBatamModule(new GolTarifBatamChart())
        this.rpkwhModule      = new RpkwhModule(new RpkwhChart())
        this.pelangganModule  = new PelangganModule(new PelangganChart())
        this.kvaModule        = new KvaModule(new KvaChart())
        this.babinModule      = new BabinModule(new BabinChart())
        this.jamNyalaModule   = new JamNyalaModule(new JamNyalaChart())
        this.lossesModule     = new LossesModule(new LossesChart())
        this.tunggakanModule  = new TunggakanModule(new TunggakanChart())
        this.p2tlModule       = new P2tlModule(new P2tlChart())

        // Data
        this.kwhData = this.pendapatanData = this.golTarifData = null
        this.rpkwhData = this.pelangganData = this.kvaData = null
        this.babinData = this.jamNyalaData = this.lossesData = null
        this.tunggakanData = this.p2tlData = null

        // State
        this.state = {
            global:     { month: "all", year: "2025" },
            bpp:        { type: "BPP PEMBANGKIT", month: "all" },
            penjualan:  { view: "bulanan", tarif: "JUMLAH", month: "all" },
            kwh:        { view: "bulanan" },
            pendapatan: { view: "bulanan", tarif: "JUMLAH" },
            golTarif:   { tahun: "2025", view: "bulanan", bulan: "11" },
            rpkwh:      { tarif: "JUMLAH", view: "bulanan", bulan: "11", tahun: "2025" },
            pelanggan:  { tarif: "JUMLAH", bulan: 11 },
            kva:        { tarif: "JUMLAH", bulan: 11, view: "bulanan" },
            babin:      { view: "bulanan", section: "babin" },
            jamNyala:   { tarif: "ALL" },
            losses:     { emin: "with" },
            tunggakan:  {},
            p2tl:       {}
        }

        this.labels = labels
    }

    async init() {
        try {
            console.log("Loading Dashboard...")

            await Promise.all([this.bppModule.load(), this.penjualanModule.load()])
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
            this.bppModule.initFilters(this.state.bpp, () => this.bppModule.render(this.state.bpp))
            this.penjualanModule.initFilters(this.state.penjualan, () => this.penjualanModule.render(this.state.penjualan))

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

            this.babinData = await this.babinService.getData()
            this.babinModule.render(this.babinData, this.state.babin)
            this.babinModule.initFilters(this.state.babin, () => this.babinModule.render(this.babinData, this.state.babin))

            this.jamNyalaData = await this.jamNyalaService.getData()
            this.jamNyalaModule.render(this.jamNyalaData, this.state.jamNyala)
            this.jamNyalaModule.initFilters(this.state.jamNyala, () => this.jamNyalaModule.render(this.jamNyalaData, this.state.jamNyala))

            this.lossesData = await this.lossesService.getData()
            this.lossesModule.render(this.lossesData, this.state.losses)
            this.lossesModule.initFilters(this.state.losses, () => this.lossesModule.render(this.lossesData, this.state.losses))

            this.tunggakanData = await this.tunggakanService.getData()
            this.tunggakanModule.render(this.tunggakanData, this.state.tunggakan)
            this.tunggakanModule.initFilters(this.state.tunggakan, () => this.tunggakanModule.render(this.tunggakanData, this.state.tunggakan))

            this.p2tlData = await this.p2tlService.getData()
            this.p2tlModule.render(this.p2tlData, this.state.p2tl)
            this.p2tlModule.initFilters(this.state.p2tl, () => this.p2tlModule.render(this.p2tlData, this.state.p2tl))

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
            this.state.bpp.month = e.target.value
            this.state.penjualan.month = e.target.value
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
        })
        document.getElementById("filterYear")?.addEventListener("change", e => {
            this.state.global.year = e.target.value
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
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