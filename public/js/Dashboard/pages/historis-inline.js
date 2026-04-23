/**
 * historis-inline.js — All-in-One Script for Historis Page
 * Handles: Chart Historis (1997-2025) + Entry Panel (2026+)
 * 
 * ⚠️ NO IIFE - Functions exposed to global scope for inline onclick compatibility
 */

'use strict';

// ════════════════════════════════════════════════════════════════════
// STATE — Chart Historis (prefix: hist_)
// ════════════════════════════════════════════════════════════════════

let histChart = null;
let histChartType = 'bar';
let histLastData = null;
let histTableVisible = false;

// ════════════════════════════════════════════════════════════════════
// STATE — Entry Panel (prefix: entry_)
// ════════════════════════════════════════════════════════════════════

let entryIsLocked = false;
let entryPanelOpen = false;

// ════════════════════════════════════════════════════════════════════
// CONSTANTS
// ════════════════════════════════════════════════════════════════════

const HIST_COLORS = {
    S: '#ff9800', R: '#00ff88', B: '#2196f3',
    I: '#e91e63', P: '#9c27b0', M: '#00bcd4',
    TOTAL: '#ffffff', JUMLAH: '#aaaaaa',
};

const ENTRY_METRIKS = [
    { kode: 'kwh_jual',  nama: 'KWH Jual',        satuan: 'KWH' },
    { kode: 'mwh_jual',  nama: 'MWH Jual',        satuan: 'MWH' },
    { kode: 'rupiah',    nama: 'Rupiah Penjualan', satuan: 'Rp'  },
    { kode: 'pelanggan', nama: 'Pelanggan',        satuan: 'org' },
    { kode: 'daya_va',   nama: 'Daya Tersambung',  satuan: 'VA'  },
];

// ════════════════════════════════════════════════════════════════════
// INIT — DOMContentLoaded
// ════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    // Init Chart Historis
    histInitChipToggle();
    histInitButtons();
    histInitTahunChange();
});

// ════════════════════════════════════════════════════════════════════
// SECTION 1: CHART HISTORIS FUNCTIONS (1997-2025)
// ════════════════════════════════════════════════════════════════════

function histInitChipToggle() {
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

function histInitButtons() {
    document.getElementById('histBtnGenerate')?.addEventListener('click', histLoadChart);
    document.getElementById('histBtnBar')?.addEventListener('click', () => {
        histSetChartType('bar');
        if (histLastData) histRenderChart(histLastData);
    });
    document.getElementById('histBtnLine')?.addEventListener('click', () => {
        histSetChartType('line');
        if (histLastData) histRenderChart(histLastData);
    });
}

function histSetChartType(type) {
    histChartType = type;
    document.getElementById('histBtnBar')?.classList.toggle('active', type === 'bar');
    document.getElementById('histBtnLine')?.classList.toggle('active', type === 'line');
}

function histInitTahunChange() {
    const updateMetrik = async () => {
        const dari = document.getElementById('histDari')?.value;
        const sampai = document.getElementById('histSampai')?.value;
        if (!dari || !sampai) return;

        try {
            const res = await fetch(`/historis/metrik?dari=${dari}&sampai=${sampai}`);
            const list = await res.json();
            const sel = document.getElementById('histMetrik');
            const cur = sel.value;
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

async function histLoadChart() {
    const dari = document.getElementById('histDari')?.value;
    const sampai = document.getElementById('histSampai')?.value;
    const metrik = document.getElementById('histMetrik')?.value;
    const mode = document.getElementById('histMode')?.value;
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

        const res = await fetch(`/historis/chart?${params}`);
        const json = await res.json();

        if (!json.chart?.labels || !json.chart?.datasets) {
            throw new Error('Response tidak valid: ' + JSON.stringify(json));
        }

        const warnEl = document.getElementById('histWarning');
        if (warnEl) {
            warnEl.textContent = json.warning ?? '';
            warnEl.style.display = json.warning ? 'block' : 'none';
        }

        histLastData = json.chart;
        histRenderChart(json.chart);
        histRenderTable(json.chart);
        document.getElementById('histCardChart').style.display = 'block';

    } catch (e) {
        console.error('histLoadChart error:', e);
        alert('Gagal memuat chart: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-chart-line"></i> Generate';
    }
}

function histRenderChart(data) {
    const ctx = document.getElementById('histChart');
    const satuan = document.getElementById('histMetrik')?.selectedOptions[0]?.dataset?.satuan ?? '';
    const nama = document.getElementById('histMetrik')?.selectedOptions[0]?.text ?? '';

    document.getElementById('histChartTitle').textContent = `${nama} — ${data.mode.toUpperCase()}`;
    document.getElementById('histChartSubtitle').textContent =
        `${document.getElementById('histDari')?.value} – ${document.getElementById('histSampai')?.value} | Satuan: ${satuan}`;

    if (histChart) histChart.destroy();

    const totalLabels = data.labels.length;
    const step = totalLabels > 80 ? Math.ceil(totalLabels / 60) : 1;

    histChart = new Chart(ctx, {
        type: histChartType,
        data: {
            labels: data.labels,
            datasets: data.datasets.map(d => ({
                label: d.label,
                data: d.data,
                borderColor: d.borderColor,
                backgroundColor: histChartType === 'bar' ? histHexToRgba(d.backgroundColor, 0.7) : 'transparent',
                tension: 0.35,
                fill: false,
                pointRadius: totalLabels > 60 ? 0 : 3,
                pointHoverRadius: 5,
                spanGaps: true,
                borderWidth: histChartType === 'bar' ? 0 : 2,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation: { duration: 400 },
            plugins: {
                legend: {
                    labels: { color: '#ccc', font: { size: 11 }, padding: 16 },
                },
                tooltip: {
                    backgroundColor: '#1e1e1e',
                    borderColor: '#333',
                    borderWidth: 1,
                    titleColor: '#ccc',
                    bodyColor: '#aaa',
                    callbacks: {
                        label: ctx => {
                            const v = ctx.raw;
                            if (v === null || v === undefined) return `${ctx.dataset.label}: —`;
                            return `${ctx.dataset.label}: ${histFormatNilai(v, satuan)} ${satuan}`;
                        },
                    },
                },
            },
            scales: {
                y: {
                    grid: { color: '#2a2a2a' },
                    ticks: { color: '#666', font: { size: 10 }, callback: v => histFormatNilai(v, satuan) },
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#666',
                        font: { size: 9 },
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

function histRenderTable(data) {
    const thead = document.getElementById('histThead');
    const tbody = document.getElementById('histTbody');
    const satuan = document.getElementById('histMetrik')?.selectedOptions[0]?.dataset?.satuan ?? '';
    if (!thead || !tbody) return;

    thead.innerHTML = `<tr>
        <th>Label</th>
        ${data.datasets.map(d => `<th style="color:${d.borderColor}">${d.label}</th>`).join('')}
    </tr>`;

    tbody.innerHTML = data.labels.map((lbl, i) =>
        `<tr>
            <td>${lbl}</td>
            ${data.datasets.map(d => {
                const v = d.data[i];
                return `<td>${v !== null && v !== undefined ? histFormatNilai(v, satuan) : '—'}</td>`;
            }).join('')}
        </tr>`
    ).join('');
}

// ════════════════════════════════════════════════════════════════════
// SECTION 2: ENTRY PANEL FUNCTIONS (2026+)
// ════════════════════════════════════════════════════════════════════

// ✅ Global function for inline onclick
window.toggleEntryPanel = function() {
    const panel = document.getElementById('entryPanel');
    const chevron = document.getElementById('entryChevron');
    if (!panel) return;

    entryPanelOpen = !entryPanelOpen;
    panel.style.display = entryPanelOpen ? 'block' : 'none';
    if (chevron) chevron.style.transform = entryPanelOpen ? 'rotate(180deg)' : 'rotate(0)';
};

// ✅ Global function for inline onclick
window.loadEntryData = async function() {
    const tahun = document.getElementById('entryTahun')?.value;
    const bulan = document.getElementById('entryBulan')?.value;

    entrySetStatus('Memuat data...', 'info');
    document.getElementById('entryTableWrap').style.display = 'none';
    document.getElementById('entryLocked').style.display = 'none';
    document.getElementById('entrySaveBtn').style.display = 'none';

    try {
        const res = await fetch(`/fact-historis/existing-data?tahun=${tahun}&bulan=${bulan}`);
        const contentType = res.headers.get('content-type');
        
        if (!contentType?.includes('application/json')) {
            const text = await res.text();
            if (text.trim().startsWith('<!DOCTYPE')) {
                throw new Error('Session mungkin habis. Silakan refresh halaman.');
            }
        }

        const result = await res.json();

        if (!res.ok || result.locked || !result.success) {
            document.getElementById('entryLocked').style.display = 'block';
            entryIsLocked = true;
            entrySetStatus(result.message || 'Data terkunci', 'error');
            return;
        }

        entryIsLocked = false;
        entryBuildTable(result.data || {});
        document.getElementById('entryTableWrap').style.display = 'block';
        document.getElementById('entrySaveBtn').style.display = 'inline-flex';
        entrySetStatus(`Data dimuat: ${entryGetBulanName(bulan)} ${tahun}`, 'success');

    } catch (e) {
        console.error('loadEntryData error:', e);
        entrySetStatus('Gagal memuat: ' + e.message, 'error');
    }
};

// ✅ Global function for inline onclick
window.saveEntryData = async function() {
    if (entryIsLocked) {
        entrySetStatus('Data terkunci', 'error');
        return;
    }

    const tahun = document.getElementById('entryTahun')?.value;
    const bulan = document.getElementById('entryBulan')?.value;
    const entries = [];

    document.querySelectorAll('.entry-input').forEach(inp => {
        const nilai = parseFloat(inp.value);
        const metrik = inp.dataset.metrik;
        const golongan = inp.dataset.golongan;
        
        if (!isNaN(nilai) && metrik && golongan) {
            entries.push({
                metrik_kode: metrik,
                golongan_kode: golongan,
                nilai: nilai,
            });
        }
    });

    if (!entries.length) {
        entrySetStatus('Tidak ada data valid untuk disimpan', 'warning');
        return;
    }

    const btn = document.getElementById('entrySaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf) throw new Error('CSRF token tidak ditemukan');

        // ✅ FIX: Kirim dengan key "data"
        const payload = {
            tahun: parseInt(tahun),
            bulan: parseInt(bulan),
            data: entries,  // ← INI PERUBAHAN UTAMANYA
        };

        console.log('Sending payload:', payload); // Debug log

        const res = await fetch('/fact-historis', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const result = await res.json();

        if (!res.ok) {
            throw new Error(result.message || `HTTP ${res.status}`);
        }

        if (result.success) {
            entrySetStatus(`✓ ${result.message}`, 'success');
            // ... success handling
        }

    } catch (e) {
        console.error('saveEntryData error:', e);
        entrySetStatus('Gagal: ' + e.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
    }
};

function entryBuildTable(data) {
    const thead = document.getElementById('entryThead');
    const tbody = document.getElementById('entryTbody');
    if (!thead || !tbody) return;

    const golongans = [...document.querySelectorAll('.cb-golongan')].map(c => ({
        kode: c.value,
        nama: c.closest('label')?.textContent?.trim() || c.value,
    }));

    // Header
    thead.innerHTML = `<tr>
        <th style="text-align:left; padding:8px 10px;">Metrik</th>
        <th style="padding:8px 6px;">Satuan</th>
        ${golongans.map(g => `<th style="text-align:center; min-width:110px;">${g.nama}</th>`).join('')}
    </tr>`;

    // Body - ✅ Pastikan setiap input punya data-metrik dan data-golongan
    tbody.innerHTML = ENTRY_METRIKS.map(m => {
        const existingRows = data[m.kode] || [];
        return `<tr>
            <td style="padding:8px 10px; color:#ccc;">${m.nama}</td>
            <td style="padding:8px 6px; color:var(--text-muted); font-size:0.75rem;">${m.satuan}</td>
            ${golongans.map(g => {
                const row = existingRows.find(r => r.golongan_kode === g.kode);
                const value = row?.nilai ?? '';
                return `<td style="padding:4px 6px;">
                    <input type="number" step="any"
                        class="entry-input ${value ? 'has-value' : ''}"
                        data-metrik="${m.kode}"
                        data-golongan="${g.kode}"
                        value="${value}"
                        placeholder="0">
                </td>`;
            }).join('')}
        </tr>`;
    }).join('');
}

function entrySetStatus(msg, type) {
    const el = document.getElementById('entryStatus');
    if (!el) return;
    
    const styles = {
        success: 'background:rgba(0,255,136,0.1); border:1px solid rgba(0,255,136,0.3); color:#00ff88;',
        error: 'background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#ff4444;',
        warning: 'background:rgba(255,152,0,0.1); border:1px solid rgba(255,152,0,0.3); color:#ff9800;',
        info: 'background:rgba(33,150,243,0.1); border:1px solid rgba(33,150,243,0.3); color:#2196f3;',
    };
    
    el.style.cssText = styles[type] || styles.info;
    el.textContent = msg;
    el.style.display = 'block';
    
    if (type === 'success') {
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    }
}

function entryGetBulanName(num) {
    const names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return names[+num] || '';
}

// ════════════════════════════════════════════════════════════════════
// GLOBAL UTILS (for inline onclick compatibility)
// ════════════════════════════════════════════════════════════════════

// ✅ Global function for inline onclick
window.toggleTable = function() {
    const card = document.getElementById('histCardTable');
    histTableVisible = !histTableVisible;
    card.style.display = histTableVisible ? 'block' : 'none';
};

function histFormatNilai(v, satuan) {
    if (v === null || v === undefined) return '—';
    const abs = Math.abs(v);
    if (abs >= 1_000_000_000) return (v / 1_000_000_000).toFixed(2) + ' M';
    if (abs >= 1_000_000) return (v / 1_000_000).toFixed(2) + ' Jt';
    if (abs >= 1_000) return (v / 1_000).toFixed(1) + ' rb';
    return v.toLocaleString('id-ID', { maximumFractionDigits: 2 });
}

function histHexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}