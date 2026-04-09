export default class PenjualanChart {
    constructor(canvasId, tableBodyId = "penjualanTableBody") {
        this.canvasId = canvasId;
        this.tableBodyId = tableBodyId;
        this.chart = null;
    }

    render(target, realisasi, labels, view = 'bulanan') {
        const ctx = document.getElementById(this.canvasId);
        if (this.chart) this.chart.destroy();

        const isAccumulated = view === 'akumulasi' || view === 'persenAkumulasi';
        const isPersen = view.includes('persen') || view.includes('Persen');

        this.chart = new Chart(ctx, {
            type: isAccumulated ? 'line' : 'bar',
            data: {
                labels,
                datasets: [
                    ...(target.length > 0 ? [{
                        label: 'Target Rencana',
                        data: target,
                        backgroundColor: 'rgba(255, 255, 255, 0.1)',
                        borderColor: '#888',
                        borderWidth: 1,
                        fill: isAccumulated
                    }] : []),
                    {
                        label: isPersen ? '% Realisasi' : 'Realisasi',
                        data: realisasi,
                        backgroundColor: '#00ff88',
                        borderColor: '#00ff88',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        grid: { color: '#333' },
                        ticks: {
                            color: '#888',
                            callback: isPersen ? v => v.toFixed(1) + '%' : undefined
                        }
                    },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        });

        this.renderTable(target, realisasi, labels, isPersen);
    }

    renderTable(target, realisasi, labels, isPersen = false) {
        const tbody = document.getElementById(this.tableBodyId);
        if (!tbody) return;

        const fmt = isPersen
            ? v => (v ?? 0).toFixed(1) + '%'
            : v => (v ?? 0).toLocaleString('id-ID');

        tbody.innerHTML = labels.map((bulan, i) => {
            const t = target[i] ?? 0;
            const r = realisasi[i] ?? 0;
            const delta = r - t;
            const color = delta >= 0 ? 'var(--accent-green)' : '#f44336';
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${bulan}</td>
                <td style="text-align:right; color:#888;">${fmt(t)}</td>
                <td style="text-align:right; color:var(--accent-green); font-weight:600;">${fmt(r)}</td>
                <td style="text-align:right; color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${fmt(delta)}</td>
            </tr>`;
        }).join('');
    }
}