import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const BULAN_NAMA = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
const API_LOSSES  = '/api/v1/fact/losses'
const API_PRODUKSI = '/api/rt-fact-produksi'

export default class LossesModule {
    constructor(chart) {
        this.chart    = chart
        this._rawData = null
        this._tahun   = TAHUN_INI
    }

    render(data, state) {
        if (!data) return
        this._rawData = data
        this._tahun   = data.tahun_efektif ?? TAHUN_INI

        const warning = document.getElementById('lossesWarning')
        if (warning) warning.style.display = data.is_fallback ? 'inline-block' : 'none'

        const source = (state.emin === 'without' && data.withoutEmin) ? data.withoutEmin : data.withEmin
        if (!source?.length) return

        const d = (state.bulan && state.bulan !== 'all')
            ? source.slice(0, parseInt(state.bulan) + 1) : source
        const labels = d.map(r => r.bulan)

        this.chart.make('chartLossesProduksi', 'bar', labels, [
            { label: 'Prod Bruto', data: d.map(r => r.prodBruto), backgroundColor: '#2196f3' },
            { label: 'Prod Netto', data: d.map(r => r.prodNetto), backgroundColor: '#00ff88' },
            { label: 'Siap Salur', data: d.map(r => r.siapSalur), backgroundColor: '#ff9800' },
        ], v => (v / 1_000_000).toFixed(1) + ' jt kWh')

        this.chart.make('chartLossesPct', 'line', labels, [
            { label: 'Losses Transmisi Bln (%)',  data: d.map(r => r.lossesBulanan * 100),         borderColor: '#f44336', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Losses Transmisi Akum (%)', data: d.map(r => r.lossesAkum * 100),            borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Losses Jaringan Bln (%)',   data: d.map(r => r.lossesJaringanBulanan * 100), borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Losses Jaringan Akum (%)',  data: d.map(r => r.lossesJaringanAkum * 100),    borderColor: '#9c27b0', tension: 0.4, fill: false, pointRadius: 3 },
        ], v => v.toFixed(3) + '%')

        this.chart.make('chartLossesKwh', 'bar', labels, [
            { label: 'Losses Transmisi',  data: d.map(r => r.lossesKwh),           backgroundColor: '#e91e63' },
            { label: 'Losses Distribusi', data: d.map(r => r.lossesDistribusiKwh), backgroundColor: '#9c27b0' },
            { label: 'Losses Jaringan',   data: d.map(r => r.lossesJaringanKwh),   backgroundColor: '#f44336' },
        ], v => (v / 1_000_000).toFixed(2) + ' jt kWh')

        this._renderProduksiTable(d)
        this._renderPctTable(d)
        this._renderKwhTable(d)
        this._renderDetailTable(d)
        this._renderEditTable(source)
    }

    initFilters(state, onChange) {
        document.getElementById('filterEminLosses')?.addEventListener('change',  e => { state.emin  = e.target.value; onChange() })
        document.getElementById('filterBulanLosses')?.addEventListener('change', e => { state.bulan = e.target.value; onChange() })
        this._initCrud(state, onChange)
    }

    _renderProduksiTable(d) {
        const tbody = document.getElementById('lossesSideTable')
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        tbody.innerHTML = d.map(r =>
            '<tr><td>' + r.bulan + '</td>' +
            '<td style="color:#2196f3;">' + fmt(r.prodBruto) + '</td>' +
            '<td style="color:var(--accent-green);">' + fmt(r.prodNetto) + '</td>' +
            '<td style="color:#ff9800;">' + fmt(r.siapSalur) + '</td></tr>'
        ).join('')
    }

    _renderPctTable(d) {
        const tbody = document.getElementById('tabelLossesPct')
        if (!tbody) return
        const fmtPct = v => (v * 100).toFixed(4) + '%'
        tbody.innerHTML = d.map(r =>
            '<tr><td>' + r.bulan + '</td>' +
            '<td style="color:#f44336;">' + fmtPct(r.lossesBulanan) + '</td>' +
            '<td style="color:#ff9800;">' + fmtPct(r.lossesAkum) + '</td>' +
            '<td style="color:var(--accent-green);">' + fmtPct(r.lossesJaringanBulanan) + '</td>' +
            '<td style="color:#9c27b0;">' + fmtPct(r.lossesJaringanAkum) + '</td></tr>'
        ).join('')
    }

    _renderKwhTable(d) {
        const tbody = document.getElementById('tabelLossesKwh')
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        tbody.innerHTML = d.map(r =>
            '<tr><td>' + r.bulan + '</td>' +
            '<td style="color:#e91e63;">' + fmt(r.lossesKwh) + '</td>' +
            '<td style="color:#9c27b0;">' + fmt(r.lossesDistribusiKwh) + '</td>' +
            '<td style="color:#f44336;">' + fmt(r.lossesJaringanKwh) + '</td></tr>'
        ).join('')
    }

    _renderDetailTable(d) {
        const tbody = document.getElementById('tabelLosses')
        if (!tbody) return
        const fmt    = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(4) + '%'
        tbody.innerHTML = d.map(r =>
            '<tr><td>' + r.bulan + '</td>' +
            '<td>' + fmt(r.prodBruto) + '</td>' +
            '<td>' + fmt(r.prodNetto) + '</td>' +
            '<td>' + fmt(r.siapSalur) + '</td>' +
            '<td style="color:#e91e63;">' + fmt(r.lossesKwh) + '</td>' +
            '<td style="color:#f44336;">' + fmtPct(r.lossesBulanan) + '</td>' +
            '<td style="color:#ff9800;">' + fmtPct(r.lossesAkum) + '</td>' +
            '<td>' + fmt(r.interkoneksi) + '</td>' +
            '<td style="color:#9c27b0;">' + fmt(r.lossesDistribusiKwh) + '</td>' +
            '<td style="color:#9c27b0;">' + fmtPct(r.lossesDistribusiBulanan) + '</td>' +
            '<td style="color:#9c27b0;">' + fmtPct(r.lossesDistribusiAkum) + '</td>' +
            '<td style="color:#f44336;">' + fmt(r.lossesJaringanKwh) + '</td>' +
            '<td style="color:#00ff88;">' + fmtPct(r.lossesJaringanBulanan) + '</td>' +
            '<td style="color:#00ff88;">' + fmtPct(r.lossesJaringanAkum) + '</td>' +
            '<td style="color:var(--accent-orange);font-weight:600;">' + fmtPct(r.lossesTarget) + '</td>' +
            '</tr>'
        ).join('')
    }

    _renderEditTable(source) {
        const isReadOnly = this._tahun < TAHUN_INI
        // Edit produksi (prod_bruto, prod_netto, siap_salur, interkoneksi)
        const rowsProduksi = BULAN_NAMA.map((bln, i) => {
            const r = source[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'prod_bruto',           value: r.prodBruto     ?? '', color: '#2196f3' },
                    { key: 'prod_netto',           value: r.prodNetto     ?? '', color: '#00ff88' },
                    { key: 'siap_salur_transmisi', value: r.siapSalur     ?? '', color: '#ff9800' },
                    { key: 'interkoneksi',         value: r.interkoneksi  ?? '', color: '#aaa'    },
                ],
            }
        })
        renderEditTable(
            'lossesEditProduksi',
            rowsProduksi,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                await upsert(API_PRODUKSI, { tahun: this._tahun, bulan },
                    { tahun: this._tahun, bulan, sumber: 'manual', ...values })
            },
            ['Bulan', 'Prod Bruto (kWh)', 'Prod Netto (kWh)', 'Siap Salur (kWh)', 'Interkoneksi (kWh)'],
            // ── Live-recalc ──────────────────────────────────────────────────
            (ri, values) => {
                const r = source[ri]
                if (!r) return
                if (values.prod_bruto           !== null) r.prodBruto   = values.prod_bruto
                if (values.prod_netto           !== null) r.prodNetto   = values.prod_netto
                if (values.siap_salur_transmisi !== null) r.siapSalur   = values.siap_salur_transmisi
                if (values.interkoneksi         !== null) r.interkoneksi = values.interkoneksi

                this._renderProduksiTable(source)
                this.chart.make('chartLossesProduksi', 'bar', source.map(r => r.bulan), [
                    { label: 'Prod Bruto', data: source.map(r => r.prodBruto), backgroundColor: '#2196f3' },
                    { label: 'Prod Netto', data: source.map(r => r.prodNetto), backgroundColor: '#00ff88' },
                    { label: 'Siap Salur', data: source.map(r => r.siapSalur), backgroundColor: '#ff9800' },
                ], v => (v / 1_000_000).toFixed(1) + ' jt kWh')
            }
        )

        // Edit losses per kategori (nilai_kwh + nilai_pct)
        const rowsLosses = BULAN_NAMA.map((bln, i) => {
            const r = source[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'losses_trans_kwh',  value: r.lossesKwh              ?? '', color: '#e91e63' },
                    { key: 'losses_trans_pct',  value: r.lossesBulanan !== undefined ? (r.lossesBulanan * 100).toFixed(4) : '', color: '#f44336' },
                    { key: 'losses_dist_kwh',   value: r.lossesDistribusiKwh    ?? '', color: '#9c27b0' },
                    { key: 'losses_dist_pct',   value: r.lossesDistribusiBulanan !== undefined ? (r.lossesDistribusiBulanan * 100).toFixed(4) : '', color: '#9c27b0' },
                    { key: 'losses_jar_kwh',    value: r.lossesJaringanKwh      ?? '', color: '#f44336' },
                    { key: 'losses_jar_pct',    value: r.lossesJaringanBulanan  !== undefined ? (r.lossesJaringanBulanan * 100).toFixed(4) : '', color: '#00ff88' },
                    { key: 'losses_target_pct', value: r.lossesTarget           !== undefined ? (r.lossesTarget * 100).toFixed(4) : '', color: '#ff9800' },
                ],
            }
        })
        renderEditTable(
            'lossesEditLosses',
            rowsLosses,
            isReadOnly,
            async (ri, values) => {
                const bulan      = ri + 1
                const tipeLosses = 'dengan_emin'
                const baseParams  = { tahun: this._tahun, bulan, tipe: 'realisasi', tipe_losses: tipeLosses, is_triwulan: 0, is_akumulasi: 0 }
                const basePayload = { ...baseParams, sumber: 'manual' }
                if (values.losses_trans_kwh !== null || values.losses_trans_pct !== null)
                    await upsert(API_LOSSES, { ...baseParams, kategori: 'transmisi' },
                        { ...basePayload, kategori: 'transmisi', nilai_kwh: values.losses_trans_kwh, nilai_pct: values.losses_trans_pct })
                if (values.losses_dist_kwh !== null || values.losses_dist_pct !== null)
                    await upsert(API_LOSSES, { ...baseParams, kategori: 'distribusi' },
                        { ...basePayload, kategori: 'distribusi', nilai_kwh: values.losses_dist_kwh, nilai_pct: values.losses_dist_pct })
                if (values.losses_jar_kwh !== null || values.losses_jar_pct !== null)
                    await upsert(API_LOSSES, { ...baseParams, kategori: 'jaringan' },
                        { ...basePayload, kategori: 'jaringan', nilai_kwh: values.losses_jar_kwh, nilai_pct: values.losses_jar_pct })
                if (values.losses_target_pct !== null)
                    await upsert(API_LOSSES,
                        { tahun: this._tahun, bulan, tipe: 'target', tipe_losses: tipeLosses, kategori: 'jaringan', is_triwulan: 0, is_akumulasi: 0 },
                        { tahun: this._tahun, bulan, tipe: 'target', tipe_losses: tipeLosses, kategori: 'jaringan', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_pct: values.losses_target_pct })
            },
            ['Bulan', 'Trans kWh', 'Trans %', 'Dist kWh', 'Dist %', 'Jar kWh', 'Jar %', 'Target %'],
            // ── Live-recalc ──────────────────────────────────────────────────
            (ri, values) => {
                const r = source[ri]
                if (!r) return
                if (values.losses_trans_kwh  !== null) r.lossesKwh               = values.losses_trans_kwh
                if (values.losses_trans_pct  !== null) r.lossesBulanan            = values.losses_trans_pct / 100
                if (values.losses_dist_kwh   !== null) r.lossesDistribusiKwh      = values.losses_dist_kwh
                if (values.losses_dist_pct   !== null) r.lossesDistribusiBulanan  = values.losses_dist_pct / 100
                if (values.losses_jar_kwh    !== null) r.lossesJaringanKwh        = values.losses_jar_kwh
                if (values.losses_jar_pct    !== null) r.lossesJaringanBulanan    = values.losses_jar_pct / 100
                if (values.losses_target_pct !== null) r.lossesTarget             = values.losses_target_pct / 100

                this._renderPctTable(source)
                this._renderKwhTable(source)
                this._renderDetailTable(source)
                const labels = source.map(r => r.bulan)
                this.chart.make('chartLossesPct', 'line', labels, [
                    { label: 'Losses Transmisi Bln (%)',  data: source.map(r => r.lossesBulanan * 100),         borderColor: '#f44336', tension: 0.4, fill: false, pointRadius: 3 },
                    { label: 'Losses Transmisi Akum (%)', data: source.map(r => r.lossesAkum * 100),            borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
                    { label: 'Losses Jaringan Bln (%)',   data: source.map(r => r.lossesJaringanBulanan * 100), borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
                    { label: 'Losses Jaringan Akum (%)',  data: source.map(r => r.lossesJaringanAkum * 100),    borderColor: '#9c27b0', tension: 0.4, fill: false, pointRadius: 3 },
                ], v => v.toFixed(3) + '%')
                this.chart.make('chartLossesKwh', 'bar', labels, [
                    { label: 'Losses Transmisi',  data: source.map(r => r.lossesKwh),           backgroundColor: '#e91e63' },
                    { label: 'Losses Distribusi', data: source.map(r => r.lossesDistribusiKwh), backgroundColor: '#9c27b0' },
                    { label: 'Losses Jaringan',   data: source.map(r => r.lossesJaringanKwh),   backgroundColor: '#f44336' },
                ], v => (v / 1_000_000).toFixed(2) + ' jt kWh')
            }
        )
    }

    _initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatLosses')
        const modalHapus = document.getElementById('modalHapusLosses')

        const btnMuat = document.getElementById('btnMuatLosses')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatLosses')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatLosses')?.addEventListener('click', async () => {
                const tahun = parseInt(document.getElementById('muatTahunLosses')?.value)
                const tipe  = document.getElementById('muatTipeLosses')?.value
                if (modalMuat) modalMuat.style.display = 'none'
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }

        const btnHapus = document.getElementById('btnHapusLosses')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusLosses')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusLosses')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunLosses')?.value
                const tipe  = document.getElementById('hapusTipeLosses')?.value
                if (!confirm('Hapus data Losses tahun ' + tahun + ' (' + tipe + ')? Tidak dapat dibatalkan.')) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    const params = tipe === 'semua' ? { tahun } : { tahun, tipe }
                    await deleteByFilter(API_LOSSES, params)
                    onChange()
                } catch (e) {
                    alert('Gagal menghapus data')
                }
            })
        }

        const modalBuat = document.getElementById('modalBuatLosses')
        const btnBuat   = document.getElementById('btnBuatLosses')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatLosses')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatLosses')?.addEventListener('click', async () => {
                const tahun      = parseInt(document.getElementById('buatTahunLosses')?.value)
                const tipeLosses = document.getElementById('buatTipeLosses')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true

                for (let bulan = 1; bulan <= 12; bulan++) {
                    // Produksi
                    try {
                        const res = await fetch(API_PRODUKSI, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, sumber: 'manual', prod_bruto: 0, prod_netto: 0, siap_salur_transmisi: 0, interkoneksi: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }

                    // Losses: transmisi, distribusi, jaringan (realisasi)
                    for (const kategori of ['transmisi', 'distribusi', 'jaringan']) {
                        try {
                            const res = await fetch(API_LOSSES, {
                                method: 'POST', headers: heads,
                                body: JSON.stringify({ tahun, bulan, tipe: 'realisasi', tipe_losses: tipeLosses, kategori, nilai_kwh: 0, nilai_pct: 0, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                            })
                            if (!res.ok) ok = false
                        } catch { ok = false }
                    }
                    // Target jaringan
                    try {
                        const res = await fetch(API_LOSSES, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, tipe: 'target', tipe_losses: tipeLosses, kategori: 'jaringan', nilai_pct: 0, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data Losses tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }
    }
}
