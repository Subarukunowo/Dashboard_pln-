export default class PendapatanBatamModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        // populate tarif select kalau belum
        const sel = document.getElementById("filterTarifPendapatan")
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = state.tarif || "JUMLAH"
        }

        const tarif = state.tarif || "JUMLAH"
        const selected = data.data?.[tarif]
        if (!selected) return

        const labels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
        const bulan  = state.bulan

        // slice kalau filter bulan aktif
        const sliceIdx = (bulan && bulan !== "all") ? parseInt(bulan) + 1 : 12
        const slicedLabels    = labels.slice(0, sliceIdx)
        const slicedRencana   = selected.rencana.slice(0, sliceIdx)
        const slicedRealisasi = selected.realisasi.slice(0, sliceIdx)

        this.chart.renderWithLabels("chartPendapatanBatam", slicedLabels, {
            rencana:   slicedRencana,
            realisasi: slicedRealisasi,
        })

        const delta = slicedRealisasi.map((v, i) => v - (slicedRencana[i] || 0))
        this.chart.make("chartDeltaPendapatan", "bar", [{
            label: "Delta",
            data: delta,
            backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336"),
        }], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

        this._renderSideTable(slicedLabels, slicedRencana, slicedRealisasi)
        this._renderTable(data.realisasiBulanan, sliceIdx)
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewPendapatan")?.addEventListener("change", e => {
            state.view = e.target.value; onChange()
        })
        document.getElementById("filterTarifPendapatan")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
        document.getElementById("filterBulanPendapatan")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    _renderSideTable(labels, rencana, realisasi) {
        const tbody = document.getElementById("pendapatanSideTable")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M"
        tbody.innerHTML = labels.map((bln, i) => {
            const r  = rencana[i]   || 0
            const rl = realisasi[i] || 0
            const d  = rl - r
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${bln}</td>
                <td style="text-align:right; color:#ff9800;">${fmt(r)}</td>
                <td style="text-align:right; color:var(--accent-green);">${fmt(rl)}</td>
                <td style="text-align:right; color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${fmt(d)}</td>
            </tr>`
        }).join("")
    }

    _renderTable(realisasiBulanan, sliceIdx) {
        const tbody = document.getElementById("tabelPendapatan")
        if (!tbody || !realisasiBulanan) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + " M"
        tbody.innerHTML = realisasiBulanan.slice(0, sliceIdx).map(row => `
            <tr>
                <td>${row.bulan}</td>
                <td>${fmt(row.target * 1_000_000)}</td>
                <td>${fmt(row.realisasi * 1_000_000)}</td>
                <td style="color:${row.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(row.delta * 1_000_000)}</td>
                <td style="color:${row.delta >= 0 ? '#00ff88' : '#f44336'}">${(row.pencapaian * 100).toFixed(2)}%</td>
            </tr>
        `).join("")
    }
}