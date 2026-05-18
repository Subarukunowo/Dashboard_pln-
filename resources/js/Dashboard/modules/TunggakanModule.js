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

        // Render all tables
        this.renderPendapatanTable(filtered)
        this.renderRatioTable(filtered)
        this.renderDetailTable(filtered)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBulanTunggakan")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    // Table for Pendapatan Chart
    renderPendapatanTable(d) {
        const tbody = document.getElementById("tunggakanSideTable")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
        tbody.innerHTML = d.map(r => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${r.bulan}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(r.pendapatan)}</td>
                <td style="padding:8px 10px; text-align:center; color:${ok ? 'var(--accent-green)' : '#f44336'};">${(r.ratioTunggakan * 100).toFixed(4)}%</td>
            </tr>`
        }).join("")
    }

    // Table for Ratio Chart (NEW)
    renderRatioTable(d) {
        const tbody = document.getElementById("tabelTunggakanRatio")
        if (!tbody) return
        tbody.innerHTML = d.map(r => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${r.bulan}</td>
                <td style="padding:8px 10px; text-align:center; color:${ok ? '#00ff88' : '#f44336'}; font-weight:600;">
                    ${(r.ratioTunggakan * 100).toFixed(4)}%
                </td>
                <td style="padding:8px 10px; text-align:center; color:#ff9800;">
                    ${(r.ratioTarget * 100).toFixed(4)}%
                </td>
            </tr>`
        }).join("")
    }

    // Table for Detail Kinerja
    renderDetailTable(d) {
        const tbody = document.getElementById("tabelTunggakan")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
        tbody.innerHTML = d.map(r => {
            const ok = r.ratioTunggakan <= r.ratioTarget
            return `<tr>
                <td style="padding:8px 10px; text-align:center;">${r.bulan}</td>
                <td style="padding:8px 10px; text-align:center;">${r.palBerjalan  || "-"}</td>
                <td style="padding:8px 10px; text-align:center;">${r.palTunggakan || "-"}</td>
                <td style="padding:8px 10px; text-align:center;">${r.tsBerjalan   || "-"}</td>
                <td style="padding:8px 10px; text-align:center;">${r.tsTunggakan  || "-"}</td>
                <td style="padding:8px 10px; text-align:center;">${r.jumlahTunggakan || "-"}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(r.pendapatan)}</td>
                <td style="padding:8px 10px; text-align:center; color:${ok ? '#00ff88' : '#f44336'}; font-weight:600;">
                    ${(r.ratioTunggakan * 100).toFixed(4)}%
                </td>
                <td style="padding:8px 10px; text-align:center; color:#ff9800;">
                    ${(r.ratioTarget * 100).toFixed(4)}%
                </td>
            </tr>`
        }).join("")
    }
}