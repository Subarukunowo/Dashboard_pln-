export default class TunggakanModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const d = data.data
        const labels = d.map(r => r.bulan)

        this.chart.make("chartTunggakanPendapatan", "bar", labels, [
            { label: "Pendapatan", data: d.map(r => r.pendapatan), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        this.chart.make("chartTunggakanRatio", "line", labels, [
            { label: "Ratio Tunggakan", data: d.map(r => r.ratioTunggakan * 100), borderColor: "#f44336", tension: 0.4, fill: false, pointRadius: 4 },
            { label: "Target Ratio",    data: d.map(r => r.ratioTarget * 100),    borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 4, borderDash: [5,5] },
        ], v => v.toFixed(4) + "%")

        const tbody = document.getElementById("tabelTunggakan")
        if (tbody) {
            const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
            tbody.innerHTML = d.map(r => `<tr>
                <td>${r.bulan}</td>
                <td>${r.palBerjalan || "-"}</td>
                <td>${r.palTunggakan || "-"}</td>
                <td>${r.tsBerjalan || "-"}</td>
                <td>${r.tsTunggakan || "-"}</td>
                <td>${r.jumlahTunggakan || "-"}</td>
                <td>${fmt(r.pendapatan)}</td>
                <td style="color:${r.ratioTunggakan <= r.ratioTarget ? '#00ff88' : '#f44336'}">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
                <td>${(r.ratioTarget * 100).toFixed(4)}%</td>
            </tr>`).join("")
        }
    }

    initFilters(state, onChange) {}
}