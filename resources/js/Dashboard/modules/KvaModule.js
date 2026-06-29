import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const API         = '/api/v1/fact/kva'
const API_PELANGGAN = '/api/v1/fact/pelanggan'
const BULAN_NAMA  = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI',
                     'JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER']
const GRUP_ORDER = ['S', 'R', 'B', 'I', 'P', 'M']
const GRUP_LABEL = { S: 'Sosial', R: 'Rumah Tangga', B: 'Bisnis', I: 'Industri', P: 'Publik', M: 'Multiguna' }

const getGrup = (t) => {
    if (t === 'JUMLAH' || t.startsWith('JUMLAH-')) return '__JUMLAH__'
    const m = t.match(/^([A-Z])/)
    return m?.[1] ?? 'LAIN'
}

export default class KvaModule {
    constructor(chart) {
        this.chart    = chart
        this._rawData = null
        this._tahun   = TAHUN_INI
    }

    #getLabels() {
        return [...document.querySelectorAll('#filterBulanKva option')]
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return
        this._rawData = data
        this._tahun   = data.tahun_efektif ?? TAHUN_INI

        const warning = document.getElementById('kvaWarning')
        if (warning) warning.style.display = (this._tahun < TAHUN_INI) ? 'inline-block' : 'none'

        const r    = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl   = data.realisasiData[state.tarif] || new Array(12).fill(0)
        const akum = state.view === 'akumulasi'

        const toAkum = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
        const displayR  = akum ? toAkum(r)  : r
        const displayRl = akum ? toAkum(rl) : rl

        const sliceIdx     = (state.bulan ?? 11) + 1
        const labels       = this.#getLabels()
        const slicedLabels = labels.slice(0, sliceIdx)
        const slicedR      = displayR.slice(0, sliceIdx)
        const slicedRl     = displayRl.slice(0, sliceIdx)

        this.chart.make('chartKvaBar', 'bar', slicedLabels, [
            { label: 'Rencana',   data: slicedR,  backgroundColor: '#ff9800' },
            { label: 'Realisasi', data: slicedRl, backgroundColor: '#00ff88' },
        ])

        const delta = slicedRl.map((v, i) => v - slicedR[i])
        this.chart.make('chartKvaDelta', 'bar', slicedLabels, [
            { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
        ])

        const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
        const pieData   = data.golongan.map(g => data.targetGolAkum[g][state.bulan ?? 11] || 0)
        const pieTarget = data.golongan.map(g => data.targetGolAkum[g][state.bulan ?? 11])
        const pieReal   = data.golongan.map(g => data.realisasiGolAkum[g][state.bulan ?? 11])

        this.chart.pie('chartKvaPie', pieLabels, pieData)
        this.chart.make('chartKvaGol', 'bar', pieLabels, [
            { label: 'Target Thn Ini', data: pieTarget, backgroundColor: '#ff9800' },
            { label: 'Real Thn Lalu',  data: pieReal,   backgroundColor: '#2196f3' },
        ])

        const p = data.perbandinganData
        this.chart.make('chartKvaPerbandingan', 'bar', labels.slice(0, p.length), [
            { label: 'Target Thn Lalu',    data: p.map(d => akum ? d.akum2024          : d.target2024),    backgroundColor: '#555'    },
            { label: 'Realisasi Thn Lalu', data: p.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: '#2196f3' },
            { label: 'Target Thn Ini',     data: p.map(d => akum ? d.akum2025          : d.target2025),    backgroundColor: '#ff9800' },
            { label: 'Realisasi Thn Ini',  data: p.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: '#00ff88' },
        ])

        const deltaPerb = p.map(d => akum ? d.deltaAkumKva : d.deltaKva)
        this.chart.make('chartKvaDeltaPerbandingan', 'bar', labels.slice(0, p.length), [
            { label: 'Delta', data: deltaPerb, backgroundColor: deltaPerb.map(v => v >= 0 ? '#00ff88' : '#f44336') }
        ])

        this.#populateTarif(data, state.tarif)
        this.#renderBarTable(slicedLabels, slicedR, slicedRl)
        this.#renderDeltaTable(slicedLabels, delta)
        this.#renderPieTable(pieLabels, pieData)
        this.#renderGolTable(pieLabels, pieTarget, pieReal)
        this.#renderPerbandinganTable(p, labels, akum)
        this.#renderDeltaPerbandinganTable(labels.slice(0, p.length), deltaPerb)
        this.#renderRkapTable(data)
        this.#renderPerbandinganDetailTable(data, labels, akum)
        this.#renderEditTable(data, state)
        this.#renderEditGolonganTable(data, state)
        this.#updateEditWarning()
    }

    initFilters(state, onChange) {
        document.getElementById('filterTarifKva')?.addEventListener('change', e => { state.tarif = e.target.value;          onChange() })
        document.getElementById('filterBulanKva')?.addEventListener('change', e => { state.bulan = parseInt(e.target.value); onChange() })
        document.getElementById('filterViewKva')?.addEventListener('change',  e => { state.view  = e.target.value;          onChange() })
        this.#initCrud(state, onChange)
    }

    #populateTarif(data, currentTarif) {
        const sel = document.getElementById('filterTarifKva')
        if (!sel || sel.options.length > 0 || !data.tarif) return

        const optAll = document.createElement('option')
        optAll.value = 'JUMLAH'; optAll.textContent = 'Semua'
        sel.appendChild(optAll)

        // Kelompokkan tarif per grup
        const grouped = {}
        const jumlahItems = {}
        data.tarif.forEach(t => {
            if (t === 'JUMLAH') return
            const key = getGrup(t)
            if (key === '__JUMLAH__') {
                // JUMLAH-S, JUMLAH-R, dst — petakan ke huruf grupnya
                const sufiks = t.replace('JUMLAH-', '')
                jumlahItems[sufiks] = t
            } else {
                if (!grouped[key]) grouped[key] = []
                grouped[key].push(t)
            }
        })

        // Render sesuai urutan GRUP_ORDER
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

    #renderBarTable(labels, r, rl) {
        const tbody = document.getElementById('kvaSideTable')
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')

        tbody.innerHTML = labels.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td>${bln}</td>
                <td style="color:#ff9800;">${fmt(r[i])}</td>
                <td style="color:var(--accent-green);">${fmt(rl[i])}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${fmt(d)}</td>
            </tr>`
        }).join('')
    }

    #renderDeltaTable(labels, delta) {
        const tbody = document.getElementById('tabelKvaDelta')
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td>${bln}</td>
            <td style="color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}; font-weight:600;">
                ${delta[i] >= 0 ? '+' : ''}${fmt(delta[i])}
            </td>
        </tr>`).join('')
    }

    #renderPieTable(labels, data) {
        const tbody = document.getElementById('kvaPieTable')
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

    #renderGolTable(labels, target, real) {
        const tbody = document.getElementById('tabelKvaGol')
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')

        tbody.innerHTML = labels.map((lbl, i) => `<tr>
            <td>${lbl}</td>
            <td style="color:#ff9800;">${fmt(target[i])}</td>
            <td style="color:#2196f3;">${fmt(real[i])}</td>
        </tr>`).join('')
    }

    #renderPerbandinganTable(p, labels, akum) {
        const tbody = document.getElementById('kvaPerbandinganSideTable')
        if (!tbody) return
        const fmt = v => (v / 1000).toFixed(1)

        tbody.innerHTML = p.map((d, i) => {
            const delta = akum ? d.deltaAkumKva : d.deltaKva
            const color = delta >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td>${labels[i] ?? d.bulan}</td>
                <td style="color:#555;">${fmt(akum ? d.akum2024          : d.target2024)}</td>
                <td style="color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td style="color:#ff9800;">${fmt(akum ? d.akum2025          : d.target2025)}</td>
                <td style="color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${fmt(delta)}</td>
            </tr>`
        }).join('')
    }

    #renderDeltaPerbandinganTable(labels, delta) {
        const tbody = document.getElementById('tabelKvaDeltaPerbandingan')
        if (!tbody) return
        const fmt = v => (v / 1000).toFixed(1)

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td>${bln}</td>
            <td style="color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}; font-weight:600;">
                ${delta[i] >= 0 ? '+' : ''}${fmt(delta[i])}
            </td>
        </tr>`).join('')
    }

    #renderRkapTable(data) {
        const tbody = document.getElementById('tabelRkapKva')
        if (!tbody || !data.rkapData) return

        tbody.innerHTML = data.rkapData.map(d => `<tr>
            <td>${d.golongan}</td>
            <td>${d.targetTw1.toFixed(2)}</td>
            <td>${d.targetTw2.toFixed(2)}</td>
            <td>${d.targetTw3.toFixed(2)}</td>
            <td>${d.targetTw4.toFixed(2)}</td>
            <td>${d.targetTotal.toFixed(2)}</td>
            <td>${d.realTw1.toFixed(2)}</td>
            <td>${d.realTw2.toFixed(2)}</td>
            <td>${d.realTw3.toFixed(2)}</td>
            <td>${d.realTw4.toFixed(2)}</td>
            <td>${d.realTotal.toFixed(2)}</td>
            <td style="color:${d.pencapaian >= 1 ? '#00ff88' : '#f44336'}">
                ${(d.pencapaian * 100).toFixed(2)}%
            </td>
        </tr>`).join('')
    }

    #renderPerbandinganDetailTable(data, labels, akum) {
        const tbody = document.getElementById('tabelPerbandinganKva')
        if (!tbody || !data.perbandinganData) return
        const fmt    = v => (v / 1000).toFixed(2) + ' kVA'
        const fmtPct = v => (v * 100).toFixed(2) + '%'

        tbody.innerHTML = data.perbandinganData.map((d, i) => `<tr>
            <td>${labels[i] ?? d.bulan}</td>
            <td style="color:#555;">${fmt(akum ? d.akum2024          : d.target2024)}</td>
            <td style="color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
            <td style="color:#ff9800;">${fmt(akum ? d.akum2025          : d.target2025)}</td>
            <td style="color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
            <td style="color:${d.deltaKva >= 0 ? '#00ff88' : '#f44336'}">
                ${fmt(akum ? d.deltaAkumKva : d.deltaKva)}
            </td>
            <td style="color:${d.deltaKva >= 0 ? '#00ff88' : '#f44336'}">
                ${fmtPct(akum ? d.deltaAkumPct : d.deltaPct)}
            </td>
            <td>${fmtPct(d.pencapaianTarget)}</td>
            <td>${fmtPct(d.pencapaianRealisasi)}</td>
        </tr>`).join('')
    }

    #renderEditGolonganTable(data, state) {
        const container = document.getElementById('kvaEditGolonganContainer')
        if (!container || !data.golongan) return

        const isReadOnly = this._tahun < TAHUN_INI
        const bulanIdx   = state.bulan ?? 11
        const disStyle   = isReadOnly ? 'opacity:0.45; pointer-events:none;' : ''

        const headers  = ['Golongan', 'Target Akum KVA', 'Realisasi Akum KVA']
        const headHtml = `<thead style="position:sticky;top:0;background:#252525;z-index:1;">
            <tr style="font-size:0.65rem;text-transform:uppercase;">
                ${headers.map((h, i) => `<th style="padding:8px 10px;color:${i === 1 ? '#ff9800' : i === 2 ? '#00ff88' : '#888'};text-align:left;">${h}</th>`).join('')}
                <th style="padding:8px 10px;color:#aaa;text-align:center;">Aksi</th>
            </tr>
        </thead>`

        const rows = data.golongan.map((gol, ri) => {
            const label = gol.replace(/ \(.\) ?/, '')
            const tVal  = data.targetGolAkum?.[gol]?.[bulanIdx] ?? ''
            const rVal  = data.realisasiGolAkum?.[gol]?.[bulanIdx] ?? ''

            const mkInput = (val, field, color) => `
                <input type="number"
                    data-row="${ri}" data-field="${field}"
                    value="${val}" placeholder="—"
                    ${isReadOnly ? 'disabled' : ''}
                    style="width:110px;padding:4px 6px;border-radius:4px;
                           border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                           background:#1a1a1a;color:${isReadOnly ? '#444' : color};
                           font-size:0.75rem;text-align:right;" />`

            return `<tr style="border-bottom:1px solid #2a2a2a;${disStyle}" data-gol="${gol}">
                <td style="padding:8px 10px;color:#888;font-size:0.75rem;white-space:nowrap;">${label}</td>
                <td style="padding:4px 6px;">${mkInput(tVal, 'nilai_kva_target', '#ff9800')}</td>
                <td style="padding:4px 6px;">${mkInput(rVal, 'nilai_kva_realisasi', '#00ff88')}</td>
                <td style="padding:4px 6px;text-align:center;">
                    <button id="btnSaveKvaGol_${ri}" data-row="${ri}"
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

        container.innerHTML = `<table style="width:100%;border-collapse:collapse;">${headHtml}<tbody>${rows}</tbody></table>`

        if (isReadOnly) return

        data.golongan.forEach((gol, ri) => {
            const btn = document.getElementById(`btnSaveKvaGol_${ri}`)
            if (!btn) return
            btn.addEventListener('click', async () => {
                btn.textContent = '⏳'; btn.disabled = true
                const inputs = container.querySelectorAll(`input[data-row="${ri}"]`)
                const values = {}
                inputs.forEach(inp => { values[inp.dataset.field] = inp.value === '' ? null : parseFloat(inp.value) })
                try {
                    const base = {
                        tahun: this._tahun, bulan: bulanIdx + 1,
                        sub_golongan_kode: gol, golongan_kode: gol.charAt(0),
                        is_triwulan: 0, is_akumulasi: 1, sumber: 'manual', nilai_pelanggan: 0,
                    }
                    if (values.nilai_kva_target !== null)
                        await upsert(API_PELANGGAN, { ...base, tipe: 'target'    }, { ...base, tipe: 'target',    nilai_kva: values.nilai_kva_target })
                    if (values.nilai_kva_realisasi !== null)
                        await upsert(API_PELANGGAN, { ...base, tipe: 'realisasi' }, { ...base, tipe: 'realisasi', nilai_kva: values.nilai_kva_realisasi })
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

    #renderEditTable(data, state) {
        const isReadOnly   = this._tahun < TAHUN_INI
        const subGolKode   = state.tarif ?? (data.tarif?.[0] ?? '')
        const targetArr    = data.rencanaData?.[subGolKode]   ?? new Array(12).fill(null)
        const realisasiArr = data.realisasiData?.[subGolKode] ?? new Array(12).fill(null)

        const rows = BULAN_NAMA.map((bln, i) => ({
            label: bln,
            fields: [
                { key: 'nilai_kva_target',    value: targetArr[i]    ?? '', color: '#ff9800' },
                { key: 'nilai_kva_realisasi', value: realisasiArr[i] ?? '', color: '#00ff88' },
            ],
        }))

        renderEditTable(
            'kvaEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan       = ri + 1
                const baseParams  = { tahun: this._tahun, bulan, sub_golongan_kode: subGolKode, is_triwulan: 0, is_akumulasi: 0 }
                const basePayload = { ...baseParams, golongan_kode: subGolKode.charAt(0), sumber: 'manual', nilai_pelanggan: 0 }
                if (values.nilai_kva_target !== null)
                    await upsert(API_PELANGGAN, { ...baseParams, tipe: 'target'    }, { ...basePayload, tipe: 'target',    nilai_kva: values.nilai_kva_target })
                if (values.nilai_kva_realisasi !== null)
                    await upsert(API_PELANGGAN, { ...baseParams, tipe: 'realisasi' }, { ...basePayload, tipe: 'realisasi', nilai_kva: values.nilai_kva_realisasi })
            },
            ['Bulan', 'Target KVA', 'Realisasi KVA'],
            (ri, values) => {
                if (values.nilai_kva_target    !== null) targetArr[ri]    = values.nilai_kva_target
                if (values.nilai_kva_realisasi !== null) realisasiArr[ri] = values.nilai_kva_realisasi

                const labels   = this.#getLabels()
                const sliceIdx = (state.bulan ?? 11) + 1
                const akum     = state.view === 'akumulasi'
                const toAkum   = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
                const dR       = akum ? toAkum(targetArr)    : targetArr
                const dRl      = akum ? toAkum(realisasiArr) : realisasiArr
                const slicedR  = dR.slice(0, sliceIdx)
                const slicedRl = dRl.slice(0, sliceIdx)
                const delta    = slicedRl.map((v, i) => (v ?? 0) - (slicedR[i] ?? 0))

                this.#renderBarTable(labels.slice(0, sliceIdx), slicedR, slicedRl)
                this.#renderDeltaTable(labels.slice(0, sliceIdx), delta)
                this.chart.make('chartKvaBar', 'bar', labels.slice(0, sliceIdx), [
                    { label: 'Rencana',   data: slicedR,  backgroundColor: '#ff9800' },
                    { label: 'Realisasi', data: slicedRl, backgroundColor: '#00ff88' },
                ])
                this.chart.make('chartKvaDelta', 'bar', labels.slice(0, sliceIdx), [
                    { label: 'Delta', data: delta, backgroundColor: delta.map(v => v >= 0 ? '#00ff88' : '#f44336') }
                ])
            }
        )
    }

    #updateEditWarning() {
        const isOld = this._tahun < TAHUN_INI
        const w1 = document.getElementById('kvaEditWarning')
        const w2 = document.getElementById('kvaEditWarning2')
        if (w1) w1.style.display = isOld ? 'inline-block' : 'none'
        if (w2) w2.style.display = isOld ? 'inline-block' : 'none'
    }

    #initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatKva')
        const modalHapus = document.getElementById('modalHapusKva')

        const btnMuat = document.getElementById('btnMuatKva')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatKva')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatKva')?.addEventListener('click', async () => {
                const tahun = parseInt(document.getElementById('muatTahunKva')?.value)
                if (modalMuat) modalMuat.style.display = 'none'
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }

        const btnHapus = document.getElementById('btnHapusKva')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusKva')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusKva')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunKva')?.value
                const tipe  = document.getElementById('hapusTipeKva')?.value
                if (!confirm(`Hapus data KVA tahun ${tahun} (${tipe})? Tindakan ini tidak dapat dibatalkan.`)) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    const params = tipe === 'semua' ? { tahun } : { tahun, tipe }
                    await deleteByFilter(API_PELANGGAN, params)
                    onChange()
                } catch {
                    alert('Gagal menghapus data KVA')
                }
            })
        }

        const modalBuat = document.getElementById('modalBuatKva')
        const btnBuat   = document.getElementById('btnBuatKva')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatKva')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatKva')?.addEventListener('click', async () => {
                const tahun  = parseInt(document.getElementById('buatTahunKva')?.value)
                const tipe   = document.getElementById('buatTipeKva')?.value
                const subGol = document.getElementById('buatSubGolKva')?.value || 'JUMLAH'
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch(API_PELANGGAN, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, tipe, sub_golongan_kode: subGol, golongan_kode: subGol.charAt(0), is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', nilai_pelanggan: 0, nilai_kva: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data KVA tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }
    }
}