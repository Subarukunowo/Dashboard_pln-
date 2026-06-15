const COLORS = ['#ff9800', '#00ff88', '#2196f3', '#e91e63', '#9c27b0', '#00bcd4']

const BULAN_NAMES = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

export default class JamNyalaModule {
    constructor(chart) {
        this.chart    = chart
        this.rawData  = null  // simpan data asli untuk edit
        this.tahunAktif = null
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

        // Deteksi apakah data tahun lalu
        const warning = document.getElementById('jamNyalaWarning')
        if (data.tahun_efektif && data.tahun_efektif < new Date().getFullYear()) {
            if (warning) warning.style.display = 'inline-block'
        } else {
            if (warning) warning.style.display = 'none'
        }

        const sliceIdx = (state.bulan && state.bulan !== 'all')
            ? parseInt(state.bulan) + 1 : 12
        const labels = this._getLabels().slice(0, sliceIdx)

        this._populateTarif(data, state.tarif)

        const selected = state.tarif === 'ALL'
            ? data.tarif.filter(t => t.toLowerCase().includes('jumlah'))
            : [state.tarif]

        this.chart.make('chartJamNyalaLine', 'line', labels,
            selected.map((t, i) => ({
                label:       t,
                data:        (data.data[t] || new Array(12).fill(null)).slice(0, sliceIdx),
                borderColor: COLORS[i % COLORS.length],
                tension:     0.4,
                fill:        false,
                pointRadius: 3,
            }))
        )

        this._renderLineTable(labels, selected.map((t, i) => ({
            label: t,
            data:  (data.data[t] || new Array(12).fill(null)).slice(0, sliceIdx),
        })))

        const tarifBar = state.tarif === 'ALL' ? (selected[0] || data.tarif[0]) : state.tarif
        const d = (data.data[tarifBar] || new Array(12).fill(null)).slice(0, sliceIdx)
        this.chart.make('chartJamNyalaBar', 'bar', labels, [
            { label: tarifBar, data: d, backgroundColor: '#00ff88' },
        ], v => v !== null ? v.toFixed(0) + ' jam' : '—')

        this._renderEditTable(data)
    }

    initFilters(state, onChange) {
        document.getElementById('filterTarifJamNyala')?.addEventListener('change', e => {
            state.tarif = e.target.value; onChange()
        })
        document.getElementById('filterBulanJamNyala')?.addEventListener('change', e => {
            state.bulan = e.target.value; onChange()
        })

        this._initCrud(state, onChange)
    }

    _initCrud(state, onChange) {
        // Modal Muat
        const modalMuat  = document.getElementById('modalMuatJamNyala')
        document.getElementById('btnMuatJamNyala')?.addEventListener('click', () => {
            if (modalMuat) modalMuat.style.display = 'flex'
        })
        document.getElementById('btnBatalMuat')?.addEventListener('click', () => {
            if (modalMuat) modalMuat.style.display = 'none'
        })
        document.getElementById('btnKonfirmasiMuat')?.addEventListener('click', async () => {
            const tahun = document.getElementById('muatTahunJamNyala')?.value
            const tipe  = document.getElementById('muatTipeJamNyala')?.value
            if (modalMuat) modalMuat.style.display = 'none'
            await this._fetchAndRender(parseInt(tahun), tipe, state, onChange)
        })

        // Modal Hapus
        const modalHapus = document.getElementById('modalHapusJamNyala')
        document.getElementById('btnHapusJamNyala')?.addEventListener('click', () => {
            if (modalHapus) modalHapus.style.display = 'flex'
        })
        document.getElementById('btnBatalHapus')?.addEventListener('click', () => {
            if (modalHapus) modalHapus.style.display = 'none'
        })
        document.getElementById('btnKonfirmasiHapus')?.addEventListener('click', async () => {
            const tahun = document.getElementById('hapusTahunJamNyala')?.value
            const tipe  = document.getElementById('hapusTipeJamNyala')?.value
            if (modalHapus) modalHapus.style.display = 'none'
            await this._hapusData(parseInt(tahun), tipe)
        })

        // Modal Buat
        const modalBuat = document.getElementById('modalBuatJamNyala')
        const btnBuat   = document.getElementById('btnBuatJamNyala')
        if (btnBuat && !btnBuat._boundBuat) {
            btnBuat._boundBuat = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatJamNyala')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatJamNyala')?.addEventListener('click', async () => {
                const tahun = parseInt(document.getElementById('buatTahunJamNyala')?.value)
                const tipe  = document.getElementById('buatTipeJamNyala')?.value
                if (modalBuat) modalBuat.style.display = 'none'
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch('/api/v1/fact/jam-nyala', {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, tipe, golongan_kode: 'JUMLAH', nilai_jam: 0, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual' }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data Jam Nyala tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                await this._fetchAndRender(tahun, tipe, state, onChange)
            })
        }
    }

    async _fetchAndRender(tahun, tipe, state, onChange) {
        try {
            const res    = await fetch(`/api/chart/jam-nyala?tahun=${tahun}&tipe=${tipe}`)
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            const res = await fetch(`/api/v1/fact/jam-nyala?${params}`, {
                method:  'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                },
            })
            if (res.ok) {
                alert(`Data tahun ${tahun} berhasil dihapus`)
                // Reset tabel
                const tbody = document.getElementById('jamNyalaSideTable')
                if (tbody) tbody.innerHTML = ''
            } else {
                alert('Gagal menghapus data')
            }
        } catch (err) {
            console.error('Hapus gagal:', err)
        }
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById('filterTarifJamNyala')
        if (!sel || sel.options.length > 0 || !data.tarif) return

        const opt0 = document.createElement('option')
        opt0.value = 'ALL'
        opt0.textContent = 'Semua Golongan'
        sel.appendChild(opt0)

        data.tarif.forEach(t => {
            const opt = document.createElement('option')
            opt.value = t
            opt.textContent = t
            sel.appendChild(opt)
        })
        sel.value = currentTarif
    }

    _renderLineTable(labels, datasets) {
        const tbody = document.getElementById('tabelJamNyalaLine')
        const thead = document.getElementById('jamNyalaLineHead')
        if (!tbody) return

        if (thead) {
            if (datasets.length > 1) {
                thead.innerHTML = `
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    ${datasets.map((ds, i) => `
                        <th style="padding:10px; text-align:center; color:${COLORS[i % COLORS.length]}; font-size:0.7rem;">${ds.label}</th>
                    `).join('')}
                `
            } else {
                thead.innerHTML = `
                    <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
                    <th style="padding:10px; text-align:center; color:var(--accent-green);">Jam Nyala</th>
                `
            }
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

    _renderEditTable(data) {
        const tbody = document.getElementById('jamNyalaSideTable')
        if (!tbody || !data.tarif) return
        const isReadOnly = this._tahunAktif
            ? this._tahunAktif < new Date().getFullYear()
            : false

        tbody.innerHTML = data.tarif.map((tarif, ti) => {
            const isJumlah  = tarif.toLowerCase().includes('jumlah')
            const rowColor  = isJumlah ? 'rgba(255,255,255,0.04)' : 'transparent'
            const fontStyle = isJumlah ? 'font-weight:600; color:#fff;' : 'color:#aaa;'

            const cells = Array.from({ length: 12 }, (_, i) => {
                const val = data.data[tarif]?.[i]
                return `<td style="padding:4px 6px; text-align:center;">
                    <input
                        type="number"
                        data-tarif="${ti}"
                        data-bulan="${i + 1}"
                        value="${val !== null && val !== undefined ? val : ''}"
                        placeholder="—"
                        ${isReadOnly ? 'disabled' : ''}
                        style="width:52px; text-align:center; padding:3px 4px; border-radius:4px;
                               border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                               background:#1a1a1a; color:${val !== null && !isReadOnly ? '#ccc' : '#444'};
                               font-size:0.75rem;"
                    />
                </td>`
            }).join('')

            return `<tr style="background:${rowColor}; border-bottom:1px solid #2a2a2a;" data-tarif-index="${ti}">
                <td style="padding:8px 10px; font-size:0.75rem; ${fontStyle} min-width:180px;">${tarif}</td>
                ${cells}
                <td style="padding:4px 6px; text-align:center;">
                    ${isReadOnly
                        ? '<span style="font-size:0.7rem;color:#444;">🔒</span>'
                        : `<button data-tarif-index="${ti}"
                                style="padding:3px 8px; border-radius:4px; border:none; background:#ff9800;
                                       color:#000; font-size:0.7rem; cursor:pointer; font-weight:600;">
                                Simpan
                           </button>`
                    }
                </td>
            </tr>`
        }).join('')

        if (isReadOnly) return

        // Gunakan event delegation — 1 listener untuk semua tombol Simpan
        tbody.removeEventListener('click', tbody._saveHandler)
        tbody._saveHandler = async (e) => {
            const btn = e.target.closest('button[data-tarif-index]')
            if (!btn) return
            const ti     = parseInt(btn.dataset.tarifIndex)
            const tarif  = data.tarif[ti]
            if (!tarif) return

            btn.textContent = '⏳'
            btn.disabled    = true

            const tahun     = this.rawData?.tahun_efektif ?? new Date().getFullYear()
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            const inputs    = tbody.querySelectorAll(`input[data-tarif="${ti}"]`)
            let saved = 0

            for (const input of inputs) {
                const bulan = parseInt(input.dataset.bulan)
                const nilai = input.value === '' ? null : parseFloat(input.value)

                try {
                    const checkRes = await fetch(
                        `/api/v1/fact/jam-nyala?tahun=${tahun}&bulan=${bulan}&golongan_kode=${encodeURIComponent(tarif)}&tipe=realisasi&is_triwulan=0&is_akumulasi=0`,
                        { headers: { Accept: 'application/json' } }
                    )
                    const checkData = await checkRes.json()
                    const existing  = checkData.data?.data?.[0]

                    if (existing) {
                        await fetch(`/api/v1/fact/jam-nyala/${existing.id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ nilai_jam: nilai }),
                        })
                    } else if (nilai !== null) {
                        await fetch('/api/v1/fact/jam-nyala', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({
                                tahun, bulan, tipe: 'realisasi', golongan_kode: tarif,
                                nilai_jam: nilai, is_triwulan: 0, is_akumulasi: 0, sumber: 'manual',
                            }),
                        })
                    }
                    saved++
                } catch (err) {
                    console.error(`Gagal simpan ${tarif} bulan ${bulan}:`, err)
                }
            }

            btn.disabled    = false
            btn.textContent = saved > 0 ? '✓ OK' : '✗ Error'
            btn.style.background = saved > 0 ? '#00ff88' : '#f44336'
            setTimeout(() => {
                btn.textContent      = 'Simpan'
                btn.style.background = '#ff9800'
            }, 2000)
        }
        tbody.addEventListener('click', tbody._saveHandler)
    }
}