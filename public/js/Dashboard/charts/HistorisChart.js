/**
 * HistorisChart.js — Chart Historis Handler (1997-2025)
 * Path: public/js/Dashboard/charts/HistorisChart.js
 */
'use strict';

(function() {
    // ─────────────────────────────────────────────────────────────────
    // 📦 STATE & CONFIG
    // ─────────────────────────────────────────────────────────────────

    const State = {
        chart: null,
        type: 'bar',
        lastData: null,
        tableVisible: false,
        initialized: false,
    };

    // Metrik yang satuannya % dan dihitung on-the-fly
    const GROWTH_METRICS = ['pertumbuhan_yoy', 'growth'];

    const Config = {
        colors: {
            S: '#ff9800', R: '#00ff88', B: '#2196f3',
            I: '#e91e63', P: '#9c27b0', M: '#00bcd4',
            TOTAL: '#ffffff', JUMLAH: '#aaaaaa', YOY: '#00bcd4',
        },
        selectors: {
            chart: 'histChart', chartCard: 'histCardChart', tableCard: 'histCardTable',
            thead: 'histThead', tbody: 'histTbody', warning: 'histWarning',
            title: 'histChartTitle', subtitle: 'histChartSubtitle',
            btnGenerate: 'histBtnGenerate', btnBar: 'histBtnBar', btnLine: 'histBtnLine',
            btnTable: 'histBtnTable', btnHideTable: 'histBtnHideTable',
            metrik: 'histMetrik', dari: 'histDari', sampai: 'histSampai', mode: 'histMode',
        },
    };

    // ─────────────────────────────────────────────────────────────────
    // 🎯 DOM CACHE
    // ─────────────────────────────────────────────────────────────────

    const DOM = {};
    function cacheElements() {
        Object.entries(Config.selectors).forEach(([key, id]) => {
            DOM[key] = document.getElementById(id);
            if (!DOM[key] && key !== 'warning') console.warn(`⚠️ Element #${id} not found`);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // 🔧 UTILITIES
    // ─────────────────────────────────────────────────────────────────

    const Utils = {
        isGrowthMetrik: () => GROWTH_METRICS.includes(DOM.metrik?.value?.toLowerCase() ?? ''),

        getEffectiveSatuan: () => {
            if (Utils.isGrowthMetrik()) return '%';
            return DOM.metrik?.selectedOptions?.[0]?.dataset?.satuan ?? '';
        },

        formatNilai: (v, satuan) => {
            if (v === null || v === undefined || isNaN(v)) return '—';
            if (satuan === '%') return v.toFixed(2) + '%';
            if (satuan === 'Rp' || satuan === 'Rp/KWh') return 'Rp ' + Math.abs(v).toLocaleString('id-ID');
            const abs = Math.abs(v);
            if (abs >= 1e9) return (v / 1e9).toFixed(2) + ' M';
            if (abs >= 1e6) return (v / 1e6).toFixed(2) + ' Jt';
            if (abs >= 1e3) return (v / 1e3).toFixed(1) + ' rb';
            return v.toLocaleString('id-ID', { maximumFractionDigits: 2 });
        },

        hexToRgba: (hex, alpha = 0.7) => {
            if (!hex || typeof hex !== 'string') return `rgba(255,255,255,${alpha})`;
            const c = hex.replace('#', '');
            const r = parseInt(c.substring(0,2), 16) || 255;
            const g = parseInt(c.substring(2,4), 16) || 255;
            const b = parseInt(c.substring(4,6), 16) || 255;
            return `rgba(${r},${g},${b},${alpha})`;
        },

        getSelectedMetrik: () => ({
            nama:   DOM.metrik?.selectedOptions?.[0]?.text?.split('(')[0]?.trim() || 'Metrik',
            satuan: Utils.getEffectiveSatuan(),
        }),

        getSelectedGolongan: () =>
            [...document.querySelectorAll('.cb-golongan:checked')].map(c => c.value),
    };

    // ─────────────────────────────────────────────────────────────────
    // 🎨 UI HANDLERS
    // ─────────────────────────────────────────────────────────────────

    const UI = {
        setWarning: (msg, type = 'warning') => {
            if (!DOM.warning) return;
            DOM.warning.textContent = msg || '';
            DOM.warning.style.display = msg ? 'block' : 'none';
            if (msg) {
                const styles = {
                    warning: 'background:rgba(255,152,0,0.08);border-color:rgba(255,152,0,0.25);color:#ff9800;',
                    error:   'background:rgba(244,67,54,0.08);border-color:rgba(244,67,54,0.25);color:#f44336;',
                    info:    'background:rgba(0,255,136,0.08);border-color:rgba(0,255,136,0.25);color:#00ff88;',
                };
                DOM.warning.style.cssText = `border:1px solid;border-radius:6px;padding:8px 14px;font-size:0.78rem;${styles[type] || styles.warning}`;
                if (type !== 'error') {
                    setTimeout(() => {
                        if (DOM.warning.textContent === msg) DOM.warning.style.display = 'none';
                    }, 5000);
                }
            }
        },

        setLoading: (loading) => {
            if (!DOM.btnGenerate) return;
            DOM.btnGenerate.disabled = loading;
            DOM.btnGenerate.innerHTML = loading
                ? '<i class="fas fa-spinner fa-spin"></i> Loading...'
                : '<i class="fas fa-chart-line"></i> Generate';
        },

        updateChartInfo: (mode, dari, sampai, satuan) => {
            const { nama } = Utils.getSelectedMetrik();
            if (DOM.title)    DOM.title.textContent    = `${nama} — ${(mode || 'DATA').toUpperCase()}`;
            if (DOM.subtitle) DOM.subtitle.textContent = `${dari || '?'} – ${sampai || '?'} | Satuan: ${satuan || '-'}`;
        },

        showChartCard: () => {
            if (DOM.chartCard) {
                DOM.chartCard.style.display = 'block';
                setTimeout(() => State.chart?.resize?.(), 100);
            }
        },

        toggleView: () => {
            if (!DOM.chartCard || !DOM.tableCard) return;
            State.tableVisible = !State.tableVisible;
            DOM.chartCard.style.display = State.tableVisible ? 'none' : 'block';
            DOM.tableCard.style.display = State.tableVisible ? 'block' : 'none';
            if (!State.tableVisible && State.chart) setTimeout(() => State.chart.resize(), 50);
            console.log(`🔀 View: ${State.tableVisible ? 'TABLE' : 'CHART'}`);
        },

        // Disable mode selector saat growth metric — controller selalu return tahunan
        updateGrowthHint: () => {
            if (!DOM.mode) return;
            const isGrowth = Utils.isGrowthMetrik();
            DOM.mode.disabled = isGrowth;
            DOM.mode.title    = isGrowth ? 'Growth/YoY selalu ditampilkan dalam mode tahunan' : '';
            if (isGrowth) DOM.mode.value = 'tahunan';
        },
    };

    // ─────────────────────────────────────────────────────────────────
    // 📊 CHART RENDERER
    // ─────────────────────────────────────────────────────────────────

    function renderChart(data) {
        console.log('🎨 renderChart:', { type: State.type, labels: data.labels?.length, datasets: data.datasets?.length });

        const ctx = document.getElementById(Config.selectors.chart);
        if (!ctx) {
            console.error('❌ Canvas #histChart not found');
            UI.setWarning('❌ Canvas chart tidak ditemukan', 'error');
            return;
        }

        const satuan   = Utils.getEffectiveSatuan();
        const isGrowth = Utils.isGrowthMetrik();
        const dari     = DOM.dari?.value;
        const sampai   = DOM.sampai?.value;

        UI.updateChartInfo(data.mode, dari, sampai, satuan);

        if (State.chart) {
            State.chart.destroy();
            State.chart = null;
            console.log('🗑️ Previous chart destroyed');
        }

        const totalLabels = data.labels?.length || 0;
        const step        = totalLabels > 80 ? Math.ceil(totalLabels / 60) : 1;

        try {
            State.chart = new Chart(ctx, {
                type: State.type,
                data: {
                    labels: data.labels || [],
                    datasets: (data.datasets || []).map(d => ({
                        label:            d.label,
                        data:             d.data || [],
                        borderColor:      d.borderColor || Config.colors.TOTAL,
                        backgroundColor:  State.type === 'bar'
                            ? Utils.hexToRgba(d.backgroundColor || d.borderColor || Config.colors.TOTAL, 0.7)
                            : 'transparent',
                        tension:          0.35,
                        fill:             false,
                        pointRadius:      totalLabels > 60 ? 0 : 3,
                        pointHoverRadius: 5,
                        spanGaps:         true,
                        borderWidth:      State.type === 'bar' ? 0 : 2,
                    })),
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    interaction:         { mode: 'index', intersect: false },
                    animation:           { duration: 400 },
                    plugins: {
                        legend: {
                            labels: { color: '#ccc', font: { size: 11 }, padding: 16, usePointStyle: true }
                        },
                        tooltip: {
                            backgroundColor: '#1e1e1e',
                            borderColor:     '#333',
                            borderWidth:     1,
                            titleColor:      '#ccc',
                            bodyColor:       '#aaa',
                            displayColors:   true,
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.raw, label = ctx.dataset.label;
                                    if (v === null || v === undefined) return `${label}: —`;
                                    if (isGrowth) {
                                        const sign = v >= 0 ? '+' : '';
                                        return `${label}: ${sign}${v.toFixed(2)}%`;
                                    }
                                    return `${label}: ${Utils.formatNilai(v, satuan)}${satuan ? ' ' + satuan : ''}`;
                                }
                            }
                        },
                    },
                    scales: {
                        y: {
                            grid: {
                                // Garis Y=0 lebih terang untuk growth chart
                                color: isGrowth
                                    ? (ctx) => ctx.tick.value === 0 ? 'rgba(255,255,255,0.25)' : '#2a2a2a'
                                    : '#2a2a2a',
                                lineWidth: isGrowth
                                    ? (ctx) => ctx.tick.value === 0 ? 2 : 1
                                    : 1,
                            },
                            ticks: {
                                color:    '#666',
                                font:     { size: 10 },
                                callback: (v) => isGrowth ? v.toFixed(1) + '%' : Utils.formatNilai(v, satuan),
                            },
                            beginAtZero: !isGrowth,
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color:       '#666',
                                font:        { size: 9 },
                                maxRotation: 45,
                                minRotation: 45,
                                callback: function(val, idx) {
                                    return idx % step === 0 ? this.getLabelForValue(val) : '';
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Chart rendered successfully');
            return true;
        } catch (e) {
            console.error('❌ Chart render error:', e);
            UI.setWarning(`Render chart gagal: ${e.message}`, 'error');
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 📋 TABLE RENDERER
    // ─────────────────────────────────────────────────────────────────

    function renderTable(data) {
        console.log('📋 renderTable:', { labels: data.labels?.length, datasets: data.datasets?.length });

        const satuan   = Utils.getEffectiveSatuan();
        const isGrowth = Utils.isGrowthMetrik();

        if (DOM.thead) {
            DOM.thead.innerHTML = `
                <tr>
                    <th style="text-align:left;padding:8px 10px;position:sticky;left:0;background:#1a1a1a;z-index:2;min-width:120px;">Periode</th>
                    ${(data.datasets || []).map(d => `<th style="color:${d.borderColor || '#fff'};min-width:100px;">${d.label}</th>`).join('')}
                </tr>
            `;
        }

        if (DOM.tbody) {
            if (!data.labels?.length) {
                DOM.tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center;padding:20px;color:#666;">Tidak ada data untuk ditampilkan</td></tr>';
                return;
            }

            DOM.tbody.innerHTML = (data.labels || []).map((lbl, i) => `
                <tr>
                    <td style="padding:8px 10px;color:#ccc;position:sticky;left:0;background:#1a1a1a;z-index:1;font-weight:500;">${lbl}</td>
                    ${(data.datasets || []).map(d => {
                        const v = d.data?.[i];
                        let cell = '—';
                        if (v !== null && v !== undefined && !isNaN(v)) {
                            if (isGrowth) {
                                const sign  = v >= 0 ? '+' : '';
                                const color = v >= 0 ? '#00ff88' : '#f44336';
                                cell = `<span style="color:${color};font-weight:600;">${sign}${v.toFixed(2)}%</span>`;
                            } else {
                                cell = `${Utils.formatNilai(v, satuan)}${satuan ? ` <small style="color:#555">${satuan}</small>` : ''}`;
                            }
                        }
                        return `<td style="text-align:right;padding:8px 6px;color:#aaa;font-variant-numeric:tabular-nums;">${cell}</td>`;
                    }).join('')}
                </tr>
            `).join('');
        }

        console.log('✅ Table rendered');
    }

    // ─────────────────────────────────────────────────────────────────
    // 📥 LOAD CHART DATA
    // ─────────────────────────────────────────────────────────────────

    async function loadChart() {
        console.log('🔄 loadChart called');

        const dari     = DOM.dari?.value;
        const sampai   = DOM.sampai?.value;
        const metrik   = DOM.metrik?.value;
        const mode     = DOM.mode?.value;
        const golongan = Utils.getSelectedGolongan();

        if (!dari || !sampai || !metrik) {
            UI.setWarning('⚠️ Lengkapi filter: Metrik, Dari, dan Sampai', 'warning');
            return;
        }
        if (!golongan.length) {
            UI.setWarning('⚠️ Pilih minimal satu golongan tarif', 'warning');
            return;
        }

        UI.setLoading(true);
        UI.setWarning('');

        try {
            const params = new URLSearchParams({ tahun_dari: dari, tahun_sampai: sampai, metrik, mode });
            golongan.forEach(g => params.append('golongan[]', g));

            console.log('🌐 Fetching:', `/historis/chart?${params}`);
            const res = await fetch(`/historis/chart?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) {
                const errorText = await res.text().then(t => t.substring(0, 200));
                throw new Error(`HTTP ${res.status}: ${errorText}`);
            }

            const json = await res.json();

            if (!json?.chart?.labels || !json?.chart?.datasets) {
                throw new Error('Format response tidak valid: missing chart data');
            }

            if (json.warning) UI.setWarning(json.warning, 'warning');

            State.lastData = json.chart;

            const rendered = renderChart(json.chart);
            renderTable(json.chart);

            if (rendered) {
                UI.showChartCard();
                console.log('✅ Chart loaded & displayed');
            }

        } catch (e) {
            console.error('❌ loadChart error:', e);
            UI.setWarning(`Gagal memuat data: ${e.message}`, 'error');
            if (DOM.chartCard) DOM.chartCard.style.display = 'none';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error', title: 'Error Memuat Data', text: e.message,
                    confirmButtonColor: '#ff4444', customClass: { popup: 'dark-theme-popup' }
                });
            }
        } finally {
            UI.setLoading(false);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 🔘 CHART TYPE TOGGLE
    // ─────────────────────────────────────────────────────────────────

    function setChartType(type) {
        State.type = type;
        if (DOM.btnBar)  DOM.btnBar.classList.toggle('active',  type === 'bar');
        if (DOM.btnLine) DOM.btnLine.classList.toggle('active', type === 'line');
        console.log(`📊 Chart type changed to: ${type}`);
        if (State.lastData) renderChart(State.lastData);
    }

    // ─────────────────────────────────────────────────────────────────
    // 🔗 EVENT BINDING (Called ONCE)
    // ─────────────────────────────────────────────────────────────────

    function bindEvents() {
        if (State.initialized) {
            console.log('⚠️ Events already bound, skipping');
            return;
        }

        console.log('🔗 Binding events...');

        DOM.btnGenerate?.addEventListener('click',    (e) => { e.preventDefault(); loadChart(); });
        DOM.btnBar?.addEventListener('click',         (e) => { e.preventDefault(); setChartType('bar'); });
        DOM.btnLine?.addEventListener('click',        (e) => { e.preventDefault(); setChartType('line'); });
        DOM.btnTable?.addEventListener('click',       (e) => { e.preventDefault(); UI.toggleView(); });
        DOM.btnHideTable?.addEventListener('click',   (e) => { e.preventDefault(); UI.toggleView(); });

        // Update mode hint saat metrik berubah
        DOM.metrik?.addEventListener('change', () => UI.updateGrowthHint());

        // Update metrik list saat range tahun berubah
        const updateMetrik = async () => {
            const dari = DOM.dari?.value, sampai = DOM.sampai?.value;
            if (!dari || !sampai) return;
            try {
                const res = await fetch(`/historis/metrik?dari=${dari}&sampai=${sampai}`);
                if (!res.ok) return;
                const list = await res.json();
                const sel  = DOM.metrik, cur = sel?.value;
                if (sel && list?.length) {
                    sel.innerHTML = list.map(m =>
                        `<option value="${m.kode}" data-satuan="${m.satuan}" ${m.kode === cur ? 'selected' : ''}>
                            ${m.nama} (${m.satuan})
                        </option>`
                    ).join('');
                    if (!list.find(m => m.kode === cur) && list[0]) sel.value = list[0].kode;
                }
                UI.updateGrowthHint();
            } catch (e) {
                console.warn('⚠️ Gagal update metrik:', e);
            }
        };
        DOM.dari?.addEventListener('change',   updateMetrik);
        DOM.sampai?.addEventListener('change', updateMetrik);

// Golongan chips — setup click handler
document.querySelectorAll('.hist-gol-chip').forEach(chip => {
    chip.addEventListener('click', function(e) {
        // Jangan interfer jika checkbox yang diklik langsung
        if (e.target.classList.contains('cb-golongan')) {
            return;
        }
        
        const cb = this.querySelector('input[type=checkbox]');
        if (!cb) return;
        
        cb.checked = !cb.checked;
        this.classList.toggle('checked', cb.checked);
        
        // Trigger change event
        cb.dispatchEvent(new Event('change', { bubbles: true }));
        
        console.log(`🏷️ Golongan "${cb.value}": ${cb.checked ? '✓' : '✗'}`);
    });
    // ✅ HAPUS { passive: true } — tidak perlu karena tidak ada preventDefault
});

        document.getElementById('histSelectAll')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.cb-golongan').forEach(cb => {
                cb.checked = true;
                cb.closest('.hist-gol-chip')?.classList.add('checked');
            });
            console.log('✓ All golongan selected');
        });

        document.getElementById('histClearAll')?.addEventListener('click', (e) => {
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
            console.log('✗ All golongan cleared (TOTAL kept)');
        });

        State.initialized = true;
        console.log('✅ Events bound successfully');
    }

    // ─────────────────────────────────────────────────────────────────
    // 🚀 PUBLIC API
    // ─────────────────────────────────────────────────────────────────

    window.toggleHistTable = UI.toggleView;
    window.HistorisChart   = {
        load:        loadChart,
        render:      renderChart,
        renderTable,
        setChartType,
        toggleTable: UI.toggleView,
        getState:    () => ({ ...State }),
        isReady:     () => State.initialized && !!State.chart,
    };

    // ─────────────────────────────────────────────────────────────────
    // 🎬 INIT
    // ─────────────────────────────────────────────────────────────────

    function init() {
        if (State.initialized) return;
        console.log('🔌 HistorisChart.js initializing...');
        if (typeof Chart === 'undefined') {
            console.warn('⚠️ Chart.js not loaded, retrying in 500ms...');
            setTimeout(init, 500);
            return;
        }
        cacheElements();
        bindEvents();
        UI.updateGrowthHint(); // set initial state
        console.log('✅ HistorisChart.js ready!');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();