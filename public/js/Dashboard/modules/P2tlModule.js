export default class P2tlModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const d = data.data
        const labels = d.map(r => r.bulan)

        this.chart.make("chartP2tlTemuan", "bar", labels, [
            { label: "Pemeriksaan", data: d.map(r => r.pemeriksaan),     backgroundColor: "#2196f3" },
            { label: "Temuan",      data: d.map(r => r.temuanPelanggan), backgroundColor: "#f44336" },
        ])

        this.chart.make("chartP2tlKwh", "bar", labels, [
            { label: "Temuan kWh", data: d.map(r => r.temuanKwh), backgroundColor: "#ff9800" }
        ], v => v.toFixed(0) + " kWh")

        this.chart.make("chartP2tlTagsus", "bar", labels, [
            { label: "Tagsus (Rp)", data: d.map(r => r.tagsus), backgroundColor: "#00ff88" }
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        const tbody = document.getElementById("tabelP2tl")
        if (tbody) {
            tbody.innerHTML = d.map(r => `<tr>
                <td>${r.no}</td>
                <td>${r.bulan}</td>
                <td>${r.pemeriksaan.toLocaleString()}</td>
                <td>${r.temuanPelanggan.toLocaleString()}</td>
                <td>${r.temuanKwh.toFixed(2)}</td>
                <td>Rp ${(r.tagsus / 1_000_000_000).toFixed(2)}M</td>
            </tr>`).join("")
        }
    }

    initFilters(state, onChange) {
        // tidak ada filter tambahan
    }
}