import { renderEditTable, upsert, TAHUN_INI } from '../utils/CrudHelper.js'

const LABELS     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
const API        = '/api/v1/fact/pelanggan'
const GRUP_ORDER = ['S', 'R', 'B', 'I', 'P', 'M']
const GRUP_LABEL = { S: 'Sosial', R: 'Rumah Tangga', B: 'Bisnis', I: 'Industri', P: 'Publik', M: 'Multiguna' }

const getGrup = (t) => {
    if (t === 'JUMLAH' || t.startsWith('JUMLAH-')) return '__JUMLAH__'
    const m = t.match(/^([A-Z])/)
    return m?.[1] ?? 'LAIN'
}

export default class PelangganModule {
    constructor(chart, service) {
        this.chart   = chart
        this.service = service
        this._tahun  = TAHUN_INI
    }

    render(data, state) {
        if (!data) return
        this._tahun = data.tahun_efektif ?? TAHUN_INI

        const warning = document.getElementById('pelangganWarning')
        if (warning) warning.style.display = data.is_fallback ? 'inline-block' : 'none'

        const r  = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl = data.realisasiData[state.tarif] || new Array(12).fill(0)

        this.chart.make('chartPelangganBar', 'bar', LABELS, [
            { label: 'Rencana',   data: r,  backgroundColor: '#ff9800' },
            { label: 'Realisasi', data: rl, backgroundColor: '#00ff88' },
        ])

        const delta = rl.map((v, i) => v - (r[i] || 0))
        this.chart.make('chartPelangganDelta', 'bar', LABELS, [
            { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
        ])

        const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
        const pieData   = data.golongan.map(g => (data.targetGolAkum[g] || [])[state.bulan] || 0)
        const pieTarget = data.golongan.map(g => (data.targetGolAkum[g] || [])[state.bulan] || 0)
        const pieReal24 = data.golongan.map(g => (data.realisasiGolAkum[g] || [])[state.bulan] || 0)

        this.chart.pie('chartPelangganPie', pieLabels, pieData)
        this.chart.make('chartPelangganGol', 'bar', pieLabels, [
            { label: 'Target Tahun Ini', data: pieTarget, backgroundColor: '#ff9800' },
            { label: 'Realisasi Lalu',   data: pieReal24, backgroundColor: '#2196f3' },
        ])

        this.#populateTarif(data, state.tarif)
        this.#renderBarTable(r, rl)
        this.#renderDeltaTable(LABELS, delta)
        this.#renderPieTable(pieLabels, pieData)
        this.#renderGolTable(pieLabels, pieTarget, pieReal24)
        this.#renderRkapTable(data)
        this.#renderEditTable(data, state)
    }

    initFilters(state, onChange) {
        document.getElementById('filterTarifPelanggan')?.addEventListener('change', e => { state.tarif = e.target.value;          onChange() })
        document.getElementById('filterBulanPelanggan')?.addEventListener('change', e => { state.bulan = Number.parseInt(e.target.value); onChange() })
        this.#initCrud(state, onChange)
    }

    #populateTarif(data, currentTarif) {
        const sel = document.getElementById('filterTarifPelanggan')
        if (!sel || sel.options.length > 0 || !data.tarif) return

        const optAll = document.createElement('option')
        optAll.value = 'JUMLAH'; optAll.textContent = 'Semua'
        sel.appendChild(optAll)

        const grouped = {}
        const jumlahItems = {}
        data.tarif.forEach(t => {
            if (t === 'JUMLAH') return
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
            sel.appendChild(grp)
        })

        sel.value = currentTarif || 'JUMLAH'
    }

    #renderBarTable(r, rl) {
        const tbody = document.getElementById('pelangganSideTable')
        if (!tbody) return

        tbody.innerHTML = LABELS.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td>${bln}</td>
                <td style="color:#ff9800;">${(r[i] || 0).toLocaleString('id-ID')}</td>
                <td style="color:var(--accent-green);">${(rl[i] || 0).toLocaleString('id-ID')}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${d.toLocaleString('id-ID')}</td>
            </tr>`
        }).join('')
    }

    #renderDeltaTable(labels, delta) {
        const tbody = document.getElementById('tabelPelangganDelta')
        if (!tbody) return

        tbody.innerHTML = labels.map((bln, i) => {
            const d     = delta[i] || 0
            const color = d >= 0 ? '#00ff88' : '#f44336'
            return `<tr>
                <td>${bln}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${d.toLocaleString('id-ID')}</td>
            </tr>`
        }).join('')
    }

    #renderPieTable(labels, data) {
        const tbody = document.getElementById('pelangganPieTable')
        if (!tbody) return
        const total = data.reduce((a, b) => a + b, 0)

        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? ((data[i] / total) * 100).toFixed(1) : '0.0'
            return `<tr>
                <td>${lbl}</td>
                <td style="color:var(--accent-green);">${(data[i] || 0).toLocaleString('id-ID')}</td>
                <td style="color:#aaa;">${pct}%</td>
            </tr>`
        }).join('')
    }

    #renderGolTable(labels, target, real24) {
        const tbody = document.getElementById('tabelPelangganGol')
        if (!tbody) return

        tbody.innerHTML = labels.map((lbl, i) => `<tr>
            <td>${lbl}</td>
            <td style="color:#ff9800;">${(target[i] || 0).toLocaleString('id-ID')}</td>
            <td style="color:#2196f3;">${(real24[i] || 0).toLocaleString('id-ID')}</td>
        </tr>`).join('')
    }

    #renderRkapTable(data) {
        const tbody = document.getElementById('tabelRkapPelanggan')
        if (!tbody || !data.rkapData) return

        tbody.innerHTML = data.rkapData.map(d =>
            '<tr>' +
            '<td style="border-right:1px solid #444;">' + d.golongan + '</td>' +
            '<td>' + d.targetTw1.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.targetTw2.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.targetTw3.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.targetTw4.toLocaleString('id-ID') + '</td>' +
            '<td style="border-right:1px solid #444;">' + d.targetTotal.toLocaleString('id-ID') + '</td>' +
            '<td style="border-left:1px solid #444;">' + d.realTw1.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.realTw2.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.realTw3.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.realTw4.toLocaleString('id-ID') + '</td>' +
            '<td>' + d.realTotal.toLocaleString('id-ID') + '</td>' +
            '<td style="color:' + (d.pencapaian >= 1 ? '#00ff88' : '#f44336') + ';font-weight:600;">' +
            (d.pencapaian * 100).toFixed(2) + '%</td>' +
            '</tr>'
        ).join('')
    }

    #renderEditTable(data, state) {
        const isReadOnly = this._tahun < TAHUN_INI
        const tarif      = state.tarif
        const r          = data.rencanaData?.[tarif]   ?? new Array(12).fill(null)
        const rl         = data.realisasiData?.[tarif] ?? new Array(12).fill(null)
        const golKode    = tarif?.charAt(0) ?? 'S'

        const rows = LABELS.map((bln, i) => ({
            label: bln,
            fields: [
                { key: 'nilai_target',    value: r[i]  ?? '', color: '#ff9800' },
                { key: 'nilai_realisasi', value: rl[i] ?? '', color: '#00ff88' },
            ],
        }))

        renderEditTable(
            'pelangganEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                const base  = { tahun: this._tahun, bulan, golongan_kode: golKode, sub_golongan_kode: tarif, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_pelanggan: 0 }
                if (values.nilai_target !== null)
                    await upsert(API, { tahun: this._tahun, bulan, sub_golongan_kode: tarif, tipe: 'target', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'target', nilai_pelanggan: values.nilai_target })
                if (values.nilai_realisasi !== null)
                    await upsert(API, { tahun: this._tahun, bulan, sub_golongan_kode: tarif, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                        { ...base, tipe: 'realisasi', nilai_pelanggan: values.nilai_realisasi })
            },
            ['Bulan', 'Target', 'Realisasi'],
            (ri, values) => {
                if (values.nilai_target    !== null) r[ri]  = values.nilai_target
                if (values.nilai_realisasi !== null) rl[ri] = values.nilai_realisasi

                const delta = rl.map((v, i) => (v ?? 0) - (r[i] ?? 0))
                this.#renderBarTable(r, rl)
                this.#renderDeltaTable(LABELS, delta)
                this.chart.make('chartPelangganBar', 'bar', LABELS, [
                    { label: 'Rencana',   data: r,  backgroundColor: '#ff9800' },
                    { label: 'Realisasi', data: rl, backgroundColor: '#00ff88' },
                ])
                this.chart.make('chartPelangganDelta', 'bar', LABELS, [
                    { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
                ])
            }
        )

        const w = document.getElementById('pelangganEditWarning')
        if (w) w.style.display = isReadOnly ? 'inline-block' : 'none'
    }

    #initCrud(state, onChange) {
        const btnMuat    = document.getElementById('btnMuatPelanggan')
        const btnHapus   = document.getElementById('btnHapusPelanggan')
        const modalMuat  = document.getElementById('modalMuatPelanggan')
        const modalHapus = document.getElementById('modalHapusPelanggan')

        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatPelanggan')?.addEventListener('click', () => { modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatPelanggan')?.addEventListener('click', async () => {
                const tahun = document.getElementById('muatTahunPelanggan').value
                const tipe  = document.getElementById('muatTipePelanggan').value
                try {
                    await this.service.muatData(tahun, tipe)
                    modalMuat.style.display = 'none'
                    this._tahun = Number.parseInt(tahun)
                    state.tahun = Number.parseInt(tahun)
                    onChange()
                } catch { alert('Gagal memuat data') }
            })
        }

        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusPelanggan')?.addEventListener('click', () => { modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusPelanggan')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunPelanggan').value
                const tipe  = document.getElementById('hapusTipePelanggan').value
                if (!confirm('Hapus data pelanggan tahun ' + tahun + ' (' + tipe + ')? Tidak dapat dibatalkan.')) return
                try {
                    await this.service.hapusData(tahun, tipe)
                    modalHapus.style.display = 'none'
                    onChange()
                } catch { alert('Gagal menghapus data') }
            })
        }

        const modalBuat = document.getElementById('modalBuatPelanggan')
        const btnBuat   = document.getElementById('btnBuatPelanggan')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatPelanggan')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatPelanggan')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('buatTahunPelanggan')?.value)
                const tipe  = document.getElementById('buatTipePelanggan')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch(API, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, tipe, golongan_kode: 'TOTAL', sub_golongan_kode: 'JUMLAH', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_pelanggan: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data Pelanggan tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }
    }
}