import SheetsService from "../services/SheetsService.js"
import BppModule from "../modules/BppModule.js"
import PenjualanModule from "../modules/PenjualanModule.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PendapatanBatamService from "../services/PendapatanBatamService.js"
import PendapatanBatamChart from "../charts/PendapatanBatamChart.js"
import GolTarifBatamService from "../services/GolTarifBatamService.js"
import GolTarifBatamChart from "../charts/GolTarifBatamChart.js"
import RpkwhService from "../services/RpkwhService.js"
import RpkwhChart from "../charts/RpkwhChart.js"
import PelangganService from "../services/PelangganService.js"
import PenjualanKwhChart from "../charts/PenjualankwhChart.js"  
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
import ChatbotModule from "../modules/ChatbotModule.js"

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
       this.chatbot = new ChatbotModule();
        // State
        this.state = {
            global:     { month: "all", year: "2025" },
            bpp:        { type: "BPP PEMBANGKIT", month: "all" },
            penjualan:  { view: "bulanan", tarif: "JUMLAH", month: "all" },
            kwh:        { view: "bulanan" , bulan : "all"},
            pendapatan: { view: "bulanan", tarif: "JUMLAH", bulan: "all" },
            golTarif: { tahun: "2025", view: "bulanan", bulan: "11", bulanRealisasi: "all" },
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
        console.log("Loading Dashboard Assets...");

        // 1. Inisialisasi UI Statis & Chatbot (Harus Paling Atas)
        // Ini memastikan bubble bisa diklik meskipun data chart masih loading
        this._initChatbotEvents();
        this._initClock();
        this._initGlobalFilters();

        // 2. Load Core Modules (BPP & Penjualan)
        // Kita gunakan await di sini karena biasanya ini data utama
        await Promise.all([this.bppModule.load(), this.penjualanModule.load()]);
        
        this.bppModule.render(this.state.bpp);
        this.penjualanModule.render(this.state.penjualan);
        this.bppModule.initFilters(this.state.bpp, () => this.bppModule.render(this.state.bpp));
        this.penjualanModule.initFilters(this.state.penjualan, () => this.penjualanModule.render(this.state.penjualan));

        // 3. Load All Data Modules
        // Saya buatkan helper agar kodenya tidak menggunung ke bawah
        await this._loadDashboardData();

        console.log("Dashboard Fully Loaded.");

    } catch (err) {
        console.error("Dashboard Init Error:", err);
    }
}
/**
 * Menangani update jam secara terpisah
 */
_initClock() {
    this.updateClock();
    setInterval(() => this.updateClock(), 1000);
}

/**
 * Helper untuk memproses semua data service & render module
 */
async _loadDashboardData() {
    // Definisi pasangan Service -> Module -> State
    const dataConfigs = [
        { s: this.kwhService, m: this.kwhModule, st: this.state.kwh, key: 'kwhData' },
        { s: this.pendapatanService, m: this.pendapatanModule, st: this.state.pendapatan, key: 'pendapatanData' },
        { s: this.golTarifService, m: this.golTarifModule, st: this.state.golTarif, key: 'golTarifData' },
        { s: this.rpkwhService, m: this.rpkwhModule, st: this.state.rpkwh, key: 'rpkwhData' },
        { s: this.pelangganService, m: this.pelangganModule, st: this.state.pelanggan, key: 'pelangganData' },
        { s: this.kvaService, m: this.kvaModule, st: this.state.kva, key: 'kvaData' },
        { s: this.babinService, m: this.babinModule, st: this.state.babin, key: 'babinData' },
        { s: this.jamNyalaService, m: this.jamNyalaModule, st: this.state.jamNyala, key: 'jamNyalaData' },
        { s: this.lossesService, m: this.lossesModule, st: this.state.losses, key: 'lossesData' },
        { s: this.tunggakanService, m: this.tunggakanModule, st: this.state.tunggakan, key: 'tunggakanData' },
        { s: this.p2tlService, m: this.p2tlModule, st: this.state.p2tl, key: 'p2tlData' }
    ];

    // Eksekusi secara sekuensial agar tidak membombardir server sekaligus,
    // tapi tetap rapi dalam satu perulangan.
    for (const item of dataConfigs) {
        try {
            this[item.key] = await item.s.getData();
            item.m.render(this[item.key], item.st);
            item.m.initFilters(item.st, () => item.m.render(this[item.key], item.st));
        } catch (e) {
            console.warn(`Gagal memuat modul: ${item.key}`, e);
        }
    }
}
_initChatbotEvents() {
    // Gunakan listener tanpa 'onclick' di HTML untuk menghindari konflik
    const bubble = document.getElementById('chat-bubble');
    const sendBtn = document.getElementById('send-btn');
    const closeBtn = document.querySelector('.close-btn');

    if (bubble) {
        bubble.addEventListener('click', (e) => this.chatbot.toggle(e));
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => this.chatbot.toggle(e));
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', (e) => this.chatbot.send(e));
    }

    this.chatbot.input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') this.chatbot.send(e);
    });
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