import { apiGet, apiPost, apiPut, apiDelete } from '@/Dashboard/utils/apiClient'

const COLORS      = ['#ff9800', '#00ff88', '#2196f3', '#e91e63', '#9c27b0', '#00bcd4']
const BULAN_NAMES = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
const GRUP_ORDER  = ['S', 'R', 'B', 'I', 'P', 'M']
const GRUP_LABEL  = { S: 'Sosial', R: 'Rumah Tangga', B: 'Bisnis', I: 'Industri', P: 'Publik', M: 'Multiguna' }

export default class JamNyalaModule {
    constructor(chart) {
        this.chart        = chart
        this.rawData      = null
        this.tahunAktif   = null
        this.golonganList = []
        this.golonganEdit = null
        this.tahunEdit    = new Date().getFullYear()
        this.detailEdit   = null
        this._ready       = null
    }

    async init(state, onChange) {
        if (this._ready) return this._ready
        this._ready = (async () => {
            await this._loadGolonganList()
            await this._loadTahunTersedia()
            this._initEditFilters(state, onChange)
        })()
        return this._ready
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanJamNyala option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return
        this.rawData    = data
        this.tahunAktif = data.tahun_efektif ?? null

        const warning = document.getElementById('jamNyalaWarning')
        if (warning) warning.style.display = (data.tahun_efektif && data.tahun_efektif < new Date().getFullYear()) ? 'inline-block' : 'none'

        const sliceIdx = (state.bulan && state.bulan !== 'all') ? Number.parseInt(state.bulan) + 1 : 12
        const labels   = this._getLabels().slice(0, sliceIdx)

        const tarifTersedia = Array.isArray(data.tarif) ? data.tarif : []
        const selected = state.tarif === 'ALL'
            ? tarifTersedia.filter(t => typeof t === 'string' && t.toLowerCase().includes('jumlah'))
            : [state.tarif]

        this.chart.make('chartJamNyalaLine', 'line', labels,
            selected.map((t, i) => ({
                label:       t,
                data:        (data.data?.[t] || new Array(12).fill(null)).slice(0, sliceIdx),
                borderColor: COLORS[i % COLORS.length],
                tension:     0.4,
                fill:        false,
                pointRadius: 3,
            }))
        )

        this._renderLineTable(labels, selected.map((t, i) => ({
            label: t,
            data:  (data.data?.[t] || new Array(12).fill(null)).slice(0, sliceIdx),
        })))

        const tarifBar = state.tarif === 'ALL' ? (selected[0] || tarifTersedia[0]) : state.tarif
        const d = (data.data?.[tarifBar] || new Array(12).fill(null)).slice(0, sliceIdx)
        this.chart.make('chartJamNyalaBar', 'bar', labels, [
            { label: tarifBar ?? '—', data: d, backgroundColor: '#00ff88' },
        ], v => v == null ? '—' : v.toFixed(0) + ' jam')
    }

    initFilters(state, onChange) {
        document.getElementById('filterTarifJamNyala')?.addEventListener('change', e => {
            state.tarif = e.target.value
            this.render(this.rawData, state)
        })
        document.getElementById('filterBulanJamNyala')?.addEventListener('change', e => {
            state.bulan = e.target.value
            this.render(this.rawData, state)
        })
        this._initCrud(state, onChange)
    }

    _renderLineTable(labels, datasets) {
        const tbody = document.getElementById('tabelJamNyalaLine')
        const thead = document.getElementById('jamNyalaLineHead')
        if (!tbody) return

        if (thead) {
            thead.innerHTML = datasets.length > 1
                ? `<th style="padding:10px; text-align:center; color:#888;">Bulan</th>` +
                  datasets.map((ds, i) => `<th style="padding:10px; text-align:center; color:${COLORS[i % COLORS.length]}; font-size:0.7rem;">${ds.label}</th>`).join('')
                : `<th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                   <th style="padding:10px; text-align:center; color:var(--accent-green);">Jam Nyala</th>`
        }

        tbody.innerHTML = labels.map((bln, i) => {
            const cells = datasets.map((ds, j) => {
                const val = ds.data[i]
                return `<td style="padding:8px 10px; text-align:center; color:${COLORS[j % COLORS.length]}; font-weight:500;">
                    ${val !== null && val !== undefined ? val.toFixed(0) : '—'}
                </td>`
            }).join('')
            return `<tr style="border-bottom:1px solid #333;">
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
                ${cells}
            </tr>`
        }).join('')
    }

    _initCrud(state, onChange) {
        const modalMuat = document.getElementById('modalMuatJamNyala')
        const btnMuat   = document.getElementById('btnMuatJamNyala')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuat')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuat')?.addEventListener('click', async () => {
                const tahun = document.getElementById('muatTahunJamNyala')?.value
                const tipe  = document.getElementById('muatTipeJamNyala')?.value
                if (modalMuat) modalMuat.style.display = 'none'
                await this._fetchAndRender(Number.parseInt(tahun), tipe, state, onChange)
            })
        }

        const modalHapus = document.getElementById('modalHapusJamNyala')
        const btnHapus    = document.getElementById('btnHapusJamNyala')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapus')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapus')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('hapusTahunJamNyala')?.value)
                const tipe  = document.getElementById('hapusTipeJamNyala')?.value
                if (modalHapus) modalHapus.style.display = 'none'
                await this._hapusData(tahun, tipe)
                await this._loadTahunTersedia()
                if (tahun === this.tahunEdit) await this._loadDetailEdit()
            })
        }

        const modalBuat = document.getElementById('modalBuatJamNyala')
        const btnBuat   = document.getElementById('btnBuatJamNyala')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatJamNyala')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatJamNyala')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('buatTahunJamNyala')?.value)
                const tipe  = document.getElementById('buatTipeJamNyala')?.value
                if (modalBuat) modalBuat.style.display = 'none'

                let result = null
                let ok = true
                try {
                    const res = await apiPost('/api/v1/fact/jam-nyala/buat-tahun', { tahun, tipe })
                    result = await res.json()
                    if (!res.ok) ok = false
                } catch {
                    ok = false
                }

                if (ok && result?.data) {
                    const { created, skipped, failed, total_kode } = result.data
                    let msg = `Tahun ${tahun} — Golongan: ${total_kode}, Baris baru: ${created}, Sudah ada: ${skipped}`
                    if (failed > 0) msg += `\n⚠ GAGAL: ${failed} baris — cek log server untuk detail.`
                    alert(msg)
                } else {
                    alert('Gagal membuat data.')
                }

                await this._loadTahunTersedia()
                await this._fetchAndRender(tahun, tipe, state, onChange)
                if (tahun === this.tahunEdit) await this._loadDetailEdit()
            })
        }
    }

    async _fetchAndRender(tahun, tipe, state, onChange) {
        try {
            const res    = await apiGet(`/api/chart/jam-nyala?tahun=${tahun}&tipe=${tipe}`)
            const result = await res.json()
            if (result.data) {
                result.data.tahun_efektif = tahun
                this.rawData = result.data
                this.render(result.data, state)
            }
        } catch (err) {
            console.error('Gagal muat data:', err)
        }
    }

    async _hapusData(tahun, tipe) {
        const params = new URLSearchParams({ tahun })
        if (tipe !== 'semua') params.append('tipe', tipe)
        try {
            const res = await apiDelete(`/api/v1/fact/jam-nyala?${params}`)
            if (res.ok) {
                alert(`Data tahun ${tahun} berhasil dihapus`)
            } else {
                alert('Gagal menghapus data')
            }
        } catch (err) {
            console.error('Hapus gagal:', err)
        }
    }

    async _loadGolonganList() {
        try {
            const res = await apiGet('/api/v1/dim/sub-golongan?per_page=200')
            if (!res.ok) throw new Error(`HTTP ${res.status}`)
            const json = await res.json()

            const raw = json.data?.data ?? json.data ?? []
            this.golonganList = Array.isArray(raw) ? raw.filter(g => g && typeof g.kode === 'string') : []
        } catch (err) {
            console.error('Gagal ambil daftar golongan:', err)
            this.golonganList = []
        }

        this._populateTarifDropdown()
        this._populateEditDropdown()
    }

    _groupGolongan() {
        const grouped     = {}
        const jumlahItems = {}

        this.golonganList.forEach(g => {
            if (g.kode === 'JUMLAH') return
            if (g.kode.startsWith('JUMLAH-')) {
                if (g.golongan_kode) jumlahItems[g.golongan_kode] = g
                return
            }
            if (!g.golongan_kode) return
            if (!grouped[g.golongan_kode]) grouped[g.golongan_kode] = []
            grouped[g.golongan_kode].push(g)
        })

        Object.values(grouped).forEach(list => list.sort((a, b) => (a.urutan ?? 0) - (b.urutan ?? 0)))
        return { grouped, jumlahItems }
    }

    _buildGolonganOptions() {
        const { grouped, jumlahItems } = this._groupGolongan()
        let html = ''

        GRUP_ORDER.forEach(key => {
            const items = grouped[key] ?? []
            const jumlah = jumlahItems[key]
            if (!items.length && !jumlah) return

            html += `<optgroup label="${GRUP_LABEL[key] ?? key}">`
            items.forEach(g => { html += `<option value="${g.kode}">${g.nama ?? g.kode}</option>` })
            if (jumlah) html += `<option value="${jumlah.kode}">${jumlah.nama ?? jumlah.kode}</option>`
            html += `</optgroup>`
        })

        return html
    }

    _populateTarifDropdown() {
        const sel = document.getElementById('filterTarifJamNyala')
        if (!sel) return

        const prevValue = sel.value
        const options   = this._buildGolonganOptions()

        sel.innerHTML = `<option value="ALL">Semua Golongan</option>` + options

        const hasPrev = prevValue && [...sel.options].some(o => o.value === prevValue)
        sel.value = hasPrev ? prevValue : 'ALL'
    }

    _populateEditDropdown() {
        const sel = document.getElementById('filterGolonganEdit')
        if (!sel) return

        const prevValue = sel.value
        const options    = this._buildGolonganOptions()

        sel.innerHTML = options
        if (!sel.options.length) {
            this.golonganEdit = null
            return
        }

        const hasPrev = prevValue && [...sel.options].some(o => o.value === prevValue)
        sel.value = hasPrev ? prevValue : sel.options[0].value
        this.golonganEdit = sel.value
    }

    async _loadTahunTersedia() {
        try {
            const res    = await apiGet('/api/v1/fact/jam-nyala/tahun-tersedia')
            if (!res.ok) throw new Error(`HTTP ${res.status}`)
            const json   = await res.json()
            const tahuns = Array.isArray(json.data) ? json.data : []

            const currentYear = new Date().getFullYear()
            const opts = tahuns.length
                ? tahuns.map(t => `<option value="${t}">${t}</option>`).join('')
                : `<option value="${currentYear}">${currentYear}</option>`

            const muat  = document.getElementById('muatTahunJamNyala')
            const hapus = document.getElementById('hapusTahunJamNyala')
            if (muat)  muat.innerHTML  = opts
            if (hapus) hapus.innerHTML = opts
        } catch (err) {
            console.error('Gagal ambil daftar tahun tersedia:', err)
        }
    }

    _initEditFilters(state, onChange) {
        this._editState    = state
        this._editOnChange = onChange

        document.getElementById('filterGolonganEdit')?.addEventListener('change', e => {
            this.golonganEdit = e.target.value
            this._loadDetailEdit()
        })
        this._loadDetailEdit()
    }

    async _loadDetailEdit() {
        if (!this.golonganEdit) return
        try {
            const res  = await apiGet(`/api/v1/fact/jam-nyala/detail?tahun=${this.tahunEdit}&golongan_kode=${encodeURIComponent(this.golonganEdit)}`)
            if (!res.ok) throw new Error(`HTTP ${res.status}`)
            const json = await res.json()
            this.detailEdit = json.data
            this._renderGolonganEditTable(json.data)
        } catch (err) {
            console.error('Gagal ambil detail golongan:', err)
        }
    }

    _renderGolonganEditTable(detail) {
        const tbody = document.getElementById('jamNyalaSideTable')
        if (!tbody || !detail) return

        tbody.innerHTML = BULAN_NAMES.map((nama, i) => {
            const tVal = detail.target?.[i]
            const rVal = detail.realisasi?.[i]
            return `<tr style="border-bottom:1px solid #2a2a2a;" data-bulan="${i + 1}">
                <td style="padding:8px 10px; font-size:0.8rem; color:#ccc;">${nama}</td>
                <td style="padding:4px 6px; text-align:center;">
                    <input type="number" data-tipe="target" data-bulan="${i + 1}"
                        value="${tVal !== null && tVal !== undefined ? tVal : ''}" placeholder="—"
                        style="width:110px; text-align:center; padding:5px 6px; border-radius:4px;
                               border:1px solid #333; background:#1a1a1a; color:#ff9800; font-size:0.8rem;" />
                </td>
                <td style="padding:4px 6px; text-align:center;">
                    <input type="number" data-tipe="realisasi" data-bulan="${i + 1}"
                        value="${rVal !== null && rVal !== undefined ? rVal : ''}" placeholder="—"
                        style="width:110px; text-align:center; padding:5px 6px; border-radius:4px;
                               border:1px solid #333; background:#1a1a1a; color:#00ff88; font-size:0.8rem;" />
                </td>
                <td style="padding:4px 6px; text-align:center;">
                    <button data-bulan="${i + 1}"
                        style="padding:5px 14px; border-radius:4px; border:none; background:#ff9800;
                               color:#000; font-size:0.75rem; cursor:pointer; font-weight:600;">
                        Simpan
                    </button>
                </td>
            </tr>`
        }).join('')

        tbody.removeEventListener('click', tbody._saveRowHandler)
        tbody._saveRowHandler = async (e) => {
            const btn = e.target.closest('button[data-bulan]')
            if (!btn) return
            const bulan = Number.parseInt(btn.dataset.bulan)
            const row   = tbody.querySelector(`tr[data-bulan="${bulan}"]`)
            const ids   = this.detailEdit?.ids?.[bulan - 1] ?? { target: null, realisasi: null }

            btn.textContent = '⏳'; btn.disabled = true

            let ok = true
            for (const tipe of ['target', 'realisasi']) {
                const input = row.querySelector(`input[data-tipe="${tipe}"]`)
                const nilai = input.value === '' ? null : Number.parseFloat(input.value)
                const id    = ids[tipe]

                try {
                    if (id) {
                        const res = await apiPut(`/api/v1/fact/jam-nyala/${id}`, { nilai_jam: nilai })
                        if (!res.ok) ok = false
                    } else if (nilai !== null) {
                        const res = await apiPost('/api/v1/fact/jam-nyala', {
                            tahun: this.tahunEdit, bulan, tipe,
                            golongan_kode: this.golonganEdit, nilai_jam: nilai,
                            is_triwulan: 0, is_akumulasi: 0, sumber: 'manual',
                        })
                        if (!res.ok) ok = false
                    }
                } catch {
                    ok = false
                }
            }

            btn.disabled    = false
            btn.textContent = ok ? '✓ OK' : '✗ Error'
            btn.style.background = ok ? '#00ff88' : '#f44336'
            setTimeout(() => { btn.textContent = 'Simpan'; btn.style.background = '#ff9800' }, 2000)

            await this._loadDetailEdit()

            if (ok && this._editState && this._editOnChange) {
                await this._fetchAndRender(this.tahunEdit, 'realisasi', this._editState, this._editOnChange)
            }
        }
        tbody.addEventListener('click', tbody._saveRowHandler)
    }
}