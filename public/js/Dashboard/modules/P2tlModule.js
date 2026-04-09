export default class P2tlModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const source = data.data
        const filtered = (state.bulan && state.bulan !== "all")
            ? source.slice(0, parseInt(state.bulan) + 1)
            : source
        const labels = filtered.map(r => r.bulan)

        this.chart.make("chartP2tlTemuan", "bar", labels, [
            { label: "Pemeriksaan", data: filtered.map(r => r.pemeriksaan),     backgroundColor: "#2196f3" },
            { label: "Temuan",      data: filtered.map(r => r.temuanPelanggan), backgroundColor: "#f44336" },
        ])
        this.chart.make("chartP2tlKwh", "bar", labels, [
            { label: "Temuan kWh", data: filtered.map(r => r.temuanKwh), backgroundColor: "#ff9800" }
        ], v => v.toFixed(0) + " kWh")
        this.chart.make("chartP2tlTagsus", "bar", labels, [
            { label: "Tagsus (Rp)", data: filtered.map(r => r.tagsus), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        this._renderSideTable(filtered)
        this._renderTable(filtered)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBulanP2tl")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    _renderSideTable(d) {
        const tbody = document.getElementById("p2tlSideTable")
        if (!tbody) return
        tbody.innerHTML = d.map(r => `<tr>
            <td style="color:#888; font-size:0.75rem;">${r.bulan}</td>
            <td style="text-align:right; color:#2196f3;">${r.pemeriksaan.toLocaleString('id-ID')}</td>
            <td style="text-align:right; color:#f44336;">${r.temuanPelanggan.toLocaleString('id-ID')}</td>
            <td style="text-align:right; color:#ff9800;">${r.temuanKwh.toFixed(0)}</td>
        </tr>`).join("")
    }

    _renderTable(d) {
        const tbody = document.getElementById("tabelP2tl")
        if (!tbody) return
        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.no}</td>
            <td>${r.bulan}</td>
            <td>${r.pemeriksaan.toLocaleString('id-ID')}</td>
            <td>${r.temuanPelanggan.toLocaleString('id-ID')}</td>
            <td>${r.temuanKwh.toFixed(2)}</td>
            <td>Rp ${(r.tagsus / 1_000_000_000).toFixed(2)}M</td>
        </tr>`).join("")
    }
}