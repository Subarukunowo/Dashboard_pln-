export default class LossesModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const source = (state.emin === "without" && data.withoutEmin)
            ? data.withoutEmin
            : data.withEmin

        if (!source?.length) return

        const d = (state.bulan && state.bulan !== "all")
            ? source.slice(0, parseInt(state.bulan) + 1)
            : source

        const labels = d.map(r => r.bulan)

        this.chart.make("chartLossesProduksi", "bar", labels, [
            { label: "Prod Bruto", data: d.map(r => r.prodBruto), backgroundColor: "#2196f3" },
            { label: "Prod Netto", data: d.map(r => r.prodNetto), backgroundColor: "#00ff88" },
            { label: "Siap Salur", data: d.map(r => r.siapSalur), backgroundColor: "#ff9800" },
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        this.chart.make("chartLossesPct", "line", labels, [
            { label: "Losses Transmisi Bln (%)",  data: d.map(r => r.lossesBulanan * 100),         borderColor: "#f44336", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "Losses Transmisi Akum (%)", data: d.map(r => r.lossesAkum * 100),            borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "Losses Jaringan Bln (%)",   data: d.map(r => r.lossesJaringanBulanan * 100), borderColor: "#00ff88", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "Losses Jaringan Akum (%)",  data: d.map(r => r.lossesJaringanAkum * 100),    borderColor: "#9c27b0", tension: 0.4, fill: false, pointRadius: 3 },
        ], v => v.toFixed(3) + "%")

        this.chart.make("chartLossesKwh", "bar", labels, [
            { label: "Losses Transmisi",  data: d.map(r => r.lossesKwh),           backgroundColor: "#e91e63" },
            { label: "Losses Distribusi", data: d.map(r => r.lossesDistribusiKwh), backgroundColor: "#9c27b0" },
            { label: "Losses Jaringan",   data: d.map(r => r.lossesJaringanKwh),   backgroundColor: "#f44336" },
        ], v => (v / 1_000_000).toFixed(2) + " jt kWh")

        this.#renderProduksiTable(d)
        this.#renderPctTable(d)
        this.#renderKwhTable(d)
        this.#renderDetailTable(d)
    }

    initFilters(state, onChange) {
        document.getElementById("filterEminLosses")?.addEventListener("change",  e => { state.emin  = e.target.value; onChange() })
        document.getElementById("filterBulanLosses")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
    }

    #renderProduksiTable(d) {
        const tbody = document.getElementById("lossesSideTable")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td style="color:#2196f3;">${fmt(r.prodBruto)}</td>
            <td style="color:var(--accent-green);">${fmt(r.prodNetto)}</td>
            <td style="color:#ff9800;">${fmt(r.siapSalur)}</td>
        </tr>`).join("")
    }

    #renderPctTable(d) {
        const tbody = document.getElementById("tabelLossesPct")
        if (!tbody) return
        const fmtPct = v => (v * 100).toFixed(4) + "%"

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td style="color:#f44336;">${fmtPct(r.lossesBulanan)}</td>
            <td style="color:#ff9800;">${fmtPct(r.lossesAkum)}</td>
            <td style="color:var(--accent-green);">${fmtPct(r.lossesJaringanBulanan)}</td>
            <td style="color:#9c27b0;">${fmtPct(r.lossesJaringanAkum)}</td>
        </tr>`).join("")
    }

    #renderKwhTable(d) {
        const tbody = document.getElementById("tabelLossesKwh")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td style="color:#e91e63;">${fmt(r.lossesKwh)}</td>
            <td style="color:#9c27b0;">${fmt(r.lossesDistribusiKwh)}</td>
            <td style="color:#f44336;">${fmt(r.lossesJaringanKwh)}</td>
        </tr>`).join("")
    }

    #renderDetailTable(d) {
        const tbody = document.getElementById("tabelLosses")
        if (!tbody) return
        const fmt    = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(4) + "%"

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td>${fmt(r.prodBruto)}</td>
            <td>${fmt(r.prodNetto)}</td>
            <td>${fmt(r.siapSalur)}</td>
            <td style="color:#e91e63;">${fmt(r.lossesKwh)}</td>
            <td style="color:#f44336;">${fmtPct(r.lossesBulanan)}</td>
            <td style="color:#ff9800;">${fmtPct(r.lossesAkum)}</td>
            <td>${fmt(r.interkoneksi)}</td>
            <td style="color:#9c27b0;">${fmt(r.lossesDistribusiKwh)}</td>
            <td style="color:#9c27b0;">${fmtPct(r.lossesDistribusiBulanan)}</td>
            <td style="color:#9c27b0;">${fmtPct(r.lossesDistribusiAkum)}</td>
            <td style="color:#f44336;">${fmt(r.lossesJaringanKwh)}</td>
            <td style="color:#00ff88;">${fmtPct(r.lossesJaringanBulanan)}</td>
            <td style="color:#00ff88;">${fmtPct(r.lossesJaringanAkum)}</td>
            <td style="color:var(--accent-orange); font-weight:600;">${fmtPct(r.lossesTarget)}</td>
        </tr>`).join("")
    }
}