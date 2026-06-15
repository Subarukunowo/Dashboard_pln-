import BppModule             from '../modules/BppModule.js'
import PenjualanModule       from '../modules/PenjualanModule.js'
import PendapatanBatamService from '../services/PendapatanBatamService.js'
import GolTarifBatamService  from '../services/GolTarifBatamService.js'
import RpkwhService          from '../services/RpkwhService.js'
import PelangganService      from '../services/PelangganService.js'
import KvaService            from '../services/KvaService.js'
import BabinService          from '../services/BabinService.js'
import JamNyalaService       from '../services/JamNyalaService.js'
import LossesService         from '../services/LossesService.js'
import TunggakanService      from '../services/TunggakanService.js'
import P2tlService           from '../services/P2tlService.js'

import PendapatanBatamModule from '../modules/PendapatanBatamModule.js'
import GolTarifBatamModule   from '../modules/GolTarifBatamModule.js'
import RpkwhModule           from '../modules/RpkwhModule.js'
import PelangganModule       from '../modules/PelangganModule.js'
import KvaModule             from '../modules/KvaModule.js'
import BabinModule           from '../modules/BabinModule.js'
import JamNyalaModule        from '../modules/JamNyalaModule.js'
import LossesModule          from '../modules/LossesModule.js'
import TunggakanModule       from '../modules/TunggakanModule.js'
import P2tlModule            from '../modules/P2tlModule.js'
import ChatbotModule         from '../modules/ChatbotModule.js'

import PendapatanBatamChart  from '../charts/PendapatanBatamChart.js'
import GolTarifBatamChart    from '../charts/GolTarifBatamChart.js'
import RpkwhChart            from '../charts/RpkwhChart.js'
import PelangganChart        from '../charts/PelangganChart.js'
import KvaChart              from '../charts/KvaChart.js'
import BabinChart            from '../charts/BabinChart.js'
import JamNyalaChart         from '../charts/JamNyalaChart.js'
import LossesChart           from '../charts/LossesChart.js'
import TunggakanChart        from '../charts/TunggakanChart.js'
import P2tlChart             from '../charts/P2tlChart.js'
import ScreenshotProtection  from '../utils/ScreenshotProtection.js'
import { initStats }         from '../layout/UpdateStats.js'

const CURRENT_YEAR = new Date().getFullYear()

class Dashboard {
    constructor() {
        const labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

        this.bppModule       = new BppModule()
        this.penjualanModule = new PenjualanModule(null, labels)

        this.pendapatanService = new PendapatanBatamService()
        this.golTarifService   = new GolTarifBatamService()
        this.rpkwhService      = new RpkwhService()
        this.pelangganService  = new PelangganService()
        this.kvaService        = new KvaService()
        this.babinService      = new BabinService()
        this.jamNyalaService   = new JamNyalaService()
        this.lossesService     = new LossesService()
        this.tunggakanService  = new TunggakanService()
        this.p2tlService       = new P2tlService()

        this.pendapatanModule = new PendapatanBatamModule(new PendapatanBatamChart())
        this.golTarifModule   = new GolTarifBatamModule(new GolTarifBatamChart())
        this.rpkwhModule      = new RpkwhModule(new RpkwhChart(), this.rpkwhService)
        this.pelangganModule  = new PelangganModule(new PelangganChart(), this.pelangganService)
        this.kvaModule        = new KvaModule(new KvaChart())
        this.babinModule      = new BabinModule(new BabinChart())
        this.jamNyalaModule   = new JamNyalaModule(new JamNyalaChart())
        this.lossesModule     = new LossesModule(new LossesChart())
        this.tunggakanModule  = new TunggakanModule(new TunggakanChart(), this.tunggakanService)
        this.p2tlModule       = new P2tlModule(new P2tlChart())

        this.state = {
            global    : { month: 'all', year: String(CURRENT_YEAR) },
            bpp       : { type: 'BPP PEMBANGKIT', month: 'all', year: CURRENT_YEAR },
            penjualan : { view: 'bulanan', tarif: 'JUMLAH', month: 'all' },
            pendapatan: { view: 'bulanan', tarif: 'JUMLAH', bulan: 'all' },
            golTarif  : { tahun: String(CURRENT_YEAR), view: 'bulanan', bulan: '11', bulanRealisasi: 'all' },
            rpkwh     : { tarif: null, view: 'bulanan', bulan: '11', tahun: String(CURRENT_YEAR), kwhBulan: 'all', kwhView: 'bulanan' },
            pelanggan : { tarif: 'JUMLAH', bulan: 11 },
            kva       : { tarif: 'JUMLAH', bulan: 11, view: 'bulanan' },
            babin     : { view: 'bulanan', section: 'babin', tahun: CURRENT_YEAR, bulan: 'all' },
            jamNyala  : { tarif: 'ALL', bulan: 'all' },
            losses    : { emin: 'with' },
            tunggakan : {},
            p2tl      : { bulan: 'all' },
        }

        this.chatbot = new ChatbotModule()
    }

    async init() {
        ScreenshotProtection.init()
        this._initChatbotEvents()
        this._initClock()
        this._initGlobalFilters()

        document.getElementById('dashboardLayout')?.classList.add('loaded')

        this.bppModule.load(this.state.bpp.year).then(() => {
            this.bppModule.render(this.state.bpp)
            this.bppModule.initFilters(this.state.bpp, () => this.bppModule.render(this.state.bpp))
        })

        this.penjualanModule.load(CURRENT_YEAR).then(() => {
            this.penjualanModule.render(this.state.penjualan)
            this.penjualanModule.initFilters(this.state.penjualan, () => this.penjualanModule.render(this.state.penjualan))
        })

        this._loadAllModules()
    }

    _loadAllModules() {
        const configs = [
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

        configs.forEach(cfg => {
            cfg.fetcher()
                .then(data => {
                    if (!data || (Array.isArray(data) && !data.length)) {
                        console.warn(`[Dashboard] Tidak ada data: ${cfg.key}`)
                        return
                    }
                    this[cfg.key] = data
                    cfg.module.render(data, cfg.state)
                    cfg.module.initFilters(cfg.state, () => cfg.module.render(this[cfg.key], cfg.state))
                })
                .catch(err => console.warn(`[Dashboard] Gagal load ${cfg.key}:`, err))
        })

        const now = new Date()
        initStats(this.pelangganService, { year: now.getFullYear(), month: now.getMonth() + 1 })
    }

    _initClock() {
        this.updateClock()
        setInterval(() => this.updateClock(), 1000)
    }

    updateClock() {
        const el = document.getElementById('clock')
        if (!el) return
        const now    = new Date()
        const locale = document.documentElement.lang === 'en' ? 'en-US' : 'id-ID'
        el.textContent = now.toLocaleDateString(locale, {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        }) + ' | ' + now.toLocaleTimeString(locale)
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

        document.getElementById('filterYear')?.addEventListener('change', async e => {
            const year = parseInt(e.target.value)
            this.state.global.year = String(year)
            this.state.bpp.year    = year

            await this.bppModule.load(year)
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
        })
    }
}

document.addEventListener('DOMContentLoaded', () => new Dashboard().init())