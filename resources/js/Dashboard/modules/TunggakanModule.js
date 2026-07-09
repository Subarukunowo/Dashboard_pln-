import { upsert, TAHUN_INI } from '../utils/CrudHelper.js'

const API = '/api/v1/fact/tunggakan'

export default class TunggakanModule {
    constructor(chart, service) {
        this.chart    = chart
        this.service  = service
        this._rawData = null
        this._tahun   = TAHUN_INI
    }

    _getLabels() {
        return [...document.querySelectorAll('#filterBulanTunggakan option')]
            .filter(o => o.value !== 'all')
            .map(o => o.textContent.trim())
    }

    render(data, state, onChange) {
        if (!data) return
        this._rawData = data
        this._tahun   = data.tahun_efektif ?? TAHUN_INI

        const warning = document.getElementById('tunggakanWarning')
        if (warning) warning.style.display = data.is_fallback ? 'inline-block' : 'none'

        const source       = data.data
        const labels       = this._getLabels()
        const filtered     = (state.bulan && state.bulan !== 'all')
            ? source.slice(0, Number.parseInt(state.bulan) + 1) : source
        const slicedLabels = labels.slice(0, filtered.length)

        this.chart.make('chartTunggakanPendapatan', 'bar', slicedLabels, [
            { label: 'Pendapatan', data: filtered.map(r => r.pendapatan), backgroundColor: '#00ff88' },
        ], v => 'Rp ' + (v / 1_000_000_000).toFixed(2) + 'M')

        this.chart.make('chartTunggakanRatio', 'line', slicedLabels, [
            { label: 'Ratio Tunggakan', data: filtered.map(r => r.ratioTunggakan * 100), borderColor: '#f44336', tension: 0.4, fill: false, pointRadius: 4 },
            { label: 'Target Ratio',    data: filtered.map(r => r.ratioTarget * 100),    borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 4, borderDash: [5, 5] },
        ], v => v.toFixed(4) + '%')

        this._renderPendapatanTable(filtered, slicedLabels)
        this._renderRatioTable(filtered, slicedLabels)
        this._renderDetailTable(source, labels, onChange)
    }

    initFilters(state, onChange) {
        document.getElementById('filterBulanTunggakan')?.addEventListener('change', e => {
            state.bulan = e.target.value; onChange()
        })
        this._initCrud(state, onChange)
    }

    _initCrud(state, onChange) {
        const btnBuat    = document.getElementById('btnBuatTahunTunggakan')
        const btnHapus   = document.getElementById('btnHapusTunggakan')
        const modalBuat  = document.getElementById('modalBuatTunggakan')
        const modalHapus = document.getElementById('modalHapusTunggakan')

        if (btnBuat && !btnBuat._bound) {
            btnBuat._bound = true
            btnBuat.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'flex' })
            document.getElementById('btnBatalBuatTunggakan')?.addEventListener('click', () => { if (modalBuat) modalBuat.style.display = 'none' })
            document.getElementById('btnKonfirmasiBuatTunggakan')?.addEventListener('click', async () => {
                const tahun = Number.parseInt(document.getElementById('tahunBuatTunggakan')?.value)
                try {
                    await this.service.createTahun(tahun)
                    if (modalBuat) modalBuat.style.display = 'none'
                    onChange()
                } catch { alert('Gagal membuat data tahun baru') }
            })
        }

        if (btnHapus && !btnHapus._bound) {
            btnHapus._bound = true
            btnHapus.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'flex' })
            document.getElementById('btnBatalHapusTunggakan')?.addEventListener('click', () => { if (modalHapus) modalHapus.style.display = 'none' })
            document.getElementById('btnKonfirmasiHapusTunggakan')?.addEventListener('click', async () => {
                const tahun = document.getElementById('hapusTahunTunggakan')?.value
                if (!confirm('Hapus semua data tunggakan tahun ' + tahun + '? Tidak dapat dibatalkan.')) return
                try {
                    await this.service.hapusData(tahun)
                    if (modalHapus) modalHapus.style.display = 'none'
                    onChange()
                } catch { alert('Gagal menghapus data') }
            })
        }
    }

    _renderPendapatanTable(d, labels) {
        const tbody = document.getElementById('tunggakanSideTable')
        if (!tbody) return
        const fmt = v => 'Rp ' + (v / 1_000_000_000).toFixed(2) + 'M'
        tbody.innerHTML = d.map((r, i) => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return '<tr>' +
                '<td>' + (labels[i] ?? r.bulan) + '</td>' +
                '<td style="color:var(--accent-green);">' + fmt(r.pendapatan) + '</td>' +
                '<td style="color:' + (ok ? 'var(--accent-green)' : '#f44336') + ';">' + (r.ratioTunggakan * 100).toFixed(4) + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderRatioTable(d, labels) {
        const tbody = document.getElementById('tabelTunggakanRatio')
        if (!tbody) return
        tbody.innerHTML = d.map((r, i) => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return '<tr>' +
                '<td>' + (labels[i] ?? r.bulan) + '</td>' +
                '<td style="color:' + (ok ? '#00ff88' : '#f44336') + ';font-weight:600;">' + (r.ratioTunggakan * 100).toFixed(4) + '%</td>' +
                '<td style="color:#ff9800;">' + (r.ratioTarget * 100).toFixed(4) + '%</td>' +
                '</tr>'
        }).join('')
    }

    _renderDetailTable(source, labels, onChange) {
        const tbody      = document.getElementById('tabelTunggakan')
        if (!tbody) return
        const isReadOnly = this._tahun < TAHUN_INI
        const dis        = isReadOnly ? 'disabled' : ''
        const disStyle   = isReadOnly
            ? 'background:#1a1a1a;color:#444;border-color:#2a2a2a;'
            : 'background:#1a1a1a;color:#ccc;border-color:#333;'
        const inp = (val, field) =>
            '<input type="number" data-field="' + field + '" value="' + (val ?? '') + '" ' + dis + ' ' +
            'style="width:90px;padding:3px 5px;border-radius:4px;border:1px solid;font-size:0.74rem;text-align:right;' + disStyle + '">'

        tbody.innerHTML = source.map((r, i) => {

            return '<tr data-id="' + (r.idRealisasi || '') + '">' +
                '<td style="padding:6px 8px;color:#888;white-space:nowrap;">' + (labels[i] ?? r.bulan) + '</td>' +
                '<td style="padding:3px 5px;">' + inp(r.palBerjalan,       'pal_berjalan')       + '</td>' +
                '<td style="padding:3px 5px;">' + inp(r.palTunggakan,      'pal_tunggakan')      + '</td>' +
                '<td style="padding:3px 5px;">' + inp(r.tsBerjalan,        'ts_berjalan')        + '</td>' +
                '<td style="padding:3px 5px;">' + inp(r.tsTunggakan,       'ts_tunggakan')       + '</td>' +
                '<td style="padding:3px 5px;">' + inp(r.jumlahTunggakan,   'jumlah_tunggakan')   + '</td>' +
                '<td style="padding:3px 5px;">' + inp(r.pendapatan,        'pendapatan')         + '</td>' +
                '<td style="padding:6px 8px;color:#888;">' + (r.ratioTunggakan * 100).toFixed(4) + '% <span style="font-size:0.65rem;color:#555;">(auto)</span></td>' +
                '<td style="padding:6px 8px;color:var(--accent-orange);">' + (r.ratioTarget * 100).toFixed(4) + '%</td>' +
                '<td style="padding:3px 5px;text-align:center;">' +
                    (isReadOnly
                        ? '<span style="font-size:0.7rem;color:#444;">🔒</span>'
                        : '<button data-row="' + i + '" style="padding:3px 10px;border-radius:4px;border:none;background:#ff9800;color:#000;font-size:0.7rem;cursor:pointer;font-weight:600;">Simpan</button>'
                    ) +
                '</td>' +
                '</tr>'
        }).join('')

        if (isReadOnly) return

        // Bind tombol Simpan per baris
        tbody.querySelectorAll('button[data-row]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const row    = btn.closest('tr')
                const id     = row.dataset.id
                const inputs = row.querySelectorAll('input[data-field]')
                const raw    = {}
                inputs.forEach(inp => {
                    const v = inp.value.replace(/[^\d.,-]/g, '').replace(',', '.')
                    raw[inp.dataset.field] = v === '' ? 0 : Number.parseFloat(v)
                })

                // Breakdown dikirim apa adanya (pal_berjalan, pal_tunggakan, ts_berjalan, ts_tunggakan).
                // Total (pal, tagihan_susulan) dan ratio_pct dihitung otomatis di backend
                // (RtFactTunggakanService::hitungTotalPalTs & hitungRatio).
                const values = {
                    pal_berjalan:     raw.pal_berjalan ?? 0,
                    pal_tunggakan:    raw.pal_tunggakan ?? 0,
                    ts_berjalan:      raw.ts_berjalan ?? 0,
                    ts_tunggakan:     raw.ts_tunggakan ?? 0,
                    jumlah_tunggakan: raw.jumlah_tunggakan ?? 0,
                    pendapatan:       raw.pendapatan ?? 0,
                }

                btn.textContent = '⏳'
                btn.disabled    = true
                try {
                    if (id) {
                        // Update existing record
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                        const res  = await fetch(API + '/' + id, {
                            method:  'PUT',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                            body:    JSON.stringify(values),
                        })
                        if (!res.ok) throw new Error('PUT failed')
                    } else {
                        // Record belum ada, pakai upsert
                        const bulan = Number.parseInt(btn.dataset.row) + 1
                        await upsert(API,
                            { tahun: this._tahun, bulan, tipe: 'realisasi' },
                            { tahun: this._tahun, bulan, tipe: 'realisasi', sumber: 'manual', ...values }
                        )
                    }
                    btn.textContent      = '✓ OK'
                    btn.style.background = '#00ff88'
                    onChange()
                } catch {
                    btn.textContent      = '✗ Error'
                    btn.style.background = '#f44336'
                } finally {
                    btn.disabled = false
                    setTimeout(() => {
                        btn.textContent      = 'Simpan'
                        btn.style.background = '#ff9800'
                    }, 2000)
                }
            })
        })
    }
}