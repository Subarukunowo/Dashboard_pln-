export default class PendapatanBatamModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        this.chart.render(data, state.view, state.tarif)

        const tbody = document.getElementById("tabelPendapatan")
        if (tbody && data.realisasiBulanan) {
            const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + " M"
            tbody.innerHTML = data.realisasiBulanan.map(row => `
                <tr>
                    <td>${row.bulan}</td>
                    <td>${fmt(row.target * 1_000_000)}</td>
                    <td>${fmt(row.realisasi * 1_000_000)}</td>
                    <td style="color:${row.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(row.delta * 1_000_000)}</td>
                    <td style="color:${row.delta >= 0 ? '#00ff88' : '#f44336'}">${(row.pencapaian * 100).toFixed(2)}%</td>
                </tr>
            `).join("")
        }
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewPendapatan")?.addEventListener("change", e => {
            state.view = e.target.value; onChange()
        })
        document.getElementById("filterTarifPendapatan")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
    }
}