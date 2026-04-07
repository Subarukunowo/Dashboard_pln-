export default class KwhModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const akum = state.view === "akumulasi"
        this.chart.renderTargetRealisasi(data, akum)
        this.chart.renderDelta(data, akum)
        this.chart.renderPencapaian(data)
        this.chart.renderPertumbuhan(data)
        this._table(data, akum)
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewKwh")?.addEventListener("change", e => {
            state.view = e.target.value; onChange()
        })
    }

    _table(data, akum) {
        const tbody = document.getElementById("tabelRingkasan")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(2) + "%"
        tbody.innerHTML = data.map(d => {
            const dv = akum ? d.deltaAkumKwh : d.deltaKwh
            const dp = akum ? (d.deltaAkumPersen - 1) : (d.deltaPersen - 1)
            const c = dv >= 0 ? '#00ff88' : '#f44336'
            return `<tr>
                <td>${d.bulan}</td>
                <td>${fmt(akum ? d.targetAkum2024 : d.target2024)}</td>
                <td>${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td>${fmt(akum ? d.targetAkum2025 : d.target2025)}</td>
                <td>${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${c}">${fmt(dv)}</td>
                <td style="color:${c}">${fmtPct(dp)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pertumbuhanReal)}</td>
            </tr>`
        }).join("")
    }
}