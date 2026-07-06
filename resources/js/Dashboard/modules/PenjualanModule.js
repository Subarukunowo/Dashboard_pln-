import PenjualanService from '../services/PenjualanService.js'
import { chartColors, getChartDefaults, onThemeChange } from '../utils/ThemeChart.js'

const BULAN_LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
const GRUP_ORDER   = ['S', 'R', 'B', 'I', 'P', 'M']
const GRUP_LABEL   = { S: 'Sosial', R: 'Rumah Tangga', B: 'Bisnis', I: 'Industri', P: 'Publik', M: 'Multiguna' }

const getGrup = (t) => {
    if (t === 'JUMLAH' || t.startsWith('JUMLAH-')) return '__JUMLAH__'
    const m = t.match(/^([A-Z])/)
    return m?.[1] ?? 'LAIN'
}

export default class PenjualanModule {
    constructor(sheetsService, labels) {
        this.service = new PenjualanService()
        this.data    = null
        this.labels  = labels ?? BULAN_LABELS
        this._chart  = null
    }

    async load(tahun = new Date().getFullYear()) {
        this.data = await this.service.getKinerjaData(tahun)

        const warning = document.getElementById('penjualanWarning')
        if (warning) warning.style.display = this.data?.is_fallback ? 'inline-block' : 'none'

        if (!this._themeUnsubscribe) {
            this._themeUnsubscribe = onThemeChange(() => {
                if (this._lastState) this._renderChart(
                    this._lastTarget, this._lastRealisasi,
                    this._lastLabels,  this._lastView, this._lastIsPersen
                )
            })
        }
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanPenjualan option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    /** Ambil { target, realisasi } sesuai kombinasi tarif + view yang dipilih */
    _resolveSeries(view, tarif) {
        if (tarif !== 'JUMLAH') {
            const d = this.data.perTarif?.[tarif]
            if (!d) return null
            return {
                target:    view === 'akumulasi' ? (d.targetAkum    ?? []) : (d.target    ?? []),
                realisasi: view === 'akumulasi' ? (d.realisasiAkum ?? []) : (d.realisasi ?? []),
            }
        }

        switch (view) {
            case 'akumulasi':
                return {
                    target:    this.data.akumulasi?.target    ?? [],
                    realisasi: this.data.akumulasi?.realisasi ?? [],
                }
            case 'persenBulanan':
                return { target: [], realisasi: this.data.persenBulanan ?? [] }
            case 'persenAkumulasi':
                return { target: [], realisasi: this.data.persenAkumulasi ?? [] }
            default:
                return {
                    target:    this.data.bulanan?.target    ?? [],
                    realisasi: this.data.bulanan?.realisasi ?? [],
                }
        }
    }

    render(state) {
        if (!this.data) return

        const { view, tarif, month } = state
        const labels = this._getLabels()

        const series = this._resolveSeries(view, tarif)
        if (!series) return
        const { target, realisasi } = series

        const isPersen       = view.includes('persen')
        let displayLabels    = [...labels]
        let displayTarget    = target
        let displayRealisasi = realisasi

        if (month !== 'all') {
            const idx        = Number.parseInt(month)
            displayLabels    = [labels[idx]]
            displayTarget    = [target[idx]    ?? 0]
            displayRealisasi = [realisasi[idx] ?? 0]
        }

        this._renderChart(displayTarget, displayRealisasi, displayLabels, view, isPersen)
        this._renderTable(displayLabels, displayTarget, displayRealisasi, isPersen)
        this._renderEditTable(tarif, this.data.tahun_efektif ?? new Date().getFullYear())
    }

    initFilters(state, onChange) {
        document.getElementById('filterViewPenjualan')?.addEventListener('change',  e => { state.view  = e.target.value; onChange() })
        document.getElementById('filterBulanPenjualan')?.addEventListener('change', e => { state.month = e.target.value; onChange() })

        // populate tarif filter dengan optgroup
        const selTarif = document.getElementById('filterTarif')
        if (selTarif?.options.length === 0 && this.data?.perTarif) {
            const optAll = document.createElement('option')
            optAll.value = 'JUMLAH'; optAll.textContent = 'Semua'
            selTarif.appendChild(optAll)

            const tarifs   = Object.keys(this.data.perTarif)
            const grouped  = {}
            const jumlahItems = {}
            tarifs.forEach(t => {
                const key = getGrup(t)
                if (key === '__JUMLAH__') {
                    jumlahItems[t.replace('JUMLAH-', '')] = t
                } else {
                    if (!grouped[key]) grouped[key] = []
                    grouped[key].push(t)
                }
            })

            GRUP_ORDER.forEach(key => {
                if (!grouped[key]?.length && !jumlahItems[key]) return
                const grp = document.createElement('optgroup')
                grp.label = GRUP_LABEL[key] ?? key
                ;(grouped[key] ?? []).forEach(t => {
                    const opt = document.createElement('option')
                    opt.value = t; opt.textContent = t
                    grp.appendChild(opt)
                })
                if (jumlahItems[key]) {
                    const opt = document.createElement('option')
                    opt.value = jumlahItems[key]; opt.textContent = jumlahItems[key]
                    grp.appendChild(opt)
                }
                selTarif.appendChild(grp)
            })

            selTarif.value = state.tarif || 'JUMLAH'
        }

        selTarif?.addEventListener('change', e => { state.tarif = e.target.value; onChange() })

        // Tombol muat data
        const btnMuat  = document.getElementById('btnMuatPenjualan')
        const modal    = document.getElementById('modalMuatPenjualan')
        btnMuat?.addEventListener('click',  () => { if (modal) modal.style.display = 'flex' })
        document.getElementById('btnBatalMuatPenjualan')?.addEventListener('click', () => { if (modal) modal.style.display = 'none' })
        document.getElementById('btnKonfirmasiMuatPenjualan')?.addEventListener('click', async () => {
            const tahun = Number.parseInt(document.getElementById('muatTahunPenjualan')?.value)
            if (modal) modal.style.display = 'none'
            await this.load(tahun)
            onChange()
        })

        // Tombol hapus data
        const modalHapus = document.getElementById('modalHapusPenjualan')
        document.getElementById('btnHapusPenjualan')?.addEventListener('click',        () => { if (modalHapus) modalHapus.style.display = 'flex' })
        document.getElementById('btnBatalHapusPenjualan')?.addEventListener('click',   () => { if (modalHapus) modalHapus.style.display = 'none' })
        document.getElementById('btnKonfirmasiHapusPenjualan')?.addEventListener('click', async () => {
            const tahun = document.getElementById('hapusTahunPenjualan')?.value
            const tipe  = document.getElementById('hapusTipePenjualan')?.value
            if (!confirm(`Hapus data penjualan tahun ${tahun} (${tipe})? Tindakan ini tidak dapat dibatalkan.`)) return
            if (modalHapus) modalHapus.style.display = 'none'
            try {
                const csrf   = document.querySelector('meta[name="csrf-token"]')?.content
                const params = new URLSearchParams({ tahun })
                if (tipe !== 'semua') params.append('tipe', tipe)
                const res = await fetch(`/api/v1/fact/penjualan?${params}`, {
                    method: 'DELETE',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                })
                if (!res.ok) throw new Error(`Gagal menghapus data penjualan (HTTP ${res.status})`)
                await this.load(this.data?.tahun_efektif ?? new Date().getFullYear())
                onChange()
            } catch { alert('Gagal menghapus data penjualan') }
        })

        // Tombol buat data
        const modalBuat = document.getElementById('modalBuatPenjualan')
        document.getElementById('btnBuatPenjualan')?.addEventListener('click',        () => { if (modalBuat) modalBuat.style.display = 'flex' })
        document.getElementById('btnBatalBuatPenjualan')?.addEventListener('click',   () => { if (modalBuat) modalBuat.style.display = 'none' })
        document.getElementById('btnKonfirmasiBuatPenjualan')?.addEventListener('click', async () => {
            const tahun = Number.parseInt(document.getElementById('buatTahunPenjualan')?.value)
            const tipe  = document.getElementById('buatTipePenjualan')?.value
            if (modalBuat) modalBuat.style.display = 'none'
            const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
            const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
            let ok = true
            for (let bulan = 1; bulan <= 12; bulan++) {
                try {
                    const res = await fetch('/api/v1/fact/penjualan', {
                        method: 'POST', headers: heads,
                        body: JSON.stringify({ tahun, bulan, tipe, sub_golongan_kode: 'JUMLAH', golongan_kode: 'TOTAL', nilai_kwh: 0, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                    })
                    if (!res.ok) ok = false
                } catch { ok = false }
            }
            alert(ok ? `Data Penjualan tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
            await this.load(tahun)
            onChange()
        })
    }

    _renderChart(target, realisasi, labels, view, isPersen) {
        const ctx = document.getElementById('penjualanChart')
        if (!ctx) return
        if (this._chart) { this._chart.destroy(); this._chart = null }

        this._lastTarget    = target
        this._lastRealisasi = realisasi
        this._lastLabels    = labels
        this._lastView      = view
        this._lastIsPersen  = isPersen

        const isLine   = view === 'akumulasi' || view === 'persenAkumulasi'
        const c        = chartColors()
        const yFmt     = isPersen ? v => v.toFixed(1) + '%' : undefined
        const defaults = getChartDefaults(yFmt)

        this._chart = new Chart(ctx, {
            type: isLine ? 'line' : 'bar',
            data: {
                labels,
                datasets: [
                    ...(target.length > 0 && !isPersen ? [{
                        label: 'Target', data: target,
                        backgroundColor: '#ff9800', borderColor: '#ff9800',
                        borderWidth: 1, fill: false, tension: 0.4,
                    }] : []),
                    {
                        label: isPersen ? '% Realisasi' : 'Realisasi', data: realisasi,
                        backgroundColor: '#00ff88', borderColor: '#00ff88',
                        borderWidth: 2, tension: 0.4, fill: false,
                    },
                ],
            },
            options: {
                ...defaults,
                plugins: {
                    ...defaults.plugins,
                    datalabels: {
                        display: true,
                        color:   c.datalabel,
                        font:    { size: 8, weight: '600' },
                        anchor:  isLine ? 'top' : 'end',
                        align:   isLine ? 'top' : 'end',
                        offset:  2,
                        formatter: (v) => {
                            if (v == null) return ''
                            if (isPersen) return v.toFixed(1) + '%'
                            const abs = Math.abs(v)
                            if (abs >= 1_000_000_000) return (v / 1_000_000_000).toFixed(1) + 'M'
                            if (abs >= 1_000_000)     return (v / 1_000_000).toFixed(0) + 'jt'
                            return Number(v).toLocaleString('id-ID')
                        },
                    },
                },
            },
        })
    }

    _renderTable(labels, target, realisasi, isPersen) {
        const tbody = document.getElementById('penjualanTableBody')
        if (!tbody) return

        const fmt = v => {
            if (v === null || v === undefined) return '—'
            return isPersen ? v.toFixed(2) + '%' : (v / 1_000_000).toFixed(2) + ' jt kWh'
        }

        tbody.innerHTML = labels.map((bln, i) => {
            const t = target[i]    ?? 0
            const r = realisasi[i] ?? 0
            const d = r - t
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            const sign  = d > 0 ? '+' : ''
            const deltaText = d === 0 ? '—' : sign + fmt(d)
            return `<tr>
                <td style="padding:8px 10px; color:#888;">${bln}</td>
                <td style="padding:8px 10px; color:var(--accent-orange); text-align:right;">${fmt(t)}</td>
                <td style="padding:8px 10px; color:var(--accent-green); text-align:right;">${fmt(r)}</td>
                <td style="padding:8px 10px; color:${color}; font-weight:600; text-align:right;">${deltaText}</td>
            </tr>`
        }).join('')
    }

    _renderEditTable(tarif, tahun) {
        const container = document.getElementById('penjualanEditContainer')
        if (!container || !this.data) return

        const d = tarif === 'JUMLAH'
            ? { target: this.data.bulanan?.target ?? [], realisasi: this.data.bulanan?.realisasi ?? [] }
            : this.data.perTarif?.[tarif]
        if (!d) return

        const labels     = this._getLabels()
        const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content
        const isReadOnly = tahun < new Date().getFullYear()
        const disStyle   = isReadOnly ? 'opacity:0.45; pointer-events:none;' : ''

        const rows = labels.map((bln, i) => {
            const tVal = d.target?.[i]
            const rVal = d.realisasi?.[i]
            const mkInput = (val, tipeV, color) => `
                <input type="number" data-bulan="${i+1}" data-tipe="${tipeV}"
                    value="${val ?? ''}" placeholder="—"
                    ${isReadOnly ? 'disabled' : ''}
                    style="width:120px; padding:4px 6px; border-radius:4px;
                           border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                           background:#1a1a1a; color:${isReadOnly ? '#444' : color};
                           font-size:0.75rem; text-align:right;" />`
            return `<tr style="border-bottom:1px solid #2a2a2a; ${disStyle}">
                <td style="padding:7px 10px; color:#888; font-size:0.75rem; white-space:nowrap;">${bln}</td>
                <td style="padding:4px 6px;">${mkInput(tVal, 'target', '#ff9800')}</td>
                <td style="padding:4px 6px;">${mkInput(rVal, 'realisasi', '#00ff88')}</td>
                <td style="padding:4px 6px; text-align:center;">
                    ${isReadOnly
                        ? '<span style="font-size:0.7rem;color:#444;">🔒</span>'
                        : `<button data-bulan="${i+1}" data-tahun="${tahun}"
                                style="padding:3px 10px; border-radius:4px; border:none; background:#ff9800;
                                       color:#000; font-size:0.7rem; cursor:pointer; font-weight:600;">
                                Simpan
                           </button>`
                    }
                </td>
            </tr>`
        }).join('')

        container.innerHTML = `
            <table style="width:100%; border-collapse:collapse;">
                <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                    <tr style="font-size:0.65rem; text-transform:uppercase;">
                        <th style="padding:10px; color:#888; text-align:left;">Bulan</th>
                        <th style="padding:10px; color:#ff9800; text-align:right;">Target (kWh)</th>
                        <th style="padding:10px; color:#00ff88; text-align:right;">Realisasi (kWh)</th>
                        <th style="padding:10px; color:#aaa; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`

        if (isReadOnly) return

        container.removeEventListener('click', container._saveHandler)
        container._saveHandler = async (e) => {
            const btn = e.target.closest('button[data-bulan]')
            if (!btn) return
            const bulan  = Number.parseInt(btn.dataset.bulan)
            const tahunV = Number.parseInt(btn.dataset.tahun)

            btn.textContent = '⏳'; btn.disabled = true
            const inputs = container.querySelectorAll(`input[data-bulan="${bulan}"]`)
            let success  = true

            const processInput = async (input) => {
                const tipeV = input.dataset.tipe
                const nilai = input.value === '' ? null : Number.parseFloat(input.value)

                const checkRes = await fetch(
                    `/api/v1/fact/penjualan?tahun=${tahunV}&bulan=${bulan}&sub_golongan_kode=${encodeURIComponent(tarif)}&tipe=${tipeV}&is_triwulan=0&is_akumulasi=0&per_page=1`,
                    { headers: { Accept: 'application/json' } }
                )
                const check    = await checkRes.json()
                const existing = check.data?.data?.[0]

                if (existing) {
                    const res = await fetch(`/api/v1/fact/penjualan/${existing.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ nilai_kwh: nilai }),
                    })
                    if (!res.ok) throw new Error(`Gagal memperbarui data penjualan (HTTP ${res.status})`)
                    return
                }

                if (nilai === null) return

                const golMatch = tarif.match(/^(JUMLAH|[A-Z])/) 
                const golKode  = golMatch?.[1] === 'JUMLAH' ? 'TOTAL' : (golMatch?.[1] ?? 'TOTAL')
                const res = await fetch('/api/v1/fact/penjualan', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ tahun: tahunV, bulan, tipe: tipeV, sub_golongan_kode: tarif, golongan_kode: golKode, nilai_kwh: nilai, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                })
                if (!res.ok) throw new Error(`Gagal membuat data penjualan (HTTP ${res.status})`)
            }

            for (const input of inputs) {
                try { await processInput(input) } catch { success = false }
            }

            btn.disabled    = false
            btn.textContent = success ? '✓ OK' : '✗ Error'
            btn.style.background = success ? '#00ff88' : '#f44336'
            setTimeout(() => { btn.textContent = 'Simpan'; btn.style.background = '#ff9800' }, 2000)
        }
        container.addEventListener('click', container._saveHandler)
    }
}