import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const API        = '/api/v1/fact/pendapatan'
const BULAN_NAMA = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER']

export default class PendapatanBatamModule {
    constructor(chart) {
        this.chart  = chart
        this._tahun = TAHUN_INI
        this._data  = null
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanPendapatan option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return
        this._data  = data
        this._tahun = data.tahun_efektif ?? TAHUN_INI

        const warning = document.getElementById('pendapatanWarning')
        if (warning) warning.style.display = data.is_fallback ? 'inline-block' : 'none'

        const sel = document.getElementById('filterTarifPendapatan')
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement('option')
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = state.tarif || 'JUMLAH'
        }

        const tarif    = state.tarif || 'JUMLAH'
        const selected = data.data?.[tarif]
        if (!selected) return

        const sliceIdx        = (state.bulan && state.bulan !== 'all') ? parseInt(state.bulan) + 1 : 12
        const labels          = this._getLabels()
        const slicedLabels    = labels.slice(0, sliceIdx)
        const slicedRencana   = selected.rencana.slice(0, sliceIdx)
        const slicedRealisasi = selected.realisasi.slice(0, sliceIdx)

        this.chart.renderWithLabels('chartPendapatanBatam', slicedLabels, {
            rencana:   slicedRencana,
            realisasi: slicedRealisasi,
        })

        const delta = slicedRealisasi.map((v, i) => (v ?? 0) - (slicedRencana[i] ?? 0))
        this.chart.make('chartDeltaPendapatan', 'bar', slicedLabels, [{
            label:           'Delta',
            data:            delta,
            backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336'),
        }], v => 'Rp ' + (v / 1_000_000_000).toFixed(2) + 'M')

        this._renderRencanaTable(slicedLabels, slicedRencana, slicedRealisasi)
        this._renderDeltaTable(slicedLabels, delta)
        this._renderDetailTable(data.realisasiBulanan, labels, sliceIdx)
        this._renderEditTable(data, tarif)
    }

    initFilters(state, onChange) {
        document.getElementById('filterViewPendapatan')?.addEventListener('change',  e => { state.view  = e.target.value; onChange() })
        document.getElementById('filterTarifPendapatan')?.addEventListener('change', e => { state.tarif = e.target.value; onChange() })
        document.getElementById('filterBulanPendapatan')?.addEventListener('change', e => { state.bulan = e.target.value; onChange() })
        this._initCrud(state, onChange)
    }

    _renderRencanaTable(labels, rencana, realisasi) {
        const tbody = document.getElementById('pendapatanSideTable')
        if (!tbody) return
        const fmt = v => 'Rp ' + ((v ?? 0) / 1_000_000_000).toFixed(2) + 'M'
        tbody.innerHTML = labels.map((bln, i) => {
            const r  = rencana[i]   ?? 0
            const rl = realisasi[i] ?? 0
            const d  = rl - r
            const c  = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return '<tr>' +
                '<td>' + bln + '</td>' +
                '<td style="color:var(--accent-orange);">' + fmt(r) + '</td>' +
                '<td style="color:var(--accent-green);">' + fmt(rl) + '</td>' +
                '<td style="color:' + c + ';font-weight:600;">' + (d >= 0 ? '+' : '') + fmt(d) + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderDeltaTable(labels, delta) {
        const tbody = document.getElementById('tabelPendapatanDelta')
        if (!tbody) return
        const fmt = v => 'Rp ' + ((v ?? 0) / 1_000_000_000).toFixed(2) + 'M'
        tbody.innerHTML = labels.map((bln, i) => {
            const d = delta[i] ?? 0
            const c = d >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td>' + bln + '</td>' +
                '<td style="color:' + c + ';font-weight:600;">' + (d >= 0 ? '+' : '') + fmt(d) + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderDetailTable(realisasiBulanan, labels, sliceIdx) {
        const tbody = document.getElementById('tabelPendapatan')
        if (!tbody || !realisasiBulanan) return
        const fmt = v => 'Rp ' + ((v ?? 0) / 1_000_000_000).toFixed(2) + ' M'
        tbody.innerHTML = realisasiBulanan.slice(0, sliceIdx).map((row, i) =>
            '<tr>' +
            '<td>' + (labels[i] ?? row.bulan) + '</td>' +
            '<td style="color:var(--accent-orange);">' + fmt(row.target) + '</td>' +
            '<td style="color:var(--accent-green);">' + fmt(row.realisasi) + '</td>' +
            '<td style="color:' + (row.delta >= 0 ? '#00ff88' : '#f44336') + ';">' + fmt(row.delta) + '</td>' +
            '<td style="color:var(--accent-orange);font-weight:600;">' + (row.pencapaian * 100).toFixed(2) + '%</td>' +
            '</tr>'
        ).join('')
    }

    _renderEditTable(data, tarif) {
        const isReadOnly = this._tahun < TAHUN_INI
        const selected   = data.data?.[tarif]
        const rencana    = selected?.rencana   ?? new Array(12).fill(null)
        const realisasi  = selected?.realisasi ?? new Array(12).fill(null)

        // Deteksi golongan_kode dari tarif (e.g. 'JUMLAH Sosial (S)' → 'S', 'JUMLAH' → 'TOTAL')
        const golMatch = tarif.match(/\(([A-Z])\)/)
        const golKode  = golMatch ? golMatch[1] : 'TOTAL'
        const subKode  = tarif

        const rows = BULAN_NAMA.map((bln, i) => ({
            label: bln,
            fields: [
                { key: 'nilai_target',    value: rencana[i]   ?? '', color: '#ff9800' },
                { key: 'nilai_realisasi', value: realisasi[i] ?? '', color: '#00ff88' },
            ],
        }))

        renderEditTable(
            'pendapatanEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                const base  = { tahun: this._tahun, bulan, golongan_kode: golKode, sub_golongan_kode: subKode, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }
                if (values.nilai_target !== null)
                    await upsert(API,
                        { tahun: this._tahun, bulan, sub_golongan_kode: subKode, tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'target', nilai_rp: values.nilai_target })
                if (values.nilai_realisasi !== null)
                    await upsert(API,
                        { tahun: this._tahun, bulan, sub_golongan_kode: subKode, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'realisasi', nilai_rp: values.nilai_realisasi })
            },
            ['Bulan', 'Target (Rp)', 'Realisasi (Rp)'],
            // ── Live-recalc ──────────────────────────────────────────────────
            (ri, values) => {
                if (values.nilai_target    !== null) rencana[ri]   = values.nilai_target
                if (values.nilai_realisasi !== null) realisasi[ri] = values.nilai_realisasi

                const labels      = this._getLabels()
                const sliceIdx    = rencana.length
                const slicedLabels    = labels.slice(0, sliceIdx)
                const slicedRencana   = rencana.slice(0, sliceIdx)
                const slicedRealisasi = realisasi.slice(0, sliceIdx)
                const delta = slicedRealisasi.map((v, i) => (v ?? 0) - (slicedRencana[i] ?? 0))

                this._renderRencanaTable(slicedLabels, slicedRencana, slicedRealisasi)
                this._renderDeltaTable(slicedLabels, delta)
                this.chart.renderWithLabels('chartPendapatanBatam', slicedLabels, { rencana: slicedRencana, realisasi: slicedRealisasi })
                this.chart.make('chartDeltaPendapatan', 'bar', slicedLabels, [{
                    label: 'Delta', data: delta,
                    backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336'),
                }], v => 'Rp ' + (v / 1_000_000_000).toFixed(2) + 'M')
            }
        )

        const w = document.getElementById('pendapatanEditWarning')
        if (w) w.style.display = isReadOnly ? 'inline-block' : 'none'
    }

    _initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatPendapatan')
        const modalHapus = document.getElementById('modalHapusPendapatan')

        const btnMuat = document.getElementById('btnMuatPendapatan')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatPendapatan')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatPendapatan')?.addEventListener('click', async () => {
                const tahun = parseInt(document.getElementById('muatTahunPendapatan')?.value)
                if (modalMuat) modalMuat.style.display = 'none'
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }

        const btnHapus = document.getElementById('btnHapusPendapatan')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusPendapatan')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusPendapatan')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunPendapatan')?.value
                const tipe  = document.getElementById('hapusTipePendapatan')?.value
                if (!confirm('Hapus data Pendapatan tahun ' + tahun + ' (' + tipe + ')? Tidak dapat dibatalkan.')) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    const params = tipe === 'semua' ? { tahun } : { tahun, tipe }
                    await deleteByFilter(API, params)
                    onChange()
                } catch { alert('Gagal menghapus data') }
            })
        }

        const modalBuat = document.getElementById('modalBuatPendapatan')
        const btnBuat   = document.getElementById('btnBuatPendapatan')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatPendapatan')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatPendapatan')?.addEventListener('click', async () => {
                const tahun = parseInt(document.getElementById('buatTahunPendapatan')?.value)
                const tipe  = document.getElementById('buatTipePendapatan')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch(API, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, golongan_kode: 'TOTAL', sub_golongan_kode: 'JUMLAH', tipe, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_rp: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data Pendapatan tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }
    }
}
