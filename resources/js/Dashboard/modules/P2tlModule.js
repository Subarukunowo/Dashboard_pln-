import { renderEditTable, upsert, deleteByFilter, TAHUN_INI } from '../utils/CrudHelper.js'

const BULAN_NAMA = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER']
const API = '/api/v1/fact/p2tl'

export default class P2tlModule {
    constructor(chart) {
        this.chart    = chart
        this._rawData = null
        this._tahun   = TAHUN_INI
    }

    render(data, state) {
        if (!data) return
        this._rawData = data
        this._tahun   = state.tahun ?? TAHUN_INI

        const filtered = (state.bulan && state.bulan !== 'all')
            ? data.slice(0, Number.parseInt(state.bulan) + 1)
            : data
        const labels = filtered.map(r => r.bulan)

        this.chart.make('chartP2tlTemuan', 'bar', labels, [
            { label: 'Pemeriksaan', data: filtered.map(r => r.pemeriksaan),     backgroundColor: '#2196f3' },
            { label: 'Temuan',      data: filtered.map(r => r.temuanPelanggan), backgroundColor: '#f44336' },
        ])
        this.chart.make('chartP2tlKwh', 'bar', labels, [
            { label: 'Temuan kWh', data: filtered.map(r => r.temuanKwh), backgroundColor: '#ff9800' },
        ], v => v.toFixed(0) + ' kWh')
        this.chart.make('chartP2tlTagsus', 'bar', labels, [
            { label: 'Tagsus (Rp)', data: filtered.map(r => r.tagsus), backgroundColor: '#00ff88' },
        ], v => 'Rp ' + (v / 1_000_000_000).toFixed(2) + 'M')

        this._renderTemuanTable(filtered)
        this._renderKwhTable(filtered)
        this._renderTagsusTable(filtered)
        this._renderDetailTable(filtered)
        this._renderEditTable(data)
    }

    initFilters(state, onChange) {
        document.getElementById('filterBulanP2tl')?.addEventListener('change', e => {
            state.bulan = e.target.value; onChange()
        })
        this._initCrud(state, onChange)
    }

    _renderTemuanTable(d) {
        const tbody = document.getElementById('p2tlSideTable')
        if (!tbody) return
        tbody.innerHTML = d.map(r =>
            '<tr>' +
            '<td>' + r.bulan + '</td>' +
            '<td style="color:#2196f3;">' + r.pemeriksaan.toLocaleString('id-ID') + '</td>' +
            '<td style="color:#f44336;">' + r.temuanPelanggan.toLocaleString('id-ID') + '</td>' +
            '<td style="color:#ff9800;">' + r.temuanKwh.toFixed(0) + '</td>' +
            '</tr>'
        ).join('')
    }

    _renderKwhTable(d) {
        const tbody = document.getElementById('tabelP2tlKwh')
        if (!tbody) return
        tbody.innerHTML = d.map(r =>
            '<tr><td>' + r.bulan + '</td>' +
            '<td style="color:#ff9800;">' + r.temuanKwh.toFixed(0) + ' kWh</td></tr>'
        ).join('')
    }

    _renderTagsusTable(d) {
        const tbody = document.getElementById('tabelP2tlTagsus')
        if (!tbody) return
        tbody.innerHTML = d.map(r =>
            '<tr><td>' + r.bulan + '</td>' +
            '<td style="color:#00ff88;font-weight:600;">Rp ' + (r.tagsus / 1_000_000_000).toFixed(2) + 'M</td></tr>'
        ).join('')
    }

    _renderDetailTable(d) {
        const tbody = document.getElementById('tabelP2tl')
        if (!tbody) return
        tbody.innerHTML = d.map(r =>
            '<tr>' +
            '<td>' + r.no + '</td>' +
            '<td>' + r.bulan + '</td>' +
            '<td style="color:#2196f3;">' + r.pemeriksaan.toLocaleString('id-ID') + '</td>' +
            '<td style="color:#f44336;">' + r.temuanPelanggan.toLocaleString('id-ID') + '</td>' +
            '<td style="color:#ff9800;">' + r.temuanKwh.toFixed(2) + '</td>' +
            '<td style="color:#00ff88;font-weight:600;">Rp ' + (r.tagsus / 1_000_000_000).toFixed(2) + 'M</td>' +
            '</tr>'
        ).join('')
    }

    _renderEditTable(d) {
        const isReadOnly = this._tahun < TAHUN_INI
        const rows = BULAN_NAMA.map((bln, i) => {
            const r = d[i] ?? {}
            return {
                label: bln,
                fields: [
                    { key: 'jumlah_pemeriksaan', value: r.pemeriksaan     ?? '', color: '#2196f3' },
                    { key: 'jumlah_temuan',       value: r.temuanPelanggan ?? '', color: '#f44336' },
                    { key: 'temuan_kwh',           value: r.temuanKwh      ?? '', color: '#ff9800' },
                    { key: 'nilai_tagihan_rp',     value: r.tagsus         ?? '', color: '#00ff88' },
                ],
            }
        })

        renderEditTable(
            'p2tlEditContainer',
            rows,
            isReadOnly,
            async (ri, values) => {
                const bulan = ri + 1
                await upsert(
                    API,
                    { tahun: this._tahun, bulan, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0 },
                    { tahun: this._tahun, bulan, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', ...values }
                )
            },
            ['Bulan', 'Pemeriksaan', 'Temuan', 'Temuan kWh', 'Tagihan (Rp)']
        )
    }

    _initCrud(state, onChange) {
        const modalMuat  = document.getElementById('modalMuatP2tl')
        const modalHapus = document.getElementById('modalHapusP2tl')

        const btnMuat = document.getElementById('btnMuatP2tl')
        if (btnMuat && !btnMuat._bound) {
            btnMuat._bound = true
            btnMuat.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'flex' })
            document.getElementById('btnBatalMuatP2tl')?.addEventListener('click', () => { if (modalMuat) modalMuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiMuatP2tl')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('muatTahunP2tl')?.value)
                if (modalMuat) modalMuat.style.display = 'none'
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }

        const btnHapus = document.getElementById('btnHapusP2tl')
        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusP2tl')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusP2tl')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunP2tl')?.value
                if (!confirm('Hapus data P2TL tahun ' + tahun + '? Tidak dapat dibatalkan.')) return
                if (modalHapus) modalHapus.style.display = 'none'
                try {
                    await deleteByFilter(API, { tahun })
                    onChange()
                } catch (e) {
                    console.error('Gagal menghapus data P2TL:', e)
                    alert('Gagal menghapus data')
                }
            })
        }

        const modalBuat = document.getElementById('modalBuatP2tl')
        const btnBuat   = document.getElementById('btnBuatP2tl')
        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatP2tl')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatP2tl')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('buatTahunP2tl')?.value)
                if (modalBuat) modalBuat.style.display = 'none'
                const csrf  = document.querySelector('meta[name="csrf-token"]')?.content
                const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
                let ok = true
                for (let bulan = 1; bulan <= 12; bulan++) {
                    try {
                        const res = await fetch(API, {
                            method: 'POST', headers: heads,
                            body: JSON.stringify({ tahun, bulan, tipe: 'realisasi', is_triwulan: 0, is_akumulasi: 0, sumber: 'manual', jumlah_pemeriksaan: 0, jumlah_temuan: 0, temuan_kwh: 0, nilai_tagihan_rp: 0 }),
                        })
                        if (!res.ok) ok = false
                    } catch { ok = false }
                }
                alert(ok ? `Data P2TL tahun ${tahun} berhasil dibuat.` : 'Sebagian data gagal dibuat.')
                this._tahun = tahun
                state.tahun = tahun
                onChange()
            })
        }
    }
}