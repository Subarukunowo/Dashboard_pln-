import BppModule              from '../modules/BppModule.js'
import PenjualanModule        from '../modules/PenjualanModule.js'
import PendapatanBatamService from '../services/PendapatanBatamService.js'
import GolTarifBatamService   from '../services/GolTarifBatamService.js'
import RpkwhService           from '../services/RpkwhService.js'
import PelangganService       from '../services/PelangganService.js'
import KvaService             from '../services/KvaService.js'
import BabinService           from '../services/BabinService.js'
import JamNyalaService        from '../services/JamNyalaService.js'
import LossesService          from '../services/LossesService.js'
import TunggakanService       from '../services/TunggakanService.js'
import P2tlService            from '../services/P2tlService.js'

import PendapatanBatamModule  from '../modules/PendapatanBatamModule.js'
import GolTarifBatamModule    from '../modules/GolTarifBatamModule.js'
import RpkwhModule            from '../modules/RpkwhModule.js'
import PelangganModule        from '../modules/PelangganModule.js'
import KvaModule              from '../modules/KvaModule.js'
import BabinModule            from '../modules/BabinModule.js'
import JamNyalaModule         from '../modules/JamNyalaModule.js'
import LossesModule           from '../modules/LossesModule.js'
import TunggakanModule        from '../modules/TunggakanModule.js'
import P2tlModule             from '../modules/P2tlModule.js'
import ChatbotModule          from '../modules/ChatbotModule.js'

import PendapatanBatamChart   from '../charts/PendapatanBatamChart.js'
import GolTarifBatamChart     from '../charts/GolTarifBatamChart.js'
import RpkwhChart             from '../charts/RpkwhChart.js'
import PelangganChart         from '../charts/PelangganChart.js'
import KvaChart               from '../charts/KvaChart.js'
import BabinChart             from '../charts/BabinChart.js'
import JamNyalaChart          from '../charts/JamNyalaChart.js'
import LossesChart            from '../charts/LossesChart.js'
import TunggakanChart         from '../charts/TunggakanChart.js'
import P2tlChart              from '../charts/P2tlChart.js'
import { initStats }          from '../layout/UpdateStats.js'
import { onThemeChange }      from '../utils/ThemeChart.js'
import CustomSelect           from '../components/CustomSelect.js'

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
        this.babinModule = new BabinModule(
            new BabinChart(),
            (tahun) => this.babinService.getData(tahun)
        )
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
        this._initChatbotEvents()
        this._initClock()
        this._initGlobalFilters()

        // Upgrade semua <select> ke custom dropdown
        CustomSelect.initAll()

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
        // JamNyalaModule butuh init() eksplisit untuk load daftar golongan
        // (beda dari module lain yang datanya di-load lewat render() via fetcher).
        // state & callback dioper supaya chart utama ikut refresh setelah simpan di Edit Data.
        this.jamNyalaModule.init(this.state.jamNyala, () => this.jamNyalaModule.render(this.jamNyalaData, this.state.jamNyala))

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

        this._initStatsSwap()
        this._initThemeRerender()
        // Saat load awal mode all — stats semua tersembunyi
        this._showStatsForSection('all')
    }

    _initThemeRerender() {
        // Re-render semua chart aktif saat tema light/dark berubah
        onThemeChange(() => {
            // BPP
            this.bppModule.render(this.state.bpp)

            // Module-module dengan data
            const rerenderMap = [
                { key: 'pendapatanData', module: this.pendapatanModule, state: this.state.pendapatan },
                { key: 'golTarifData',   module: this.golTarifModule,   state: this.state.golTarif   },
                { key: 'rpkwhData',      module: this.rpkwhModule,      state: this.state.rpkwh      },
                { key: 'pelangganData',  module: this.pelangganModule,  state: this.state.pelanggan  },
                { key: 'kvaData',        module: this.kvaModule,        state: this.state.kva        },
                { key: 'babinData',      module: this.babinModule,      state: this.state.babin      },
                { key: 'jamNyalaData',   module: this.jamNyalaModule,   state: this.state.jamNyala   },
                { key: 'lossesData',     module: this.lossesModule,     state: this.state.losses     },
                { key: 'tunggakanData',  module: this.tunggakanModule,  state: this.state.tunggakan  },
                { key: 'p2tlData',       module: this.p2tlModule,       state: this.state.p2tl       },
            ]

            rerenderMap.forEach(({ key, module, state }) => {
                const data = this[key]
                if (data) {
                    try { module.render(data, state) } catch (e) { /* modul mungkin belum siap */ }
                }
            })

            // Penjualan — pakai state internal module
            if (this.penjualanModule.data) {
                try { this.penjualanModule.render(this.state.penjualan) } catch (e) { /**/ }
            }
        })
    }

    _initStatsSwap() {
        // Listen sectionChange dari sidebar.js — event dispatch SETELAH all-mode dihapus dari DOM
        document.addEventListener('sectionChange', (e) => {
            console.log('[Stats] sectionChange received:', e.detail.section, '| body.all-mode:', document.body.classList.contains('all-mode'))
            this._showStatsForSection(e.detail.section)
        })
    }

    _showStatsForSection(section) {
        console.log('[Stats] _showStatsForSection:', section)

        // Sembunyikan semua stat cards
        document.querySelectorAll('[data-stat-section]').forEach(el => {
            el.style.display = 'none'
        })
        console.log('[Stats] cards hidden, all-mode on body:', document.body.classList.contains('all-mode'))

        if (section === 'all') {
            console.log('[Stats] mode all — stats hidden')
            return
        }

        const statsKeyMap = {
            bpp:       'bpp',
            losses:    'losses',
            penjualan: 'penjualan',
            babin:     'babin',
            jamnyala:  'jamnyala',
            goltarif:  'goltarif',
            pendapatan:'pendapatan',
            rpkwh:     'rpkwh',
            pelanggan: 'pelanggan',
            kva:       'kva',
            tunggakan: 'tunggakan',
            p2tl:      'p2tl',
        }
        const statsSection = statsKeyMap[section]
        if (!statsSection) {
            console.warn('[Stats] no statsSection for:', section)
            return
        }

        // Tampilkan card untuk section ini
        const cards = document.querySelectorAll('[data-stat-section="' + statsSection + '"]')
        console.log('[Stats] showing', cards.length, 'cards for section:', statsSection)
        cards.forEach(el => {
            el.style.display = 'block'
            console.log('[Stats] card shown:', el.id || el.className)
        })

        // Fetch & render stats
        const year  = parseInt(this.state.global.year) || new Date().getFullYear()
        const month = new Date().getMonth() + 1
        console.log('[Stats] calling initStats(', statsSection, ', { year:', year, ', month:', month, '})')
        initStats(statsSection, { year, month })
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
            const month = e.target.value
            this.state.global.month    = month
            this.state.bpp.month       = month
            this.state.penjualan.month = month
            this.state.p2tl.bulan      = month
            this.state.losses.bulan    = month
            this.state.tunggakan.bulan = month
            this.state.babin.bulan     = month
            this.state.jamNyala.bulan  = month
            this.state.rpkwh.bulan     = month
            this.state.kva.bulan       = month === 'all' ? 11 : parseInt(month)
            this.state.pelanggan.bulan = month === 'all' ? 11 : parseInt(month)
            this.state.pendapatan.bulan = month
            this.state.golTarif.bulanRealisasi = month

            // Sync ke select DOM masing-masing modul
            this._syncSelectValue('filterBulanBpp',       month)
            this._syncSelectValue('filterBulanPenjualan', month)
            this._syncSelectValue('filterBulanP2tl',      month)
            this._syncSelectValue('filterBulanLosses',    month)
            this._syncSelectValue('filterBulanTunggakan', month)
            this._syncSelectValue('filterBulanBabin',     month)
            this._syncSelectValue('filterBulanJamNyala',  month)
            this._syncSelectValue('filterBulanRpkwh',     month)
            this._syncSelectValue('filterBulanKva',       month)
            this._syncSelectValue('filterBulanPelanggan', month)
            this._syncSelectValue('filterBulanPendapatan', month)
            this._syncSelectValue('filterBulanRealisasiGol', month)

            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)
            if (this.p2tlData)       this.p2tlModule.render(this.p2tlData, this.state.p2tl)
            if (this.lossesData)     this.lossesModule.render(this.lossesData, this.state.losses)
            if (this.tunggakanData)  this.tunggakanModule.render(this.tunggakanData, this.state.tunggakan)
            if (this.babinData)      this.babinModule.render(this.babinData, this.state.babin)
            if (this.jamNyalaData)   this.jamNyalaModule.render(this.jamNyalaData, this.state.jamNyala)
            if (this.rpkwhData)      this.rpkwhModule.render(this.rpkwhData, this.state.rpkwh)
            if (this.kvaData)        this.kvaModule.render(this.kvaData, this.state.kva)
            if (this.pelangganData)  this.pelangganModule.render(this.pelangganData, this.state.pelanggan)
            if (this.pendapatanData) this.pendapatanModule.render(this.pendapatanData, this.state.pendapatan)
            if (this.golTarifData)   this.golTarifModule.render(this.golTarifData, this.state.golTarif)
        })

        document.getElementById('filterYear')?.addEventListener('change', async e => {
            const year = parseInt(e.target.value)
            this.state.global.year = String(year)

            // Update state semua modul
            this.state.bpp.year         = year
            this.state.babin.tahun      = year
            this.state.jamNyala.tahun   = year
            this.state.losses.tahun     = year
            this.state.tunggakan.tahun  = year
            this.state.p2tl.tahun       = year

            // Sync ke select DOM
            this._syncSelectValue('filterTahunBabin',   String(year))
            this._syncSelectValue('muatTahunJamNyala',  String(year))

            // Re-fetch semua modul yang tahunnya berpengaruh
            await this.bppModule.load(year)
            this.bppModule.render(this.state.bpp)
            this.penjualanModule.render(this.state.penjualan)

            const reloads = [
                { key: 'jamNyalaData',  fetcher: () => this.jamNyalaService.getData(year),  module: this.jamNyalaModule,  state: this.state.jamNyala },
                { key: 'lossesData',    fetcher: () => this.lossesService.getData(year),    module: this.lossesModule,    state: this.state.losses },
                { key: 'tunggakanData', fetcher: () => this.tunggakanService.getData(year), module: this.tunggakanModule, state: this.state.tunggakan },
                { key: 'p2tlData',      fetcher: () => this.p2tlService.getData(year),      module: this.p2tlModule,      state: this.state.p2tl },
                { key: 'babinData',     fetcher: () => this.babinService.getData(year),     module: this.babinModule,     state: this.state.babin },
                { key: 'pelangganData', fetcher: () => this.pelangganService.getData(year), module: this.pelangganModule, state: this.state.pelanggan },
                { key: 'kvaData',       fetcher: () => this.kvaService.getData(year),       module: this.kvaModule,       state: this.state.kva },
                { key: 'rpkwhData',     fetcher: () => this.rpkwhService.getData(year),     module: this.rpkwhModule,     state: this.state.rpkwh },
                { key: 'pendapatanData',fetcher: () => this.pendapatanService.getData(year),module: this.pendapatanModule,state: this.state.pendapatan },
                { key: 'golTarifData',  fetcher: () => this.golTarifService.getData(year),  module: this.golTarifModule,  state: this.state.golTarif },
            ]

            reloads.forEach(cfg => {
                cfg.fetcher()
                    .then(data => {
                        if (!data) return
                        this[cfg.key] = data
                        cfg.module.render(data, cfg.state)
                    })
                    .catch(err => console.warn('[Dashboard] Gagal reload ' + cfg.key + ':', err))
            })
        })
    }

    /** Sync nilai select DOM tanpa trigger event */
    _syncSelectValue(id, value) {
        const el = document.getElementById(id)
        if (el) el.value = value
    }
}

document.addEventListener('DOMContentLoaded', () => new Dashboard().init())