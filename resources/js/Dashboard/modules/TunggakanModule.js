export default class TunggakanModule {
    constructor(chart) { this.chart = chart }

    #getLabels() {
        return [...document.querySelectorAll("#filterBulanTunggakan option")]
            .filter(o => o.value !== "all")
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return

        const source   = data.data
        const labels   = this.#getLabels()
        const filtered = (state.bulan && state.bulan !== "all")
            ? source.slice(0, parseInt(state.bulan) + 1)
            : source
        const slicedLabels = labels.slice(0, filtered.length)

        this.chart.make("chartTunggakanPendapatan", "bar", slicedLabels, [
            { label: "Pendapatan", data: filtered.map(r => r.pendapatan), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        this.chart.make("chartTunggakanRatio", "line", slicedLabels, [
            { label: "Ratio Tunggakan", data: filtered.map(r => r.ratioTunggakan * 100), borderColor: "#f44336", tension: 0.4, fill: false, pointRadius: 4 },
            { label: "Target Ratio",    data: filtered.map(r => r.ratioTarget * 100),    borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 4, borderDash: [5, 5] },
        ], v => v.toFixed(4) + "%")

        this.#renderPendapatanTable(filtered, slicedLabels)
        this.#renderRatioTable(filtered, slicedLabels)
        this.#renderDetailTable(filtered, slicedLabels)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBulanTunggakan")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
    }

    #renderPendapatanTable(d, labels) {
        const tbody = document.getElementById("tunggakanSideTable")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"

        tbody.innerHTML = d.map((r, i) => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td>${labels[i] ?? r.bulan}</td>
                <td style="color:var(--accent-green);">${fmt(r.pendapatan)}</td>
                <td style="color:${ok ? "var(--accent-green)" : "#f44336"};">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
            </tr>`
        }).join("")
    }

    #renderRatioTable(d, labels) {
        const tbody = document.getElementById("tabelTunggakanRatio")
        if (!tbody) return

        tbody.innerHTML = d.map((r, i) => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td>${labels[i] ?? r.bulan}</td>
                <td style="color:${ok ? "#00ff88" : "#f44336"}; font-weight:600;">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
                <td style="color:#ff9800;">${(r.ratioTarget * 100).toFixed(4)}%</td>
            </tr>`
        }).join("")
    }

    #renderDetailTable(d, labels) {
        const tbody = document.getElementById("tabelTunggakan")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"

        tbody.innerHTML = d.map((r, i) => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td>${labels[i] ?? r.bulan}</td>
                <td>${r.palBerjalan      || "—"}</td>
                <td>${r.palTunggakan     || "—"}</td>
                <td>${r.tsBerjalan       || "—"}</td>
                <td>${r.tsTunggakan      || "—"}</td>
                <td>${r.jumlahTunggakan  || "—"}</td>
                <td style="color:var(--accent-green);">${fmt(r.pendapatan)}</td>
                <td style="color:${ok ? "#00ff88" : "#f44336"}; font-weight:600;">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
                <td style="color:#ff9800;">${(r.ratioTarget * 100).toFixed(4)}%</td>
            </tr>`
        }).join("")
    }
}