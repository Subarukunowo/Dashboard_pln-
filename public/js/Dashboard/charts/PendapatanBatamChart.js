export default class PendapatanBatamChart {
    constructor() {
        this.charts = {};
    }

    render(container, viewType, tarif) {
        const allData = container?.data;
        if (!allData) { console.error("Data Pendapatan tidak ditemukan!"); return; }

        let selectedData = allData[tarif];
        if (!selectedData) {
            console.warn(`Tarif ${tarif} tidak ditemukan, fallback ke JUMLAH`);
            selectedData = allData["JUMLAH"];
        }
        if (!selectedData) { console.error("Data kosong."); return; }

        this.renderBar("chartPendapatanBatam", {
            rencana: selectedData.rencana,
            realisasi: selectedData.realisasi,
        });

        const delta = selectedData.realisasi.map((v, i) => v - (selectedData.rencana[i] || 0));
        this.make("chartDeltaPendapatan", "bar", [{
            label: "Delta (Realisasi - Rencana)",
            data: delta,
            backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#ff4444"),
        }], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M");
    }

    destroy(id) {
        if (this.charts[id]) { this.charts[id].destroy(); delete this.charts[id]; }
    }

    make(id, type, datasets, yCallback = null) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        this.destroy(id);
        this.charts[id] = new Chart(ctx, {
            type,
            data: {
                labels: ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"],
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: "#ccc", font: { size: 11 } } } },
                scales: {
                    y: { grid: { color: "#333" }, ticks: { color: "#888", callback: yCallback || undefined } },
                    x: { grid: { display: false }, ticks: { color: "#888" } }
                }
            }
        });
    }

    renderBar(id, { rencana, realisasi }) {
        this.make(id, "bar", [
            { label: "Rencana",   data: rencana,   backgroundColor: "#ff9800" },
            { label: "Realisasi", data: realisasi, backgroundColor: "#00ff88" },
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M");
    }
    renderWithLabels(id, labels, { rencana, realisasi }) {
    this.make2(id, "bar", labels, [
        { label: "Rencana",   data: rencana,   backgroundColor: "#ff9800" },
        { label: "Realisasi", data: realisasi, backgroundColor: "#00ff88" },
    ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M")
}

make2(id, type, labels, datasets, yCallback = null) {
    const ctx = document.getElementById(id)
    if (!ctx) return
    this.destroy(id)
    this.charts[id] = new Chart(ctx, {
        type,
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: "#ccc", font: { size: 11 } } } },
            scales: {
                y: { grid: { color: "#333" }, ticks: { color: "#888", callback: yCallback || undefined } },
                x: { grid: { display: false }, ticks: { color: "#888" } }
            }
        }
    })
}
}