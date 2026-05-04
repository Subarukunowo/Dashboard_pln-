/**
 * HistorisEntry.js — Entry Panel Handler (2026+)
 * Path: public/js/Dashboard/pages/HistorisEntry.js
 */
'use strict';

(function() {

    // ─────────────────────────────────────────────────────────────────
    // STATE & CONFIG
    // ─────────────────────────────────────────────────────────────────

    const State = {
        isLocked: false,
        panelOpen: false,
        currentData: null,
    };

    const Config = {
        endpoints: {
            loadData: '/fact-historis/existing-data',
            saveData: '/fact-historis',
        },
        metrics: [
            { kode: 'daya_va',         nama: 'Daya Tersambung',           satuan: 'VA' },
            { kode: 'delta_gwh',       nama: 'Delta GWh',                 satuan: 'GWh' },
            { kode: 'growth',          nama: 'Growth',                    satuan: '%' },
            { kode: 'komposisi',       nama: 'Komposisi',                 satuan: '%' },
            { kode: 'kwh_jual',        nama: 'KWH Jual',                  satuan: 'KWH' },
            { kode: 'mwh_jual',        nama: 'MWH Jual',                  satuan: 'MWH' },
            { kode: 'pelanggan',       nama: 'Pelanggan',                 satuan: 'pelanggan' },
            { kode: 'pertumbuhan_yoy', nama: 'Pertumbuhan YoY Kumulatif', satuan: '%' },
            { kode: 'rpkwh',           nama: 'RP/KWH Jual',               satuan: 'Rp/KWh' },
            { kode: 'rupiah',          nama: 'Rupiah Penjualan',          satuan: 'Rp' },
        ],
        statusStyles: {
            success: 'background:rgba(0,255,136,0.1);border:1px solid rgba(0,255,136,0.3);color:#00ff88;',
            error:   'background:rgba(255,68,68,0.1);border:1px solid rgba(255,68,68,0.3);color:#ff4444;',
            warning: 'background:rgba(255,152,0,0.1);border:1px solid rgba(255,152,0,0.3);color:#ff9800;',
            info:    'background:rgba(33,150,243,0.1);border:1px solid rgba(33,150,243,0.3);color:#2196f3;',
        },
        bulanNames: ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
    };

    // ─────────────────────────────────────────────────────────────────
    // DOM CACHE
    // ─────────────────────────────────────────────────────────────────

    const DOM = {};

    function cacheElements() {
        const ids = [
            'inputTahun', 'inputBulan', 'btnLoadData', 'entrySaveBtn',
            'btnFillAll', 'btnClearAll', 'entryStatus', 'entryLocked',
            'entryTableWrap', 'entryThead', 'entryTbody',
            'entryPanel', 'entryChevron', 'entryToggleBtn',   // ← toggle button di-cache
        ];
        ids.forEach(id => { DOM[id] = document.getElementById(id); });
    }

    // ─────────────────────────────────────────────────────────────────
    // UTILITIES
    // ─────────────────────────────────────────────────────────────────

    const Utils = {
        getCsrfToken: () =>
            document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('[name="csrf-token"]')?.content,

        getBulanName: (num) => Config.bulanNames[+num] || '',

        parseInteger: (val, fallback = null) => {
            const parsed = parseInt(val, 10);
            return isNaN(parsed) ? fallback : parsed;
        },

        isValidDate: (tahun, bulan) =>
            Number.isInteger(tahun) && Number.isInteger(bulan) &&
            tahun >= 1900 && tahun <= 2100 && bulan >= 1 && bulan <= 12,

        getSatuan: (metrikKode) => {
            const metric = Config.metrics.find(m => m.kode === metrikKode);
            return metric ? metric.satuan : null;
        },
    };

    // ─────────────────────────────────────────────────────────────────
    // UI HANDLERS
    // ─────────────────────────────────────────────────────────────────

    const UI = {
        setStatus: (message, type = 'info') => {
            if (!DOM.entryStatus) return;
            DOM.entryStatus.style.cssText =
                (Config.statusStyles[type] || Config.statusStyles.info) +
                'display:block;padding:8px 14px;border-radius:7px;font-size:0.78rem;margin-bottom:10px;';
            DOM.entryStatus.textContent = message;

            if (type === 'success') {
                setTimeout(() => {
                    if (DOM.entryStatus?.textContent === message)
                        DOM.entryStatus.style.display = 'none';
                }, 4000);
            }
        },

        setLoading: (loading) => {
            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = loading ? 'none' : 'block';
            if (DOM.entryLocked)   DOM.entryLocked.style.display = 'none';
            if (DOM.entrySaveBtn)  DOM.entrySaveBtn.style.display = loading || State.isLocked ? 'none' : 'inline-flex';
            if (DOM.btnLoadData) {
                DOM.btnLoadData.disabled  = loading;
                DOM.btnLoadData.innerHTML = loading
                    ? '<i class="fas fa-spinner fa-spin"></i> Loading...'
                    : '<i class="fas fa-rotate-right"></i> Muat Data';
            }
        },

        // ── TOGGLE PANEL ─────────────────────────────────────────────
        togglePanel: () => {
            if (!DOM.entryPanel) return;

            State.panelOpen = !State.panelOpen;

            // Support both hidden attribute (blade) and display style
            if ('hidden' in DOM.entryPanel) {
                DOM.entryPanel.hidden = !State.panelOpen;
            } else {
                DOM.entryPanel.style.display = State.panelOpen ? 'block' : 'none';
            }

            if (DOM.entryToggleBtn)
                DOM.entryToggleBtn.setAttribute('aria-expanded', String(State.panelOpen));

            if (DOM.entryChevron)
                DOM.entryChevron.classList.toggle('open', State.panelOpen);
        },

        showLocked: (message) => {
            if (DOM.entryLocked) {
                DOM.entryLocked.innerHTML = `<i class="fas fa-lock"></i> <strong>Periode Terkunci</strong><br>${message || 'Data sudah tidak dapat diedit.'}`;
                DOM.entryLocked.style.display = 'block';
            }
            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = 'none';
            if (DOM.entrySaveBtn)   DOM.entrySaveBtn.style.display = 'none';
            State.isLocked = true;
        },

        highlightSaved: () => {
            document.querySelectorAll('.entry-input.has-value').forEach(input => {
                input.style.borderColor = 'var(--accent-green, #00ff88)';
                input.style.background  = 'rgba(0,255,136,0.05)';
                setTimeout(() => {
                    input.style.borderColor = '';
                    input.style.background  = '';
                }, 2000);
            });
        },
    };

    // ─────────────────────────────────────────────────────────────────
    // TABLE RENDERER
    // ─────────────────────────────────────────────────────────────────

    function buildEntryTable(data) {
        if (!DOM.entryThead || !DOM.entryTbody) return;

        // Ambil golongan dari chips
        const golongans = [...document.querySelectorAll('.cb-golongan')].map(cb => ({
            kode: cb.value,
            nama: cb.closest('label')?.textContent?.trim() || cb.value,
        })).filter(g => g.kode);

        if (!golongans.length) {
            golongans.push(
                { kode: 'S', nama: 'Sosial' }, { kode: 'R', nama: 'Rumah Tangga' },
                { kode: 'B', nama: 'Bisnis' }, { kode: 'I', nama: 'Industri' },
                { kode: 'P', nama: 'Pemerintah' }, { kode: 'M', nama: 'Meteran' },
                { kode: 'TOTAL', nama: 'TOTAL' }
            );
        }

        // Header
        DOM.entryThead.innerHTML = `
            <tr>
                <th style="text-align:left;padding:8px 10px;">Metrik</th>
                <th style="padding:8px 6px;">Satuan</th>
                ${golongans.map(g => `<th style="text-align:center;min-width:110px;">${g.nama}</th>`).join('')}
            </tr>
        `;

        // Body
        // ↓ data-metrik-nama diset agar HistorisImport.js bisa matching
        DOM.entryTbody.innerHTML = Config.metrics.map(metric => {
            const existingRows = data?.[metric.kode] || [];

            return `
                <tr>
                    <td style="padding:8px 10px;color:#ccc;">${metric.nama}</td>
                    <td style="padding:8px 6px;color:var(--text-muted,#888);font-size:0.75rem;">${metric.satuan}</td>
                    ${golongans.map(gol => {
                        const row      = existingRows.find(r => r.golongan_kode === gol.kode);
                        const value    = row?.nilai ?? '';
                        const hasValue = value !== '' && value !== null && value !== undefined;

                        return `
                            <td style="padding:4px 6px;">
                                <input type="number" step="any"
                                    class="entry-input ${hasValue ? 'has-value' : ''}"
                                    data-metrik="${metric.kode}"
                                    data-metrik-nama="${metric.nama.toLowerCase()}"
                                    data-golongan="${gol.kode}"
                                    value="${hasValue ? value : ''}"
                                    placeholder="0"
                                    ${State.isLocked ? 'disabled' : ''}>
                            </td>
                        `;
                    }).join('')}
                </tr>
            `;
        }).join('');

        // Input listeners
        document.querySelectorAll('.entry-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--accent-orange, #ff9800)';
                this.style.background  = '#252525';
            });
            input.addEventListener('blur', function() {
                this.style.borderColor = '#333';
                if (!this.value) this.style.background = '#1a1a1a';
            });
            input.addEventListener('input', function() {
                this.classList.toggle('has-value', !!this.value);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // DATA LOADER
    // ─────────────────────────────────────────────────────────────────

    async function loadEntryData() {
        const tahun = Utils.parseInteger(DOM.inputTahun?.value);
        const bulan = Utils.parseInteger(DOM.inputBulan?.value);

        if (!Utils.isValidDate(tahun, bulan)) {
            UI.setStatus('Pilih tahun dan bulan terlebih dahulu', 'warning');
            return;
        }

        UI.setStatus('Memuat data...', 'info');
        UI.setLoading(true);

        try {
            const url      = `${Config.endpoints.loadData}?tahun=${tahun}&bulan=${bulan}`;
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

            const ct = response.headers.get('content-type');
            if (!ct?.includes('application/json')) {
                const text = await response.text();
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html'))
                    throw new Error('Session habis. Silakan refresh halaman atau login ulang.');
                throw new Error(`Response tidak valid (${ct})`);
            }

            const result = await response.json();

            if (!response.ok || result.locked || !result.success) {
                UI.showLocked(result.message || 'Data terkunci');
                return;
            }

            State.isLocked   = false;
            State.currentData = result.data || {};

            buildEntryTable(State.currentData);

            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = 'block';
            if (DOM.entrySaveBtn)   DOM.entrySaveBtn.style.display   = 'inline-flex';

            const deadline = result.reason ? ` • ${result.reason}` : '';
            UI.setStatus(`✓ ${Utils.getBulanName(bulan)} ${tahun} dimuat${deadline}`, 'success');

        } catch (error) {
            console.error('loadEntryData error:', error);
            UI.setStatus(`Gagal memuat: ${error.message}`, 'error');
        } finally {
            UI.setLoading(false);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // DATA SAVER
    // ─────────────────────────────────────────────────────────────────

    async function saveEntryData() {
        if (State.isLocked) {
            UI.setStatus('Data terkunci, tidak dapat disimpan', 'error');
            return;
        }

        const tahun = Utils.parseInteger(DOM.inputTahun?.value);
        const bulan = Utils.parseInteger(DOM.inputBulan?.value);

        if (!Utils.isValidDate(tahun, bulan)) {
            UI.setStatus('Pilih tahun dan bulan terlebih dahulu', 'warning');
            return;
        }

        const entries = [];
        document.querySelectorAll('.entry-input').forEach(input => {
            const nilai = parseFloat(input.value);
            if (!isNaN(nilai) && input.dataset.metrik && input.dataset.golongan) {
                entries.push({
                    metrik_kode:   input.dataset.metrik,
                    golongan_kode: input.dataset.golongan,
                    nilai:         nilai,
                    satuan:        Utils.getSatuan(input.dataset.metrik),
                });
            }
        });

        if (!entries.length) {
            UI.setStatus('Tidak ada data untuk disimpan', 'warning');
            return;
        }

        const btn         = DOM.entrySaveBtn;
        const originalHtml = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'; }

        try {
            const csrf = Utils.getCsrfToken();
            if (!csrf) throw new Error('CSRF token tidak ditemukan');

            const response = await fetch(Config.endpoints.saveData, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ tahun, bulan, data: entries }),
            });

            const result = await response.json();

            if (!response.ok) throw new Error(result.message || `HTTP ${response.status}`);

            if (result.success) {
                UI.setStatus(`✓ ${result.message}`, 'success');
                UI.highlightSaved();
                return true;
            }
            throw new Error(result.message || 'Gagal menyimpan data');

        } catch (error) {
            console.error('saveEntryData error:', error);
            UI.setStatus(`Gagal: ${error.message}`, 'error');
            return false;
        } finally {
            if (btn && originalHtml) { btn.disabled = false; btn.innerHTML = originalHtml; }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // FORM UTILITIES
    // ─────────────────────────────────────────────────────────────────

    function fillAllInputs(value = 0) {
        if (State.isLocked) return;
        document.querySelectorAll('.entry-input').forEach(input => {
            input.value = value;
            input.classList.toggle('has-value', value !== '');
        });
        UI.setStatus('Semua field diisi dengan 0', 'info');
    }

    function clearAllInputs() {
        if (State.isLocked) return;
        document.querySelectorAll('.entry-input').forEach(input => {
            input.value = '';
            input.classList.remove('has-value');
            input.style.background = '';
        });
        UI.setStatus('Form dikosongkan', 'info');
    }

    // ─────────────────────────────────────────────────────────────────
    // EVENT BINDING
    // ─────────────────────────────────────────────────────────────────

    function bindEvents() {
        // ✅ FIX UTAMA: Toggle panel di-bind langsung di sini
        DOM.entryToggleBtn?.addEventListener('click', UI.togglePanel);

        DOM.btnLoadData?.addEventListener('click',  (e) => { e?.preventDefault(); loadEntryData(); });
        DOM.entrySaveBtn?.addEventListener('click', (e) => { e?.preventDefault(); saveEntryData(); });
        DOM.btnFillAll?.addEventListener('click',   () => fillAllInputs(0));
        DOM.btnClearAll?.addEventListener('click',  clearAllInputs);
    }

    // ─────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────────

    window.HistorisEntry = {
        load:        loadEntryData,
        save:        saveEntryData,
        buildTable:  buildEntryTable,
        togglePanel: UI.togglePanel,
        setStatus:   UI.setStatus,
        getState:    () => ({ ...State }),
        utils:       Utils,
    };

    // Backward compatibility
    window.entryBuildTable  = buildEntryTable;
    window.loadEntryData    = loadEntryData;
    window.saveEntryData    = saveEntryData;
    window.toggleEntryPanel = UI.togglePanel;

    // ─────────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────────

    function init() {
        cacheElements();
        bindEvents();

        // Sync chip checked state
        document.querySelectorAll('.cb-golongan').forEach(cb => {
            const chip = cb.closest('.hist-gol-chip');
            if (chip) chip.classList.toggle('checked', cb.checked);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();