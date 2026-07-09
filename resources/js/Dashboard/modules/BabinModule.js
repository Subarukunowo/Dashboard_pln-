import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const API = '/api/v1/fact/babin'

export default class BabinModule {
    constructor(chart, fetchByYear) {
        this.chart          = chart
        this.fetchByYear    = fetchByYear   // (tahun) => Promise<data>
        this._rawData       = null
        this._tahun         = TAHUN_INI
        this._tahunSekarang = TAHUN_INI
        this._editTahun     = TAHUN_INI
        this._editData      = null
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanBabin option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return
        this._rawData       = data
        this._tahun         = data.tahun_efektif ?? state.tahun ?? TAHUN_INI
        this._tahunSekarang = data.tahun_sekarang ?? TAHUN_INI

        const warning = document.getElementById('babinWarning')
        if (warning) warning.style.display = (this._tahun < this._tahunSekarang) ? 'inline-block' : 'none'

        const area     = state.section === 'babin' ? 'interkoneksi' : 'sembulang'
        const isAkum   = state.view === 'akumulasi'
        const viewData = isAkum ? data.akumulasi[area] : data.bulanan[area]
        const yoyData  = data.growth_yoy[area]
        const histData = data.historis_yoy[area]
        const tahunIni  = this._tahun
        const tahunLalu = tahunIni - 1

        const sliceIdx  = (state.bulan && state.bulan !== 'all') ? Number.parseInt(state.bulan) + 1 : 12
        const labels    = this._getLabels().slice(0, sliceIdx)
        const target    = viewData.target.slice(0, sliceIdx)
        const realisasi = viewData.realisasi.slice(0, sliceIdx)
        const capaian   = viewData.capaian.slice(0, sliceIdx)
        const thnLalu   = (yoyData.tahun_lalu ?? []).slice(0, sliceIdx)

        this.chart.make('chartBabinBar', 'bar', labels, [
            { label: 'Realisasi ' + tahunLalu, data: thnLalu,   backgroundColor: '#2196f3' },
            { label: 'Target ' + tahunIni,     data: target,    backgroundColor: '#ff9800' },
            { label: 'Realisasi ' + tahunIni,  data: realisasi, backgroundColor: '#00ff88' },
        ], v => v == null ? '—' : (v / 1_000_000).toFixed(1) + ' jt kWh')

        const delta      = realisasi.map((r, i) => r !== null && target[i] !== null ? r - target[i] : null)
        const deltaColor = delta.map(v => {
            if (v === null) return '#555'
            return v >= 0 ? '#00ff88' : '#f44336'
        })
        this.chart.make('chartBabinDelta', 'bar', labels, [
            { label: 'Delta (Realisasi - Target)', data: delta, backgroundColor: deltaColor },
        ], v => v == null ? '—' : (v / 1_000_000).toFixed(1) + ' jt kWh')

        const akumData     = data.akumulasi[area]
        const validAkumIdx = akumData.realisasi.map((v, i) => v == null ? -1 : i).filter(i => i >= 0)
        const lastAkumIdx  = validAkumIdx.length > 0 ? validAkumIdx[validAkumIdx.length - 1] : sliceIdx - 1
        const lastAkumReal = akumData.realisasi[lastAkumIdx] ?? 0
        const lastAkumTgt  = akumData.target[lastAkumIdx] ?? 0
        const lastLabel    = this._getLabels()[lastAkumIdx] ?? ''
        const sisaTgt      = Math.max(0, lastAkumTgt - lastAkumReal)

        this.chart.pie('chartBabinPie',
            ['Tercapai (' + lastLabel + ')', 'Belum'],
            [lastAkumReal, sisaTgt],
            ['#ff9800', '#2a2a2a']
        )

        const histLabels = histData.map(d => String(d.tahun))
        const histValues = histData.map(d => d.total)
        this.chart.make('chartAllJualBar', 'bar', histLabels, [
            {
                label: 'Total Realisasi per Tahun',
                data: histValues,
                backgroundColor: histValues.map((_, i) => i === histValues.length - 1 ? '#00ff88' : '#2196f3'),
            },
        ], v => v == null ? '—' : (v / 1_000_000).toFixed(1) + ' jt kWh')

        this._renderTable(labels, target, realisasi, thnLalu, delta, capaian)
        this._renderGrowthTable(histData)

        // Edit table pakai tahun edit sendiri (independen dari filter chart)
        this._renderEditTable(this._editData ?? data, area)
    }

    initFilters(state, onChange) {
        document.getElementById('filterSectionBabin')?.addEventListener('change', e => {
            state.section = e.target.value
            onChange()
        })
        document.getElementById('filterTahunBabin')?.addEventListener('change', e => {
            state.tahun = Number.parseInt(e.target.value)
            onChange()
        })
        document.getElementById('filterViewBabin')?.addEventListener('change', e => {
            state.view = e.target.value
            onChange()
        })
        document.getElementById('filterBulanBabin')?.addEventListener('change', e => {
            state.bulan = e.target.value
            onChange()
        })

        document.getElementById('filterTahunEditBabin')?.addEventListener('change', async e => {
            const tahun = Number.parseInt(e.target.value)
            this._editTahun = tahun
            if (this.fetchByYear) {
                this._editData = await this.fetchByYear(tahun)
            } else {
                this._editData = (tahun === this._tahun) ? this._rawData : null
            }
            const area = state.section === 'babin' ? 'interkoneksi' : 'sembulang'
            this._renderEditTable(this._editData ?? this._rawData, area)
        })

        this._initCrud(state, onChange)
    }

    _renderTable(labels, target, realisasi, thnLalu, delta, capaian) {
        const tbody = document.getElementById('babinSideTable')
        if (!tbody) return
        const fmt  = v => v == null ? '—' : (v / 1_000_000).toFixed(2)
        const fmtP = v => v == null ? '—' : v.toFixed(1) + '%'

        tbody.innerHTML = labels.map((bln, i) => {
            let color = '#888'
            if (delta[i] !== null) color = delta[i] >= 0 ? 'var(--accent-green)' : '#f44336'
            let deltaCell = '—'
            if (delta[i] !== null) {
                const sign = delta[i] >= 0 ? '+' : ''
                deltaCell = sign + fmt(delta[i])
            }
            return '<tr>' +
                '<td style="padding:7px 8px;">' + bln + '</td>' +
                '<td style="padding:7px 8px; color:#2196f3;">' + fmt(thnLalu[i]) + '</td>' +
                '<td style="padding:7px 8px; color:#ff9800;">' + fmt(target[i]) + '</td>' +
                '<td style="padding:7px 8px; color:var(--accent-green);">' + fmt(realisasi[i]) + '</td>' +
                '<td style="padding:7px 8px; color:' + color + ';">' + deltaCell + '</td>' +
                '<td style="padding:7px 8px; color:' + color + ';">' + fmtP(capaian[i]) + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderGrowthTable(histData) {
        const tbody = document.getElementById('allJualSideTable')
        if (!tbody) return

        const thead = tbody.closest('table')?.querySelector('thead tr')
        if (thead) {
            const thTahun = histData.map(d =>
                '<th style="padding:8px; color:#ce93d8; min-width:90px; text-align:right;">' + d.tahun + '<br>' +
                '<span style="color:#555; font-size:0.6rem; font-weight:normal;">Growth</span></th>'
            ).join('')
            thead.innerHTML = '<th style="padding:10px; color:#888;">Bulan</th>' + thTahun
        }

        const bulanLabels = this._getLabels()
        const fmt  = v => v == null ? '—' : (v / 1_000_000).toFixed(2)
        const fmtG = v => {
            if (v === null) return '<div style="color:#444; font-size:0.7rem;">—</div>'
            const c = v >= 0 ? '#00c853' : '#f44336'
            return '<div style="color:' + c + '; font-size:0.7rem;">' + (v >= 0 ? '+' : '') + v.toFixed(1) + '%</div>'
        }

        const rows = bulanLabels.map((bln, i) => {
            const cells = histData.map(d =>
                '<td style="padding:6px 8px; text-align:right;">' +
                '<div style="color:#aaa; font-size:0.8rem;">' + fmt(d.bulanan[i]) + '</div>' +
                fmtG(d.growth_bulanan[i]) + '</td>'
            ).join('')
            return '<tr><td style="padding:7px 10px; color:#888;">' + bln + '</td>' + cells + '</tr>'
        }).join('')

        const totalCells = histData.map(d => {
            let c = '#555'
            if (d.growth_tahunan !== null) c = d.growth_tahunan >= 0 ? '#00c853' : '#f44336'
            let growthText
            if (d.growth_tahunan === null) {
                growthText = '—'
            } else {
                const sign = d.growth_tahunan >= 0 ? '+' : ''
                growthText = sign + d.growth_tahunan.toFixed(1) + '%'
            }
            return '<td style="padding:8px; text-align:right; border-top:1px solid #333;">' +
                '<div style="color:#fff; font-weight:600; font-size:0.8rem;">' + fmt(d.total) + '</div>' +
                '<div style="color:' + c + '; font-size:0.7rem;">' + growthText + '</div></td>'
        }).join('')

        tbody.innerHTML = rows +
            '<tr style="background:rgba(255,255,255,0.04);">' +
            '<td style="padding:8px 10px; color:#fff; font-weight:600; border-top:1px solid #333;">TOTAL</td>' +
            totalCells + '</tr>'
    }

    _renderEditTable(data, area) {
        const editTahun  = this._editTahun ?? this._tahun
        const isReadOnly = editTahun < this._tahunSekarang
        const bulanan    = data?.bulanan?.[area]

        const warn = document.getElementById('babinEditWarning')
        if (warn) warn.style.display = isReadOnly ? 'inline-block' : 'none'

        const rows = this._getLabels().map((bln, i) => ({
            label: bln,
            fields: [
                { key: 'nilai_kwh_target',    value: bulanan?.target?.[i]    ?? '', color: '#ff9800' },
                { key: 'nilai_kwh_realisasi', value: bulanan?.realisasi?.[i] ?? '', color: '#00ff88' },
                { key: 'nilai_rp_target',     value: '', color: '#ff9800' },
                { key: 'nilai_rp_realisasi',  value: '', color: '#00ff88' },
            ],
        }))

        renderEditTable(
            'babinEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                const base  = { tahun: editTahun, bulan, area, is_akumulasi: 0, sumber: 'manual' }
                if (values.nilai_kwh_target !== null || values.nilai_rp_target !== null) {
                    await upsert(API, { tahun: editTahun, bulan, area, tipe: 'target', is_akumulasi: 0 },
                        { ...base, tipe: 'target', nilai_kwh: values.nilai_kwh_target, nilai_rp: values.nilai_rp_target ?? 0 })
                }
                if (values.nilai_kwh_realisasi !== null || values.nilai_rp_realisasi !== null) {
                    await upsert(API, { tahun: editTahun, bulan, area, tipe: 'realisasi', is_akumulasi: 0 },
                        { ...base, tipe: 'realisasi', nilai_kwh: values.nilai_kwh_realisasi, nilai_rp: values.nilai_rp_realisasi ?? 0 })
                }
            },
            ['Bulan', 'Target kWh', 'Realisasi kWh', 'Target Rp', 'Realisasi Rp']
        )
    }

    _initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatBabin')
        const modalHapus = document.getElementById('modalHapusBabin')

        const btnMuat = document.getElementById('btnMuatBabin')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatBabin')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatBabin')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('muatTahunBabin')?.value)
                if (modalMuat) modalMuat.style.display = 'none'
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }

        const btnHapus = document.getElementById('btnHapusBabin')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusBabin')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusBabin')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunBabin')?.value
                const tipe  = document.getElementById('hapusTipeBabin')?.value
                if (!confirm('Hapus data Babin tahun ' + tahun + ' (' + tipe + ')? Tidak dapat dibatalkan.')) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    const params = tipe === 'semua' ? { tahun } : { tahun, tipe }
                    await deleteByFilter(API, params)
                    onChange()
                } catch {
                    alert('Gagal menghapus data')
                }
            })
        }

        const modalBuat = document.getElementById('modalBuatBabin')
        const btnBuat   = document.getElementById('btnBuatBabin')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatBabin')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatBabin')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('buatTahunBabin')?.value)
                const area  = document.getElementById('buatAreaBabin')?.value
                const tipe  = document.getElementById('buatTipeBabin2')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                const areas = area === 'semua' ? ['interkoneksi', 'sembulang'] : [area]
                let ok = true
                for (const a of areas) {
                    for (let bulan = 1; bulan <= 12; bulan++) {
                        try {
                            const res = await fetch(API, {
                                method: 'POST', headers: heads,
                                body: JSON.stringify({ tahun, bulan, area: a, tipe, is_akumulasi: 0, sumber: 'manual', nilai_kwh: 0, nilai_rp: 0 }),
                            })
                            if (!res.ok) ok = false
                        } catch { ok = false }
                    }
                }
                alert(ok ? `Data Babin tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }
    }
}