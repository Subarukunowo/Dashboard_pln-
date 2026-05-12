const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class JamNyalaModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const sliceIdx = (state.bulan && state.bulan !== "all")
            ? parseInt(state.bulan) + 1 : 12
        const labels = LABELS.slice(0, sliceIdx)

        const selected = state.tarif === "ALL"
            ? data.tarif.filter(t => t.toLowerCase().includes("jumlah"))
            : [state.tarif]

        this.chart.make("chartJamNyalaLine", "line", labels,
            selected.map((t, i) => ({
                label: t,
                data: (data.data[t] || new Array(12).fill(0)).slice(0, sliceIdx),
                borderColor: COLORS[i % COLORS.length],
                tension: 0.4, fill: false, pointRadius: 3
            }))
        )

        // Render table for line chart (NEW)
        const lineData = selected.map((t, i) => ({
            label: t,
            data: (data.data[t] || new Array(12).fill(0)).slice(0, sliceIdx)
        }))
        this.renderLineTable(labels, lineData)

        const tarifBar = state.tarif === "ALL" ? (selected[0] || data.tarif[0]) : state.tarif
        const d = (data.data[tarifBar] || new Array(12).fill(0)).slice(0, sliceIdx)
        this.chart.make("chartJamNyalaBar", "bar", labels, [
            { label: tarifBar, data: d, backgroundColor: "#00ff88" }
        ], v => v.toFixed(0) + " jam")

        // Render table for bar chart
        this.renderTable(labels, d, tarifBar)
        this._populateTarif(data, state.tarif)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifJamNyala")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
        document.getElementById("filterBulanJamNyala")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    // Table for Line Chart (NEW)
// Table for Line Chart (REVISED)
renderLineTable(labels, datasets) {
    const tbody = document.getElementById("tabelJamNyalaLine")
    const thead = document.querySelector("#tabelJamNyalaLine").previousElementSibling
    if (!tbody || !thead) return
    
    // Update header dengan multiple columns
    const headerRow = thead.querySelector("tr")
    if (datasets.length > 1) {
        // Multiple tarifs: show all as column headers
        headerRow.innerHTML = `
            <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
            ${datasets.map((ds, i) => `
                <th style="padding:10px; text-align:center; color:${COLORS[i % COLORS.length]}; font-size:0.7rem;">
                    ${ds.label}
                </th>
            `).join("")}
        `
    } else {
        // Single tarif: show simple header
        headerRow.innerHTML = `
            <th style="padding:10px; text-align:center; color:#888;">Bulan</th>
            <th style="padding:10px; text-align:center; color:var(--accent-green);">Jam Nyala</th>
        `
    }

    // Render table body
    tbody.innerHTML = labels.map((bln, i) => {
        const cells = datasets.map((ds, j) => {
            const val = ds.data[i] || 0
            const color = COLORS[j % COLORS.length]
            return `<td style="padding:8px 10px; text-align:center; color:${color}; font-weight:500;">${val.toFixed(0)}</td>`
        }).join("")
        
        return `<tr style="border-bottom:1px solid #333;">
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
            ${cells}
        </tr>`
    }).join("")
}
    // Table for Bar Chart
    renderTable(labels, d, tarifName) {
        const tbody = document.getElementById("jamNyalaSideTable")
        if (!tbody) return
        
        // Update header to show tarif name
        const header = document.querySelector("#jamNyalaSideTable").previousElementSibling?.querySelector("th:last-child")
        if (header) {
            header.textContent = tarifName
        }
        
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${(d[i] || 0).toFixed(0)} jam</td>
        </tr>`).join("")
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifJamNyala")
        if (sel && sel.options.length === 0 && data.tarif) {
            const opt0 = document.createElement("option")
            opt0.value = "ALL"; opt0.textContent = "Semua Golongan"
            sel.appendChild(opt0)
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }
}