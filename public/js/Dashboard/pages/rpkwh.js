import SheetsService from "../services/SheetsService.js"
import RpkwhService from "../services/RpkwhService.js"
import RpkwhChart from "../charts/RpkwhChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

let data = null
let currentTarif  = "JUMLAH"
let currentView   = "bulanan"
let currentBulan  = "11"
let currentTahun  = "2025"
const chart = new RpkwhChart()

function fmt(v)    { return v.toFixed(2) + " Rp/kWh" }
function fmtPct(v) { return (v * 100).toFixed(2) + "%" }

function renderRencanaRealisasi() {
    if (!data) return
    const tarif = currentTarif
    const r  = data.rencanaData[tarif]   || new Array(12).fill(0)
    const rl = data.realisasiData[tarif] || new Array(12).fill(0)

    chart.make("chartRencanaRealisasi", "bar", LABELS, [
        { label: "Rencana",   data: r,  backgroundColor: "#ff9800" },
        { label: "Realisasi", data: rl, backgroundColor: "#00ff88" },
    ], v => v.toFixed(0) + " Rp/kWh")

    const delta = rl.map((v, i) => v - r[i])
    chart.make("chartDeltaRpkwh", "bar", LABELS, [
        { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
    ], v => v.toFixed(0) + " Rp/kWh")
}

function renderGolongan() {
    if (!data) return
    const akum   = currentView === "akumulasi"
    const idx    = parseInt(currentBulan)
    const is2024 = currentTahun === "2024"

    const nilaiGol = data.golongan.map(g =>
        is2024
            ? (akum ? data.realisasiGolAkum[g][idx] : data.realisasiGolBul[g][idx])
            : (akum ? data.targetGolAkum[g][idx]    : data.targetGolBul[g][idx])
    )

    document.getElementById("judulPieRpkwh").textContent =
        (is2024 ? "Realisasi" : "Target") + " RPKWH " + currentTahun +
        (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]

    chart.pie("chartPieRpkwh",
        data.golongan.map(g => g.replace(/ \(.\) ?/, '')),
        nilaiGol
    )
}

function renderGrowth() {
    if (!data) return
    const akum = currentView === "akumulasi"

    chart.make("chartGrowthRpkwh", "line",
        data.golongan.map(g => g.replace(/ \(.\) ?/, '')),
        data.golongan.map((g, i) => ({
            label: g.replace(/ \(.\) ?/, ''),
            data: akum ? data.growthAkum[g] : data.growthBul[g],
            borderColor: COLORS[i],
            tension: 0.4, fill: false, pointRadius: 3
        })),
        v => (v * 100).toFixed(1) + "%"
    )
}

function renderBppHjr() {
    if (!data) return
    const tarifBpp = Object.keys(data.bppHjrData)

    chart.make("chartBppHjr", "line", LABELS,
        tarifBpp.map((t, i) => ({
            label: t,
            data: data.bppHjrData[t],
            borderColor: COLORS[i % COLORS.length],
            tension: 0.4, fill: false, pointRadius: 3
        })),
        v => v.toFixed(0) + " Rp/kWh"
    )
}

function renderAnalisa() {
    if (!data) return
    const a = data.analisaData
    const labels = a.map(d => d.bulan)

    chart.make("chartAnalisa2024vs2025", "bar", labels, [
        { label: "Target 2024",    data: a.map(d => d.target2024),    backgroundColor: "#555" },
        { label: "Realisasi 2024", data: a.map(d => d.realisasi2024), backgroundColor: "#2196f3" },
        { label: "Target 2025",    data: a.map(d => d.target2025),    backgroundColor: "#ff9800" },
        { label: "Realisasi 2025", data: a.map(d => d.realisasi2025), backgroundColor: "#00ff88" },
    ], v => v.toFixed(0) + " Rp/kWh")

    const delta = a.map(d => d.deltaRpkwh)
    chart.make("chartAnalisaDelta", "bar", labels, [
        { label: "Delta Rp/kWh", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
    ], v => v.toFixed(0) + " Rp/kWh")

    // Tabel analisa
    const tbody = document.getElementById("tabelAnalisa")
    if (tbody) {
        tbody.innerHTML = a.map(d => `
            <tr>
                <td>${d.bulan}</td>
                <td>${d.target2024.toFixed(2)}</td>
                <td>${d.realisasi2024.toFixed(2)}</td>
                <td>${d.target2025.toFixed(2)}</td>
                <td>${d.realisasi2025.toFixed(2)}</td>
                <td style="color:${d.deltaRpkwh >= 0 ? '#00ff88' : '#f44336'}">${d.deltaRpkwh.toFixed(2)}</td>
                <td style="color:${d.deltaRpkwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pencapaianRealisasi)}</td>
            </tr>
        `).join("")
    }
}

function renderPersen() {
    if (!data) return
    const p = data.persenData
    const labels = p.map(d => d.bulan)

    chart.make("chartPersenRpkwh", "bar", labels, [
        { label: "Target",    data: p.map(d => d.target),    backgroundColor: "#ff9800" },
        { label: "Realisasi", data: p.map(d => d.realisasi), backgroundColor: "#00ff88" },
    ], v => v.toFixed(0) + " Rp/kWh")

    const tbody = document.getElementById("tabelPersen")
    if (tbody) {
        tbody.innerHTML = p.map(d => `
            <tr>
                <td>${d.bulan}</td>
                <td>${d.target.toFixed(2)}</td>
                <td>${d.realisasi.toFixed(2)}</td>
                <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${d.delta.toFixed(2)}</td>
            </tr>
        `).join("")
    }
}

function renderAll() {
    renderRencanaRealisasi()
    renderGolongan()
    renderGrowth()
    renderBppHjr()
    renderAnalisa()
    renderPersen()
}

async function init() {
    const sheets  = new SheetsService(SCRIPT_URL)
    const service = new RpkwhService(sheets)
    data = await service.getData()

    // Populate dropdown tarif
    const sel = document.getElementById("filterTarifRpkwh")
    if (sel && data) {
        data.tarif.forEach(t => {
            const opt = document.createElement("option")
            opt.value = t; opt.textContent = t
            sel.appendChild(opt)
        })
    }

    renderAll()

    document.getElementById("filterTarifRpkwh")?.addEventListener("change", e => { currentTarif = e.target.value; renderRencanaRealisasi() })
    document.getElementById("filterViewRpkwh")?.addEventListener("change",  e => { currentView  = e.target.value; renderGolongan(); renderGrowth() })
    document.getElementById("filterBulanRpkwh")?.addEventListener("change", e => { currentBulan = e.target.value; renderGolongan() })
    document.getElementById("filterTahunRpkwh")?.addEventListener("change", e => { currentTahun = e.target.value; renderGolongan() })
}

document.addEventListener("DOMContentLoaded", init)