    export default class BabinModule {
        constructor(chart) { this.chart = chart }

        #getLabels() {
            return [...document.querySelectorAll("#filterBulanBabin option")]
                .filter(o => o.value !== "all")
                .map(o => o.textContent.trim())
        }

        render(data, state) {
            if (!data) return

            const akum     = state.view === "akumulasi"
            const is2024   = state.tahun === "2024"
            const sliceIdx = (state.bulan && state.bulan !== "all")
                ? parseInt(state.bulan) + 1 : 12
            const labels   = this.#getLabels().slice(0, sliceIdx)

            const src = state.section === "babin" ? {
                real2024:   data.babinReal2024,
                target2025: data.babinTarget2025,
                akum2025:   data.babinAkum2025,
            } : {
                real2024:   data.semReal2024,
                target2025: data.semTarget2025,
                akum2025:   data.semAkum2025,
            }

            const rows          = (akum ? src.akum2025 : src.target2025).slice(0, sliceIdx)
            const real2024      = src.real2024.slice(0, sliceIdx)
            const allJualSliced = data.allJualData.slice(0, sliceIdx)

            this.chart.make("chartBabinBar", "bar", labels, [
                { label: "Realisasi Thn Lalu", data: real2024.map(d => d.realisasi), backgroundColor: "#2196f3" },
                { label: "Target Thn Ini",     data: rows.map(d => d.target),        backgroundColor: "#ff9800" },
                { label: "Realisasi Thn Ini",  data: rows.map(d => d.realisasi),     backgroundColor: "#00ff88" },
            ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

            const delta = rows.map(d => d.realisasi - d.target)
            this.chart.make("chartBabinDelta", "bar", labels, [
                { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
            ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

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

            this.chart.make("chartAllJualBar", "bar", labels, [
                { label: "KWH Batam Thn Lalu",    data: allJualSliced.map(d => d.kwhBatam2024),    backgroundColor: "#2196f3" },
                { label: "KWH Batam Thn Ini",     data: allJualSliced.map(d => d.kwhBatam2025),    backgroundColor: "#00ff88" },
                { label: "Batam-Bintan Thn Lalu", data: allJualSliced.map(d => d.batamBintan2024), backgroundColor: "#555"    },
                { label: "Batam-Bintan Thn Ini",  data: allJualSliced.map(d => d.batamBintan2025), backgroundColor: "#ff9800" },
            ], v => (v / 1_000_000).toFixed(1) + " jt kWh")

            this.#renderTable(labels, rows, real2024)
            this.#renderAllJualTable(labels, allJualSliced, is2024)
        }

        initFilters(state, onChange) {
            document.getElementById("filterSectionBabin")?.addEventListener("change", e => { state.section = e.target.value; onChange() })
            document.getElementById("filterTahunBabin")?.addEventListener("change",   e => { state.tahun   = e.target.value; onChange() })
            document.getElementById("filterViewBabin")?.addEventListener("change",    e => { state.view    = e.target.value; onChange() })
            document.getElementById("filterBulanBabin")?.addEventListener("change",   e => { state.bulan   = e.target.value; onChange() })
        }

        #renderTable(labels, rows, real2024) {
            const tbody = document.getElementById("babinSideTable")
            if (!tbody) return
            const fmt = v => (v / 1_000_000).toFixed(2)

            tbody.innerHTML = labels.map((bln, i) => {
                const delta = (rows[i]?.realisasi || 0) - (rows[i]?.target || 0)
                const color = delta >= 0 ? "var(--accent-green)" : "#f44336"
                return `<tr>
                    <td>${bln}</td>
                    <td>${fmt(real2024[i]?.realisasi || 0)}</td>
                    <td>${fmt(rows[i]?.target        || 0)}</td>
                    <td>${fmt(rows[i]?.realisasi     || 0)}</td>
                    <td style="color:${color};">${delta >= 0 ? "+" : ""}${fmt(delta)}</td>
                </tr>`
            }).join("")
        }

        #renderAllJualTable(labels, d, is2024) {
            const tbody = document.getElementById("allJualSideTable")
            if (!tbody) return
            const fmt = v => (v / 1_000_000).toFixed(2)

            tbody.innerHTML = d.map((r, i) => `<tr>
                <td>${labels[i] ?? r.bulan}</td>
                <td>${fmt(is2024 ? r.kwhBatam2024   : r.kwhBatam2025)}</td>
                <td>${fmt(is2024 ? r.batamBintan2024 : r.batamBintan2025)}</td>
            </tr>`).join("")
        }
    }