export default class KvaModule {
    constructor(chart) { this.chart = chart }

    #getLabels() {
        return [...document.querySelectorAll("#filterBulanKva option")]
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return

        const r    = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl   = data.realisasiData[state.tarif] || new Array(12).fill(0)
        const akum = state.view === "akumulasi"

        const toAkum = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
        const displayR  = akum ? toAkum(r)  : r
        const displayRl = akum ? toAkum(rl) : rl

        const sliceIdx     = (state.bulan ?? 11) + 1
        const labels       = this.#getLabels()
        const slicedLabels = labels.slice(0, sliceIdx)
        const slicedR      = displayR.slice(0, sliceIdx)
        const slicedRl     = displayRl.slice(0, sliceIdx)

        this.chart.make("chartKvaBar", "bar", slicedLabels, [
            { label: "Rencana",   data: slicedR,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: slicedRl, backgroundColor: "#00ff88" },
        ])

        const delta = slicedRl.map((v, i) => v - slicedR[i])
        this.chart.make("chartKvaDelta", "bar", slicedLabels, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
        const pieData   = data.golongan.map(g => data.targetGolAkum[g][state.bulan ?? 11] || 0)
        const pieTarget = data.golongan.map(g => data.targetGolAkum[g][state.bulan ?? 11])
        const pieReal   = data.golongan.map(g => data.realisasiGolAkum[g][state.bulan ?? 11])

        this.chart.pie("chartKvaPie", pieLabels, pieData)
        this.chart.make("chartKvaGol", "bar", pieLabels, [
            { label: "Target Thn Ini",  data: pieTarget, backgroundColor: "#ff9800" },
            { label: "Real Thn Lalu",   data: pieReal,   backgroundColor: "#2196f3" },
        ])

        const p = data.perbandinganData
        this.chart.make("chartKvaPerbandingan", "bar", labels.slice(0, p.length), [
            { label: "Target Thn Lalu",    data: p.map(d => akum ? d.akum2024          : d.target2024),    backgroundColor: "#555"    },
            { label: "Realisasi Thn Lalu", data: p.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target Thn Ini",     data: p.map(d => akum ? d.akum2025          : d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi Thn Ini",  data: p.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: "#00ff88" },
        ])

        const deltaPerb = p.map(d => akum ? d.deltaAkumKva : d.deltaKva)
        this.chart.make("chartKvaDeltaPerbandingan", "bar", labels.slice(0, p.length), [
            { label: "Delta", data: deltaPerb, backgroundColor: deltaPerb.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        this.#populateTarif(data, state.tarif)
        this.#renderBarTable(slicedLabels, slicedR, slicedRl)
        this.#renderDeltaTable(slicedLabels, delta)
        this.#renderPieTable(pieLabels, pieData)
        this.#renderGolTable(pieLabels, pieTarget, pieReal)
        this.#renderPerbandinganTable(p, labels, akum)
        this.#renderDeltaPerbandinganTable(labels.slice(0, p.length), deltaPerb)
        this.#renderRkapTable(data)
        this.#renderPerbandinganDetailTable(data, labels, akum)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifKva")?.addEventListener("change", e => { state.tarif = e.target.value;        onChange() })
        document.getElementById("filterBulanKva")?.addEventListener("change", e => { state.bulan = parseInt(e.target.value); onChange() })
        document.getElementById("filterViewKva")?.addEventListener("change",  e => { state.view  = e.target.value;        onChange() })
    }

    #populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifKva")
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }

    #renderBarTable(labels, r, rl) {
        const tbody = document.getElementById("kvaSideTable")
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')

        tbody.innerHTML = labels.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? "var(--accent-green)" : "#f44336"
            return `<tr>
                <td>${bln}</td>
                <td style="color:#ff9800;">${fmt(r[i])}</td>
                <td style="color:var(--accent-green);">${fmt(rl[i])}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? "+" : ""}${fmt(d)}</td>
            </tr>`
        }).join("")
    }

    #renderDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelKvaDelta")
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td>${bln}</td>
            <td style="color:${delta[i] >= 0 ? "#00ff88" : "#f44336"}; font-weight:600;">
                ${delta[i] >= 0 ? "+" : ""}${fmt(delta[i])}
            </td>
        </tr>`).join("")
    }

    #renderPieTable(labels, data) {
        const tbody = document.getElementById("kvaPieTable")
        if (!tbody) return
        const total = data.reduce((a, b) => a + b, 0)

        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? ((data[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td>${lbl}</td>
                <td style="color:var(--accent-green);">${(data[i] || 0).toLocaleString('id-ID')}</td>
                <td style="color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    #renderGolTable(labels, target, real) {
        const tbody = document.getElementById("tabelKvaGol")
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')

        tbody.innerHTML = labels.map((lbl, i) => `<tr>
            <td>${lbl}</td>
            <td style="color:#ff9800;">${fmt(target[i])}</td>
            <td style="color:#2196f3;">${fmt(real[i])}</td>
        </tr>`).join("")
    }

    #renderPerbandinganTable(p, labels, akum) {
        const tbody = document.getElementById("kvaPerbandinganSideTable")
        if (!tbody) return
        const fmt = v => (v / 1000).toFixed(1)

        tbody.innerHTML = p.map((d, i) => {
            const delta = akum ? d.deltaAkumKva : d.deltaKva
            const color = delta >= 0 ? "var(--accent-green)" : "#f44336"
            return `<tr>
                <td>${labels[i] ?? d.bulan}</td>
                <td style="color:#555;">${fmt(akum ? d.akum2024          : d.target2024)}</td>
                <td style="color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td style="color:#ff9800;">${fmt(akum ? d.akum2025          : d.target2025)}</td>
                <td style="color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${color}; font-weight:600;">${delta >= 0 ? "+" : ""}${fmt(delta)}</td>
            </tr>`
        }).join("")
    }

    #renderDeltaPerbandinganTable(labels, delta) {
        const tbody = document.getElementById("tabelKvaDeltaPerbandingan")
        if (!tbody) return
        const fmt = v => (v / 1000).toFixed(1)

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td>${bln}</td>
            <td style="color:${delta[i] >= 0 ? "#00ff88" : "#f44336"}; font-weight:600;">
                ${delta[i] >= 0 ? "+" : ""}${fmt(delta[i])}
            </td>
        </tr>`).join("")
    }

    #renderRkapTable(data) {
        const tbody = document.getElementById("tabelRkapKva")
        if (!tbody || !data.rkapData) return

        tbody.innerHTML = data.rkapData.map(d => `<tr>
            <td>${d.golongan}</td>
            <td>${d.targetTw1.toFixed(2)}</td>
            <td>${d.targetTw2.toFixed(2)}</td>
            <td>${d.targetTw3.toFixed(2)}</td>
            <td>${d.targetTw4.toFixed(2)}</td>
            <td>${d.targetTotal.toFixed(2)}</td>
            <td>${d.realTw1.toFixed(2)}</td>
            <td>${d.realTw2.toFixed(2)}</td>
            <td>${d.realTw3.toFixed(2)}</td>
            <td>${d.realTw4.toFixed(2)}</td>
            <td>${d.realTotal.toFixed(2)}</td>
            <td style="color:${d.pencapaian >= 1 ? "#00ff88" : "#f44336"}">
                ${(d.pencapaian * 100).toFixed(2)}%
            </td>
        </tr>`).join("")
    }

    #renderPerbandinganDetailTable(data, labels, akum) {
        const tbody = document.getElementById("tabelPerbandinganKva")
        if (!tbody || !data.perbandinganData) return
        const fmt    = v => (v / 1000).toFixed(2) + " kVA"
        const fmtPct = v => (v * 100).toFixed(2) + "%"

        tbody.innerHTML = data.perbandinganData.map((d, i) => `<tr>
            <td>${labels[i] ?? d.bulan}</td>
            <td style="color:#555;">${fmt(akum ? d.akum2024          : d.target2024)}</td>
            <td style="color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
            <td style="color:#ff9800;">${fmt(akum ? d.akum2025          : d.target2025)}</td>
            <td style="color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
            <td style="color:${d.deltaKva >= 0 ? "#00ff88" : "#f44336"}">
                ${fmt(akum ? d.deltaAkumKva : d.deltaKva)}
            </td>
            <td style="color:${d.deltaKva >= 0 ? "#00ff88" : "#f44336"}">
                ${fmtPct(akum ? d.deltaAkumPct : d.deltaPct)}
            </td>
            <td>${fmtPct(d.pencapaianTarget)}</td>
            <td>${fmtPct(d.pencapaianRealisasi)}</td>
        </tr>`).join("")
    }
}