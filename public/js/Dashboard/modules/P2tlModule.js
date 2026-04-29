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
            { label: "Temuan",       data: filtered.map(r => r.temuanPelanggan), backgroundColor: "#f44336" },
        ])
        this.chart.make("chartP2tlKwh", "bar", labels, [
            { label: "Temuan kWh", data: filtered.map(r => r.temuanKwh), backgroundColor: "#ff9800" }
        ], v => v.toFixed(0) + " kWh")
        this.chart.make("chartP2tlTagsus", "bar", labels, [
            { label: "Tagsus (Rp)", data: filtered.map(r => r.tagsus), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        // Render all tables
        this.renderTemuanTable(filtered)
        this.renderKwhTable(filtered)
        this.renderTagsusTable(filtered)
        this.renderDetailTable(filtered)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBulanP2tl")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    // Table for Temuan Chart
    renderTemuanTable(d) {
        const tbody = document.getElementById("p2tlSideTable")
        if (!tbody) return
        tbody.innerHTML = d.map(r => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${r.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${r.pemeriksaan.toLocaleString('id-ID')}</td>
            <td style="padding:8px 10px; text-align:center; color:#f44336;">${r.temuanPelanggan.toLocaleString('id-ID')}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${r.temuanKwh.toFixed(0)}</td>
        </tr>`).join("")
    }

    // Table for Temuan kWh Chart (NEW)
    renderKwhTable(d) {
        const tbody = document.getElementById("tabelP2tlKwh")
        if (!tbody) return
        tbody.innerHTML = d.map(r => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${r.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${r.temuanKwh.toFixed(0)} kWh</td>
        </tr>`).join("")
    }

    // Table for Tagsus Chart (NEW)
    renderTagsusTable(d) {
        const tbody = document.getElementById("tabelP2tlTagsus")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
        tbody.innerHTML = d.map(r => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${r.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88; font-weight:600;">${fmt(r.tagsus)}</td>
        </tr>`).join("")
    }

    // Table for Detail P2TL
    renderDetailTable(d) {
        const tbody = document.getElementById("tabelP2tl")
        if (!tbody) return
        tbody.innerHTML = d.map(r => `<tr>
            <td style="padding:8px 10px; text-align:center;">${r.no}</td>
            <td style="padding:8px 10px; text-align:center;">${r.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${r.pemeriksaan.toLocaleString('id-ID')}</td>
            <td style="padding:8px 10px; text-align:center; color:#f44336;">${r.temuanPelanggan.toLocaleString('id-ID')}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${r.temuanKwh.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88; font-weight:600;">Rp ${(r.tagsus / 1_000_000_000).toFixed(2)}M</td>
        </tr>`).join("")
    }
}