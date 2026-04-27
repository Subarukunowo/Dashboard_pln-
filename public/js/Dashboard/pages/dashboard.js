import SheetsService          from '../services/SheetsService.js'
import BppModule               from '../modules/BppModule.js'
import PenjualanModule         from '../modules/PenjualanModule.js'
import RealisasiKWHService     from '../services/RealisasiKWHService.js'
import PendapatanBatamService  from '../services/PendapatanBatamService.js'
import GolTarifBatamService    from '../services/GolTarifBatamService.js'
import RpkwhService            from '../services/RpkwhService.js'
import PelangganService        from '../services/PelangganService.js'
import KvaService              from '../services/KvaService.js'
import BabinService            from '../services/BabinService.js'
import JamNyalaService         from '../services/JamNyalaService.js'
import LossesService           from '../services/LossesService.js'
import TunggakanService        from '../services/TunggakanService.js'
import P2tlService             from '../services/P2tlService.js'

import KwhModule               from '../modules/KwhModule.js'
import PendapatanBatamModule   from '../modules/PendapatanBatamModule.js'
import GolTarifBatamModule     from '../modules/GolTarifBatamModule.js'
import RpkwhModule             from '../modules/RpkwhModule.js'
import PelangganModule         from '../modules/PelangganModule.js'
import KvaModule               from '../modules/KvaModule.js'
import BabinModule             from '../modules/BabinModule.js'
import JamNyalaModule          from '../modules/JamNyalaModule.js'
import LossesModule            from '../modules/LossesModule.js'
import TunggakanModule         from '../modules/TunggakanModule.js'
import P2tlModule              from '../modules/P2tlModule.js'
import ChatbotModule           from '../modules/ChatbotModule.js'

import PenjualanKwhChart       from '../charts/PenjualankwhChart.js'
import PendapatanBatamChart    from '../charts/PendapatanBatamChart.js'
import GolTarifBatamChart      from '../charts/GolTarifBatamChart.js'
import RpkwhChart              from '../charts/RpkwhChart.js'
import PelangganChart          from '../charts/PelangganChart.js'
import KvaChart                from '../charts/KvaChart.js'
import BabinChart              from '../charts/BabinChart.js'
import JamNyalaChart           from '../charts/JamNyalaChart.js'
import LossesChart             from '../charts/LossesChart.js'
import TunggakanChart          from '../charts/TunggakanChart.js'
import P2tlChart               from '../charts/P2tlChart.js'

import { initStats }           from '../layout/UpdateStats.js'

// Baca SCRIPT_URL dari meta tag (dari .env via config())
const SCRIPT_URL = document.querySelector('meta[name="gas-data-url"]')?.content ?? ''

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

        this.bppModule       = new BppModule(sheets, labels)
        this.penjualanModule = new PenjualanModule(sheets, labels)

        this.kwhService        = new RealisasiKWHService(sheets)
        this.pendapatanService = new PendapatanBatamService(sheets)
        this.golTarifService   = new GolTarifBatamService(sheets)
        this.rpkwhService      = new RpkwhService(sheets)
        this.pelangganService  = new PelangganService(sheets)
        this.kvaService        = new KvaService(sheets)
        this.babinService      = new BabinService(sheets)
        this.jamNyalaService   = new JamNyalaService(sheets)
        this.lossesService     = new LossesService(sheets)
        this.tunggakanService  = new TunggakanService(sheets)
        this.p2tlService       = new P2tlService(sheets)

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

        this.kwhData = this.pendapatanData = this.golTarifData = null
        this.rpkwhData = this.pelangganData = this.kvaData     = null
        this.babinData = this.jamNyalaData  = this.lossesData  = null
        this.tunggakanData = this.p2tlData                     = null

        this.chatbot = new ChatbotModule()

        this.state = {
            global:     { month: 'all', year: '2025' },
            bpp:        { type: 'BPP PEMBANGKIT', month: 'all' },
            penjualan:  { view: 'bulanan', tarif: 'JUMLAH', month: 'all' },
            kwh:        { view: 'bulanan', bulan: 'all' },
            pendapatan: { view: 'bulanan', tarif: 'JUMLAH', bulan: 'all' },
            golTarif:   { tahun: '2025', view: 'bulanan', bulan: '11', bulanRealisasi: 'all' },
            rpkwh:      { tarif: 'JUMLAH', view: 'bulanan', bulan: '11', tahun: '2025' },
            pelanggan:  { tarif: 'JUMLAH', bulan: 11 },
            kva:        { tarif: 'JUMLAH', bulan: 11, view: 'bulanan' },
            babin:      { view: 'bulanan', section: 'babin', tahun: '2025', bulan: 'all' },
            jamNyala:   { tarif: 'ALL', bulan: 'all' },
            losses:     { emin: 'with' },
            tunggakan:  {},
            p2tl:       { bulan: 'all' },
        }

        this.labels = labels
    }

    async init() {
        try {
            console.log('Loading Dashboard Assets...')

            this._initChatbotEvents()
            this._initClock()
            this._initGlobalFilters()

            await Promise.all([this.bppModule.load(), this.penjualanModule.load()])
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
            this.bppModule.initFilters(this.state.bpp, () => this.bppModule.render(this.state.bpp))
            this.penjualanModule.initFilters(this.state.penjualan, () => this.penjualanModule.render(this.state.penjualan))

            await this._loadDashboardData()

            if (document.readyState === 'complete') {
                this._finalizeInit()
            } else {
                window.addEventListener('load', () => {
                    requestAnimationFrame(() => requestAnimationFrame(() => this._finalizeInit()))
                })
            }

            console.log('Dashboard Fully Loaded.')
        } catch (err) {
            console.error('Dashboard Init Error:', err)
        }
    }

    _finalizeInit() {
        document.getElementById('dashboardLayout')?.classList.add('loaded')
        this._initChartsSafe()
    }

    _initChartsSafe() {
        document.querySelectorAll('.chart-wrapper').forEach(wrapper => {
            const canvas = wrapper.querySelector('canvas')
            if (!canvas) return
            if (wrapper.offsetWidth > 0) {
                this._initChartOnCanvas(canvas)
            } else {
                const observer = new ResizeObserver(entries => {
                    if (entries[0]?.contentRect.width > 0) {
                        observer.disconnect()
                        this._initChartOnCanvas(canvas)
                    }
                })
                observer.observe(wrapper)
            }
        })
    }

    _initChartOnCanvas(canvas) {
        if (canvas._chartInitialized) return
        canvas._chartInitialized = true
    }

    async _loadDashboardData() {
        const configs = [
            { s: this.kwhService,        m: this.kwhModule,        st: this.state.kwh,        key: 'kwhData' },
            { s: this.pendapatanService,  m: this.pendapatanModule,  st: this.state.pendapatan, key: 'pendapatanData' },
            { s: this.golTarifService,   m: this.golTarifModule,   st: this.state.golTarif,   key: 'golTarifData' },
            { s: this.rpkwhService,      m: this.rpkwhModule,      st: this.state.rpkwh,      key: 'rpkwhData' },
            { s: this.pelangganService,  m: this.pelangganModule,  st: this.state.pelanggan,  key: 'pelangganData' },
            { s: this.kvaService,        m: this.kvaModule,        st: this.state.kva,        key: 'kvaData' },
            { s: this.babinService,      m: this.babinModule,      st: this.state.babin,      key: 'babinData' },
            { s: this.jamNyalaService,   m: this.jamNyalaModule,   st: this.state.jamNyala,   key: 'jamNyalaData' },
            { s: this.lossesService,     m: this.lossesModule,     st: this.state.losses,     key: 'lossesData' },
            { s: this.tunggakanService,  m: this.tunggakanModule,  st: this.state.tunggakan,  key: 'tunggakanData' },
            { s: this.p2tlService,       m: this.p2tlModule,       st: this.state.p2tl,       key: 'p2tlData' },
        ]

        const results = await Promise.allSettled(configs.map(cfg => cfg.s.getData()))

        results.forEach((result, i) => {
            const cfg = configs[i]
            if (result.status === 'fulfilled') {
                this[cfg.key] = result.value
                cfg.m.render(this[cfg.key], cfg.st)
                cfg.m.initFilters(cfg.st, () => cfg.m.render(this[cfg.key], cfg.st))
            } else {
                console.warn(`Gagal memuat: ${cfg.key}`, result.reason)
            }
        })

        // ── Stats Cards (Total Customer, MoM, YoY) ───────────────────────
        // Dipanggil setelah semua data load — pelangganService sudah punya
        // cache dari getData() di atas, jadi tidak ada request dobel ke Sheets.
        const now = new Date()
        initStats(this.pelangganService, {
            year:  now.getFullYear(),
            month: now.getMonth() + 1,  // 1-based
        })
    }

    _initClock() {
        this.updateClock()
        setInterval(() => this.updateClock(), 1000)
    }

    _initChatbotEvents() {
        const bubble   = document.getElementById('chat-bubble')
        const sendBtn  = document.getElementById('send-btn')
        const closeBtn = document.querySelector('.close-btn')

        bubble?.addEventListener('click',   e => this.chatbot.toggle(e))
        closeBtn?.addEventListener('click', e => this.chatbot.toggle(e))
        sendBtn?.addEventListener('click',  e => this.chatbot.send(e))
        this.chatbot.input?.addEventListener('keypress', e => {
            if (e.key === 'Enter') this.chatbot.send(e)
        })
    }

    _initGlobalFilters() {
        document.getElementById('filterMonth')?.addEventListener('change', e => {
            this.state.global.month    = e.target.value
            this.state.bpp.month       = e.target.value
            this.state.penjualan.month = e.target.value
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
        })
        document.getElementById('filterYear')?.addEventListener('change', e => {
            this.state.global.year = e.target.value
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
        })
    }

    updateClock() {
        const el = document.getElementById('clock')
        if (!el) return
        const now = new Date()
        el.textContent = now.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        }) + ' | ' + now.toLocaleTimeString()
    }
}

document.addEventListener('DOMContentLoaded', () => new Dashboard().init())