const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

export default class PelangganModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const r  = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl = data.realisasiData[state.tarif] || new Array(12).fill(0)

        this.chart.make("chartPelangganBar", "bar", LABELS, [
            { label: "Rencana",   data: r,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: rl, backgroundColor: "#00ff88" },
        ])

        const delta = rl.map((v, i) => v - r[i])
        this.chart.make("chartPelangganDelta", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
        const pieData   = data.golongan.map(g => data.targetGolAkum[g][state.bulan] || 0)
        const pieTarget = data.golongan.map(g => data.targetGolAkum[g][state.bulan])
        const pieReal24 = data.golongan.map(g => data.realisasiGolAkum[g][state.bulan])

        this.chart.pie("chartPelangganPie", pieLabels, pieData)
        this.chart.make("chartPelangganGol", "bar", pieLabels, [
            { label: "Target 2025",    data: pieTarget, backgroundColor: "#ff9800" },
            { label: "Realisasi 2024", data: pieReal24, backgroundColor: "#2196f3" },
        ])

        this.#populateTarif(data, state.tarif)
        this.#renderBarTable(r, rl)
        this.#renderDeltaTable(LABELS, delta)
        this.#renderPieTable(pieLabels, pieData)
        this.#renderGolTable(pieLabels, pieTarget, pieReal24)
        this.#renderRkapTable(data)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifPelanggan")?.addEventListener("change", e => { state.tarif = e.target.value;        onChange() })
        document.getElementById("filterBulanPelanggan")?.addEventListener("change", e => { state.bulan = parseInt(e.target.value); onChange() })
    }

    #populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifPelanggan")
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }

    #renderBarTable(r, rl) {
        const tbody = document.getElementById("pelangganSideTable")
        if (!tbody) return

        tbody.innerHTML = LABELS.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? "var(--accent-green)" : "#f44336"
            return `<tr>
                <td>${bln}</td>
                <td style="color:#ff9800;">${(r[i] || 0).toLocaleString('id-ID')}</td>
                <td style="color:var(--accent-green);">${(rl[i] || 0).toLocaleString('id-ID')}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? "+" : ""}${(d || 0).toLocaleString('id-ID')}</td>
            </tr>`
        }).join("")
    }

    #renderDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelPelangganDelta")
        if (!tbody) return

        tbody.innerHTML = labels.map((bln, i) => {
            const d     = delta[i] || 0
            const color = d >= 0 ? "#00ff88" : "#f44336"
            return `<tr>
                <td>${bln}</td>
                <td style="color:${color}; font-weight:600;">${d >= 0 ? "+" : ""}${d.toLocaleString('id-ID')}</td>
            </tr>`
        }).join("")
    }

    #renderPieTable(labels, data) {
        const tbody = document.getElementById("pelangganPieTable")
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

    #renderGolTable(labels, target, real24) {
        const tbody = document.getElementById("tabelPelangganGol")
        if (!tbody) return

        tbody.innerHTML = labels.map((lbl, i) => `<tr>
            <td>${lbl}</td>
            <td style="color:#ff9800;">${(target[i] || 0).toLocaleString('id-ID')}</td>
            <td style="color:#2196f3;">${(real24[i] || 0).toLocaleString('id-ID')}</td>
        </tr>`).join("")
    }

    #renderRkapTable(data) {
        const tbody = document.getElementById("tabelRkapPelanggan")
        if (!tbody || !data.rkapData) return

        tbody.innerHTML = data.rkapData.map(d => `<tr>
            <td style="border-right:1px solid #444;">${d.golongan}</td>
            <td>${d.targetTw1}</td>
            <td>${d.targetTw2}</td>
            <td>${d.targetTw3}</td>
            <td>${d.targetTw4}</td>
            <td style="border-right:1px solid #444;">${d.targetTotal}</td>
            <td style="border-left:1px solid #444;">${d.realTw1}</td>
            <td>${d.realTw2}</td>
            <td>${d.realTw3}</td>
            <td>${d.realTw4}</td>
            <td>${d.realTotal}</td>
            <td style="color:${d.pencapaian >= 1 ? "#00ff88" : "#f44336"}; font-weight:600;">
                ${(d.pencapaian * 100).toFixed(2)}%
            </td>
        </tr>`).join("")
    }
}