const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class BabinModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const akum    = state.view === "akumulasi"
        const is2024  = state.tahun === "2024"
        const sliceIdx = (state.bulan && state.bulan !== "all")
            ? parseInt(state.bulan) + 1 : 12
        const labels  = LABELS.slice(0, sliceIdx)

        const src = state.section === "babin" ? {
            real2024:    data.babinReal2024,
            target2025:  data.babinTarget2025,
            akum2025:    data.babinAkum2025,
        } : {
            real2024:    data.semReal2024,
            target2025:  data.semTarget2025,
            akum2025:    data.semAkum2025,
        }

        const rows = (akum ? src.akum2025 : src.target2025).slice(0, sliceIdx)
        const real2024 = src.real2024.slice(0, sliceIdx)

        this.chart.make("chartBabinBar", "bar", labels, [
            { label: "Realisasi 2024", data: real2024.map(d => d.realisasi), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: rows.map(d => d.target),        backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: rows.map(d => d.realisasi),     backgroundColor: "#00ff88" },
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        const delta = rows.map((d, i) => d.realisasi - d.target)
        this.chart.make("chartBabinDelta", "bar", labels, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        // pie — pakai tahun yang dipilih
        const allJualSliced = data.allJualData.slice(0, sliceIdx)
        const latest = allJualSliced.filter(d =>
            is2024 ? d.kwhBatam2024 > 0 : d.kwhBatam2025 > 0
        ).slice(-1)[0]

        if (latest) {
            this.chart.pie("chartBabinPie",
                ["Blk Pdg", "Sembulang", "Buluh", "Nguan", "Batam-Bintan", "KWH Batam"],
                is2024
                    ? [latest.blkPdg2024, latest.sembulang2024, latest.buluh2024, latest.nguan2024, latest.batamBintan2024, latest.kwhBatam2024]
                    : [latest.blkPdg2025, latest.sembulang2025, latest.buluh2025, latest.nguan2025, latest.batamBintan2025, latest.kwhBatam2025]
            )
        }

        this.chart.make("chartAllJualBar", "bar", allJualSliced.map(d => d.bulan), [
            { label: "KWH Batam 2024",    data: allJualSliced.map(d => d.kwhBatam2024),      backgroundColor: "#2196f3" },
            { label: "KWH Batam 2025",    data: allJualSliced.map(d => d.kwhBatam2025),      backgroundColor: "#00ff88" },
            { label: "Batam-Bintan 2024", data: allJualSliced.map(d => d.batamBintan2024),   backgroundColor: "#555" },
            { label: "Batam-Bintan 2025", data: allJualSliced.map(d => d.batamBintan2025),   backgroundColor: "#ff9800" },
        ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

        this._renderSideTable(labels, rows, real2024)
        this._renderAllJualSideTable(allJualSliced, is2024)
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewBabin")?.addEventListener("change",    e => { state.view    = e.target.value; onChange() })
        document.getElementById("filterSectionBabin")?.addEventListener("change", e => { state.section = e.target.value; onChange() })
        document.getElementById("filterTahunBabin")?.addEventListener("change",   e => { state.tahun   = e.target.value; onChange() })
        document.getElementById("filterBulanBabin")?.addEventListener("change",   e => { state.bulan   = e.target.value; onChange() })
    }

    _renderSideTable(labels, rows, real2024) {
        const tbody = document.getElementById("babinSideTable")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        tbody.innerHTML = labels.map((bln, i) => {
            const delta = (rows[i]?.realisasi || 0) - (rows[i]?.target || 0)
            const color = delta >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${bln}</td>
                <td style="text-align:right; color:#2196f3;">${fmt(real2024[i]?.realisasi || 0)}</td>
                <td style="text-align:right; color:#ff9800;">${fmt(rows[i]?.target || 0)}</td>
                <td style="text-align:right; color:var(--accent-green);">${fmt(rows[i]?.realisasi || 0)}</td>
                <td style="text-align:right; color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${fmt(delta)}</td>
            </tr>`
        }).join("")
    }

    _renderAllJualSideTable(d, is2024) {
        const tbody = document.getElementById("allJualSideTable")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        tbody.innerHTML = d.map(r => {
            const kwh  = is2024 ? r.kwhBatam2024      : r.kwhBatam2025
            const bb   = is2024 ? r.batamBintan2024    : r.batamBintan2025
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${r.bulan}</td>
                <td style="text-align:right; color:#2196f3;">${fmt(is2024 ? r.kwhBatam2024 : r.kwhBatam2025)}</td>
                <td style="text-align:right; color:#ff9800;">${fmt(is2024 ? r.batamBintan2024 : r.batamBintan2025)}</td>
            </tr>`
        }).join("")
    }
}