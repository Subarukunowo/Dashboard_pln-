/**
 * HistorisChart.js — Chart Historis Handler (1997-2025)
 * Path: public/js/Dashboard/charts/HistorisChart.js
 * 
 * ✨ UPDATE: Display daya_va dalam kVA (VA → kVA conversion, frontend-only)
 */
'use strict';

(function () {

    // ── STATE ────────────────────────────────────────────────────────
    const State = {
        chart:        null,
        type:         'bar',
        lastData:     null,
        tableVisible: false,
        initialized:  false,
    };

    const GROWTH_METRICS = ['pertumbuhan_yoy', 'growth'];

    // Warna per kode golongan — dipakai chart DAN chip indicator
    const GOL_COLORS = {
        S:     '#00bcd4',
        R:     '#00c853',
        B:     '#ff9800',
        I:     '#f44336',
        P:     '#ce93d8',
        M:     '#f06292',
        TOTAL: '#ffc107',
    };

    // ── DOM REFS ─────────────────────────────────────────────────────
    function el(id) { return document.getElementById(id); }

    // ── HELPERS ──────────────────────────────────────────────────────

    function isGrowth() {
        return GROWTH_METRICS.includes(el('histMetrik')?.value?.toLowerCase() ?? '');
    }

    // ➕ NEW: Cek apakah metrik saat ini adalah daya_va
    function isDayaVA() {
        const metrik = el('histMetrik')?.value?.toLowerCase() ?? '';
        return metrik === 'daya_va';
    }

    // ➕ NEW: Konversi nilai VA → kVA hanya untuk display
    function toDisplayValue(v) {
        if (v === null || v === undefined || isNaN(v)) return v;
        return isDayaVA() ? v / 1000 : v;
    }

    function getSatuan() {
        if (isGrowth()) return '%';
        // ➕ OVERRIDE: tampilkan kVA jika metrik daya_va
        if (isDayaVA()) return 'kVA';
        return el('histMetrik')?.selectedOptions?.[0]?.dataset?.satuan ?? '';
    }

    function getMetrikNama() {
        const base = el('histMetrik')?.selectedOptions?.[0]?.text?.split('(')[0]?.trim() || 'Metrik';
        // ➕ OVERRIDE: ganti (VA) → (kVA) di judul
        return isDayaVA() ? base.replace(/\(VA\)$/i, '(kVA)') : base;
    }

    // ── KUNCI: Baca langsung dari checkbox state di DOM ──────────────
    function getSelectedGolongan() {
        const checked = [...document.querySelectorAll('.cb-golongan:checked')].map(c => c.value);
        console.log('📋 Selected golongan:', checked);
        return checked;
    }

    function formatNilai(v, satuan) {
        if (v === null || v === undefined || isNaN(v)) return '—';
        
        // ➕ APPLY konversi VA→kVA sebelum format
        const displayVal = toDisplayValue(v);
        const displaySat = isDayaVA() ? 'kVA' : satuan;

        if (displaySat === '%') return displayVal.toFixed(2) + '%';
        if (displaySat === 'Rp' || displaySat === 'Rp/KWh') 
            return 'Rp ' + Math.abs(displayVal).toLocaleString('id-ID');
        
        const abs = Math.abs(displayVal);
        if (abs >= 1e9) return (displayVal / 1e9).toFixed(2) + ' M';
        if (abs >= 1e6) return (displayVal / 1e6).toFixed(2) + ' Jt';
        if (abs >= 1e3) return (displayVal / 1e3).toFixed(1) + ' rb';
        return displayVal.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function hexToRgba(hex, alpha = 0.75) {
        if (!hex) return `rgba(255,255,255,${alpha})`;
        const c = hex.replace('#', '');
        const r = parseInt(c.substring(0, 2), 16);
        const g = parseInt(c.substring(2, 4), 16);
        const b = parseInt(c.substring(4, 6), 16);
        return `rgba(${r},${g},${b},${alpha})`;
    }

    // ── UI ────────────────────────────────────────────────────────────

    function setWarning(msg, type = 'warning') {
        const box = el('histWarning');
        if (!box) return;
        if (!msg) { box.style.display = 'none'; return; }
        const styles = {
            warning: 'background:rgba(255,152,0,0.08);border:1px solid rgba(255,152,0,0.3);color:#ff9800;',
            error:   'background:rgba(244,67,54,0.08);border:1px solid rgba(244,67,54,0.3);color:#f44336;',
            info:    'background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.3);color:#00ff88;',
        };
        box.style.cssText = `display:block;border-radius:7px;padding:8px 14px;font-size:0.78rem;${styles[type] || styles.warning}`;
        box.textContent = msg;
        if (type !== 'error') setTimeout(() => { if (box.textContent === msg) box.style.display = 'none'; }, 5000);
    }

    function setLoading(loading) {
        const btn = el('histBtnGenerate');
        if (!btn) return;
        btn.disabled = loading;
        btn.innerHTML = loading
            ? '<i class="fa-solid fa-spinner fa-spin"></i> Loading...'
            : '<i class="fa-solid fa-chart-line"></i> Generate';
    }

    function updateGrowthHint() {
        const mode = el('histMode');
        if (!mode) return;
        const g = isGrowth();
        mode.disabled = g;
        mode.title = g ? 'Growth/YoY selalu mode tahunan' : '';
        if (g) mode.value = 'tahunan';
    }

    // ── CHIP SETUP ────────────────────────────────────────────────────

    function setupChips() {
        const container = document.querySelector('.golongan-chips');
        if (!container || container._chipsBound) return;
        container._chipsBound = true;

        container.addEventListener('click', function (e) {
            const chip = e.target.closest('.hist-gol-chip');
            if (!chip) return;

            const cb = chip.querySelector('.cb-golongan');
            if (!cb) return;

            cb.checked = !cb.checked;
            chip.classList.toggle('checked', cb.checked);

            console.log(`🏷️ ${cb.value}: ${cb.checked ? '✓ ON' : '✗ OFF'} | Total checked:`, getSelectedGolongan().length);
        });

        el('histSelectAll')?.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('.cb-golongan').forEach(cb => {
                cb.checked = true;
                cb.closest('.hist-gol-chip')?.classList.add('checked');
            });
        });

        el('histClearAll')?.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('.cb-golongan').forEach(cb => {
                cb.checked = false;
                cb.closest('.hist-gol-chip')?.classList.remove('checked');
            });
            const totalCb = document.querySelector('.cb-golongan[value="TOTAL"]');
            if (totalCb) {
                totalCb.checked = true;
                totalCb.closest('.hist-gol-chip')?.classList.add('checked');
            }
        });

        console.log('✅ Chips bound via delegation');
    }

    // ── CHART RENDERER ────────────────────────────────────────────────

    function renderChart(data) {
        const canvas = el('histChart');
        if (!canvas) { setWarning('Canvas tidak ditemukan', 'error'); return false; }

        const satuan   = getSatuan();
        const growth   = isGrowth();
        const nLabels  = data.labels?.length || 0;
        const step     = nLabels > 80 ? Math.ceil(nLabels / 60) : 1;

        // Update judul & subtitle
        if (el('chart-title'))       el('chart-title').textContent       = getMetrikNama();
        if (el('histChartSubtitle')) el('histChartSubtitle').textContent =
            `${el('histDari')?.value || '?'} – ${el('histSampai')?.value || '?'} | Satuan: ${satuan || '-'}`;

        // Destroy chart lama
        if (State.chart) { State.chart.destroy(); State.chart = null; }

        try {
            State.chart = new Chart(canvas, {
                type: State.type,
                data: {
                    labels: data.labels || [],
                    datasets: (data.datasets || []).map(d => ({
                        label:           d.label,
                        data:            d.data || [],
                        borderColor:     d.borderColor || '#fff',
                        backgroundColor: State.type === 'bar'
                            ? hexToRgba(d.backgroundColor || d.borderColor || '#fff', 0.75)
                            : 'transparent',
                        tension:          0.35,
                        fill:             false,
                        pointRadius:      nLabels > 60 ? 0 : 3,
                        pointHoverRadius: 5,
                        spanGaps:         true,
                        borderWidth:      State.type === 'bar' ? 0 : 2,
                    })),
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    animation: { duration: 400 },
                    plugins: {
                        legend: {
                            labels: { color: '#bbb', font: { size: 11 }, padding: 16, usePointStyle: true }
                        },
                        tooltip: {
                            backgroundColor: '#1a1a1a', borderColor: '#333', borderWidth: 1,
                            titleColor: '#ccc', bodyColor: '#aaa', padding: 10,
                            callbacks: {
                                label: ctx => {
                                    const v = ctx.raw, lbl = ctx.dataset.label;
                                    if (v == null) return `${lbl}: —`;
                                    if (growth) return `${lbl}: ${v >= 0 ? '+' : ''}${v.toFixed(2)}%`;
                                    
                                    // ➕ FORMAT dengan konversi VA→kVA jika perlu
                                    const displayVal = toDisplayValue(v);
                                    const displaySat = isDayaVA() ? 'kVA' : satuan;
                                    return `${lbl}: ${formatNilai(displayVal, displaySat)}${displaySat ? ' ' + displaySat : ''}`;
                                }
                            }
                        },
                    },
                    scales: {
                        y: {
                            grid: {
                                color: growth
                                    ? ctx => ctx.tick.value === 0 ? 'rgba(255,255,255,0.2)' : '#252525'
                                    : '#252525',
                            },
                            ticks: {
                                color: '#555', font: { size: 10 },
                                callback: v => {
                                    if (growth) return v.toFixed(1) + '%';
                                    // ➕ APPLY konversi untuk skala Y
                                    const displayVal = toDisplayValue(v);
                                    const displaySat = isDayaVA() ? 'kVA' : satuan;
                                    return formatNilai(displayVal, displaySat);
                                },
                            },
                            beginAtZero: !growth,
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#555', font: { size: 9 }, maxRotation: 45, minRotation: 45,
                                callback: function (val, idx) {
                                    return idx % step === 0 ? this.getLabelForValue(val) : '';
                                },
                            },
                        },
                    },
                },
            });
            return true;
        } catch (e) {
            console.error('❌ Chart error:', e);
            setWarning(`Render chart gagal: ${e.message}`, 'error');
            return false;
        }
    }

    // ── TABLE RENDERER ────────────────────────────────────────────────

    function renderTable(data) {
        const satuan = getSatuan();
        const growth = isGrowth();
        const thead  = el('histThead');
        const tbody  = el('histTbody');

        if (thead) {
            thead.innerHTML = `<tr>
                <th style="text-align:left;padding:9px 12px;position:sticky;left:0;background:#1a1a1a;z-index:2;min-width:90px;">Periode</th>
                ${(data.datasets || []).map(d =>
                    `<th style="color:${d.borderColor||'#fff'};min-width:100px;padding:9px 8px;">${d.label}</th>`
                ).join('')}
            </tr>`;
        }

        if (tbody) {
            if (!data.labels?.length) {
                tbody.innerHTML = '<tr><td colspan="99" style="text-align:center;padding:24px;color:#444;">Tidak ada data</td></tr>';
                return;
            }
            tbody.innerHTML = (data.labels || []).map((lbl, i) => `
                <tr>
                    <td style="padding:7px 12px;color:#bbb;position:sticky;left:0;background:#1a1a1a;z-index:1;font-weight:500;">${lbl}</td>
                    ${(data.datasets || []).map(d => {
                        const v = d.data?.[i];
                        let cell = '<span style="color:#333">—</span>';
                        
                        if (v != null && !isNaN(v)) {
                            if (growth) {
                                const c = v >= 0 ? '#00c853' : '#f44336';
                                cell = `<span style="color:${c};font-weight:600;">${v >= 0 ? '+' : ''}${v.toFixed(2)}%</span>`;
                            } else {
                                // ➕ APPLY konversi VA→kVA untuk display di tabel
                                const displayVal = toDisplayValue(v);
                                const displaySat = isDayaVA() ? 'kVA' : satuan;
                                cell = formatNilai(displayVal, displaySat);
                            }
                        }
                        return `<td style="text-align:right;padding:7px 8px;color:#888;font-variant-numeric:tabular-nums;font-size:0.82rem;">${cell}</td>`;
                    }).join('')}
                </tr>
            `).join('');
        }
    }

    // ── TABLE VISIBILITY ─────────────────────────────────────────────

    function showTable() {
        State.tableVisible = true;
        const card = el('histCardTable');
        if (card) card.style.display = 'block';
        const btn = el('histBtnTable');
        if (btn) { btn.classList.add('active'); btn.innerHTML = '<i class="fa-solid fa-table"></i> Tabel ✓'; }
        setTimeout(() => card?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 60);
    }

    function hideTable() {
        State.tableVisible = false;
        const card = el('histCardTable');
        if (card) card.style.display = 'none';
        const btn = el('histBtnTable');
        if (btn) { btn.classList.remove('active'); btn.innerHTML = '<i class="fa-solid fa-table"></i> Tabel'; }
    }

    // ── LOAD DATA ─────────────────────────────────────────────────────

    async function loadChart() {
        const dari     = el('histDari')?.value;
        const sampai   = el('histSampai')?.value;
        const metrik   = el('histMetrik')?.value;
        const mode     = el('histMode')?.value;
        const golongan = getSelectedGolongan();

        console.log('🚀 loadChart:', { dari, sampai, metrik, mode, golongan });

        if (!dari || !sampai || !metrik) {
            setWarning('⚠️ Lengkapi filter: Metrik, Dari, dan Sampai'); return;
        }
        if (!golongan.length) {
            setWarning('⚠️ Pilih minimal satu golongan tarif'); return;
        }

        setLoading(true);
        setWarning('');

        try {
            const params = new URLSearchParams({ tahun_dari: dari, tahun_sampai: sampai, metrik, mode });
            golongan.forEach(g => params.append('golongan[]', g));

            const res = await fetch(`/historis/chart?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const json = await res.json();
            if (!json?.chart?.labels || !json?.chart?.datasets) throw new Error('Format response tidak valid');

            if (json.warning) setWarning(json.warning, 'warning');

            State.lastData = json.chart;
            renderTable(json.chart);
            const ok = renderChart(json.chart);

            if (ok) {
                const chartCard = el('histCardChart');
                if (chartCard) {
                    chartCard.style.display = 'block';
                    setTimeout(() => State.chart?.resize?.(), 100);
                }
                if (State.tableVisible) showTable();
            }

        } catch (e) {
            console.error('❌ loadChart error:', e);
            setWarning(`Gagal memuat data: ${e.message}`, 'error');
        } finally {
            setLoading(false);
        }
    }

    // ── CHART TYPE ────────────────────────────────────────────────────

    function setChartType(type) {
        State.type = type;
        el('histBtnBar')?.classList.toggle('active',  type === 'bar');
        el('histBtnLine')?.classList.toggle('active', type === 'line');
        if (State.lastData) renderChart(State.lastData);
    }

    // ── METRIK LIST UPDATE ────────────────────────────────────────────

    async function updateMetrikList() {
        const dari = el('histDari')?.value, sampai = el('histSampai')?.value;
        if (!dari || !sampai) return;
        try {
            const res = await fetch(`/historis/metrik?dari=${dari}&sampai=${sampai}`);
            if (!res.ok) return;
            const list = await res.json();
            const sel = el('histMetrik');
            const cur = sel?.value;
            if (sel && list?.length) {
                sel.innerHTML = list.map(m => {
                    // ➕ OVERRIDE: tampilkan (kVA) untuk daya_va di dropdown
                    const isDaya = m.kode.toLowerCase() === 'daya_va';
                    const displaySatuan = isDaya ? 'kVA' : m.satuan;
                    const displayName = isDaya 
                        ? m.nama.replace(/\(VA\)$/i, '(kVA)') 
                        : m.nama;
                    return `<option value="${m.kode}" data-satuan="${displaySatuan}" ${m.kode === cur ? 'selected' : ''}>
                        ${displayName} (${displaySatuan})
                    </option>`;
                }).join('');
            }
            updateGrowthHint();
        } catch (e) { console.warn('⚠️ updateMetrikList error:', e); }
    }

    // ── EVENT BINDING ─────────────────────────────────────────────────

    function bindEvents() {
        if (State.initialized) return;

        // ✅ Pastikan event listener untuk Generate tetap ada
        el('histBtnGenerate')?.addEventListener('click', e => { 
            e.preventDefault(); 
            loadChart(); 
        });
        
        el('histBtnBar')?.addEventListener('click',      e => { e.preventDefault(); setChartType('bar'); });
        el('histBtnLine')?.addEventListener('click',     e => { e.preventDefault(); setChartType('line'); });
        el('histBtnTable')?.addEventListener('click',    e => { e.preventDefault(); State.tableVisible ? hideTable() : showTable(); });
        el('histBtnHideTable')?.addEventListener('click',e => { e.preventDefault(); hideTable(); });

        el('histMetrik')?.addEventListener('change', updateGrowthHint);
        el('histDari')?.addEventListener('change',   updateMetrikList);
        el('histSampai')?.addEventListener('change', updateMetrikList);

        setupChips();

        State.initialized = true;
        console.log('✅ HistorisChart events bound');
    }

    // ── PUBLIC API ────────────────────────────────────────────────────

    window.HistorisChart = {
        load: loadChart, render: renderChart, renderTable,
        setChartType, showTable, hideTable,
        getState: () => ({ ...State }),
    };

    // ── INIT ──────────────────────────────────────────────────────────

    function init() {
        if (State.initialized) return;
        if (typeof Chart === 'undefined') { setTimeout(init, 300); return; }
        bindEvents();
        updateGrowthHint();
        console.log('✅ HistorisChart.js ready (VA→kVA display enabled)');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();