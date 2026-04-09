export default class BppChart {
    constructor(canvasId) {
        this.canvasId = canvasId;
        this.chart = null;
    }

    // Fungsi baru untuk render tabel di sebelah kanan
  renderTable(konsolidasi, internal, labels) {
    const tableBody = document.getElementById("bppTableBody");
    if (!tableBody) return;

    tableBody.innerHTML = labels.map((label, i) => {
        const delta = konsolidasi[i] - internal[i];
        const deltaColor = delta >= 0 ? '#f44336' : '#00ff88'; // merah = konsolidasi lebih tinggi
        return `
            <tr class="bpp-row">
                <td class="bpp-bulan">${label}</td>
                <td class="bpp-val kons">${konsolidasi[i].toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                <td class="bpp-val intl">${internal[i] ? internal[i].toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) : '—'}</td>
                <td class="bpp-val" style="color:${deltaColor}">${delta ? (delta > 0 ? '+' : '') + delta.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) : '—'}</td>
            </tr>
        `;
    }).join("");
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