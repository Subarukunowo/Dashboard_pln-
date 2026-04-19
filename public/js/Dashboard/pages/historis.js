/**
 * historis.js — Data Historis PLN Batam 1997-2025
 * Vanilla JS, tidak pakai module agar kompatibel dengan blade @section('scripts')
 */

// ── State ─────────────────────────────────────────────────────────────────────
let histChart     = null;
let histChartType = 'bar';
let histLastData  = null; // simpan data terakhir untuk toggle bar/line
let tableVisible  = false;

// ── Konstanta warna per golongan ───────────────────────────────────────────────
const HIST_COLORS = {
    S: '#ff9800', R: '#00ff88', B: '#2196f3',
    I: '#e91e63', P: '#9c27b0', M: '#00bcd4',
    TOTAL: '#ffffff', JUMLAH: '#aaaaaa',
};

// ── Init ───────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initChipToggle();
    initButtons();
    initTahunChange();
});

// ── Chip golongan toggle ───────────────────────────────────────────────────────
function initChipToggle() {
    document.querySelectorAll('.hist-gol-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const cb = chip.querySelector('input[type=checkbox]');
            cb.checked = !cb.checked;
            chip.classList.toggle('checked', cb.checked);
        });
    });

    document.getElementById('histSelectAll')?.addEventListener('click', () => {
        document.querySelectorAll('.cb-golongan').forEach(cb => {
            cb.checked = true;
            cb.closest('.hist-gol-chip')?.classList.add('checked');
        });
    });

    document.getElementById('histClearAll')?.addEventListener('click', () => {
        document.querySelectorAll('.cb-golongan').forEach(cb => {
            cb.checked = false;
            cb.closest('.hist-gol-chip')?.classList.remove('checked');
        });
    });
}

// ── Buttons ────────────────────────────────────────────────────────────────────
function initButtons() {
    document.getElementById('histBtnGenerate')?.addEventListener('click', loadChart);

    document.getElementById('histBtnBar')?.addEventListener('click', () => {
        setChartType('bar');
        if (histLastData) renderChart(histLastData);
    });

    document.getElementById('histBtnLine')?.addEventListener('click', () => {
        setChartType('line');
        if (histLastData) renderChart(histLastData);
    });
}

function setChartType(type) {
    histChartType = type;
    document.getElementById('histBtnBar')?.classList.toggle('active', type === 'bar');
    document.getElementById('histBtnLine')?.classList.toggle('active', type === 'line');
}

// ── Auto-update metrik saat tahun berubah ─────────────────────────────────────
function initTahunChange() {
    const updateMetrik = async () => {
        const dari   = document.getElementById('histDari')?.value;
        const sampai = document.getElementById('histSampai')?.value;
        if (!dari || !sampai) return;

        try {
            const res  = await fetch(`/historis/metrik?dari=${dari}&sampai=${sampai}`);
            const list = await res.json();
            const sel  = document.getElementById('histMetrik');
            const cur  = sel.value;
            sel.innerHTML = list.map(m =>
                `<option value="${m.kode}" data-satuan="${m.satuan}"
                    ${m.kode === cur ? 'selected' : ''}>
                    ${m.nama} (${m.satuan})
                </option>`
            ).join('');
        } catch (e) {
            console.warn('Gagal update metrik:', e);
        }
    };

    document.getElementById('histDari')?.addEventListener('change', updateMetrik);
    document.getElementById('histSampai')?.addEventListener('change', updateMetrik);
}

// ── Load & render chart ────────────────────────────────────────────────────────
async function loadChart() {
    const dari     = document.getElementById('histDari')?.value;
    const sampai   = document.getElementById('histSampai')?.value;
    const metrik   = document.getElementById('histMetrik')?.value;
    const mode     = document.getElementById('histMode')?.value;
    const golongan = [...document.querySelectorAll('.cb-golongan:checked')].map(c => c.value);

    if (!golongan.length) {
        Swal?.fire({ icon: 'warning', title: 'Pilih minimal satu golongan', timer: 2000, showConfirmButton: false });
        return;
    }

    const btn = document.getElementById('histBtnGenerate');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    try {
        const params = new URLSearchParams({ tahun_dari: dari, tahun_sampai: sampai, metrik, mode });
        golongan.forEach(g => params.append('golongan[]', g));

        const res  = await fetch(`/historis/chart?${params}`);
        const json = await res.json();

        if (!json.chart?.labels || !json.chart?.datasets) {
            throw new Error('Response tidak valid: ' + JSON.stringify(json));
        }

        // Warning mixed
        const warnEl = document.getElementById('histWarning');
        if (warnEl) {
            warnEl.textContent  = json.warning ?? '';
            warnEl.style.display = json.warning ? 'block' : 'none';
        }

        histLastData = json.chart;
        renderChart(json.chart);
        renderTable(json.chart);

        document.getElementById('histCardChart').style.display = 'block';

    } catch (e) {
        console.error('loadChart error:', e);
        alert('Gagal memuat chart: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-chart-line"></i> Generate';
    }
}

// ── Render Chart.js ────────────────────────────────────────────────────────────
function renderChart(data) {
    const ctx    = document.getElementById('histChart');
    const satuan = document.getElementById('histMetrik')?.selectedOptions[0]?.dataset?.satuan ?? '';
    const nama   = document.getElementById('histMetrik')?.selectedOptions[0]?.text ?? '';

    // Update judul
    document.getElementById('histChartTitle').textContent =
        `${nama} — ${data.mode.toUpperCase()}`;
    document.getElementById('histChartSubtitle').textContent =
        `${document.getElementById('histDari')?.value} – ${document.getElementById('histSampai')?.value} | Satuan: ${satuan}`;

    if (histChart) histChart.destroy();

    // Batasi label X kalau terlalu banyak
    const totalLabels = data.labels.length;
    const step = totalLabels > 80 ? Math.ceil(totalLabels / 60) : 1;

    histChart = new Chart(ctx, {
        type: histChartType,
        data: {
            labels: data.labels,
            datasets: data.datasets.map(d => ({
                label:            d.label,
                data:             d.data,
                borderColor:      d.borderColor,
                backgroundColor:  histChartType === 'bar'
                    ? hexToRgba(d.backgroundColor, 0.7)
                    : 'transparent',
                tension:          0.35,
                fill:             false,
                pointRadius:      totalLabels > 60 ? 0 : 3,
                pointHoverRadius: 5,
                spanGaps:         true,
                borderWidth:      histChartType === 'bar' ? 0 : 2,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation:   { duration: 400 },
            plugins: {
                legend: {
                    labels: {
                        color: '#ccc',
                        font:  { size: 11 },
                        padding: 16,
                    },
                },
                tooltip: {
                    backgroundColor: '#1e1e1e',
                    borderColor:     '#333',
                    borderWidth:     1,
                    titleColor:      '#ccc',
                    bodyColor:       '#aaa',
                    callbacks: {
                        label: ctx => {
                            const v = ctx.raw;
                            if (v === null || v === undefined) return `${ctx.dataset.label}: —`;
                            return `${ctx.dataset.label}: ${formatNilai(v, satuan)} ${satuan}`;
                        },
                    },
                },
            },
            scales: {
                y: {
                    grid:  { color: '#2a2a2a' },
                    ticks: {
                        color: '#666',
                        font:  { size: 10 },
                        callback: v => formatNilai(v, satuan),
                    },
                },
                x: {
                    grid:  { display: false },
                    ticks: {
                        color:       '#666',
                        font:        { size: 9 },
                        maxRotation: 45,
                        callback: function(val, idx) {
                            return idx % step === 0 ? this.getLabelForValue(val) : '';
                        },
                    },
                },
            },
        },
    });
}

// ── Render tabel ───────────────────────────────────────────────────────────────
function renderTable(data) {
    const thead = document.getElementById('histThead');
    const tbody = document.getElementById('histTbody');
    const satuan = document.getElementById('histMetrik')?.selectedOptions[0]?.dataset?.satuan ?? '';
    if (!thead || !tbody) return;

    // Header: Label | Golongan1 | Golongan2 | ...
    thead.innerHTML = `<tr>
        <th>Label</th>
        ${data.datasets.map(d =>
            `<th style="color:${d.borderColor}">${d.label}</th>`
        ).join('')}
    </tr>`;

    // Body
    tbody.innerHTML = data.labels.map((lbl, i) =>
        `<tr>
            <td>${lbl}</td>
            ${data.datasets.map(d => {
                const v = d.data[i];
                return `<td>${v !== null && v !== undefined ? formatNilai(v, satuan) : '—'}</td>`;
            }).join('')}
        </tr>`
    ).join('');
}

// ── Toggle tabel ───────────────────────────────────────────────────────────────
function toggleTable() {
    const card = document.getElementById('histCardTable');
    tableVisible = !tableVisible;
    card.style.display = tableVisible ? 'block' : 'none';
}

// ── Utils ──────────────────────────────────────────────────────────────────────
function formatNilai(v, satuan) {
    if (v === null || v === undefined) return '—';
    const abs = Math.abs(v);
    if (abs >= 1_000_000_000) return (v / 1_000_000_000).toFixed(2) + ' M';
    if (abs >= 1_000_000)     return (v / 1_000_000).toFixed(2) + ' Jt';
    if (abs >= 1_000)         return (v / 1_000).toFixed(1) + ' rb';
    return v.toLocaleString('id-ID', { maximumFractionDigits: 2 });
}

function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1,3), 16);
    const g = parseInt(hex.slice(3,5), 16);
    const b = parseInt(hex.slice(5,7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}