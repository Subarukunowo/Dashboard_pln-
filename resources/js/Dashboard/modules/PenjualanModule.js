import PenjualanService from '../services/PenjualanService.js'
import { TAHUN_INI } from '../utils/CrudHelper.js'

const BULAN_LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

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
        if (warning) {
            warning.style.display = this.data?.is_fallback ? 'inline-block' : 'none'
        }
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanPenjualan option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    render(state) {
        if (!this.data) return

        const { view, tarif, month } = state
        const labels = this._getLabels()
        let target, realisasi

        if (tarif === 'JUMLAH') {
            if (view === 'akumulasi') {
                target    = this.data.akumulasi?.target    ?? []
                realisasi = this.data.akumulasi?.realisasi ?? []
            } else if (view === 'persenBulanan') {
                target    = []
                realisasi = this.data.persenBulanan ?? []
            } else if (view === 'persenAkumulasi') {
                target    = []
                realisasi = this.data.persenAkumulasi ?? []
            } else {
                target    = this.data.bulanan?.target    ?? []
                realisasi = this.data.bulanan?.realisasi ?? []
            }
        } else {
            const d = this.data.perTarif?.[tarif]
            if (!d) return
            target    = view === 'akumulasi' ? (d.targetAkum    ?? []) : (d.target    ?? [])
            realisasi = view === 'akumulasi' ? (d.realisasiAkum ?? []) : (d.realisasi ?? [])
        }

        const isPersen       = view.includes('persen')
        let displayLabels    = [...labels]
        let displayTarget    = target
        let displayRealisasi = realisasi

        if (month !== 'all') {
            const idx        = parseInt(month)
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
        document.getElementById('filterTarif')?.addEventListener('change',          e => { state.tarif = e.target.value; onChange() })
        document.getElementById('filterBulanPenjualan')?.addEventListener('change', e => { state.month = e.target.value; onChange() })

        // Tombol muat data
        const btnMuat  = document.getElementById('btnMuatPenjualan')
        const modal    = document.getElementById('modalMuatPenjualan')
        const btnBatal = document.getElementById('btnBatalMuatPenjualan')
        const btnOk    = document.getElementById('btnKonfirmasiMuatPenjualan')

        btnMuat?.addEventListener('click', () => { if (modal) modal.style.display = 'flex' })
        btnBatal?.addEventListener('click', () => { if (modal) modal.style.display = 'none' })
        btnOk?.addEventListener('click', async () => {
            const tahun = parseInt(document.getElementById('muatTahunPenjualan')?.value)
            if (modal) modal.style.display = 'none'
            await this.load(tahun)
            onChange()
        })

        // Tombol hapus data
        const btnHapus     = document.getElementById('btnHapusPenjualan')
        const modalHapus   = document.getElementById('modalHapusPenjualan')
        const btnBatalHaps = document.getElementById('btnBatalHapusPenjualan')
        const btnOkHapus   = document.getElementById('btnKonfirmasiHapusPenjualan')

        btnHapus?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
        btnBatalHaps?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
        btnOkHapus?.addEventListener('click', async () => {
            const tahun = document.getElementById('hapusTahunPenjualan')?.value
            const tipe  = document.getElementById('hapusTipePenjualan')?.value
            if (!confirm(`Hapus data penjualan tahun ${tahun} (${tipe})? Tindakan ini tidak dapat dibatalkan.`)) return
            if (modalHapus) modalHapus.style.display = 'none'
            try {
                const csrf    = document.querySelector('meta[name="csrf-token"]')?.content
                const params  = new URLSearchParams({ tahun })
                if (tipe !== 'semua') params.append('tipe', tipe)
                const res = await fetch(`/api/v1/fact/penjualan?${params}`, {
                    method: 'DELETE',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                })
                if (!res.ok) throw new Error()
                await this.load(this.data?.tahun_efektif ?? new Date().getFullYear())
                onChange()
            } catch {
                alert('Gagal menghapus data penjualan')
            }
        })

        // Tombol buat data
        const btnBuat      = document.getElementById('btnBuatPenjualan')
        const modalBuat    = document.getElementById('modalBuatPenjualan')
        const btnBatalBuat = document.getElementById('btnBatalBuatPenjualan')
        const btnOkBuat    = document.getElementById('btnKonfirmasiBuatPenjualan')

        btnBuat?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
        btnBatalBuat?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
        btnOkBuat?.addEventListener('click', async () => {
            const tahun = parseInt(document.getElementById('buatTahunPenjualan')?.value)
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

        const isLine = view === 'akumulasi' || view === 'persenAkumulasi'

        this._chart = new Chart(ctx, {
            type: isLine ? 'line' : 'bar',
            data: {
                labels,
                datasets: [
                    ...(target.length > 0 && !isPersen ? [{
                        label:           'Target',
                        data:            target,
                        backgroundColor: '#ff9800',
                        borderColor:     '#ff9800',
                        borderWidth:     1,
                        fill:            false,
                        tension:         0.4,
                    }] : []),
                    {
                        label:           isPersen ? '% Realisasi' : 'Realisasi',
                        data:            realisasi,
                        backgroundColor: '#00ff88',
                        borderColor:     '#00ff88',
                        borderWidth:     2,
                        tension:         0.4,
                        fill:            false,
                    },
                ],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        grid:  { color: '#333' },
                        ticks: { color: '#888', callback: isPersen ? v => v.toFixed(1) + '%' : undefined },
                    },
                    x: { grid: { display: false }, ticks: { color: '#888' } },
                },
            },
        })
    }

    _renderTable(labels, target, realisasi, isPersen) {
        const tbody = document.getElementById('penjualanTableBody')
        if (!tbody) return

        const fmt = v => {
            if (v === null || v === undefined) return '—'
            return isPersen
                ? (v).toFixed(2) + '%'
                : (v / 1_000_000).toFixed(2) + ' jt kWh'
        }

        tbody.innerHTML = labels.map((bln, i) => {
            const t = target[i]    ?? 0
            const r = realisasi[i] ?? 0
            const d = r - t
            const c = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="padding:8px 10px; color:#888;">${bln}</td>
                <td style="padding:8px 10px; color:var(--accent-orange); text-align:right;">${fmt(t)}</td>
                <td style="padding:8px 10px; color:var(--accent-green); text-align:right;">${fmt(r)}</td>
                <td style="padding:8px 10px; color:${c}; font-weight:600; text-align:right;">${d !== 0 ? (d >= 0 ? '+' : '') + fmt(d) : '—'}</td>
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
                <input type="number"
                    data-bulan="${i+1}" data-tipe="${tipeV}"
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

        // Event delegation — 1 listener untuk semua tombol Simpan
        container.removeEventListener('click', container._saveHandler)
        container._saveHandler = async (e) => {
            const btn = e.target.closest('button[data-bulan]')
            if (!btn) return
            const bulan  = parseInt(btn.dataset.bulan)
            const tahunV = parseInt(btn.dataset.tahun)

            btn.textContent = '⏳'; btn.disabled = true
            const inputs = container.querySelectorAll(`input[data-bulan="${bulan}"]`)
            let success  = true

            for (const input of inputs) {
                const tipeV = input.dataset.tipe
                const nilai = input.value === '' ? null : parseFloat(input.value)
                try {
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
                        if (!res.ok) throw new Error()
                    } else if (nilai !== null) {
                        const golMatch = tarif.match(/^(JUMLAH|[A-Z])/)
                        const golKode  = golMatch?.[1] === 'JUMLAH' ? 'TOTAL' : (golMatch?.[1] ?? 'TOTAL')
                        const res = await fetch('/api/v1/fact/penjualan', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ tahun: tahunV, bulan, tipe: tipeV, sub_golongan_kode: tarif, golongan_kode: golKode, nilai_kwh: nilai, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                        })
                        if (!res.ok) throw new Error()
                    }
                } catch {
                    success = false
                }
            }

            btn.disabled    = false
            btn.textContent = success ? '✓ OK' : '✗ Error'
            btn.style.background = success ? '#00ff88' : '#f44336'
            setTimeout(() => { btn.textContent = 'Simpan'; btn.style.background = '#ff9800' }, 2000)
        }
        container.addEventListener('click', container._saveHandler)
    }
}