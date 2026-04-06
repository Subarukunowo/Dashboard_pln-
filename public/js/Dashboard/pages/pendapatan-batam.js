import SheetsService from "../services/SheetsService.js"
import PendapatanBatamService from "../services/PendapatanBatamService.js"
import PendapatanBatamChart from "../charts/PendapatanBatamChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

let data = null
let currentView = "bulanan"
let currentTarif = "JUMLAH"
const chart = new PendapatanBatamChart()

function fmt(v) { return "Rp " + (v / 1_000_000_000).toFixed(2) + " M" }

function renderAll() {
    if (!data) return
    const akum = currentView === "akumulasi"
    const d = data.data[currentTarif]
    if (!d) return

    chart.make("chartPendapatanBatam", "bar", [
        { label: "Rencana",   data: akum ? d.rencanaAkum   : d.rencana,   backgroundColor: "#ff9800" },
        { label: "Realisasi", data: akum ? d.realisasiAkum : d.realisasi, backgroundColor: "#00ff88" },
    ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

    // Delta
    const delta = (akum ? d.realisasiAkum : d.realisasi).map((v, i) => v - (akum ? d.rencanaAkum : d.rencana)[i])
    chart.make("chartDeltaPendapatan", "bar", [
        { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
    ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")

    // Tabel
    const tbody = document.getElementById("tabelPendapatan")
    if (tbody) {
        tbody.innerHTML = LABELS.map((bln, i) => `
            <tr>
                <td>${bln}</td>
                <td>${fmt(akum ? d.rencanaAkum[i] : d.rencana[i])}</td>
                <td>${fmt(akum ? d.realisasiAkum[i] : d.realisasi[i])}</td>
                <td style="color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}">${fmt(delta[i])}</td>
                <td style="color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}">${d.rencana[i] ? ((d.realisasi[i]/d.rencana[i])*100).toFixed(2) + "%" : "-"}</td>
            </tr>
        `).join("")
    }
}

async function init() {
    const sheets = new SheetsService(SCRIPT_URL)
    const service = new PendapatanBatamService(sheets)
    data = await service.getData()

    // Populate dropdown tarif
    const sel = document.getElementById("filterTarifPendapatan")
    if (sel && data) {
        data.tarif.forEach(t => {
            const opt = document.createElement("option")
            opt.value = t; opt.textContent = t
            sel.appendChild(opt)
        })
        sel.value = "JUMLAH"
    }

    renderAll()

    document.getElementById("filterViewPendapatan")?.addEventListener("change", e => {
        currentView = e.target.value; renderAll()
    })
    document.getElementById("filterTarifPendapatan")?.addEventListener("change", e => {
        currentTarif = e.target.value; renderAll()
    })
}

document.addEventListener("DOMContentLoaded", init)