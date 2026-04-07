export default class LossesModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const d = state.emin === "with" ? data.withEmin : data.withoutEmin
        const labels = d.map(r => r.bulan)

        this.chart.make("chartLossesProduksi", "bar", labels, [
            { label: "Prod Bruto",  data: d.map(r => r.prodBruto),  backgroundColor: "#2196f3" },
            { label: "Prod Netto",  data: d.map(r => r.prodNetto),  backgroundColor: "#00ff88" },
            { label: "Siap Salur",  data: d.map(r => r.siapSalur),  backgroundColor: "#ff9800" },
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        this.chart.make("chartLossesPct", "line", labels, [
            { label: "Losses Bulanan (%)", data: d.map(r => r.lossesBulanan * 100), borderColor: "#f44336", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "Losses Akum (%)",    data: d.map(r => r.lossesAkum * 100),   borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 3 },
        ], v => v.toFixed(3) + "%")

        this.chart.make("chartLossesKwh", "bar", labels, [
            { label: "Losses Transmisi (kWh)", data: d.map(r => r.lossesKwh), backgroundColor: "#e91e63" },
        ], v => (v / 1_000_000).toFixed(2) + " jt kWh")

        // Tabel
        const tbody = document.getElementById("tabelLosses")
        if (tbody) {
            const fmt = v => (v / 1_000_000).toFixed(2)
            const fmtPct = v => (v * 100).toFixed(4) + "%"
            tbody.innerHTML = d.map(r => `<tr>
                <td>${r.bulan}</td>
                <td>${fmt(r.prodBruto)}</td>
                <td>${fmt(r.prodNetto)}</td>
                <td>${fmt(r.siapSalur)}</td>
                <td>${fmt(r.lossesKwh)}</td>
                <td>${fmtPct(r.lossesBulanan)}</td>
                <td>${fmtPct(r.lossesAkum)}</td>
                <td>${fmt(r.interkoneksi)}</td>
            </tr>`).join("")
        }
    }

    initFilters(state, onChange) {
        document.getElementById("filterEminLosses")?.addEventListener("change", e => { state.emin = e.target.value; onChange() })
    }
}