import SheetsService        from '../services/SheetsService.js'
import BppModule            from '../modules/BppModule.js'
import PenjualanModule      from '../modules/PenjualanModule.js'
import RealisasiKWHService  from '../services/RealisasiKWHService.js'
import PendapatanBatamService from '../services/PendapatanBatamService.js'
import GolTarifBatamService from '../services/GolTarifBatamService.js'
import RpkwhService         from '../services/RpkwhService.js'
import PelangganService     from '../services/PelangganService.js'
import KvaService           from '../services/KvaService.js'
import BabinService         from '../services/BabinService.js'
import JamNyalaService      from '../services/JamNyalaService.js'
import LossesService        from '../services/LossesService.js'
import TunggakanService     from '../services/TunggakanService.js'
import P2tlService          from '../services/P2tlService.js'

import KwhModule            from '../modules/KwhModule.js'
import PendapatanBatamModule from '../modules/PendapatanBatamModule.js'
import GolTarifBatamModule  from '../modules/GolTarifBatamModule.js'
import RpkwhModule          from '../modules/RpkwhModule.js'
import PelangganModule      from '../modules/PelangganModule.js'
import KvaModule            from '../modules/KvaModule.js'
import BabinModule          from '../modules/BabinModule.js'
import JamNyalaModule       from '../modules/JamNyalaModule.js'
import LossesModule         from '../modules/LossesModule.js'
import TunggakanModule      from '../modules/TunggakanModule.js'
import P2tlModule           from '../modules/P2tlModule.js'
import ChatbotModule        from '../modules/ChatbotModule.js'

import PenjualanKwhChart    from '../charts/PenjualankwhChart.js'
import PendapatanBatamChart from '../charts/PendapatanBatamChart.js'
import GolTarifBatamChart   from '../charts/GolTarifBatamChart.js'
import RpkwhChart           from '../charts/RpkwhChart.js'
import PelangganChart       from '../charts/PelangganChart.js'
import KvaChart             from '../charts/KvaChart.js'
import BabinChart           from '../charts/BabinChart.js'
import JamNyalaChart        from '../charts/JamNyalaChart.js'
import LossesChart          from '../charts/LossesChart.js'
import TunggakanChart       from '../charts/TunggakanChart.js'
import P2tlChart            from '../charts/P2tlChart.js'
import ScreenshotProtection from '../utils/ScreenshotProtection.js'
import { initStats }        from '../layout/UpdateStats.js'

import DashboardCache, { CacheManager } from '../cache/DashboardCache.js'

// ─────────────────────────────────────────────────────────────────────────────
const SCRIPT_URL          = document.querySelector('meta[name="gas-data-url"]')?.content ?? ''
const REFRESH_INTERVAL_MS = 10 * 60 * 1000

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

        this.state = {
            global     : { month: 'all', year: '2025' },
            bpp        : { type: 'BPP PEMBANGKIT', month: 'all' },
            penjualan  : { view: 'bulanan', tarif: 'JUMLAH', month: 'all' },
            kwh        : { view: 'bulanan', bulan: 'all' },
            pendapatan : { view: 'bulanan', tarif: 'JUMLAH', bulan: 'all' },
            golTarif   : { tahun: '2025', view: 'bulanan', bulan: '11', bulanRealisasi: 'all' },
            rpkwh      : { tarif: 'JUMLAH', view: 'bulanan', bulan: '11', tahun: '2025' },
            pelanggan  : { tarif: 'JUMLAH', bulan: 11 },
            kva        : { tarif: 'JUMLAH', bulan: 11, view: 'bulanan' },
            babin      : { view: 'bulanan', section: 'babin', tahun: '2025', bulan: 'all' },
            jamNyala   : { tarif: 'ALL', bulan: 'all' },
            losses     : { emin: 'with' },
            tunggakan  : {},
            p2tl       : { bulan: 'all' },
        }

        this.labels       = labels
        this.chatbot      = new ChatbotModule()
        this.cacheManager = new CacheManager()
    }

    async init() {
        try {
            console.log('[Dashboard] Init...')
            ScreenshotProtection.init()
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

            console.log('[Dashboard] Fully Loaded.')
        } catch (err) {
            console.error('[Dashboard] Init Error:', err)
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

    // ── Helper: validasi data sebelum render ──────────────────────────────────
    _isValidData(data) {
        if (data === undefined || data === null) return false
        if (Array.isArray(data) && data.length === 0) return false
        if (typeof data === 'object' && Object.keys(data).length === 0) return false
        return true
    }

    async _loadDashboardData() {
        const configs = [
            { key: 'kwhData',        fetcher: () => this.kwhService.getData(),        module: this.kwhModule,        state: this.state.kwh },
            { key: 'pendapatanData', fetcher: () => this.pendapatanService.getData(), module: this.pendapatanModule, state: this.state.pendapatan },
            { key: 'golTarifData',   fetcher: () => this.golTarifService.getData(),   module: this.golTarifModule,   state: this.state.golTarif },
            { key: 'rpkwhData',      fetcher: () => this.rpkwhService.getData(),      module: this.rpkwhModule,      state: this.state.rpkwh },
            { key: 'pelangganData',  fetcher: () => this.pelangganService.getData(),  module: this.pelangganModule,  state: this.state.pelanggan },
            { key: 'kvaData',        fetcher: () => this.kvaService.getData(),        module: this.kvaModule,        state: this.state.kva },
            { key: 'babinData',      fetcher: () => this.babinService.getData(),      module: this.babinModule,      state: this.state.babin },
            { key: 'jamNyalaData',   fetcher: () => this.jamNyalaService.getData(),   module: this.jamNyalaModule,   state: this.state.jamNyala },
            { key: 'lossesData',     fetcher: () => this.lossesService.getData(),     module: this.lossesModule,     state: this.state.losses },
            { key: 'tunggakanData',  fetcher: () => this.tunggakanService.getData(),  module: this.tunggakanModule,  state: this.state.tunggakan },
            { key: 'p2tlData',       fetcher: () => this.p2tlService.getData(),       module: this.p2tlModule,       state: this.state.p2tl },
        ]

        const inits = configs.map(cfg => {
            const cache = new DashboardCache(cfg.key, cfg.fetcher, {
                refreshIntervalMs : REFRESH_INTERVAL_MS,
                onUpdate: (freshData) => {
                    if (!this._isValidData(freshData)) return
                    console.info(`[Dashboard] Live update: ${cfg.key}`)
                    this[cfg.key] = freshData
                    cfg.module.render(freshData, cfg.state)
                },
            })
            this.cacheManager.register(cfg.key, cache)
            return cache.init().then(data => ({ cfg, data }))
        })

        const results = await Promise.allSettled(inits)

        results.forEach(result => {
            if (result.status === 'fulfilled') {
                const { cfg, data } = result.value

                // ── Guard: jangan render kalau data tidak valid ───────────────
                if (!this._isValidData(data)) {
                    console.warn(`[Dashboard] Data tidak valid untuk "${cfg.key}", skip render`)
                    return
                }

                this[cfg.key] = data
                cfg.module.render(data, cfg.state)
                cfg.module.initFilters(cfg.state, () => cfg.module.render(this[cfg.key], cfg.state))
            } else {
                console.warn('[Dashboard] Gagal memuat:', result.reason)
            }
        })

        const now = new Date()
        initStats(this.pelangganService, {
            year  : now.getFullYear(),
            month : now.getMonth() + 1,
        })
    }

    _initClock() {
        this.updateClock()
        setInterval(() => this.updateClock(), 1000)
    }

    updateClock() {
        const el = document.getElementById('clock')
        if (!el) return
        const now = new Date()
        el.textContent = now.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        }) + ' | ' + now.toLocaleTimeString()
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
}

document.addEventListener('DOMContentLoaded', () => new Dashboard().init())