import BppService from '../services/BppService.js'
import BppChart   from '../charts/BppChart.js'
import { upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const CSRF      = () => document.querySelector('meta[name="csrf-token"]')?.content
const API_BPP   = '/api/v1/fact/bpp'
const API_HJR   = '/api/v1/fact/hjr'
const BULAN_NAMA = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

/**
 * Mapping kategori display → tipe_bpp untuk API
 * Sesuaikan dengan nilai kolom tipe_bpp di tabel fact_bpp
 */
const KATEGORI_TIPE = {
    'BPP PEMBANGKIT':          'pembangkit',
    'BPP S.D TRANSMISI':       'transmisi',
    'BPP S.D DISTRIBUSI TM':   'distribusi_tm',
    'BPP S.D DISTRIBUSI TR':   'distribusi_tr',
}

export default class BppModule {
    constructor() {
        this.service      = new BppService()
        this.chart        = new BppChart('bppComparisonChart')
        this.data         = null
        this.hjrData      = null
        this.hjrCalculated = null
        this._tahun       = TAHUN_INI
        this._isFallback  = false
    }

    async load(tahun = TAHUN_INI) {
        [this.data, this.hjrData, this.hjrCalculated] = await Promise.all([
            Promise.resolve(this.service.getAllBpp(tahun)),
            Promise.resolve(this.service.getHjrAvg(tahun)),
            Promise.resolve(this.service.getHjrCalculated(tahun)),
        ])
        // Deteksi fallback: cek tahun_efektif dari response jika ada
        const anyKey = this.data ? Object.keys(this.data)[0] : null
        const sample = anyKey ? this.data[anyKey] : null
        this._isFallback = sample?.tahun_efektif
            ? Number.parseInt(sample.tahun_efektif) < tahun
            : false
        this._tahun = tahun
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanBpp option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    _getHjrKey(bppType) {
        if (bppType.includes('DISTRIBUSI TR')) return 'TR'
        if (bppType.includes('DISTRIBUSI TM')) return 'TM'
        return null
    }

    render(state) {
        if (!this.data) return

        this._toggleFallbackWarning()

        const data = this.data[state.type]
        if (!data) return

        const { kons, intl, hjr, displayLabels, mode } = this._prepareDisplayData(data, state.month, state.type)

        this._renderTable(kons, intl, hjr, displayLabels)
        this.chart.render(kons, intl, null, displayLabels, mode)
        this._renderHjrComparison(displayLabels, mode, state.month)
        this._renderEditTable(state)
    }

    _toggleFallbackWarning() {
        const warning = document.getElementById('bppFallbackWarning')
        if (warning) warning.style.display = this._isFallback ? 'inline-block' : 'none'
    }

    _prepareDisplayData(data, month, type) {
        const labels = this._getLabels()
        const hjr = this._getHjrSeries(type)

        return {
            kons: this._applyMonthFilter([...data.konsolidasi], month),
            intl: this._applyMonthFilter([...data.internal], month),
            hjr: hjr ? this._applyMonthFilter(hjr, month) : null,
            displayLabels: month === 'all' ? labels : [labels[Number.parseInt(month)]],
            mode: month === 'all' ? 'trend' : 'single',
        }
    }

    _getHjrSeries(type) {
        const hjrKey = this._getHjrKey(type)
        return hjrKey && this.hjrData?.[hjrKey]
            ? [...this.hjrData[hjrKey]]
            : null
    }

    _applyMonthFilter(values, month) {
        if (month === 'all') return [...values]

        const index = Number.parseInt(month)
        return [values[index]]
    }

    _renderHjrComparison(displayLabels, mode, month) {
        const bppTr = this.data['BPP S.D DISTRIBUSI TR']
        const hjrCal = this.hjrCalculated?.calculated ?? null

        if (!bppTr || !hjrCal) {
            this.chart.renderHjrComparison(null, null, displayLabels, 'HJR', mode)
            return
        }

        const bppTrArr = this._applyMonthFilter([...bppTr.konsolidasi], month)
        const hjrCalArr = this._applyMonthFilter([...hjrCal], month)
        const isFallback = this.hjrCalculated?.is_fallback ?? false
        const hjrLabel = isFallback
            ? `HJR (${this.hjrCalculated.tahun_efektif}) — Pendapatan/Penjualan`
            : 'HJR (Pendapatan ÷ Penjualan)'

        this.chart.renderHjrComparison(bppTrArr, hjrCalArr, displayLabels, hjrLabel, mode)
    }

    initFilters(state, onChange) {
        document.getElementById('filterBpp')?.addEventListener('change', e => {
            state.type = e.target.value
            onChange()
        })
        document.getElementById('filterBulanBpp')?.addEventListener('change', e => {
            state.month = e.target.value
            onChange()
        })

        const filterBpp = document.getElementById('filterBpp')
        if (filterBpp) filterBpp.value = state.type

        this._initCrud(state, onChange)
    }

    _renderTable(konsolidasi, internal, hjr, labels) {
        const tbody = document.getElementById('bppTableBody')
        if (!tbody) return

        const fmt = v => (v !== null && v !== undefined)
            ? v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '—'

        const hasHjr = hjr !== null

        const thead = tbody.closest('table')?.querySelector('thead tr')
        if (thead) {
            thead.innerHTML = `
                <th style="padding:10px;">Bulan</th>
                <th style="padding:10px; color:var(--accent-orange);">BPP Konsolidasi</th>
                <th style="padding:10px; color:var(--accent-green);">BPP Internal</th>
                ${hasHjr ? '<th style="padding:10px; color:var(--accent-blue, #4fc3f7);">HJR Rata-rata</th>' : ''}
                <th style="padding:10px;">Selisih</th>
            `
        }

        tbody.innerHTML = labels.map((label, i) => {
            const hasInt = internal[i] !== null && internal[i] !== undefined
            const delta  = hasInt ? konsolidasi[i] - internal[i] : null

            let deltaColor = '#888'
            let deltaText  = '—'
            if (delta !== null) {
                deltaColor = delta > 0 ? '#00ff88' : '#f44336'
                deltaText  = (delta > 0 ? '+' : '') + fmt(delta)
            }

            const hjrCell = hasHjr
                ? `<td style="padding:8px; color:var(--accent-blue, #4fc3f7);">${fmt(hjr[i])}</td>`
                : ''

            return `<tr>
                <td style="padding:8px;">${label}</td>
                <td style="padding:8px; color:var(--accent-orange);">${fmt(konsolidasi[i])}</td>
                <td style="padding:8px; color:var(--accent-green);">${hasInt ? fmt(internal[i]) : '—'}</td>
                ${hjrCell}
                <td style="padding:8px; color:${deltaColor};">${deltaText}</td>
            </tr>`
        }).join('')
    }

    // ── Edit Tables ──────────────────────────────────────────────────────────

    _renderEditTable(state) {
        this._renderEditBpp()   // tidak perlu state — selalu tampilkan semua kategori
        this._renderEditHjr()   // selalu tampilkan TR & TM
    }

    /** Tabel edit BPP — semua 4 kategori dengan tab switcher */
    _renderEditBpp() {
        const container = document.getElementById('bppEditBppContainer')
        const tabBar    = document.getElementById('bppTabBar')
        if (!container || !this.data) return

        const isReadOnly = this._tahun < TAHUN_INI

        const KATEGORI = [
            { key: 'BPP PEMBANGKIT',        tipe: 'pembangkit',    label: 'Pembangkit' },
            { key: 'BPP S.D TRANSMISI',      tipe: 'transmisi',     label: 'Transmisi' },
            { key: 'BPP S.D DISTRIBUSI TM',  tipe: 'distribusi_tm', label: 'Distribusi TM' },
            { key: 'BPP S.D DISTRIBUSI TR',  tipe: 'distribusi_tr', label: 'Distribusi TR' },
        ]

        if (!this._activeBppTab) this._activeBppTab = KATEGORI[0].tipe
        const activeTab = this._activeBppTab

        if (tabBar) {
            tabBar.innerHTML = KATEGORI.map(k => `
                <button data-bpptab="${k.tipe}"
                    style="padding:5px 14px;border-radius:6px;border:none;cursor:pointer;font-size:0.72rem;font-weight:600;
                           background:${k.tipe === activeTab ? 'var(--accent-orange,#ff9800)' : 'rgba(255,255,255,0.06)'};
                           color:${k.tipe === activeTab ? '#000' : '#888'};transition:all 0.15s;">
                    ${k.label}
                </button>`).join('')

            // FIX: bind arrow function agar `this` tidak hilang
            tabBar.querySelectorAll('[data-bpptab]').forEach(btn => {
                btn.addEventListener('click', () => {
                    this._activeBppTab = btn.dataset.bpptab
                    this._renderEditBpp()
                })
            })
        }

        const activeKat = KATEGORI.find(k => k.tipe === activeTab) ?? KATEGORI[0]
        const d    = this.data[activeKat.key]

        // FIX: debug jika data tidak ditemukan
        if (!d) {
            console.warn('[BppModule] Data tidak ditemukan untuk key:', activeKat.key, '| Keys tersedia:', Object.keys(this.data))
            container.innerHTML = `<p style="color:#f44336;padding:12px;">Data untuk "${activeKat.label}" tidak ditemukan.</p>`
            return
        }

        const kons = d.konsolidasi ?? new Array(12).fill(null)
        const intl = d.internal    ?? new Array(12).fill(null)
        const dis  = isReadOnly ? 'opacity:0.45;pointer-events:none;' : ''

        const rows = BULAN_NAMA.map((bln, i) => {
            const mkInput = (val, field, color) =>
                `<input type="number" data-row="${i}" data-field="${field}"
                    value="${val ?? ''}" placeholder="—" ${isReadOnly ? 'disabled' : ''}
                    style="width:110px;padding:4px 6px;border-radius:4px;
                           border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                           background:#1a1a1a;color:${isReadOnly ? '#444' : color};
                           font-size:0.75rem;text-align:right;" />`

            return `<tr style="border-bottom:1px solid #2a2a2a;${dis}">
                <td style="padding:7px 10px;color:#888;font-size:0.75rem;white-space:nowrap;">${bln}</td>
                <td style="padding:4px 6px;">${mkInput(kons[i], 'nilai_konsolidasi', 'var(--accent-orange,#ff9800)')}</td>
                <td style="padding:4px 6px;">${mkInput(intl[i], 'nilai_internal', 'var(--accent-green,#00ff88)')}</td>
                <td style="padding:4px 6px;text-align:center;">
                    <button id="btnSaveBpp_${activeKat.tipe}_${i}"
                        ${isReadOnly ? 'disabled' : ''}
                        style="padding:3px 10px;border-radius:4px;border:none;
                               background:${isReadOnly ? '#2a2a2a' : '#ff9800'};
                               color:${isReadOnly ? '#444' : '#000'};
                               font-size:0.7rem;cursor:${isReadOnly ? 'not-allowed' : 'pointer'};font-weight:600;">
                        ${isReadOnly ? '🔒' : 'Simpan'}
                    </button>
                </td>
            </tr>`
        }).join('')

        container.innerHTML = `
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;background:#252525;z-index:1;">
                    <tr style="font-size:0.65rem;text-transform:uppercase;">
                        <th style="padding:8px 10px;color:#888;text-align:left;">Bulan</th>
                        <th style="padding:8px 10px;color:var(--accent-orange,#ff9800);text-align:left;">BPP Konsolidasi</th>
                        <th style="padding:8px 10px;color:var(--accent-green,#00ff88);text-align:left;">BPP Internal</th>
                        <th style="padding:8px 10px;color:#aaa;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`

        if (isReadOnly) return

        BULAN_NAMA.forEach((_, i) => {
            const btn = document.getElementById(`btnSaveBpp_${activeKat.tipe}_${i}`)
            if (!btn) return
            btn.addEventListener('click', async () => {
                btn.textContent = '⏳'
                btn.disabled    = true
                const inputs = container.querySelectorAll(`input[data-row="${i}"]`)
                const values = {}
                inputs.forEach(inp => { values[inp.dataset.field] = inp.value === '' ? null : Number.parseFloat(inp.value) })
                try {
                    const base = { tahun: this._tahun, bulan: i + 1, jenis_bpp: activeKat.tipe, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }
                    if (values.nilai_konsolidasi !== null)
                        await upsert(API_BPP, { tahun: this._tahun, bulan: i + 1, jenis_bpp: activeKat.tipe, versi: 'konsolidasi' },
                            { ...base, versi: 'konsolidasi', nilai_rpkwh: values.nilai_konsolidasi })
                    if (values.nilai_internal !== null)
                        await upsert(API_BPP, { tahun: this._tahun, bulan: i + 1, jenis_bpp: activeKat.tipe, versi: 'internal' },
                            { ...base, versi: 'internal', nilai_rpkwh: values.nilai_internal })
                    btn.textContent = '✓ OK'; btn.style.background = '#00ff88'
                } catch {
                    btn.textContent = '✗ Error'; btn.style.background = '#f44336'
                } finally {
                    btn.disabled = false
                    setTimeout(() => { btn.textContent = 'Simpan'; btn.style.background = '#ff9800' }, 2000)
                }
            })
        })
    }

    /** Tabel edit HJR — selalu tampilkan TR dan TM, tidak bergantung filter kategori */
    _renderEditHjr() {
        const container = document.getElementById('bppEditHjrContainer')
        const tabBar    = document.getElementById('hjrTabBar')
        if (!container) return

        const isReadOnly = this._tahun < TAHUN_INI
        if (!this._activeHjrTab) this._activeHjrTab = 'TR'
        const activeTab = this._activeHjrTab

        // Tab bar ke elemen LUAR
        if (tabBar) {
            tabBar.innerHTML = ['TR', 'TM'].map(t => `
                <button data-hjrtab="${t}"
                    style="padding:5px 14px;border-radius:6px;border:none;cursor:pointer;font-size:0.72rem;font-weight:600;
                           background:${t === activeTab ? 'var(--accent-blue,#4fc3f7)' : 'rgba(255,255,255,0.06)'};
                           color:${t === activeTab ? '#000' : '#888'};transition:all 0.15s;">
                    HJR ${t}
                </button>`).join('')

            tabBar.querySelectorAll('[data-hjrtab]').forEach(btn => {
                btn.addEventListener('click', () => {
                    this._activeHjrTab = btn.dataset.hjrtab
                    this._renderEditHjr()
                })
            })
        }

        const hjrArr = this.hjrData?.[activeTab] ?? new Array(12).fill(null)
        const dis    = isReadOnly ? 'opacity:0.45;pointer-events:none;' : ''

        const rows = BULAN_NAMA.map((bln, i) => `
            <tr style="border-bottom:1px solid #2a2a2a;${dis}">
                <td style="padding:7px 10px;color:#888;font-size:0.75rem;white-space:nowrap;">${bln}</td>
                <td style="padding:4px 6px;">
                    <input type="number" data-row="${i}"
                        value="${hjrArr[i] ?? ''}" placeholder="—" ${isReadOnly ? 'disabled' : ''}
                        style="width:110px;padding:4px 6px;border-radius:4px;
                               border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                               background:#1a1a1a;color:${isReadOnly ? '#444' : 'var(--accent-blue,#4fc3f7)'};
                               font-size:0.75rem;text-align:right;" />
                </td>
                <td style="padding:4px 6px;text-align:center;">
                    <button id="btnSaveHjr_${activeTab}_${i}"
                        ${isReadOnly ? 'disabled' : ''}
                        style="padding:3px 10px;border-radius:4px;border:none;
                               background:${isReadOnly ? '#2a2a2a' : '#ff9800'};
                               color:${isReadOnly ? '#444' : '#000'};
                               font-size:0.7rem;cursor:${isReadOnly ? 'not-allowed' : 'pointer'};font-weight:600;">
                        ${isReadOnly ? '🔒' : 'Simpan'}
                    </button>
                </td>
            </tr>`).join('')

        container.innerHTML = `
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;background:#252525;z-index:1;">
                    <tr style="font-size:0.65rem;text-transform:uppercase;">
                        <th style="padding:8px 10px;color:#888;text-align:left;">Bulan</th>
                        <th style="padding:8px 10px;color:var(--accent-blue,#4fc3f7);text-align:left;">HJR Rata-rata (${activeTab})</th>
                        <th style="padding:8px 10px;color:#aaa;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`

        if (isReadOnly) return

        BULAN_NAMA.forEach((_, i) => {
            const btn = document.getElementById(`btnSaveHjr_${activeTab}_${i}`)
            if (!btn) return
            btn.addEventListener('click', async () => {
                btn.textContent = '⏳'; btn.disabled = true
                const inp   = container.querySelector(`input[data-row="${i}"]`)
                const nilai = inp?.value === '' ? null : Number.parseFloat(inp?.value)
                try {
                    if (nilai !== null)
                        await upsert(API_HJR,
                            { tahun: this._tahun, bulan: i + 1, tegangan: activeTab, is_triwulan: 0, is_akumulasi: 0 },
                            { tahun: this._tahun, bulan: i + 1, tegangan: activeTab, is_triwulan: 0, is_akumulasi: 0, nilai_rpkwh: nilai, sumber: 'manual' })
                    btn.textContent = '✓ OK'; btn.style.background = '#00ff88'
                } catch {
                    btn.textContent = '✗ Error'; btn.style.background = '#f44336'
                } finally {
                    btn.disabled = false
                    setTimeout(() => { btn.textContent = 'Simpan'; btn.style.background = '#ff9800' }, 2000)
                }
            })
        })
    }

    // ── CRUD Modal ───────────────────────────────────────────────────────────

    _initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatBpp')
        const modalHapus = document.getElementById('modalHapusBpp')
        const modalBuat  = document.getElementById('modalBuatBpp')

        // --- Muat Data ---
        const btnMuat = document.getElementById('btnMuatBpp')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatBpp')?.addEventListener('click', () => {
                if (modalMuat) modalMuat.style.display = 'none'
            })
            document.getElementById('btnKonfirmasiMuatBpp')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('muatTahunBpp')?.value)
                if (modalMuat) modalMuat.style.display = 'none'
                await this.load(tahun)
                onChange()
            })
        }

        // --- Hapus Data ---
        const btnHapus = document.getElementById('btnHapusBpp')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusBpp')?.addEventListener('click', () => {
                if (modalHapus) modalHapus.style.display = 'none'
            })
            document.getElementById('btnKonfirmasiHapusBpp')?.addEventListener('click', async () => {
                const tahun    = document.getElementById('hapusTahunBpp')?.value
                const tipeBpp  = document.getElementById('hapusTipeBpp')?.value
                const tipeData = document.getElementById('hapusTipeDataBpp')?.value
                if (!confirm(`Hapus data BPP tahun ${tahun}? Tindakan ini tidak dapat dibatalkan.`)) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    const params = { tahun }
                    if (tipeBpp  !== 'semua') params.tipe_bpp  = tipeBpp
                    if (tipeData !== 'semua') params.kategori  = tipeData
                    await deleteByFilter(API_BPP, params)
                    await this.load(this._tahun)
                    onChange()
                } catch {
                    alert('Gagal menghapus data BPP')
                }
            })
        }

        // --- Buat Data (create tahun kosong) ---
        const btnBuat = document.getElementById('btnBuatBpp')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatBpp')?.addEventListener('click', () => {
                if (modalBuat) modalBuat.style.display = 'none'
            })
            document.getElementById('btnKonfirmasiBuatBpp')?.addEventListener('click', async () => {
                const tahun   = Number.parseInt(document.getElementById('buatTahunBpp')?.value)
                const tipeBpp = document.getElementById('buatTipeBpp')?.value
                const csrf    = CSRF()
                if (modalBuat) modalBuat.style.display = 'none'

                // Buat 12 record kosong (bulan 1-12) untuk kategori konsolidasi & internal
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    for (const kat of ['konsolidasi', 'internal']) {
                        try {
                            await fetch(API_BPP, {
                                method: 'POST', headers: heads,
                                body: JSON.stringify({ tahun, bulan, tipe_bpp: tipeBpp, kategori: kat, nilai: 0, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                            })
                        } catch { ok = false }
                    }
                }
                alert(ok ? `Data BPP tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                await this.load(tahun)
                onChange()
            })
        }
    }
}