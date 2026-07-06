import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'
import { apiFetch } from '@/Dashboard/utils/apiClient'

const BULAN_NAMA   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
const API_LOSSES   = '/api/v1/fact/losses'
// FIX: sebelumnya '/api/rt-fact-produksi' (hilang /v1) — route sebenarnya
// terdaftar di dalam Route::prefix('v1') pada routes/api.php
const API_PRODUKSI = '/api/v1/rt-fact-produksi'

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
        this._state   = state   // simpan state untuk dipakai di _renderEditTable (tipe_losses match key)

        const warning = document.getElementById('lossesWarning')
        if (warning) warning.style.display = data.is_fallback ? 'inline-block' : 'none'

        const source = (state.emin === 'without' && data.withoutEmin) ? data.withoutEmin : data.withEmin
        if (!source?.length) return

        const d = (state.bulan && state.bulan !== 'all')
            ? source.slice(0, Number.parseInt(state.bulan) + 1) : source
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

    /**
     * ── REVISI ──────────────────────────────────────────────────────────
     * Mengikuti kontrak data baru dari RtFactLossesService:
     * - Semua field produksi (termasuk yang sebelumnya tidak ada di form:
     *   loko_transmisi, ps_gi, ps_distribusi, blkg_padang, sembulang,
     *   buluh, p_nguan, penjualan_tul3_09) jadi editable di sini —
     *   inilah data primer yang jadi dasar hitung losses.
     * - Losses kWh & % (transmisi/distribusi/jaringan) TIDAK LAGI diedit
     *   manual di sini — sudah dihitung otomatis oleh backend
     *   (RtFactLossesService::buildSource) dari field produksi di atas.
     * - Satu-satunya nilai losses yang tetap manual: Target %.
     * - Setiap simpan produksi menyertakan `tipe_losses` (mengikuti
     *   filter emin yang sedang aktif) sebagai bagian dari match-key,
     *   karena sekarang 1 bulan bisa punya 2 baris (dengan_emin/tanpa_emin).
     */
    _renderEditTable(source) {
        const isReadOnly = this._tahun < TAHUN_INI
        const tipeLosses = (this._state?.emin === 'without') ? 'tanpa_emin' : 'dengan_emin'

        // ── Edit Produksi (data primer, lengkap) ────────────────────────
        const rowsProduksi = BULAN_NAMA.map((bln, i) => {
            const r = source[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'prod_bruto',           value: r.prodBruto     ?? '', color: '#2196f3' },
                    { key: 'ps_kit',               value: r.psKit         ?? '', color: '#888'    },
                    { key: 'prod_netto',           value: r.prodNetto     ?? '', color: '#00ff88' },
                    { key: 'loko_transmisi',       value: r.lokoTransmisi ?? '', color: '#ff9800' },
                    { key: 'siap_salur_transmisi', value: r.siapSalur     ?? '', color: '#ff9800' },
                    { key: 'ps_gi',                value: r.psGi          ?? '', color: '#aaa'    },
                    { key: 'interkoneksi',         value: r.interkoneksi  ?? '', color: '#aaa'    },
                    { key: 'ps_distribusi',        value: r.psDistribusi  ?? '', color: '#9c27b0' },
                    { key: 'blkg_padang',          value: r.blkgPadang    ?? '', color: '#888'    },
                    { key: 'sembulang',            value: r.sembulang     ?? '', color: '#888'    },
                    { key: 'buluh',                value: r.buluh         ?? '', color: '#888'    },
                    { key: 'p_nguan',              value: r.pNguan        ?? '', color: '#888'    },
                    { key: 'penjualan_tul3_09',    value: r.penjualan     ?? '', color: '#00ff88' },
                ],
            }
        })

        renderEditTable(
            'lossesEditProduksi',
            rowsProduksi,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                await upsert(API_PRODUKSI,
                    { tahun: this._tahun, bulan, tipe_losses: tipeLosses },
                    { tahun: this._tahun, bulan, tipe_losses: tipeLosses, sumber: 'manual', ...values })
            },
            ['Bulan', 'Prod Bruto (kWh)', 'PS KIT (kWh)', 'Prod Netto (kWh)', 'Loko Transmisi (kWh)',
             'Siap Salur Trans (kWh)', 'PS GI (kWh)', 'Interkoneksi (kWh)', 'PS Distribusi (kWh)',
             'Blkg Padang (kWh)', 'Sembulang (kWh)', 'Buluh (kWh)', 'P. Nguan (kWh)', 'Penjualan TUL III-09 (kWh)'],
            // ── Live-recalc: update field mentah + hitung ulang losses turunan ──
            (ri, values) => {
                const r = source[ri]
                if (!r) return

                const map = {
                    prod_bruto: 'prodBruto', ps_kit: 'psKit', prod_netto: 'prodNetto',
                    loko_transmisi: 'lokoTransmisi', siap_salur_transmisi: 'siapSalur', ps_gi: 'psGi',
                    interkoneksi: 'interkoneksi', ps_distribusi: 'psDistribusi',
                    blkg_padang: 'blkgPadang', sembulang: 'sembulang', buluh: 'buluh',
                    p_nguan: 'pNguan', penjualan_tul3_09: 'penjualan',
                }
                Object.entries(values).forEach(([k, v]) => {
                    if (v !== null && map[k]) r[map[k]] = v
                })

                // Hitung ulang losses turunan di FE (preview optimis) — rumus
                // sama persis dengan RtFactLossesService::buildSource().
                // Catatan: kolom *Akum* butuh SUM seluruh bulan s.d. bulan ini,
                // jadi tidak di-recalc optimis di sini — akan akurat lagi
                // setelah reload dari API (backend tetap source of truth).
                const kirimLain = (r.blkgPadang || 0) + (r.sembulang || 0) + (r.buluh || 0) + (r.pNguan || 0)

                r.lossesKwh               = (r.lokoTransmisi || 0) - (r.siapSalur || 0) - (r.psGi || 0)
                r.lossesBulanan           = r.lokoTransmisi ? r.lossesKwh / r.lokoTransmisi : 0

                // Produksi Siap Salur → Loko Distribusi (samakan dgn backend)
                const produksiSiapSalur = (r.prodNetto || 0) - r.lossesKwh - (r.psGi || 0)
                const lokoDistribusi    = produksiSiapSalur - (r.interkoneksi || 0)

                r.lokoDistribusi          = lokoDistribusi
                r.lossesDistribusiKwh     = lokoDistribusi - (r.psDistribusi || 0) - kirimLain - (r.penjualan || 0)
                r.lossesDistribusiBulanan = lokoDistribusi ? r.lossesDistribusiKwh / lokoDistribusi : 0

                r.lossesJaringanKwh       = (r.prodNetto || 0) - (r.psGi || 0) - (r.interkoneksi || 0)
                                            - (r.psDistribusi || 0) - kirimLain - (r.penjualan || 0)
                r.lossesJaringanBulanan   = r.prodNetto ? r.lossesJaringanKwh / r.prodNetto : 0

                this._renderProduksiTable(source)
                this._renderPctTable(source)
                this._renderKwhTable(source)
                this._renderDetailTable(source)

                const labels = source.map(r => r.bulan)
                this.chart.make('chartLossesProduksi', 'bar', labels, [
                    { label: 'Prod Bruto', data: source.map(r => r.prodBruto), backgroundColor: '#2196f3' },
                    { label: 'Prod Netto', data: source.map(r => r.prodNetto), backgroundColor: '#00ff88' },
                    { label: 'Siap Salur', data: source.map(r => r.siapSalur), backgroundColor: '#ff9800' },
                ], v => (v / 1_000_000).toFixed(1) + ' jt kWh')
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

        // ── Edit Target % (satu-satunya nilai losses yang tetap manual) ─
        const rowsTarget = BULAN_NAMA.map((bln, i) => {
            const r = source[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'losses_target_pct', value: r.lossesTarget !== undefined ? (r.lossesTarget * 100).toFixed(4) : '', color: '#ff9800' },
                ],
            }
        })

        renderEditTable(
            'lossesEditLosses',
            rowsTarget,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                if (values.losses_target_pct !== null)
                    await upsert(API_LOSSES,
                        { tahun: this._tahun, bulan, tipe: 'target', tipe_losses: tipeLosses, kategori: 'jaringan', is_triwulan: 0, is_akumulasi: 0 },
                        { tahun: this._tahun, bulan, tipe: 'target', tipe_losses: tipeLosses, kategori: 'jaringan', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_pct: values.losses_target_pct })
            },
            ['Bulan', 'Target %'],
            (ri, values) => {
                const r = source[ri]
                if (!r) return
                if (values.losses_target_pct !== null) r.lossesTarget = values.losses_target_pct / 100
                this._renderDetailTable(source)
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
                const tahun = Number.parseInt(document.getElementById('muatTahunLosses')?.value)
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
                } catch {
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
                const tahun      = Number.parseInt(document.getElementById('buatTahunLosses')?.value)
                const tipeLosses = document.getElementById('buatTipeLosses')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true

                for (let bulan = 1; bulan <= 12; bulan++) {
                    // Produksi — sekarang punya tipe_losses & field lengkap
                    // FIX: pakai apiFetch() (bukan fetch mentah) supaya request
                    // menyertakan X-Api-Token/X-Api-Timestamp yang diwajibkan
                    // middleware `internal.api` — fetch() biasa akan ditolak.
                    try {
                        const res = await apiFetch(API_PRODUKSI, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({
                                tahun, bulan, tipe_losses: tipeLosses, sumber: 'manual',
                                prod_bruto: 0, ps_kit: 0, prod_netto: 0,
                                loko_transmisi: 0, siap_salur_transmisi: 0, ps_gi: 0,
                                interkoneksi: 0, ps_distribusi: 0,
                                blkg_padang: 0, sembulang: 0, buluh: 0, p_nguan: 0,
                                penjualan_tul3_09: 0,
                            }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }

                    // Target jaringan — satu-satunya yang masih di rt_fact_losses
                    try {
                        const res = await apiFetch(API_LOSSES, {
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