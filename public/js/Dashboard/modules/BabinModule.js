const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class BabinModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const akum = state.view === "akumulasi"
        const src = state.section === "babin" ? {
            real2024: data.babinReal2024,
            target2025: data.babinTarget2025,
            akum2025: data.babinAkum2025,
        } : {
            real2024: data.semReal2024,
            target2025: data.semTarget2025,
            akum2025: data.semAkum2025,
        }

        const rows = akum ? src.akum2025 : src.target2025

        this.chart.make("chartBabinBar", "bar", LABELS, [
            { label: "Realisasi 2024", data: src.real2024.map(d => d.realisasi),  backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: rows.map(d => d.target),             backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: rows.map(d => d.realisasi),          backgroundColor: "#00ff88" },
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        const delta = rows.map((d, i) => d.realisasi - d.target)
        this.chart.make("chartBabinDelta", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        // All Jual KWH — pie distribusi
        const latest = data.allJualData.filter(d => d.kwhBatam2025 > 0).slice(-1)[0]
        if (latest) {
            this.chart.pie("chartBabinPie",
                ["Blk Pdg", "Sembulang", "Buluh", "Nguan", "Batam-Bintan", "KWH Batam"],
                [latest.blkPdg2025, latest.sembulang2025, latest.buluh2025, latest.nguan2025, latest.batamBintan2025, latest.kwhBatam2025]
            )
        }

        this.chart.make("chartAllJualBar", "bar", data.allJualData.map(d => d.bulan), [
            { label: "KWH Batam 2024",      data: data.allJualData.map(d => d.kwhBatam2024),      backgroundColor: "#2196f3" },
            { label: "KWH Batam 2025",      data: data.allJualData.map(d => d.kwhBatam2025),      backgroundColor: "#00ff88" },
            { label: "Batam-Bintan 2024",   data: data.allJualData.map(d => d.batamBintan2024),   backgroundColor: "#555" },
            { label: "Batam-Bintan 2025",   data: data.allJualData.map(d => d.batamBintan2025),   backgroundColor: "#ff9800" },
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewBabin")?.addEventListener("change", e => { state.view = e.target.value; onChange() })
        document.getElementById("filterSectionBabin")?.addEventListener("change", e => { state.section = e.target.value; onChange() })
    }
}