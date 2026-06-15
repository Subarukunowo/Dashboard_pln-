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
        this.service  = new BppService()
        this.chart    = new BppChart('bppComparisonChart')
        this.data     = null
        this.hjrData  = null
        this._tahun   = TAHUN_INI
        this._isFallback = false
    }

    async load(tahun = TAHUN_INI) {
        [this.data, this.hjrData] = await Promise.all([
            this.service.getAllBpp(tahun),
            this.service.getHjrAvg(tahun),
        ])
        // Deteksi fallback: cek tahun_efektif dari response jika ada
        const anyKey = this.data ? Object.keys(this.data)[0] : null
        const sample = anyKey ? this.data[anyKey] : null
        this._isFallback = sample?.tahun_efektif
            ? parseInt(sample.tahun_efektif) < tahun
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

        // Tampilkan / sembunyikan warning fallback
        const warning = document.getElementById('bppFallbackWarning')
        if (warning) warning.style.display = this._isFallback ? 'inline-block' : 'none'

        const data = this.data[state.type]
        if (!data) return

        let kons          = [...data.konsolidasi]
        let intl          = [...data.internal]
        let displayLabels = this._getLabels()
        let mode          = 'trend'

        const hjrKey = this._getHjrKey(state.type)
        let hjr = hjrKey && this.hjrData?.[hjrKey]
            ? [...this.hjrData[hjrKey]]
            : null

        if (state.month !== 'all') {
            const index   = parseInt(state.month)
            kons          = [kons[index]]
            intl          = [intl[index]]
            hjr           = hjr ? [hjr[index]] : null
            displayLabels = [displayLabels[index]]
            mode          = 'single'
        }

        this._renderTable(kons, intl, hjr, displayLabels)
        this.chart.render(kons, intl, hjr, displayLabels, mode)
        this._renderEditTable(state)
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
            const hasInt     = internal[i] !== null && internal[i] !== undefined
            const delta      = hasInt ? konsolidasi[i] - internal[i] : null
            const deltaColor = delta === null ? '#888' : delta > 0 ? '#00ff88' : '#f44336'
            const deltaText  = delta === null ? '—' : (delta > 0 ? '+' : '') + fmt(delta)

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
        this._renderEditBpp(state)
        this._renderEditHjr(state)
    }

    /** Tabel edit BPP Konsolidasi & Internal per bulan */
    _renderEditBpp(state) {
        const container = document.getElementById('bppEditBppContainer')
        if (!container || !this.data) return

        const isReadOnly = this._tahun < TAHUN_INI
        const tipeBpp    = KATEGORI_TIPE[state.type] ?? 'pembangkit'
        const d          = this.data[state.type]
        const kons       = d?.konsolidasi ?? new Array(12).fill(null)
        const intl       = d?.internal    ?? new Array(12).fill(null)

        const disStyle   = isReadOnly
            ? 'opacity:0.45; pointer-events:none;'
            : ''

        const rows = BULAN_NAMA.map((bln, i) => {
            const mkInput = (val, field, color) => `
                <input type="number"
                    data-row="${i}" data-field="${field}"
                    value="${val ?? ''}" placeholder="—"
                    ${isReadOnly ? 'disabled' : ''}
                    style="width:110px; padding:4px 6px; border-radius:4px;
                           border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                           background:#1a1a1a; color:${isReadOnly ? '#444' : color};
                           font-size:0.75rem; text-align:right;" />`

            return `<tr style="border-bottom:1px solid #2a2a2a; ${disStyle}">
                <td style="padding:7px 10px; color:#888; font-size:0.75rem; white-space:nowrap;">${bln}</td>
                <td style="padding:4px 6px;">${mkInput(kons[i], 'nilai_konsolidasi', 'var(--accent-orange, #ff9800)')}</td>
                <td style="padding:4px 6px;">${mkInput(intl[i], 'nilai_internal',    'var(--accent-green, #00ff88)')}</td>
                <td style="padding:4px 6px; text-align:center;">
                    <button id="btnSaveBpp_${i}"
                        data-row="${i}"
                        ${isReadOnly ? 'disabled' : ''}
                        style="padding:3px 10px; border-radius:4px; border:none;
                               background:${isReadOnly ? '#2a2a2a' : '#ff9800'};
                               color:${isReadOnly ? '#444' : '#000'};
                               font-size:0.7rem; cursor:${isReadOnly ? 'not-allowed' : 'pointer'}; font-weight:600;">
                        ${isReadOnly ? '🔒' : 'Simpan'}
                    </button>
                </td>
            </tr>`
        }).join('')

        container.innerHTML = `
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase;">
                        <th style="padding:8px 10px; color:#888; text-align:left;">Bulan</th>
                        <th style="padding:8px 10px; color:var(--accent-orange, #ff9800); text-align:left;">BPP Konsolidasi</th>
                        <th style="padding:8px 10px; color:var(--accent-green, #00ff88); text-align:left;">BPP Internal</th>
                        <th style="padding:8px 10px; color:#aaa; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`

        if (isReadOnly) return

        BULAN_NAMA.forEach((_, i) => {
            const btn = document.getElementById(`btnSaveBpp_${i}`)
            if (!btn) return
            btn.addEventListener('click', async () => {
                btn.textContent = '⏳'
                btn.disabled    = true

                const rowInputs = container.querySelectorAll(`input[data-row="${i}"]`)
                const values    = {}
                rowInputs.forEach(inp => {
                    values[inp.dataset.field] = inp.value === '' ? null : parseFloat(inp.value)
                })

                try {
                    const base = { tahun: this._tahun, bulan: i + 1, tipe_bpp: tipeBpp, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }
                    if (values.nilai_konsolidasi !== null) {
                        await upsert(API_BPP, { ...base, kategori: 'konsolidasi' },
                            { ...base, kategori: 'konsolidasi', nilai: values.nilai_konsolidasi })
                    }
                    if (values.nilai_internal !== null) {
                        await upsert(API_BPP, { ...base, kategori: 'internal' },
                            { ...base, kategori: 'internal', nilai: values.nilai_internal })
                    }
                    btn.textContent      = '✓ OK'
                    btn.style.background = '#00ff88'
                } catch {
                    btn.textContent      = '✗ Error'
                    btn.style.background = '#f44336'
                } finally {
                    btn.disabled = false
                    setTimeout(() => {
                        btn.textContent      = 'Simpan'
                        btn.style.background = '#ff9800'
                    }, 2000)
                }
            })
        })
    }

    /** Tabel edit HJR rata-rata per bulan */
    _renderEditHjr(state) {
        const container = document.getElementById('bppEditHjrContainer')
        if (!container) return

        const hjrKey = this._getHjrKey(state.type)
        if (!hjrKey) {
            container.innerHTML = '<div style="padding:12px; color:#555; font-size:0.75rem;">HJR tidak tersedia untuk kategori ini.</div>'
            return
        }

        const isReadOnly = this._tahun < TAHUN_INI
        const hjrArr     = this.hjrData?.[hjrKey] ?? new Array(12).fill(null)

        const disStyle = isReadOnly ? 'opacity:0.45; pointer-events:none;' : ''

        const rows = BULAN_NAMA.map((bln, i) => `
            <tr style="border-bottom:1px solid #2a2a2a; ${disStyle}">
                <td style="padding:7px 10px; color:#888; font-size:0.75rem; white-space:nowrap;">${bln}</td>
                <td style="padding:4px 6px;">
                    <input type="number"
                        data-row="${i}" data-field="nilai_hjr"
                        value="${hjrArr[i] ?? ''}" placeholder="—"
                        ${isReadOnly ? 'disabled' : ''}
                        style="width:110px; padding:4px 6px; border-radius:4px;
                               border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                               background:#1a1a1a; color:${isReadOnly ? '#444' : 'var(--accent-blue, #4fc3f7)'};
                               font-size:0.75rem; text-align:right;" />
                </td>
                <td style="padding:4px 6px; text-align:center;">
                    <button id="btnSaveHjr_${i}"
                        data-row="${i}"
                        ${isReadOnly ? 'disabled' : ''}
                        style="padding:3px 10px; border-radius:4px; border:none;
                               background:${isReadOnly ? '#2a2a2a' : '#ff9800'};
                               color:${isReadOnly ? '#444' : '#000'};
                               font-size:0.7rem; cursor:${isReadOnly ? 'not-allowed' : 'pointer'}; font-weight:600;">
                        ${isReadOnly ? '🔒' : 'Simpan'}
                    </button>
                </td>
            </tr>`).join('')

        container.innerHTML = `
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase;">
                        <th style="padding:8px 10px; color:#888; text-align:left;">Bulan</th>
                        <th style="padding:8px 10px; color:var(--accent-blue, #4fc3f7); text-align:left;">HJR Rata-rata</th>
                        <th style="padding:8px 10px; color:#aaa; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`

        if (isReadOnly) return

        BULAN_NAMA.forEach((_, i) => {
            const btn = document.getElementById(`btnSaveHjr_${i}`)
            if (!btn) return
            btn.addEventListener('click', async () => {
                btn.textContent = '⏳'
                btn.disabled    = true
                const inp  = container.querySelector(`input[data-row="${i}"]`)
                const nilai = inp?.value === '' ? null : parseFloat(inp?.value)

                try {
                    if (nilai !== null) {
                        await upsert(API_HJR,
                            { tahun: this._tahun, bulan: i + 1, kategori: hjrKey, is_triwulan: 0, is_akumulasi: 0 },
                            { tahun: this._tahun, bulan: i + 1, kategori: hjrKey, is_triwulan: 0, is_akumulasi: 0, nilai_hjr: nilai, sumber: 'manual' }
                        )
                    }
                    btn.textContent      = '✓ OK'
                    btn.style.background = '#00ff88'
                } catch {
                    btn.textContent      = '✗ Error'
                    btn.style.background = '#f44336'
                } finally {
                    btn.disabled = false
                    setTimeout(() => {
                        btn.textContent      = 'Simpan'
                        btn.style.background = '#ff9800'
                    }, 2000)
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
                const tahun = parseInt(document.getElementById('muatTahunBpp')?.value)
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
                const tahun   = parseInt(document.getElementById('buatTahunBpp')?.value)
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
