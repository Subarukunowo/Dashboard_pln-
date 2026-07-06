const TAHUN_INI  = new Date().getFullYear()
const TAHUN_LALU = TAHUN_INI - 1

export default class KwhModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const akum     = state.view === 'akumulasi'
        const filtered = state.bulan === 'all'
            ? data.slice(0, Number.parseInt(state.bulan) + 1)
            : data

        this.chart.renderTargetRealisasi(filtered, akum)
        this.chart.renderDelta(filtered, akum)
        this.chart.renderPencapaian(filtered)
        this.chart.renderPertumbuhan(filtered)

        this._renderTable(filtered, akum)
        this._renderDeltaTable(filtered, akum)
        this._renderPencapaianTable(filtered)
        this._renderPertumbuhanTable(filtered)
        this._renderRingkasanTable(filtered, akum)
    }

    initFilters(state, onChange) {
        document.getElementById('filterViewKwh')?.addEventListener('change',  e => { state.view  = e.target.value; onChange() })
        document.getElementById('filterBulanKwh')?.addEventListener('change', e => { state.bulan = e.target.value; onChange() })
    }

    _renderTable(data, akum) {
        const tbody = document.getElementById('kwhTableBody')
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)

        tbody.innerHTML = data.map(d => {
            const delta = akum ? d.deltaAkumKwh : d.deltaKwh
            const color = delta >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td>${d.bulan}</td>
                <td style="color:#555;">${fmt(akum ? d.targetAkum2024    : d.target2024)}</td>
                <td style="color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td style="color:#ff9800;">${fmt(akum ? d.targetAkum2025    : d.target2025)}</td>
                <td style="color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${fmt(delta)}</td>
            </tr>`
        }).join('')
    }

    _renderDeltaTable(data, akum) {
        const tbody = document.getElementById('tabelKwhDelta')
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)

        tbody.innerHTML = data.map(d => {
            const delta = akum ? d.deltaAkumKwh : d.deltaKwh
            const color = delta >= 0 ? '#00ff88' : '#f44336'
            return `<tr>
                <td>${d.bulan}</td>
                <td style="color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${fmt(delta)}</td>
            </tr>`
        }).join('')
    }

    _renderPencapaianTable(data) {
        const tbody = document.getElementById('tabelKwhPencapaian')
        if (!tbody) return
        const fmtPct = v => (v * 100).toFixed(2) + '%'

        tbody.innerHTML = data.map(d => `<tr>
            <td>${d.bulan}</td>
            <td style="color:var(--accent-green);">${fmtPct(d.pencapaianTarget)}</td>
        </tr>`).join('')
    }

    _renderPertumbuhanTable(data) {
        const tbody = document.getElementById('tabelKwhPertumbuhan')
        if (!tbody) return
        const fmtPct = v => (v * 100).toFixed(2) + '%'

        tbody.innerHTML = data.map(d => `<tr>
            <td>${d.bulan}</td>
            <td style="color:#00ff88;">${fmtPct(d.pertumbuhanReal)}</td>
        </tr>`).join('')
    }

    _renderRingkasanTable(data, akum) {
        const tbody = document.getElementById('tabelRingkasan')
        if (!tbody) return
        const fmt    = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(2) + '%'

        tbody.innerHTML = data.map(d => {
            const deltaValue  = akum ? d.deltaAkumKwh        : d.deltaKwh
            const deltaPersen = akum ? d.deltaAkumPersen - 1 : d.deltaPersen - 1
            const color = deltaValue >= 0 ? '#00ff88' : '#f44336'
            return `<tr>
                <td>${d.bulan}</td>
                <td>${fmt(akum ? d.targetAkum2024    : d.target2024)}</td>
                <td>${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td>${fmt(akum ? d.targetAkum2025    : d.target2025)}</td>
                <td>${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${color};">${fmt(deltaValue)}</td>
                <td style="color:${color};">${fmtPct(deltaPersen)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pertumbuhanReal)}</td>
            </tr>`
        }).join('')
    }
}