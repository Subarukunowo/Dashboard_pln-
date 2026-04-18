let chartInstance = null
let currentType   = 'bar'

const COLORS = {
    S:'#ff9800', R:'#00ff88', B:'#2196f3',
    I:'#e91e63', P:'#9c27b0', M:'#00bcd4', TOTAL:'#ffffff'
}

async function loadChart() {
    const dari    = document.getElementById('filterTahunDari').value
    const sampai  = document.getElementById('filterTahunSampai').value
    const metrik  = document.getElementById('filterMetrik').value
    const mode    = document.getElementById('filterMode').value
    const golongan = [...document.querySelectorAll('.cb-golongan:checked')].map(c => c.value)

    if (!golongan.length) { alert('Pilih minimal satu golongan.'); return }

    const params = new URLSearchParams({
        tahun_dari: dari,
        tahun_sampai: sampai,
        metrik,
        mode,
    })
    golongan.forEach(g => params.append('golongan[]', g))

    try {
        const res  = await fetch(`/historis/chart?${params}`)
        const json = await res.json()

        if (!json.chart || !json.chart.labels || !json.chart.datasets) {
            console.error('Response tidak valid:', json)
            alert('Gagal memuat chart. Cek console.')
            return
        }

        // Warning mixed
        const warn = document.getElementById('warningMixed')
        if (json.warning) {
            warn.textContent = json.warning
            warn.style.display = 'block'
        } else {
            warn.style.display = 'none'
        }

        renderChart(json.chart)
        document.getElementById('cardChart').style.display = 'block'

    } catch(e) {
        console.error('Fetch error:', e)
        alert('Error: ' + e.message)
    }
}

function renderChart(data) {
    const ctx = document.getElementById('chartHistoris')
    if (chartInstance) chartInstance.destroy()

    const satuan = document.getElementById('filterMetrik')
        .selectedOptions[0]?.dataset?.satuan ?? ''

    const metrikNama = document.getElementById('filterMetrik')
        .selectedOptions[0]?.text ?? ''

    document.getElementById('chartTitle').textContent =
        `${metrikNama} — ${data.mode.toUpperCase()} (${satuan})`

    // Limit label kalau terlalu banyak
    const maxLabels = 60
    const step = data.labels.length > maxLabels
        ? Math.ceil(data.labels.length / maxLabels)
        : 1

    chartInstance = new Chart(ctx, {
        type: currentType,
        data: {
            labels: data.labels,
            datasets: data.datasets.map(d => ({
                label:           d.label,
                data:            d.data,
                borderColor:     d.borderColor,
                backgroundColor: currentType === 'bar' ? d.backgroundColor : 'transparent',
                tension:         0.4,
                fill:            false,
                pointRadius:     data.labels.length > 50 ? 0 : 3,
                spanGaps:        true,
                borderWidth:     2,
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: '#ccc', font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const v = ctx.raw
                            if (v === null || v === undefined) return `${ctx.dataset.label}: —`
                            return `${ctx.dataset.label}: ${v.toLocaleString('id-ID')} ${satuan}`
                        }
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: '#333' },
                    ticks: {
                        color: '#888',
                        callback: v => v >= 1_000_000
                            ? (v/1_000_000).toFixed(1) + 'M'
                            : v.toLocaleString('id-ID')
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#888',
                        maxRotation: 45,
                        // Tampilkan sebagian label kalau terlalu padat
                        callback: function(val, idx) {
                            return idx % step === 0 ? this.getLabelForValue(val) : ''
                        }
                    }
                }
            }
        }
    })
}

// Toggle bar/line
document.getElementById('btnToggleType')?.addEventListener('click', function() {
    currentType = currentType === 'bar' ? 'line' : 'bar'
    this.textContent = currentType === 'bar' ? 'Bar' : 'Line'
    if (chartInstance) loadChart()
})

document.getElementById('btnLoadChart')?.addEventListener('click', loadChart)

// Update metrik tersedia saat tahun berubah
async function updateMetrik() {
    const dari   = document.getElementById('filterTahunDari').value
    const sampai = document.getElementById('filterTahunSampai').value
    const res    = await fetch(`/historis/metrik?dari=${dari}&sampai=${sampai}`)
    const list   = await res.json()
    const sel    = document.getElementById('filterMetrik')
    const current = sel.value
    sel.innerHTML = list.map(m =>
        `<option value="${m.kode}" data-satuan="${m.satuan}" ${m.kode===current?'selected':''}>${m.nama}</option>`
    ).join('')
}

document.getElementById('filterTahunDari')?.addEventListener('change', updateMetrik)
document.getElementById('filterTahunSampai')?.addEventListener('change', updateMetrik)