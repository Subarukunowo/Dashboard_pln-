import SheetsService from "../services/SheetsService.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PenjualanKwhChart from "../charts/PenjualanKwhChart.js"
const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

let data = null
let currentView = "bulanan"
const chart = new PenjualanKwhChart()

function fmt(v) { return (v / 1_000_000).toFixed(2) }
function fmtPct(v) { return (v * 100).toFixed(2) + "%" }

function renderAll() {
    if (!data) return
    const akum = currentView === "akumulasi"

    chart.renderTargetRealisasi(data, akum)
    chart.renderDelta(data, akum)
    chart.renderPencapaian(data)
    chart.renderPertumbuhan(data)

    const tbody = document.getElementById("tabelRingkasan")
    if (tbody) {
        tbody.innerHTML = data.map(d => `
            <tr>
                <td>${d.bulan}</td>
                <td>${fmt(akum ? d.targetAkum2024 : d.target2024)}</td>
                <td>${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td>${fmt(akum ? d.targetAkum2025 : d.target2025)}</td>
                <td>${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="color:${(akum ? d.deltaAkumKwh : d.deltaKwh) >= 0 ? '#00ff88' : '#f44336'}">${fmt(akum ? d.deltaAkumKwh : d.deltaKwh)}</td>
                <td style="color:${(akum ? d.deltaAkumKwh : d.deltaKwh) >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(akum ? d.deltaAkumPersen - 1 : d.deltaPersen - 1)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pertumbuhanReal)}</td>
            </tr>
        `).join("")
    }
}

async function init() {
    const sheets = new SheetsService(SCRIPT_URL)
    const service = new RealisasiKWHService(sheets)
    data = await service.getData()
    renderAll()

    document.getElementById("filterView")?.addEventListener("change", e => {
        currentView = e.target.value
        renderAll()
    })
}

document.addEventListener("DOMContentLoaded", init)