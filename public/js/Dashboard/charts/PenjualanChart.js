export default class PenjualanChart {
    constructor(canvasId) {
        this.canvasId = canvasId;
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
            labels: labels,
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
                        callback: isPersen ? (v) => v.toFixed(1) + '%' : undefined
                    }
                },
                x: { grid: { display: false }, ticks: { color: '#888' } }
            }
        }
    });
}
}