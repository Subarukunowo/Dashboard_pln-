export default class P2tlModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const source   = data.data
        const filtered = (state.bulan && state.bulan !== "all")
            ? source.slice(0, parseInt(state.bulan) + 1)
            : source
        const labels = filtered.map(r => r.bulan)

        this.chart.make("chartP2tlTemuan", "bar", labels, [
            { label: "Pemeriksaan", data: filtered.map(r => r.pemeriksaan),      backgroundColor: "#2196f3" },
            { label: "Temuan",      data: filtered.map(r => r.temuanPelanggan),  backgroundColor: "#f44336" },
        ])
        this.chart.make("chartP2tlKwh", "bar", labels, [
            { label: "Temuan kWh", data: filtered.map(r => r.temuanKwh), backgroundColor: "#ff9800" }
        ], v => v.toFixed(0) + " kWh")
        this.chart.make("chartP2tlTagsus", "bar", labels, [
            { label: "Tagsus (Rp)", data: filtered.map(r => r.tagsus), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        this.#renderTemuanTable(filtered)
        this.#renderKwhTable(filtered)
        this.#renderTagsusTable(filtered)
        this.#renderDetailTable(filtered)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBulanP2tl")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
    }

    #renderTemuanTable(d) {
        const tbody = document.getElementById("p2tlSideTable")
        if (!tbody) return

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td style="color:#2196f3;">${r.pemeriksaan.toLocaleString('id-ID')}</td>
            <td style="color:#f44336;">${r.temuanPelanggan.toLocaleString('id-ID')}</td>
            <td style="color:#ff9800;">${r.temuanKwh.toFixed(0)}</td>
        </tr>`).join("")
    }

    #renderKwhTable(d) {
        const tbody = document.getElementById("tabelP2tlKwh")
        if (!tbody) return

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td style="color:#ff9800;">${r.temuanKwh.toFixed(0)} kWh</td>
        </tr>`).join("")
    }

    #renderTagsusTable(d) {
        const tbody = document.getElementById("tabelP2tlTagsus")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.bulan}</td>
            <td style="color:#00ff88; font-weight:600;">${fmt(r.tagsus)}</td>
        </tr>`).join("")
    }

    #renderDetailTable(d) {
        const tbody = document.getElementById("tabelP2tl")
        if (!tbody) return

        tbody.innerHTML = d.map(r => `<tr>
            <td>${r.no}</td>
            <td>${r.bulan}</td>
            <td style="color:#2196f3;">${r.pemeriksaan.toLocaleString('id-ID')}</td>
            <td style="color:#f44336;">${r.temuanPelanggan.toLocaleString('id-ID')}</td>
            <td style="color:#ff9800;">${r.temuanKwh.toFixed(2)}</td>
            <td style="color:#00ff88; font-weight:600;">Rp ${(r.tagsus / 1_000_000_000).toFixed(2)}M</td>
        </tr>`).join("")
    }
}