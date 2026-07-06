import { renderEditTable, upsert, TAHUN_INI } from '../utils/CrudHelper.js'

const LABELS     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
const COLORS     = ['#ff9800','#00ff88','#2196f3','#e91e63','#9c27b0','#00bcd4']
const API_RPKWH  = '/api/v1/fact/rpkwh'
const BULAN_NAMA = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER']

export default class RpkwhModule {
    constructor(chart, service = null) {
        this.chart   = chart
        this.service = service
        this._tahun  = TAHUN_INI
        this._data   = null
    }

    render(data, state) {
        if (!data) return
        this._data  = data
        this._tahun = data.tahun_efektif ?? TAHUN_INI

        if (!state.tarif || !data.rencanaData?.[state.tarif]) {
            state.tarif = data.tarif?.[0] ?? null
            const sel = document.getElementById('filterTarifRpkwh')
            if (sel && sel.options.length > 0) sel.value = state.tarif
        }

        this._renderKwh(data, state)
        this._populateTarif(data, state.tarif)
        this._renderRencanaRealisasi(data, state)
        this._renderGolongan(data, state)
        this._renderGrowth(data, state)
        this._renderBppHjr(data, state)
        this._renderAnalisa(data, state)
        this._renderPersen(data)
        this._renderEditTable(data, state)
    }

    initFilters(state, onChange) {
        document.getElementById('filterTarifRpkwh')?.addEventListener('change', e => { state.tarif = e.target.value; onChange() })
        document.getElementById('filterViewRpkwh')?.addEventListener('change',  e => { state.view  = e.target.value; onChange() })
        document.getElementById('filterBulanRpkwh')?.addEventListener('change', e => { state.bulan = e.target.value; onChange() })
        document.getElementById('filterTahunRpkwh')?.addEventListener('change', e => { state.tahun = e.target.value; onChange() })
        document.getElementById('filterBulanKwh')?.addEventListener('change',   e => { state.kwhBulan = e.target.value; onChange() })
        document.getElementById('filterViewKwh')?.addEventListener('change',    e => { state.kwhView  = e.target.value; onChange() })
        this._initCrud(onChange)
    }

    // ── KWH SECTION ──────────────────────────────────────────────────────────

    _renderKwh(data, state) {
        const kwhData = data.analisaData
        if (!kwhData?.length) return
        const akum     = state.kwhView === 'akumulasi'
        const filtered = (state.kwhBulan !== undefined && state.kwhBulan !== 'all')
            ? [kwhData[Number.parseInt(state.kwhBulan)]] : kwhData

        this.chart.renderKwhTargetRealisasi(filtered, akum)
        this.chart.renderKwhDelta(filtered, akum)
        this.chart.renderKwhPencapaian(filtered)
        this.chart.renderKwhPertumbuhan(filtered)
        this._renderKwhMainTable(filtered, akum)
        this._renderKwhDeltaTable(filtered, akum)
        this._renderKwhPencapaianTable(filtered)
        this._renderKwhPertumbuhanTable(filtered)
        this._renderKwhRingkasanTable(filtered, akum)
    }

    _renderKwhMainTable(data, akum) {
        const tbody = document.getElementById('kwhTableBody')
        if (!tbody) return
        const fmtGwh = v => v == null ? '—' : (v / 1_000_000).toFixed(2) + ' GWh'
        tbody.innerHTML = data.map(d => {
            const tLalu = akum ? d.targetAkum2024    : d.target2024
            const rLalu = akum ? d.realisasiAkum2024 : d.realisasi2024
            const tIni  = akum ? d.targetAkum2025    : d.target2025
            const rIni  = akum ? d.realisasiAkum2025 : d.realisasi2025
            const sel   = (rIni ?? 0) - (tIni ?? 0)
            const c     = sel >= 0 ? 'var(--accent-green)' : '#f44336'
            const sign  = sel >= 0 ? '+' : ''
            const selCell = sel === 0 ? '—' : sign + fmtGwh(sel)
            return '<tr>' +
                '<td style="padding:8px 10px;color:#888;">' + d.bulan + '</td>' +
                '<td style="padding:8px 10px;color:#555;text-align:right;">' + fmtGwh(tLalu) + '</td>' +
                '<td style="padding:8px 10px;color:#2196f3;text-align:right;">' + fmtGwh(rLalu) + '</td>' +
                '<td style="padding:8px 10px;color:#ff9800;text-align:right;">' + fmtGwh(tIni) + '</td>' +
                '<td style="padding:8px 10px;color:var(--accent-green);text-align:right;">' + fmtGwh(rIni) + '</td>' +
                '<td style="padding:8px 10px;color:' + c + ';font-weight:600;text-align:right;">' + selCell + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderKwhDeltaTable(data, akum) {
        const tbody = document.getElementById('tabelKwhDelta')
        if (!tbody) return
        tbody.innerHTML = data.map(d => {
            const val = akum ? (d.deltaAkumKwh ?? 0) : (d.deltaKwh ?? 0)
            const c   = val >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px;color:#888;">' + d.bulan + '</td>' +
                '<td style="padding:8px 10px;color:' + c + ';font-weight:600;text-align:right;">' + (val >= 0 ? '+' : '') + (val / 1_000_000).toFixed(2) + ' GWh</td>' +
                '</tr>'
        }).join('')
    }

    _renderKwhPencapaianTable(data) {
        const tbody = document.getElementById('tabelKwhPencapaian')
        if (!tbody) return
        tbody.innerHTML = data.map(d => {
            const val = (d.pencapaianTarget ?? 0) * 100
            const c   = val >= 100 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px;color:#888;">' + d.bulan + '</td>' +
                '<td style="padding:8px 10px;color:' + c + ';font-weight:600;text-align:right;">' + val.toFixed(1) + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderKwhPertumbuhanTable(data) {
        const tbody = document.getElementById('tabelKwhPertumbuhan')
        if (!tbody) return
        tbody.innerHTML = data.map(d => {
            const val = (d.pertumbuhanReal ?? 0) * 100
            const c   = val >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px;color:#888;">' + d.bulan + '</td>' +
                '<td style="padding:8px 10px;color:' + c + ';font-weight:600;text-align:right;">' + (val >= 0 ? '+' : '') + val.toFixed(1) + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderKwhRingkasanTable(data, akum) {
        const tbody = document.getElementById('tabelRingkasan')
        if (!tbody) return
        const fmtGwh = v => v == null ? '—' : (v / 1_000_000).toFixed(2)
        const fmtPct = v => v == null ? '—' : (v * 100).toFixed(1) + '%'
        tbody.innerHTML = data.map(d => {
            const tLalu  = akum ? d.targetAkum2024    : d.target2024
            const rLalu  = akum ? d.realisasiAkum2024 : d.realisasi2024
            const tIni   = akum ? d.targetAkum2025    : d.target2025
            const rIni   = akum ? d.realisasiAkum2025 : d.realisasi2025
            const delta  = akum ? (d.deltaAkumKwh ?? 0)     : (d.deltaKwh ?? 0)
            const selPct = akum ? (d.deltaAkumPersen ?? 0)   : (d.deltaPersen ?? 0)
            const pen    = d.pencapaianTarget  ?? 0
            const per    = d.pertumbuhanReal   ?? 0
            const dc = delta >= 0 ? '#00ff88' : '#f44336'
            const pc = pen  >= 1 ? '#00ff88' : '#f44336'
            const gc = per  >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td style="padding:8px 10px;color:#888;">' + d.bulan + '</td>' +
                '<td style="padding:8px 10px;color:#555;text-align:right;">' + fmtGwh(tLalu) + '</td>' +
                '<td style="padding:8px 10px;color:#2196f3;text-align:right;">' + fmtGwh(rLalu) + '</td>' +
                '<td style="padding:8px 10px;color:#ff9800;text-align:right;">' + fmtGwh(tIni) + '</td>' +
                '<td style="padding:8px 10px;color:var(--accent-green);text-align:right;">' + fmtGwh(rIni) + '</td>' +
                '<td style="padding:8px 10px;color:' + dc + ';font-weight:600;text-align:right;">' + (delta >= 0 ? '+' : '') + (delta / 1_000_000).toFixed(2) + '</td>' +
                '<td style="padding:8px 10px;color:' + dc + ';text-align:right;">' + fmtPct(selPct) + '</td>' +
                '<td style="padding:8px 10px;color:' + pc + ';text-align:right;">' + fmtPct(pen) + '</td>' +
                '<td style="padding:8px 10px;color:' + gc + ';text-align:right;">' + (per >= 0 ? '+' : '') + fmtPct(per) + '</td>' +
                '</tr>'
        }).join('')
    }

    // ── CRUD MODAL ────────────────────────────────────────────────────────────

    _initCrud(onChange) {
        const btnMuat    = document.getElementById('btnMuatRpkwh')
        const btnHapus   = document.getElementById('btnHapusRpkwh')
        const modalMuat  = document.getElementById('modalMuatRpkwh')
        const modalHapus = document.getElementById('modalHapusRpkwh')

        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatRpkwh')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatRpkwh')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('muatTahunRpkwh')?.value)
                const tipe  = document.getElementById('muatTipeRpkwh')?.value
                try {
                    await this.service?.muatData(tahun, tipe)
                    if (modalMuat) modalMuat.style.display = 'none'
                    this._tahun = tahun
                    onChange()
                } catch { alert('Gagal memuat data') }
            })
        }

        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusRpkwh')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusRpkwh')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunRpkwh')?.value
                const tipe  = document.getElementById('hapusTipeRpkwh')?.value
                if (!confirm('Hapus data RPKWH tahun ' + tahun + ' (' + tipe + ')? Tidak dapat dibatalkan.')) return
                try {
                    await this.service?.hapusData(tahun, tipe)
                    if (modalHapus) modalHapus.style.display = 'none'
                    onChange()
                } catch { alert('Gagal menghapus data') }
            })
        }

        const modalBuat = document.getElementById('modalBuatRpkwh')
        const btnBuat   = document.getElementById('btnBuatRpkwh')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatRpkwh')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatRpkwh')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('buatTahunRpkwh')?.value)
                const tipe  = document.getElementById('buatTipeRpkwh')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch(API_RPKWH, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, golongan_kode: 'TOTAL', sub_golongan_kode: 'JUMLAH', tipe, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_rpkwh: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data RPKWH tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                onChange()
            })
        }
    }

    // ── EDIT TABLE ────────────────────────────────────────────────────────────

    _renderEditTable(data, state) {
        const isReadOnly = this._tahun < TAHUN_INI
        const tarif      = state.tarif ?? (data.tarif?.[0] ?? '')
        const rencana    = data.rencanaData?.[tarif]   ?? new Array(12).fill(null)
        const realisasi  = data.realisasiData?.[tarif] ?? new Array(12).fill(null)

        const golMatch = tarif.match(/\(([A-Z])\)/)
        const golKode  = golMatch ? golMatch[1] : (tarif.charAt(0) || 'S')

        const rows = BULAN_NAMA.map((bln, i) => ({
            label: bln,
            fields: [
                { key: 'nilai_target',    value: rencana[i]   ?? '', color: '#ff9800' },
                { key: 'nilai_realisasi', value: realisasi[i] ?? '', color: '#00ff88' },
            ],
        }))

        renderEditTable(
            'rpkwhEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                const base  = { tahun: this._tahun, bulan, golongan_kode: golKode, sub_golongan_kode: tarif, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }
                if (values.nilai_target !== null)
                    await upsert(API_RPKWH,
                        { tahun: this._tahun, bulan, sub_golongan_kode: tarif, tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'target', nilai_rpkwh: values.nilai_target })
                if (values.nilai_realisasi !== null)
                    await upsert(API_RPKWH,
                        { tahun: this._tahun, bulan, sub_golongan_kode: tarif, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'realisasi', nilai_rpkwh: values.nilai_realisasi })
            },
            ['Bulan', 'Target (Rp/kWh)', 'Realisasi (Rp/kWh)'],
            // ── Live-recalc ──────────────────────────────────────────────────
            (ri, values) => {
                if (values.nilai_target    !== null) rencana[ri]   = values.nilai_target
                if (values.nilai_realisasi !== null) realisasi[ri] = values.nilai_realisasi

                const delta = realisasi.map((v, i) => (v ?? 0) - (rencana[i] ?? 0))
                this._renderRencanaTable(rencana, realisasi)
                this._renderDeltaRencanaTable(LABELS, delta)
                this.chart.make('chartRencanaRealisasi', 'bar', LABELS, [
                    { label: 'Rencana',   data: rencana,   backgroundColor: '#ff9800' },
                    { label: 'Realisasi', data: realisasi, backgroundColor: '#00ff88' },
                ], v => v.toFixed(0) + ' Rp/kWh')
                this.chart.make('chartDeltaRpkwh', 'bar', LABELS, [
                    { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
                ], v => v.toFixed(0) + ' Rp/kWh')
            }
        )

        const w = document.getElementById('rpkwhEditWarning')
        if (w) w.style.display = isReadOnly ? 'inline-block' : 'none'
    }

    // ── RPKWH RENDER HELPERS ─────────────────────────────────────────────────

    _renderRencanaRealisasi(data, state) {
        const akum     = state.view === 'akumulasi'
        const r        = data.rencanaData?.[state.tarif]   ?? new Array(12).fill(0)
        const rl       = data.realisasiData?.[state.tarif] ?? new Array(12).fill(0)
        const toAkum   = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
        const dR       = akum ? toAkum(r)  : r
        const dRl      = akum ? toAkum(rl) : rl

        this.chart.make('chartRencanaRealisasi', 'bar', LABELS, [
            { label: 'Rencana',   data: dR,  backgroundColor: '#ff9800' },
            { label: 'Realisasi', data: dRl, backgroundColor: '#00ff88' },
        ], v => v.toFixed(0) + ' Rp/kWh')

        const delta = dRl.map((v, i) => (v ?? 0) - (dR[i] ?? 0))
        this.chart.make('chartDeltaRpkwh', 'bar', LABELS, [
            { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
        ], v => v.toFixed(0) + ' Rp/kWh')

        this._renderRencanaTable(dR, dRl)
        this._renderDeltaRencanaTable(LABELS, delta)
    }

    _renderGolongan(data, state) {
        const akum   = state.view === 'akumulasi'
        const idx    = (state.bulan && state.bulan !== 'all') ? Number.parseInt(state.bulan) : 11
        const is2024 = state.tahun === '2024'
        const nilaiGol = (data.golongan ?? []).map(g => {
            if (is2024) return akum ? (data.realisasiGolAkum?.[g]?.[idx] ?? 0) : (data.realisasiGolBul?.[g]?.[idx] ?? 0)
            return akum ? (data.targetGolAkum?.[g]?.[idx] ?? 0) : (data.targetGolBul?.[g]?.[idx] ?? 0)
        })
        const judulEl = document.getElementById('judulPieRpkwh')
        if (judulEl) judulEl.textContent =
            (is2024 ? 'Realisasi' : 'Target') + ' RPKWH ' + state.tahun +
            (akum ? ' (Akumulasi)' : ' (Bulanan)') + ' — ' + (LABELS[idx] ?? '')

        this.chart.pie('chartPieRpkwh', (data.golongan ?? []).map(g => g.replace(/ \(.\) ?/, '')), nilaiGol)
        this._renderPieTable((data.golongan ?? []).map(g => g.replace(/ \(.\) ?/, '')), nilaiGol)
    }

    _renderGrowth(data, state) {
        const akum      = state.view === 'akumulasi'
        const growthMap = akum ? (data.growthAkum ?? {}) : (data.growthBul ?? {})
        const datasets  = (data.golongan ?? []).map((g, i) => ({
            label: g.replace(/ \(.\) ?/, ''), data: growthMap[g] ?? new Array(12).fill(0),
            borderColor: COLORS[i % COLORS.length], tension: 0.4, fill: false, pointRadius: 3,
        }))
        this.chart.make('chartGrowthRpkwh', 'line', LABELS, datasets, v => (v * 100).toFixed(1) + '%')
        this._renderGrowthTable(data.golongan ?? [], growthMap)
    }

    _renderBppHjr(data, state) {
        const bppData   = data.bppData ?? {}
        const hjrData   = data.hjrData ?? {}
        const listTarif = Object.keys(hjrData)
        if (!state.selectedTarif && listTarif.length > 0) state.selectedTarif = listTarif[0]
        const sel = state.selectedTarif
        if (sel && this.chart.bppVsHjr) {
            this.chart.bppVsHjr('chartBppHjr', bppData[sel] ?? new Array(12).fill(0), hjrData[sel] ?? new Array(12).fill(0), LABELS)
        }
        this._renderBppHjrTable(hjrData[sel] ?? [], bppData[sel] ?? [])
    }

    _renderAnalisa(data, state) {
        const a    = data.analisaData ?? []
        if (!a.length) return
        const akum = state.view === 'akumulasi'
        this.chart.make('chartAnalisa2024vs2025', 'bar', a.map(d => d.bulan), [
            { label: 'Target 2024',    data: a.map(d => akum ? (d.akum2024 ?? 0)          : (d.target2024 ?? 0)),    backgroundColor: '#555'    },
            { label: 'Realisasi 2024', data: a.map(d => akum ? (d.realisasiAkum2024 ?? 0) : (d.realisasi2024 ?? 0)), backgroundColor: '#2196f3' },
            { label: 'Target 2025',    data: a.map(d => akum ? (d.akum2025 ?? 0)          : (d.target2025 ?? 0)),    backgroundColor: '#ff9800' },
            { label: 'Realisasi 2025', data: a.map(d => akum ? (d.realisasiAkum2025 ?? 0) : (d.realisasi2025 ?? 0)), backgroundColor: '#00ff88' },
        ], v => v.toFixed(0) + ' Rp/kWh')
        const delta = a.map(d => akum ? (d.deltaAkum ?? 0) : (d.deltaRpkwh ?? 0))
        this.chart.make('chartAnalisaDelta', 'bar', a.map(d => d.bulan), [
            { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
        ], v => v.toFixed(0) + ' Rp/kWh')
        this._renderAnalisaTable(a, akum)
        this._renderAnalisaDeltaTable(a.map(d => d.bulan), delta)
        this._renderAnalisaDetailTable(a, akum)
    }

    _renderPersen(data) {
        const p = data.persenData ?? []
        if (!p.length) return
        this.chart.make('chartPersenRpkwh', 'bar', p.map(d => d.bulan), [
            { label: 'Target',    data: p.map(d => d.target    ?? 0), backgroundColor: '#ff9800' },
            { label: 'Realisasi', data: p.map(d => d.realisasi ?? 0), backgroundColor: '#00ff88' },
        ], v => v.toFixed(0) + ' Rp/kWh')
        this._renderPersenTable(p)
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById('filterTarifRpkwh')
        if (sel?.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement('option')
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }

    // ── TABLE HELPERS ─────────────────────────────────────────────────────────

    _renderRencanaTable(r, rl) {
        const tbody = document.getElementById('rpkwhSideTable')
        if (!tbody) return
        tbody.innerHTML = LABELS.map((bln, i) => {
            const d = (rl[i] ?? 0) - (r[i] ?? 0)
            const c = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return '<tr>' +
                '<td>' + bln + '</td>' +
                '<td style="color:#ff9800;">' + (r[i] ?? 0).toFixed(0) + '</td>' +
                '<td style="color:var(--accent-green);">' + (rl[i] ?? 0).toFixed(0) + '</td>' +
                '<td style="color:' + c + ';font-weight:600;">' + (d >= 0 ? '+' : '') + d.toFixed(0) + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderDeltaRencanaTable(labels, delta) {
        const tbody = document.getElementById('tabelRpkwhDelta')
        if (!tbody) return
        tbody.innerHTML = labels.map((bln, i) => {
            const v = delta[i] ?? 0
            return '<tr><td>' + bln + '</td><td style="color:' + (v >= 0 ? '#00ff88' : '#f44336') + ';font-weight:600;">' + (v >= 0 ? '+' : '') + v.toFixed(0) + '</td></tr>'
        }).join('')
    }

    _renderPieTable(labels, data) {
        const tbody = document.getElementById('rpkwhPieTable')
        if (!tbody) return
        const total = data.reduce((a, b) => a + (b ?? 0), 0)
        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? (((data[i] ?? 0) / total) * 100).toFixed(1) : '0.0'
            return '<tr><td>' + lbl + '</td><td style="color:var(--accent-green);">' + (data[i] ?? 0).toFixed(0) + '</td><td style="color:#aaa;">' + pct + '%</td></tr>'
        }).join('')
    }

    _renderGrowthTable(golongan, growthData) {
        const tbody = document.getElementById('tabelGrowthRpkwh')
        if (!tbody) return
        tbody.innerHTML = LABELS.map((bln, i) =>
            '<tr><td>' + bln + '</td>' +
            golongan.map(g => {
                const val = growthData[g]?.[i] ?? 0
                return '<td style="color:' + (val >= 0 ? '#00ff88' : '#f44336') + ';">' + (val * 100).toFixed(2) + '%</td>'
            }).join('') + '</tr>'
        ).join('')
    }

    _renderBppHjrTable(hjr, bpp) {
        const tbody = document.getElementById('tabelBppHjr')
        if (!tbody) return
        tbody.innerHTML = LABELS.map((bln, i) => {
            const hjrVal  = hjr[i] ?? 0
            const bppVal  = bpp[i] ?? 0
            const selisih = hjrVal - bppVal
            const c       = selisih >= 0 ? '#4caf50' : '#f44336'
            return '<tr>' +
                '<td>' + bln + '</td>' +
                '<td style="color:#2196f3;">' + hjrVal.toLocaleString('id-ID') + '</td>' +
                '<td style="color:#ff9800;">' + bppVal.toLocaleString('id-ID') + '</td>' +
                '<td style="color:' + c + ';font-weight:600;">' + (selisih >= 0 ? '+' : '') + selisih.toLocaleString('id-ID') + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderAnalisaTable(a, akum) {
        const tbody = document.getElementById('rpkwhAnalisaSideTable')
        if (!tbody) return
        tbody.innerHTML = a.map(d => {
            const delta = akum ? (d.deltaAkum ?? 0) : (d.deltaRpkwh ?? 0)
            const c     = delta >= 0 ? 'var(--accent-green)' : '#f44336'
            return '<tr>' +
                '<td>' + d.bulan + '</td>' +
                '<td style="color:#555;">' + (akum ? (d.akum2024 ?? 0) : (d.target2024 ?? 0)).toFixed(0) + '</td>' +
                '<td style="color:#2196f3;">' + (akum ? (d.realisasiAkum2024 ?? 0) : (d.realisasi2024 ?? 0)).toFixed(0) + '</td>' +
                '<td style="color:#ff9800;">' + (akum ? (d.akum2025 ?? 0) : (d.target2025 ?? 0)).toFixed(0) + '</td>' +
                '<td style="color:var(--accent-green);">' + (akum ? (d.realisasiAkum2025 ?? 0) : (d.realisasi2025 ?? 0)).toFixed(0) + '</td>' +
                '<td style="color:' + c + ';font-weight:600;">' + (delta >= 0 ? '+' : '') + delta.toFixed(0) + '</td>' +
                '</tr>'
        }).join('')
    }

    _renderAnalisaDeltaTable(labels, delta) {
        const tbody = document.getElementById('tabelRpkwhAnalisaDelta')
        if (!tbody) return
        tbody.innerHTML = labels.map((bln, i) => {
            const v = delta[i] ?? 0
            return '<tr><td>' + bln + '</td><td style="color:' + (v >= 0 ? '#00ff88' : '#f44336') + ';font-weight:600;">' + (v >= 0 ? '+' : '') + v.toFixed(0) + '</td></tr>'
        }).join('')
    }

    _renderPersenTable(p) {
        const tbody = document.getElementById('tabelPersen')
        if (!tbody) return
        tbody.innerHTML = p.map(d =>
            '<tr><td>' + d.bulan + '</td>' +
            '<td style="color:#ff9800;">' + (d.target ?? 0).toFixed(2) + '</td>' +
            '<td style="color:var(--accent-green);">' + (d.realisasi ?? 0).toFixed(2) + '</td>' +
            '<td style="color:' + ((d.delta ?? 0) >= 0 ? '#00ff88' : '#f44336') + ';">' + (d.delta ?? 0).toFixed(2) + '</td>' +
            '</tr>'
        ).join('')
    }

    _renderAnalisaDetailTable(a, akum) {
        const tbody = document.getElementById('tabelAnalisa')
        if (!tbody) return
        const fmtPct = v => ((v ?? 0) * 100).toFixed(2) + '%'
        tbody.innerHTML = a.map(d => {
            const dlt  = akum ? (d.deltaAkum    ?? 0) : (d.deltaRpkwh ?? 0)
            const dPct = akum ? (d.deltaAkumPct ?? 0) : (d.deltaPct   ?? 0)
            const c    = dlt >= 0 ? '#00ff88' : '#f44336'
            return '<tr>' +
                '<td>' + d.bulan + '</td>' +
                '<td style="color:#555;">'                + (akum ? (d.akum2024 ?? 0)          : (d.target2024 ?? 0)).toFixed(2)    + '</td>' +
                '<td style="color:#2196f3;">'             + (akum ? (d.realisasiAkum2024 ?? 0) : (d.realisasi2024 ?? 0)).toFixed(2) + '</td>' +
                '<td style="color:#ff9800;">'             + (akum ? (d.akum2025 ?? 0)          : (d.target2025 ?? 0)).toFixed(2)    + '</td>' +
                '<td style="color:var(--accent-green);">' + (akum ? (d.realisasiAkum2025 ?? 0) : (d.realisasi2025 ?? 0)).toFixed(2) + '</td>' +
                '<td style="color:' + c + ';">' + dlt.toFixed(2) + '</td>' +
                '<td style="color:' + c + ';">' + fmtPct(dPct - 1) + '</td>' +
                '<td>' + fmtPct(d.pencapaianTarget   ?? 0) + '</td>' +
                '<td>' + fmtPct(d.pencapaianRealisasi ?? 0) + '</td>' +
                '</tr>'
        }).join('')
    }
}