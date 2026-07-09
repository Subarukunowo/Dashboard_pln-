import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const COLORS         = ['#ff9800','#00ff88','#2196f3','#e91e63','#9c27b0','#00bcd4']
const API_PENDAPATAN = '/api/v1/fact/pendapatan'
const BULAN_NAMA     = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER']

export default class GolTarifBatamModule {
    constructor(chart) {
        this.chart  = chart
        this._data  = null
        this._tahun = TAHUN_INI
        // State lokal khusus tabel "Edit Data Golongan" — independen dari filter Section 1
        this._golEditTahun = null
        this._golEditBulan = null
    }

    _getLabels(filterId) {
        return [...document.querySelectorAll('#' + filterId + ' option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    // Ambil nilai satu golongan dari struktur baru: data.golongan adalah ARRAY of
    // { kode, nama, targetIniBul, ... } — dicari by 'kode', BUKAN object keyed-by-kode.
    // (object keyed-by-kode gampang ke-reset jadi index numerik 0..5 kalau lewat
    // lapisan lain seperti API Resource / collect()->values() — makanya diganti array.)
    _getNilaiGol(data, golKode, idx, isTahunLalu, akum) {
        const s = data.golongan?.find(g => g.kode === golKode)
        if (!s) return 0
        if (isTahunLalu) {
            return (akum ? s.realisasiLaluAkum?.[idx] : s.realisasiLaluBul?.[idx]) ?? 0
        }
        return (akum ? s.targetIniAkum?.[idx] : s.targetIniBul?.[idx]) ?? 0
    }

    render(data, state) {
        if (!data) return
        this._data  = data
        this._tahun = data.tahun ?? TAHUN_INI

        const akum        = state.view === 'akumulasi'
        const idx         = Number.parseInt(state.bulan)
        const isTahunLalu = Number.parseInt(state.tahun) === data.tahunLalu

        const sliceIdx    = (state.bulanRealisasi && state.bulanRealisasi !== 'all')
            ? Number.parseInt(state.bulanRealisasi) + 1 : 12

        const labels       = this._getLabels('filterBulanPie')
        const slicedLabels = labels.slice(0, sliceIdx)

        // Section 1 — pie
        // data.golongan adalah ARRAY: [{ kode:'S', nama:'Sosial', ... }, ...] — urutan terjaga
        // karena array, bukan object keyed-by-kode (lihat catatan di _getNilaiGol).
        const golArr   = data.golongan ?? []
        const golKodes = golArr.map(g => g.kode)
        const golNama  = golArr.map(g => g.nama ?? g.kode)
        const nilaiGol = golKodes.map(g => this._getNilaiGol(data, g, idx, isTahunLalu, akum))

        const judulEl = document.getElementById('judulPie')
        if (judulEl) judulEl.textContent =
            (isTahunLalu ? 'Realisasi' : 'Target') + ' Pendapatan ' + state.tahun +
            (akum ? ' (Akumulasi)' : ' (Bulanan)') + ' — ' + labels[idx]

        this.chart.pie('chartPieTarif', golNama, nilaiGol, COLORS)
        this._renderPieTable(golNama, nilaiGol)

        // Section 2 — realisasi (tidak berubah, sudah cocok dengan backend)
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

        // Section 3 — total (tahun lalu vs tahun ini). data.total sekarang selalu diisi backend.
        const t           = data.total ?? []
        const totalLabels = this._getLabels('filterBulanPie').slice(0, t.length)
        this.chart.bar('chartTotalLaluVsIni', totalLabels, [
            { label: `Target ${data.tahunLalu}`,    data: t.map(d => d.targetLalu),    backgroundColor: '#555'    },
            { label: `Realisasi ${data.tahunLalu}`, data: t.map(d => d.realisasiLalu), backgroundColor: '#2196f3' },
            { label: `Target ${data.tahun}`,        data: t.map(d => d.targetIni),     backgroundColor: '#ff9800' },
            { label: `Realisasi ${data.tahun}`,     data: t.map(d => d.realisasiIni),  backgroundColor: '#00ff88' },
        ])
        const deltaTotal   = t.map(d => d.deltaKwh)
        this.chart.bar('chartTotalDelta', totalLabels, [
            { label: 'Delta', data: deltaTotal, backgroundColor: deltaTotal.map(v => v >= 0 ? '#00ff88' : '#f44336') },
        ])
        const growthTarget = t.map(d => d.targetLalu ? ((d.targetIni / d.targetLalu) - 1) * 100 : 0)
        const growthReal   = t.map(d => d.realisasiLalu ? ((d.realisasiIni / d.realisasiLalu) - 1) * 100 : 0)
        this.chart.line('chartTotalPertumbuhan', totalLabels, [
            { label: '% Target',    data: growthTarget, borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
            { label: '% Realisasi', data: growthReal,   borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
        ])
        this._renderTotalTable(t, totalLabels, data)
        this._renderDeltaTotalTable(totalLabels, deltaTotal)
        this._renderGrowthTable(totalLabels, growthTarget, growthReal)
        this._renderEditRealisasiTable(data)
        this._renderEditTotalTable(data)
        this._renderEditGolonganTable(data, state)
    }

    initFilters(state, onChange) {
        document.getElementById('filterTahunPie')?.addEventListener('change',          e => { state.tahun          = e.target.value; onChange() })
        document.getElementById('filterViewPie')?.addEventListener('change',           e => { state.view           = e.target.value; onChange() })
        document.getElementById('filterBulanPie')?.addEventListener('change',          e => { state.bulan          = e.target.value; onChange() })
        document.getElementById('filterBulanRealisasiGol')?.addEventListener('change', e => { state.bulanRealisasi = e.target.value; onChange() })
        this._initCrud(state, onChange)
    }

    _renderPieTable(golNama, nilaiGol) {
        const tbody = document.getElementById('tabelPieTarif')
        if (!tbody) return
        const total = nilaiGol.reduce((a, b) => a + b, 0)
        const fmtM  = v => { const [i, d] = (v / 1000).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = golNama.map((g, i) => {
            const pct = total ? ((nilaiGol[i] / total) * 100).toFixed(1) : '0.0'
            return '<tr>' +
                '<td style="padding:8px 10px; color:#888; text-align:center;">' + g + '</td>' +
                '<td style="padding:8px 10px; text-align:center; color:var(--accent-green);">Rp ' + fmtM(nilaiGol[i]) + 'M</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#aaa;">' + pct + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderRealisasiTable(data, labels) {
        const tbody = document.getElementById('tabelRealisasi')
        if (!tbody) return
        const fmtJ = v => { const [i, d] = (v / 1_000_000).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
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
        const fmtJ = v => { const [i, d] = (v / 1_000_000).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = labels.map((bln, i) =>
            '<tr>' +
            '<td style="padding:8px 10px; text-align:center; color:#888;">' + bln + '</td>' +
            '<td style="padding:8px 10px; text-align:center; color:' + (delta[i] >= 0 ? '#00ff88' : '#f44336') + ';">Rp ' + fmtJ(delta[i]) + ' Jt</td>' +
            '</tr>'
        ).join('')
    }

    _renderTotalTable(t, labels, data) {
        const tbody = document.getElementById('tabelTotal')
        if (!tbody) return
        const fmtT = v => { const [i, d] = (v / 1e9).toFixed(2).split('.'); return i.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + d }
        tbody.innerHTML = t.map((d, i) => {
            const c = d.deltaKwh >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px; text-align:center;">' + (labels[i] ?? d.bulan) + '</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#555;">Rp ' + fmtT(d.targetLalu) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#2196f3;">Rp ' + fmtT(d.realisasiLalu) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#ff9800;">Rp ' + fmtT(d.targetIni) + 'T</td>' +
                '<td style="padding:8px 10px; text-align:center; color:#00ff88;">Rp ' + fmtT(d.realisasiIni) + 'T</td>' +
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
     * Data sumber: data.realisasiBulanan (target & realisasi per bulan, tahun yang dipilih)
     */
    _renderEditRealisasiTable(data) {
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
            this._tahun < TAHUN_INI,
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
            (ri, values) => {
                const d = data.realisasiBulanan[ri]
                if (!d) return

                if (values.nilai_target    !== null) d.target    = values.nilai_target
                if (values.nilai_realisasi !== null) d.realisasi = values.nilai_realisasi

                d.delta      = (d.realisasi ?? 0) - (d.target ?? 0)
                d.pencapaian = (d.target ?? 0) !== 0 ? (d.realisasi ?? 0) / d.target : 0

                const sliceIdx    = data.realisasiBulanan.length
                const labels      = this._getLabels('filterBulanPie').slice(0, sliceIdx)
                const slicedData  = data.realisasiBulanan.slice(0, sliceIdx)
                const slicedDelta = slicedData.map(r => r.delta ?? 0)

                this._renderRealisasiTable(slicedData, labels)
                this._renderDeltaTable(labels, slicedDelta)

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
     * Data sumber: data.total (targetLalu, realisasiLalu, targetIni, realisasiIni per bulan)
     */
    _renderEditTotalTable(data) {
        const tahunLalu = this._tahun - 1

        const rows = BULAN_NAMA.map((bln, i) => {
            const d = data.total?.[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'target_lalu', value: d.targetLalu    ?? '', color: '#555'    },
                    { key: 'real_lalu',   value: d.realisasiLalu ?? '', color: '#2196f3' },
                    { key: 'target_ini',  value: d.targetIni     ?? '', color: '#ff9800' },
                    { key: 'real_ini',    value: d.realisasiIni  ?? '', color: '#00ff88' },
                ],
            }
        })

        renderEditTable(
            'golTarifTotalEditContainer',
            rows,
            this._tahun < TAHUN_INI,
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
            (ri, values) => {
                const d = data.total[ri]
                if (!d) return

                if (values.target_lalu !== null) d.targetLalu    = values.target_lalu
                if (values.real_lalu   !== null) d.realisasiLalu = values.real_lalu
                if (values.target_ini  !== null) d.targetIni     = values.target_ini
                if (values.real_ini    !== null) d.realisasiIni  = values.real_ini

                const targetIni    = d.targetIni ?? 0
                const realisasiIni = d.realisasiIni ?? 0

                d.deltaKwh = realisasiIni - targetIni
                d.deltaPct = targetIni > 0 ? realisasiIni / targetIni : 1

                const totalLabels  = this._getLabels('filterBulanPie').slice(0, data.total.length)
                const t            = data.total
                const deltaTotal   = t.map(r => r.deltaKwh ?? 0)
                const growthTarget = t.map(r => r.targetLalu
                    ? ((r.targetIni / r.targetLalu) - 1) * 100 : 0)
                const growthReal   = t.map(r => r.realisasiLalu
                    ? ((r.realisasiIni / r.realisasiLalu) - 1) * 100 : 0)

                this._renderTotalTable(t, totalLabels, data)
                this._renderDeltaTotalTable(totalLabels, deltaTotal)
                this._renderGrowthTable(totalLabels, growthTarget, growthReal)

                this.chart.bar('chartTotalLaluVsIni', totalLabels, [
                    { label: `Target ${data.tahunLalu}`,    data: t.map(r => r.targetLalu    ?? 0), backgroundColor: '#555'    },
                    { label: `Realisasi ${data.tahunLalu}`, data: t.map(r => r.realisasiLalu ?? 0), backgroundColor: '#2196f3' },
                    { label: `Target ${data.tahun}`,        data: t.map(r => r.targetIni     ?? 0), backgroundColor: '#ff9800' },
                    { label: `Realisasi ${data.tahun}`,     data: t.map(r => r.realisasiIni  ?? 0), backgroundColor: '#00ff88' },
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

    /**
     * Edit data per GOLONGAN (Sosial, Rumah Tangga, Bisnis, Industri, Publik, Multiguna)
     * — punya filter TAHUN & BULAN sendiri, independen dari filter Section 1/2.
     * Catatan: data.golongan[].targetLaluBul TIDAK dikirim backend (backend cuma
     * kirim realisasiLaluBul/Akum utk tahun lalu) — jadi kalau tahun lalu dipilih,
     * kolom Target akan kosong sampai backend ditambah field itu.
     */
_renderEditGolonganTable(data, state) {
    const container = document.getElementById('golTarifGolonganEditContainer')
    if (!container || !data.golongan) return

    // Validasi & reset golEditTahun kalau di luar rentang [tahunLalu, tahunIni] data yang sedang dimuat.
    // Ini penting karena this._golEditTahun persist antar render — kalau user ganti tahun
    // di filter utama Section 1, this._tahun berubah tapi golEditTahun bisa nyangkut ke nilai lama
    // yang sudah tidak valid, bikin isDataTahunIni salah hitung dan data jadi kosong/salah tahun.
    if (this._golEditTahun == null || (this._golEditTahun !== this._tahun && this._golEditTahun !== this._tahun - 1)) {
        this._golEditTahun = this._tahun
    }

    // Default bulan = bulan berjalan (kalau tahun yang di-edit = tahun berjalan), bukan Desember.
    if (this._golEditBulan == null) {
        const nowMonthIdx = new Date().getMonth() // 0 = Januari
        this._golEditBulan = (state.bulanRealisasi && state.bulanRealisasi !== 'all')
            ? Number.parseInt(state.bulanRealisasi)
            : (this._golEditTahun === TAHUN_INI ? nowMonthIdx : 11)
    }

    const bulanLabels    = this._getLabels('filterBulanPie')
    const isDataTahunIni = this._golEditTahun === this._tahun
    const isReadOnly     = this._golEditTahun < TAHUN_INI
        const tahunOptions = [this._tahun - 1, this._tahun]
            .map(y => `<option value="${y}" ${y === this._golEditTahun ? 'selected' : ''}>${y}</option>`)
            .join('')
        const bulanOptions = bulanLabels
            .map((b, i) => `<option value="${i}" ${i === this._golEditBulan ? 'selected' : ''}>${b}</option>`)
            .join('')

        const filterHtml = `
            <div style="display:flex; gap:8px; align-items:center; padding:8px 10px; background:#1f1f1f; border-bottom:1px solid #2a2a2a;">
                <label style="font-size:0.7rem; color:#888;">Tahun</label>
                <select id="golEditFilterTahun" style="font-size:0.75rem; padding:3px 6px;">${tahunOptions}</select>
                <label style="font-size:0.7rem; color:#888;">Bulan</label>
                <select id="golEditFilterBulan" style="font-size:0.75rem; padding:3px 6px;">${bulanOptions}</select>
                ${isReadOnly ? '<span style="font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">🔒 Tahun lalu — hanya bisa dilihat</span>' : ''}
            </div>`

        const disStyle = isReadOnly ? 'opacity:0.45; pointer-events:none;' : ''

        const headHtml = `<thead style="position:sticky;top:0;background:#252525;z-index:1;">
            <tr style="font-size:0.65rem;text-transform:uppercase;">
                <th style="padding:8px 10px;color:#888;text-align:left;">Golongan</th>
                <th style="padding:8px 10px;color:#ff9800;text-align:left;">Target (Rp)</th>
                <th style="padding:8px 10px;color:#00ff88;text-align:left;">Realisasi (Rp)</th>
                <th style="padding:8px 10px;color:#aaa;text-align:center;">Aksi</th>
            </tr>
        </thead>`

        const getVal = (g, tipe) => {
            if (isDataTahunIni) {
                return tipe === 'target' ? g.targetIniBul?.[this._golEditBulan] : g.realisasiIniBul?.[this._golEditBulan]
            }
            return tipe === 'target' ? g.targetLaluBul?.[this._golEditBulan] : g.realisasiLaluBul?.[this._golEditBulan]
        }

        const rows = data.golongan.map((g, ri) => {
            const tVal = getVal(g, 'target')    ?? ''
            const rVal = getVal(g, 'realisasi') ?? ''

            const mkInput = (val, field, color) => `
                <input type="number"
                    data-row="${ri}" data-field="${field}"
                    value="${val}" placeholder="—"
                    ${isReadOnly ? 'disabled' : ''}
                    style="width:140px;padding:4px 6px;border-radius:4px;
                        border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                        background:#1a1a1a;color:${isReadOnly ? '#444' : color};
                        font-size:0.75rem;text-align:right;" />`

            return `<tr style="border-bottom:1px solid #2a2a2a;${disStyle}" data-gol="${g.kode}">
                <td style="padding:8px 10px;color:#888;font-size:0.75rem;white-space:nowrap;">${g.nama}</td>
                <td style="padding:4px 6px;">${mkInput(tVal, 'nilai_target', '#ff9800')}</td>
                <td style="padding:4px 6px;">${mkInput(rVal, 'nilai_realisasi', '#00ff88')}</td>
                <td style="padding:4px 6px;text-align:center;">
                    <button id="btnSaveGolTarifGol_${ri}" data-row="${ri}"
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

        container.innerHTML = filterHtml + `<table style="width:100%;border-collapse:collapse;">${headHtml}<tbody>${rows}</tbody></table>`

        document.getElementById('golEditFilterTahun')?.addEventListener('change', e => {
            this._golEditTahun = Number.parseInt(e.target.value)
            this._renderEditGolonganTable(data, state)
        })
        document.getElementById('golEditFilterBulan')?.addEventListener('change', e => {
            this._golEditBulan = Number.parseInt(e.target.value)
            this._renderEditGolonganTable(data, state)
        })

        if (isReadOnly) return

        data.golongan.forEach((g, ri) => {
            const btn = document.getElementById(`btnSaveGolTarifGol_${ri}`)
            if (!btn) return
            btn.addEventListener('click', async () => {
                btn.textContent = '⏳'; btn.disabled = true
                const inputs = container.querySelectorAll(`input[data-row="${ri}"]`)
                const values = {}
                inputs.forEach(inp => { values[inp.dataset.field] = inp.value === '' ? null : Number.parseFloat(inp.value) })
                try {
                    const tahunEdit = this._golEditTahun
                    const bulan     = this._golEditBulan + 1
                    const subKode   = 'JUMLAH-' + g.kode
                    const base      = { tahun: tahunEdit, bulan, golongan_kode: g.kode, sub_golongan_kode: subKode, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }

                    if (values.nilai_target !== null)
                        await upsert(API_PENDAPATAN,
                            { tahun: tahunEdit, bulan, sub_golongan_kode: subKode, tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                            { ...base, tipe: 'target', nilai_rp: values.nilai_target })
                    if (values.nilai_realisasi !== null)
                        await upsert(API_PENDAPATAN,
                            { tahun: tahunEdit, bulan, sub_golongan_kode: subKode, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                            { ...base, tipe: 'realisasi', nilai_rp: values.nilai_realisasi })

                    // Update in-memory + pie HANYA kalau tahun yg diedit = tahun data yg sedang tampil.
                    // Kalau edit tahun lain, in-memory data.golongan tidak mewakili tahun itu penuh,
                    // jadi pie chart tidak disentuh — cukup refresh tabel edit ini.
                    if (isDataTahunIni) {
                        if (values.nilai_target    !== null) g.targetIniBul[this._golEditBulan]    = values.nilai_target
                        if (values.nilai_realisasi !== null) g.realisasiIniBul[this._golEditBulan] = values.nilai_realisasi

                        const akum2     = state.view === 'akumulasi'
                        const nilaiGol2 = data.golongan.map(gg => this._getNilaiGol(data, gg.kode, this._golEditBulan, false, akum2))
                        const golNama2  = data.golongan.map(gg => gg.nama ?? gg.kode)
                        this.chart.pie('chartPieTarif', golNama2, nilaiGol2, COLORS)
                        this._renderPieTable(golNama2, nilaiGol2)
                    }

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