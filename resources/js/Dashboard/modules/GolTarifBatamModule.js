import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const COLORS         = ['#ff9800','#00ff88','#2196f3','#e91e63','#9c27b0','#00bcd4']
const API_PENDAPATAN = '/api/v1/fact/pendapatan'
const BULAN_NAMA     = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER']

export default class GolTarifBatamModule {
    constructor(chart) {
        this.chart  = chart
        this._data  = null
        this._tahun = TAHUN_INI
    }

    _getLabels(filterId) {
        return [...document.querySelectorAll('#' + filterId + ' option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    _getNilaiGol(data, g, idx, is2024, akum) {
        if (is2024) {
            return akum ? data.realisasi2024Akum[g][idx] : data.realisasi2024Bul[g][idx]
        }
        return akum ? data.target2025Akum[g][idx] : data.target2025Bul[g][idx]
    }

    render(data, state) {
        if (!data) return
        this._data  = data
        this._tahun = TAHUN_INI

        const akum     = state.view === 'akumulasi'
        const idx      = Number.parseInt(state.bulan)
        const is2024   = state.tahun === '2024'
        const sliceIdx = (state.bulanRealisasi && state.bulanRealisasi !== 'all')
            ? Number.parseInt(state.bulanRealisasi) + 1 : 12

        const labels       = this._getLabels('filterBulanPie')
        const slicedLabels = labels.slice(0, sliceIdx)

        // Section 1 — pie
        const nilaiGol = data.golongan.map(g => this._getNilaiGol(data, g, idx, is2024, akum))

        const judulEl = document.getElementById('judulPie')
        if (judulEl) judulEl.textContent =
            (is2024 ? 'Realisasi' : 'Target') + ' Pendapatan ' + state.tahun +
            (akum ? ' (Akumulasi)' : ' (Bulanan)') + ' — ' + labels[idx]

        this.chart.pie('chartPieTarif', data.golongan.map(g => g.replace(/ \(.\) ?/, '')), nilaiGol, COLORS)
        this._renderPieTable(data.golongan, nilaiGol)

        // Section 2 — realisasi
        const slicedTarget    = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.target)
        const slicedRealisasi = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.realisasi)
        const slicedDelta     = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.delta)

        this.chart.bar('chartRealisasiBulanan', slicedLabels, [
            { label: 'Target',    data: slicedTarget,    backgroundColor: '#ff9800' },
            { label: 'Realisasi', data: slicedRealisasi, backgroundColor: '#00ff88' },
        ])
        this.chart.bar('chartDeltaRealisasi', slicedLabels, [
            { label: 'Delta', data: slicedDelta, backgroundColor: slicedDelta.map(v => v >= 0 ? '#00ff88' : '#f44336') },
        ])
        this._renderRealisasiTable(data.realisasiBulanan.slice(0, sliceIdx), slicedLabels)
        this._renderDeltaTable(slicedLabels, slicedDelta)

        // Section 3 — total
        const t           = data.total
        const totalLabels = this._getLabels('filterBulanPie').slice(0, t.length)
        this.chart.bar('chartTotal2024vs2025', totalLabels, [
            { label: 'Target Thn Lalu',    data: t.map(d => d.target2024),    backgroundColor: '#555'    },
            { label: 'Realisasi Thn Lalu', data: t.map(d => d.realisasi2024), backgroundColor: '#2196f3' },
            { label: 'Target Thn Ini',     data: t.map(d => d.target2025),    backgroundColor: '#ff9800' },
            { label: 'Realisasi Thn Ini',  data: t.map(d => d.realisasi2025), backgroundColor: '#00ff88' },
        ])
        const deltaTotal   = t.map(d => d.deltaKwh)
        this.chart.bar('chartTotalDelta', totalLabels, [
            { label: 'Delta', data: deltaTotal, backgroundColor: deltaTotal.map(v => v >= 0 ? '#00ff88' : '#f44336') },
        ])
        const growthTarget = t.map(d => ((d.target2025 / d.target2024) - 1) * 100)
        const growthReal   = t.map(d => ((d.realisasi2025 / d.realisasi2024) - 1) * 100)
        this.chart.line('chartTotalPertumbuhan', totalLabels, [
            { label: '% Target',    data: growthTarget, borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
            { label: '% Realisasi', data: growthReal,   borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
        ])
        this._renderTotalTable(t, totalLabels)
        this._renderDeltaTotalTable(totalLabels, deltaTotal)
        this._renderGrowthTable(totalLabels, growthTarget, growthReal)
        this._renderEditRealisasiTable(data)
        this._renderEditTotalTable(data)
    }

    initFilters(state, onChange) {
        document.getElementById('filterTahunPie')?.addEventListener('change',          e => { state.tahun          = e.target.value; onChange() })
        document.getElementById('filterViewPie')?.addEventListener('change',           e => { state.view           = e.target.value; onChange() })
        document.getElementById('filterBulanPie')?.addEventListener('change',          e => { state.bulan          = e.target.value; onChange() })
        document.getElementById('filterBulanRealisasiGol')?.addEventListener('change', e => { state.bulanRealisasi = e.target.value; onChange() })
        this._initCrud(state, onChange)
    }

    _renderPieTable(golongan, nilaiGol) {
        const tbody = document.getElementById('tabelPieTarif')
        if (!tbody) return
        const total = nilaiGol.reduce((a, b) => a + b, 0)
        const fmtM  = v => { const [i, d] = (v / 1000).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = golongan.map((g, i) => {
            const pct = total ? ((nilaiGol[i] / total) * 100).toFixed(1) : '0.0'
            return '<tr>' +
                '<td style="padding:8px 10px; color:#888; text-align:center;">' + g.replace(/ \(.\) ?/, '') + '</td>' +
                '<td style="padding:8px 10px; text-align:center; color:var(--accent-green);">Rp ' + fmtM(nilaiGol[i]) + 'M</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#aaa;">' + pct + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderRealisasiTable(data, labels) {
        const tbody = document.getElementById('tabelRealisasi')
        if (!tbody) return
        const fmtJ = v => { const [i, d] = (v / 1000).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = data.map((d, i) => {
            const c = d.delta >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px; text-align:center;">' + (labels[i] ?? d.bulan) + '</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#ff9800;">Rp ' + fmtJ(d.target) + ' Jt</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#00ff88;">Rp ' + fmtJ(d.realisasi) + ' Jt</td>' +
                '<td style="padding:8px 10px; text-align:center; color:' + c + ';">Rp ' + fmtJ(d.delta) + ' Jt</td>' +
                '<td style="padding:8px 10px; text-align:center; color:' + c + ';">' + (d.pencapaian * 100).toFixed(2) + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderDeltaTable(labels, delta) {
        const tbody = document.getElementById('tabelDeltaRealisasi')
        if (!tbody) return
        const fmtJ = v => { const [i, d] = (v / 1000).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = labels.map((bln, i) =>
            '<tr>' +
            '<td style="padding:8px 10px; text-align:center; color:#888;">' + bln + '</td>' +
            '<td style="padding:8px 10px; text-align:center; color:' + (delta[i] >= 0 ? '#00ff88' : '#f44336') + ';">Rp ' + fmtJ(delta[i]) + ' Jt</td>' +
            '</tr>'
        ).join('')
    }

    _renderTotalTable(t, labels) {
        const tbody = document.getElementById('tabelTotal')
        if (!tbody) return
        const fmtT = v => { const [i, d] = (v / 1e9).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = t.map((d, i) => {
            const c = d.deltaKwh >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px; text-align:center;">' + (labels[i] ?? d.bulan) + '</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#555;">Rp ' + fmtT(d.target2024) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#2196f3;">Rp ' + fmtT(d.realisasi2024) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#ff9800;">Rp ' + fmtT(d.target2025) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#00ff88;">Rp ' + fmtT(d.realisasi2025) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:' + c + ';">Rp ' + fmtT(d.deltaKwh) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:' + c + ';">' + ((d.deltaPct - 1) * 100).toFixed(2) + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderDeltaTotalTable(labels, delta) {
        const tbody = document.getElementById('tabelDeltaTotal')
        if (!tbody) return
        const fmtT = v => { const [i, d] = (v / 1e9).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = labels.map((bln, i) =>
            '<tr>' +
            '<td style="padding:8px 10px; text-align:center; color:#888;">' + bln + '</td>' +
            '<td style="padding:8px 10px; text-align:center; color:' + (delta[i] >= 0 ? '#00ff88' : '#f44336') + ';">Rp ' + fmtT(delta[i]) + 'T</td>' +
            '</tr>'
        ).join('')
    }

    _renderGrowthTable(labels, growthTarget, growthReal) {
        const tbody = document.getElementById('tabelPertumbuhan')
        if (!tbody) return
        tbody.innerHTML = labels.map((bln, i) =>
            '<tr>' +
            '<td style="padding:8px 10px; text-align:center; color:#888;">' + bln + '</td>' +
            '<td style="padding:8px 10px; text-align:center; color:#ff9800;">' + growthTarget[i].toFixed(2) + '%</td>' +
            '<td style="padding:8px 10px; text-align:center; color:#00ff88;">' + growthReal[i].toFixed(2) + '%</td>' +
            '</tr>'
        ).join('')
    }

    /**
     * Edit data realisasi bulanan — tabelRealisasi (Section 2)
     * Data sumber: data.realisasiBulanan (target & realisasi per bulan tahun ini)
     */
    _renderEditRealisasiTable(data) {
        const isReadOnly = this._tahun < TAHUN_INI
        const rows = BULAN_NAMA.map((bln, i) => {
            const d = data.realisasiBulanan?.[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'nilai_target',    value: d.target    ?? '', color: '#ff9800' },
                    { key: 'nilai_realisasi', value: d.realisasi ?? '', color: '#00ff88' },
                ],
            }
        })

        renderEditTable(
            'golTarifRealisasiEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                const base  = { tahun: this._tahun, bulan, golongan_kode: 'TOTAL', sub_golongan_kode: 'JUMLAH', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }
                if (values.nilai_target !== null) {
                    await upsert(API_PENDAPATAN,
                        { tahun: this._tahun, bulan, sub_golongan_kode: 'JUMLAH', tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'target', nilai_rp: values.nilai_target })
                }
                if (values.nilai_realisasi !== null) {
                    await upsert(API_PENDAPATAN,
                        { tahun: this._tahun, bulan, sub_golongan_kode: 'JUMLAH', tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'realisasi', nilai_rp: values.nilai_realisasi })
                }
            },
            ['Bulan', 'Target Thn Ini (Rp)', 'Realisasi Thn Ini (Rp)'],
            // ── Live-recalc setelah simpan ──────────────────────────────────
            (ri, values) => {
                const d = data.realisasiBulanan[ri]
                if (!d) return

                // Update data lokal
                if (values.nilai_target    !== null) d.target    = values.nilai_target
                if (values.nilai_realisasi !== null) d.realisasi = values.nilai_realisasi

                // Recalculate turunan
                d.delta     = (d.realisasi ?? 0) - (d.target ?? 0)
                d.pencapaian = (d.target ?? 0) == 0 ? (d.realisasi ?? 0) / d.target : 0

                // Re-render tabel view yang terpengaruh
                const sliceIdx     = data.realisasiBulanan.length
                const labels       = this._getLabels('filterBulanPie').slice(0, sliceIdx)
                const slicedData   = data.realisasiBulanan.slice(0, sliceIdx)
                const slicedDelta  = slicedData.map(r => r.delta ?? 0)

                this._renderRealisasiTable(slicedData, labels)
                this._renderDeltaTable(labels, slicedDelta)

                // Update chart
                this.chart.bar('chartRealisasiBulanan', labels, [
                    { label: 'Target',    data: slicedData.map(r => r.target    ?? 0), backgroundColor: '#ff9800' },
                    { label: 'Realisasi', data: slicedData.map(r => r.realisasi ?? 0), backgroundColor: '#00ff88' },
                ])
                this.chart.bar('chartDeltaRealisasi', labels, [
                    { label: 'Delta', data: slicedDelta, backgroundColor: slicedDelta.map(v => v >= 0 ? '#00ff88' : '#f44336') },
                ])
            }
        )
    }

    /**
     * Edit data perbandingan thn lalu vs thn ini — tabelTotal (Section 3)
     * Data sumber: data.total (target2024, realisasi2024, target2025, realisasi2025 per bulan)
     * Menyimpan ke tahun yang sesuai masing-masing kolom.
     */
    _renderEditTotalTable(data) {
        const isReadOnly = this._tahun < TAHUN_INI
        const tahunLalu  = this._tahun - 1

        const rows = BULAN_NAMA.map((bln, i) => {
            const d = data.total?.[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'target_lalu',    value: d.target2024    ?? '', color: '#555'    },
                    { key: 'real_lalu',      value: d.realisasi2024 ?? '', color: '#2196f3' },
                    { key: 'target_ini',     value: d.target2025    ?? '', color: '#ff9800' },
                    { key: 'real_ini',       value: d.realisasi2025 ?? '', color: '#00ff88' },
                ],
            }
        })

        renderEditTable(
            'golTarifTotalEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                const mkBase = (tahun) => ({ tahun, bulan, golongan_kode: 'TOTAL', sub_golongan_kode: 'JUMLAH', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' })

                if (values.target_lalu !== null) {
                    await upsert(API_PENDAPATAN,
                        { tahun: tahunLalu, bulan, sub_golongan_kode: 'JUMLAH', tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                        { ...mkBase(tahunLalu), tipe: 'target', nilai_rp: values.target_lalu })
                }
                if (values.real_lalu !== null) {
                    await upsert(API_PENDAPATAN,
                        { tahun: tahunLalu, bulan, sub_golongan_kode: 'JUMLAH', tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                        { ...mkBase(tahunLalu), tipe: 'realisasi', nilai_rp: values.real_lalu })
                }
                if (values.target_ini !== null) {
                    await upsert(API_PENDAPATAN,
                        { tahun: this._tahun, bulan, sub_golongan_kode: 'JUMLAH', tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                        { ...mkBase(this._tahun), tipe: 'target', nilai_rp: values.target_ini })
                }
                if (values.real_ini !== null) {
                    await upsert(API_PENDAPATAN,
                        { tahun: this._tahun, bulan, sub_golongan_kode: 'JUMLAH', tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                        { ...mkBase(this._tahun), tipe: 'realisasi', nilai_rp: values.real_ini })
                }
            },
            ['Bulan', `Target ${tahunLalu} (Rp)`, `Real ${tahunLalu} (Rp)`, `Target ${this._tahun} (Rp)`, `Real ${this._tahun} (Rp)`],
            // ── Live-recalc setelah simpan ──────────────────────────────────
            (ri, values) => {
                const d = data.total[ri]
                if (!d) return

                // Update data lokal
                if (values.target_lalu !== null) d.target2024    = values.target_lalu
                if (values.real_lalu   !== null) d.realisasi2024 = values.real_lalu
                if (values.target_ini  !== null) d.target2025    = values.target_ini
                if (values.real_ini    !== null) d.realisasi2025 = values.real_ini

                // Recalculate turunan
                const targetIni    = d.target2025 ?? 0
                const realisasiIni = d.realisasi2025 ?? 0

                d.deltaKwh = realisasiIni - targetIni
                d.deltaPct = targetIni > 0 ? realisasiIni / targetIni : 1

                const totalLabels  = this._getLabels('filterBulanPie').slice(0, data.total.length)
                const t            = data.total
                const deltaTotal   = t.map(r => r.deltaKwh ?? 0)
                const growthTarget = t.map(r => r.target2024
                    ? ((r.target2025 / r.target2024) - 1) * 100 : 0)
                const growthReal   = t.map(r => r.realisasi2024
                    ? ((r.realisasi2025 / r.realisasi2024) - 1) * 100 : 0)

                // Re-render tabel view
                this._renderTotalTable(t, totalLabels)
                this._renderDeltaTotalTable(totalLabels, deltaTotal)
                this._renderGrowthTable(totalLabels, growthTarget, growthReal)

                // Update chart
                this.chart.bar('chartTotal2024vs2025', totalLabels, [
                    { label: 'Target Thn Lalu',    data: t.map(r => r.target2024    ?? 0), backgroundColor: '#555'    },
                    { label: 'Realisasi Thn Lalu', data: t.map(r => r.realisasi2024 ?? 0), backgroundColor: '#2196f3' },
                    { label: 'Target Thn Ini',     data: t.map(r => r.target2025    ?? 0), backgroundColor: '#ff9800' },
                    { label: 'Realisasi Thn Ini',  data: t.map(r => r.realisasi2025 ?? 0), backgroundColor: '#00ff88' },
                ])
                this.chart.bar('chartTotalDelta', totalLabels, [
                    { label: 'Delta', data: deltaTotal, backgroundColor: deltaTotal.map(v => v >= 0 ? '#00ff88' : '#f44336') },
                ])
                this.chart.line('chartTotalPertumbuhan', totalLabels, [
                    { label: '% Target',    data: growthTarget, borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
                    { label: '% Realisasi', data: growthReal,   borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
                ])
            }
        )
    }

    _initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatGolTarif')
        const modalHapus = document.getElementById('modalHapusGolTarif')

        const btnMuat = document.getElementById('btnMuatGolTarif')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatGolTarif')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatGolTarif')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('muatTahunGolTarif')?.value)
                if (modalMuat) modalMuat.style.display = 'none'
                this._tahun = tahun
                state.tahun = String(tahun)
                onChange()
            })
        }

        const btnHapus = document.getElementById('btnHapusGolTarif')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusGolTarif')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusGolTarif')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunGolTarif')?.value
                const tipe  = document.getElementById('hapusTipeGolTarif')?.value
                if (!confirm('Hapus data Golongan Tarif tahun ' + tahun + ' (' + tipe + ')? Tidak dapat dibatalkan.')) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    const params = tipe === 'semua' ? { tahun } : { tahun, tipe }
                    await deleteByFilter(API_PENDAPATAN, params)
                    onChange()
                } catch { alert('Gagal menghapus data') }
            })
        }

        const modalBuat = document.getElementById('modalBuatGolTarif')
        const btnBuat   = document.getElementById('btnBuatGolTarif')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatGolTarif')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatGolTarif')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('buatTahunGolTarif')?.value)
                const tipe  = document.getElementById('buatTipeGolTarif')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch(API_PENDAPATAN, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, golongan_kode: 'TOTAL', sub_golongan_kode: 'JUMLAH', tipe, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_rp: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data Golongan Tarif tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = String(tahun)
                onChange()
            })
        }
    }
}