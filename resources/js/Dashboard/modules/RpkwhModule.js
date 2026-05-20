const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS  = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class RpkwhModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        this.#renderRencanaRealisasi(data, state)
        this.#renderGolongan(data, state)
        this.#renderGrowth(data, state)
        this.#renderBppHjr(data, state)
        this.#renderAnalisa(data, state)
        this.#renderPersen(data)
        this.#populateTarif(data, state.tarif)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifRpkwh")?.addEventListener("change", e => { state.tarif = e.target.value; onChange() })
        document.getElementById("filterViewRpkwh")?.addEventListener("change",  e => { state.view  = e.target.value; onChange() })
        document.getElementById("filterBulanRpkwh")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
        document.getElementById("filterTahunRpkwh")?.addEventListener("change", e => { state.tahun = e.target.value; onChange() })
    }

    #renderRencanaRealisasi(data, state) {
        const akum = state.view === "akumulasi"
        const r    = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl   = data.realisasiData[state.tarif] || new Array(12).fill(0)

        const toAkum    = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
        const displayR  = akum ? toAkum(r)  : r
        const displayRl = akum ? toAkum(rl) : rl

        this.chart.make("chartRencanaRealisasi", "bar", LABELS, [
            { label: "Rencana",   data: displayR,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: displayRl, backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const delta = displayRl.map((v, i) => v - displayR[i])
        this.chart.make("chartDeltaRpkwh", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ], v => v.toFixed(0) + " Rp/kWh")

        this.#renderRencanaTable(displayR, displayRl)
        this.#renderDeltaRencanaTable(LABELS, delta)
    }

    #renderGolongan(data, state) {
        const akum   = state.view === "akumulasi"
        const idx    = parseInt(state.bulan)
        const is2024 = state.tahun === "2024"

        const nilaiGol = data.golongan.map(g =>
            is2024
                ? (akum ? data.realisasiGolAkum[g][idx] : data.realisasiGolBul[g][idx])
                : (akum ? data.targetGolAkum[g][idx]    : data.targetGolBul[g][idx])
        )

        const judulEl = document.getElementById("judulPieRpkwh")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " RPKWH " + state.tahun +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]

        this.chart.pie("chartPieRpkwh",
            data.golongan.map(g => g.replace(/ \(.\) ?/, '')),
            nilaiGol
        )
        this.#renderPieTable(data.golongan.map(g => g.replace(/ \(.\) ?/, '')), nilaiGol)
    }

    #renderGrowth(data, state) {
        const akum     = state.view === "akumulasi"
        const datasets = data.golongan.map((g, i) => ({
            label: g.replace(/ \(.\) ?/, ''),
            data: akum ? data.growthAkum[g] : data.growthBul[g],
            borderColor: COLORS[i], tension: 0.4, fill: false, pointRadius: 3
        }))

        this.chart.make("chartGrowthRpkwh", "line", LABELS, datasets, v => (v * 100).toFixed(1) + "%")
        this.#renderGrowthTable(data.golongan, akum ? data.growthAkum : data.growthBul)
    }

    #renderBppHjr(data, state) {
        const bppData  = data.bppData || {}
        const hjrData  = data.hjrData || {}
        const listTarif = Object.keys(hjrData)

        if (!state.selectedTarif && listTarif.length > 0) {
            state.selectedTarif = listTarif[0]
            const filter = document.getElementById("filterTarifRpkwh")
            if (filter && filter.options.length === 0) {
                filter.innerHTML = listTarif.map(t => `<option value="${t}">${t}</option>`).join("")
            }
        }

        const selectedTarif = state.selectedTarif
        if (selectedTarif && this.chart.bppVsHjr) {
            const hjrSeries = hjrData[selectedTarif] || new Array(12).fill(0)
            const bppSeries = bppData[selectedTarif] || new Array(12).fill(0)
            this.chart.bppVsHjr("chartBppHjr", bppSeries, hjrSeries, LABELS)
        }

        this.#renderBppHjrTable(hjrData[selectedTarif] || [], bppData[selectedTarif] || [])
    }

    #renderAnalisa(data, state) {
        const akum = state.view === "akumulasi"
        const a    = data.analisaData

        this.chart.make("chartAnalisa2024vs2025", "bar", a.map(d => d.bulan), [
            { label: "Target 2024",    data: a.map(d => akum ? d.akum2024          : d.target2024),    backgroundColor: "#555"    },
            { label: "Realisasi 2024", data: a.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: a.map(d => akum ? d.akum2025          : d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: a.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const delta = a.map(d => akum ? d.deltaAkum : d.deltaRpkwh)
        this.chart.make("chartAnalisaDelta", "bar", a.map(d => d.bulan), [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ], v => v.toFixed(0) + " Rp/kWh")

        this.#renderAnalisaTable(a, akum)
        this.#renderAnalisaDeltaTable(a.map(d => d.bulan), delta)
        this.#renderAnalisaDetailTable(a, akum)
    }

    #renderPersen(data) {
        const p = data.persenData
        this.chart.make("chartPersenRpkwh", "bar", p.map(d => d.bulan), [
            { label: "Target",    data: p.map(d => d.target),    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: p.map(d => d.realisasi), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")
        this.#renderPersenTable(p)
    }

    #populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifRpkwh")
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }

    #renderRencanaTable(r, rl) {
        const tbody = document.getElementById("rpkwhSideTable")
        if (!tbody) return

        tbody.innerHTML = LABELS.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? "var(--accent-green)" : "#f44336"
            return `<tr>
                <td>${bln}</td>
                <td style="color:#ff9800;">${(r[i] || 0).toFixed(0)}</td>
                <td style="color:var(--accent-green);">${(rl[i] || 0).toFixed(0)}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? "+" : ""}${d.toFixed(0)}</td>
            </tr>`
        }).join("")
    }

    #renderDeltaRencanaTable(labels, delta) {
        const tbody = document.getElementById("tabelRpkwhDelta")
        if (!tbody) return

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td>${bln}</td>
            <td style="color:${delta[i] >= 0 ? "#00ff88" : "#f44336"}; font-weight:600;">
                ${delta[i] >= 0 ? "+" : ""}${delta[i].toFixed(0)}
            </td>
        </tr>`).join("")
    }

    #renderPieTable(labels, data) {
        const tbody = document.getElementById("rpkwhPieTable")
        if (!tbody) return
        const total = data.reduce((a, b) => a + b, 0)

        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? ((data[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td>${lbl}</td>
                <td style="color:var(--accent-green);">${(data[i] || 0).toFixed(0)}</td>
                <td style="color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    #renderGrowthTable(golongan, growthData) {
        const tbody = document.getElementById("tabelGrowthRpkwh")
        if (!tbody) return
        const fmtPct = v => (v * 100).toFixed(2) + "%"

        tbody.innerHTML = LABELS.map((bln, i) => `<tr>
            <td>${bln}</td>
            ${golongan.map(g => {
                const val = growthData[g]?.[i] || 0
                return `<td style="color:${val >= 0 ? "#00ff88" : "#f44336"};">${fmtPct(val)}</td>`
            }).join("")}
        </tr>`).join("")
    }

    #renderBppHjrTable(hjr, bpp) {
        const tbody = document.getElementById("tabelBppHjr")
        if (!tbody) return

        tbody.innerHTML = LABELS.map((bln, i) => {
            const hjrVal  = hjr[i] || 0
            const bppVal  = bpp[i] || 0
            const selisih = hjrVal - bppVal
            const color   = selisih >= 0 ? "#4caf50" : "#f44336"
            return `<tr>
                <td>${bln}</td>
                <td style="color:#2196f3;">${hjrVal.toLocaleString('id-ID')}</td>
                <td style="color:#ff9800;">${bppVal.toLocaleString('id-ID')}</td>
                <td style="color:${color}; font-weight:600;">${selisih >= 0 ? "+" : ""}${selisih.toLocaleString('id-ID')}</td>
            </tr>`
        }).join("")
    }

    #renderAnalisaTable(a, akum) {
        const tbody = document.getElementById("rpkwhAnalisaSideTable")
        if (!tbody) return

        tbody.innerHTML = a.map(d => {
            const delta = akum ? d.deltaAkum : d.deltaRpkwh
            const color = delta >= 0 ? "var(--accent-green)" : "#f44336"
            return `<tr>
                <td>${d.bulan}</td>
                <td style="color:#555;">${(akum ? d.akum2024          : d.target2024).toFixed(0)}</td>
                <td style="color:#2196f3;">${(akum ? d.realisasiAkum2024 : d.realisasi2024).toFixed(0)}</td>
                <td style="color:#ff9800;">${(akum ? d.akum2025          : d.target2025).toFixed(0)}</td>
                <td style="color:var(--accent-green);">${(akum ? d.realisasiAkum2025 : d.realisasi2025).toFixed(0)}</td>
                <td style="color:${color}; font-weight:600;">${delta >= 0 ? "+" : ""}${delta.toFixed(0)}</td>
            </tr>`
        }).join("")
    }

    #renderAnalisaDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelRpkwhAnalisaDelta")
        if (!tbody) return

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td>${bln}</td>
            <td style="color:${delta[i] >= 0 ? "#00ff88" : "#f44336"}; font-weight:600;">
                ${delta[i] >= 0 ? "+" : ""}${delta[i].toFixed(0)}
            </td>
        </tr>`).join("")
    }

    #renderPersenTable(p) {
        const tbody = document.getElementById("tabelPersen")
        if (!tbody) return

        tbody.innerHTML = p.map(d => `<tr>
            <td>${d.bulan}</td>
            <td style="color:#ff9800;">${d.target.toFixed(2)}</td>
            <td style="color:var(--accent-green);">${d.realisasi.toFixed(2)}</td>
            <td style="color:${d.delta >= 0 ? "#00ff88" : "#f44336"};">${d.delta.toFixed(2)}</td>
        </tr>`).join("")
    }

    #renderAnalisaDetailTable(a, akum) {
        const tbody = document.getElementById("tabelAnalisa")
        if (!tbody) return
        const fmtPct = v => (v * 100).toFixed(2) + "%"

        tbody.innerHTML = a.map(d => {
            const dlt  = akum ? d.deltaAkum    : d.deltaRpkwh
            const dPct = akum ? d.deltaAkumPct : d.deltaPct
            return `<tr>
                <td>${d.bulan}</td>
                <td style="color:#555;">${(akum ? d.akum2024          : d.target2024).toFixed(2)}</td>
                <td style="color:#2196f3;">${(akum ? d.realisasiAkum2024 : d.realisasi2024).toFixed(2)}</td>
                <td style="color:#ff9800;">${(akum ? d.akum2025          : d.target2025).toFixed(2)}</td>
                <td style="color:var(--accent-green);">${(akum ? d.realisasiAkum2025 : d.realisasi2025).toFixed(2)}</td>
                <td style="color:${dlt >= 0 ? "#00ff88" : "#f44336"};">${dlt.toFixed(2)}</td>
                <td style="color:${dlt >= 0 ? "#00ff88" : "#f44336"};">${fmtPct(dPct - 1)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pencapaianRealisasi)}</td>
            </tr>`
        }).join("")
    }
}