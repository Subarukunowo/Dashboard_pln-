export default class TunggakanModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const source = data.data

        const filtered = (state.bulan && state.bulan !== "all")
            ? source.slice(0, parseInt(state.bulan) + 1)
            : source

        const labels = filtered.map(r => r.bulan)

        this.chart.make("chartTunggakanPendapatan", "bar", labels, [
            { label: "Pendapatan", data: filtered.map(r => r.pendapatan), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        this.chart.make("chartTunggakanRatio", "line", labels, [
            { label: "Ratio Tunggakan", data: filtered.map(r => r.ratioTunggakan * 100), borderColor: "#f44336", tension: 0.4, fill: false, pointRadius: 4 },
            { label: "Target Ratio",    data: filtered.map(r => r.ratioTarget * 100),    borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 4, borderDash: [5,5] },
        ], v => v.toFixed(4) + "%")

        this._renderSideTable(filtered)
        this._renderTable(filtered)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBulanTunggakan")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    _renderSideTable(d) {
        const tbody = document.getElementById("tunggakanSideTable")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
        tbody.innerHTML = d.map(r => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${r.bulan}</td>
                <td style="text-align:right; color:var(--accent-green);">${fmt(r.pendapatan)}</td>
                <td style="text-align:right; color:${ok ? 'var(--accent-green)' : '#f44336'};">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
            </tr>`
        }).join("")
    }

    _renderTable(d) {
        const tbody = document.getElementById("tabelTunggakan")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td>${r.palBerjalan  || "-"}</td>
            <td>${r.palTunggakan || "-"}</td>
            <td>${r.tsBerjalan   || "-"}</td>
            <td>${r.tsTunggakan  || "-"}</td>
            <td>${r.jumlahTunggakan || "-"}</td>
            <td>${fmt(r.pendapatan)}</td>
            <td style="color:${r.ratioTunggakan <= r.ratioTarget ? '#00ff88' : '#f44336'}">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
            <td>${(r.ratioTarget * 100).toFixed(4)}%</td>
        </tr>`).join("")
    }
}