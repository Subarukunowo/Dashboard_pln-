export default class BppChart {
    constructor(canvasId) {
        this.canvasId = canvasId;
        this.chart = null;
    }

    // Fungsi baru untuk render tabel di sebelah kanan
    renderTable(konsolidasi, internal, labels) {
        const tableBody = document.getElementById("bppTableBody");
        if (!tableBody) return;

        let html = '';
        // Kita loop berdasarkan label yang ada (Bulan)
        labels.forEach((label, index) => {
            html += `
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px 0; color: #aaa;">${label}</td>
                    <td style="padding: 8px 0; text-align: right;">
                        <div style="color: #ff9800; font-weight: bold;">${konsolidasi[index].toLocaleString('id-ID')}</div>
                        <div style="color: #00ff88; font-size: 0.75rem;">${internal[index].toLocaleString('id-ID')}</div>
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;
    }

    render(konsolidasi, internal, labels, mode = "trend") {
        const ctx = document.getElementById(this.canvasId);
        if (this.chart) { this.chart.destroy(); }

        const isSingle = mode === "single";

        // Panggil render tabel
        this.renderTable(konsolidasi, internal, labels);

        this.chart = new Chart(ctx, {
            type: isSingle ? 'bar' : 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Konsolidasi',
                        data: konsolidasi,
                        borderColor: '#ff9800',
                        backgroundColor: isSingle ? '#ff9800' : 'transparent',
                        tension: 0.3
                    },
                    {
                        label: 'Internal',
                        data: internal,
                        borderColor: '#00ff88',
                        backgroundColor: isSingle ? '#00ff88' : 'transparent',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false } // Sembunyikan legend bawaan karena sudah ada tabel di kanan
                }
            }
        });
    }
}