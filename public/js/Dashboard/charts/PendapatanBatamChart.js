export default class PendapatanBatamChart {
    constructor() {
        this.charts = {};
    }

    /**
     * @param {Object} container - Objek dari Service { tarif: [], data: {} }
     * @param {string} viewType - "bulanan" atau "akumulasi"
     * @param {string} tarif - Nama tarif yang dipilih (misal: "JUMLAH")
     */
    render(container, viewType, tarif) {
        const isAkum = viewType === "akumulasi";

        // Pastikan kita masuk ke properti .data karena Service membungkusnya di sana
        const allData = container && container.data ? container.data : null;

        if (!allData) {
            console.error("Data Pendapatan tidak ditemukan dalam container!");
            return;
        }

        // Ambil data spesifik berdasarkan tarif
        let selectedData = allData[tarif];

        // Fallback ke "JUMLAH" jika tarif yang dipilih tidak ada dalam data
        if (!selectedData) {
            console.warn(`Tarif ${tarif} tidak ditemukan, menggunakan JUMLAH sebagai default`);
            selectedData = allData["JUMLAH"];
        }

        if (!selectedData) {
            console.error("Data benar-benar kosong atau struktur salah.");
            return;
        }

        // Bungkus data agar sesuai dengan parameter renderBar
        const rencanaParam = {
            rencana: selectedData.rencana,
            rencanaAkum: selectedData.rencanaAkum
        };

        const realisasiParam = {
            realisasi: selectedData.realisasi,
            realisasiAkum: selectedData.realisasiAkum
        };

        this.renderBar(
            "chartPendapatanBatam", 
            `Pendapatan - ${tarif}`, 
            rencanaParam, 
            realisasiParam, 
            isAkum
        );
    }

    destroy(id) {
        if (this.charts[id]) {
            this.charts[id].destroy();
            delete this.charts[id];
        }
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
                plugins: {
                    legend: { labels: { color: "#ccc", font: { size: 11 } } }
                },
                scales: {
                    y: { 
                        grid: { color: "#333" }, 
                        ticks: { color: "#888", callback: yCallback || undefined } 
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { color: "#888" } 
                    }
                }
            }
        });
    }

    renderBar(id, title, rencana, realisasi, akum) {
        this.make(id, "bar", [
            { 
                label: "Rencana",   
                data: akum ? rencana.rencanaAkum : rencana.rencana, 
                backgroundColor: "#ff9800" 
            },
            { 
                label: "Realisasi", 
                data: akum ? realisasi.realisasiAkum : realisasi.realisasi, 
                backgroundColor: "#00ff88" 
            },
        ], v => "Rp " + (v / 1_000_000_000).toFixed(2) + "M");
    }
}