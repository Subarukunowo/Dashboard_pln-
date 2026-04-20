/**
 * Fact Entry — Standalone Input Page
 * Handles data entry for historical metrics (2026+)
 * 
 * Features:
 * - Load existing data via API
 * - Save/create entries with validation
 * - Handle JSON errors gracefully
 * - Auto CSRF token detection
 */

(function() {
    'use strict';

    // ════════════════════════════════════════════════════════════════════
    // CONFIG & STATE
    // ════════════════════════════════════════════════════════════════════

    const CONFIG = {
        endpoints: {
            loadData: '/fact-historis/existing-data',
            saveData: '/fact-historis',
        },
        selectors: {
            tahun: '#inputTahun',
            bulan: '#inputBulan',
            btnLoad: '#btnLoadData',
            btnSave: '#btnSave',
            btnFillAll: '#btnFillAll',
            btnClearAll: '#btnClearAll',
            cardForm: '#cardForm',
            loading: '#loadingData',
            entryForm: '#entryForm',
            status: '#statusInfo',
            tableHead: '#entryThead',
            tableBody: '#entryTbody',
        },
        metrics: [
            { kode: 'kwh_jual',  nama: 'KWH Jual',        satuan: 'KWH' },
            { kode: 'mwh_jual',  nama: 'MWH Jual',        satuan: 'MWH' },
            { kode: 'rupiah',    nama: 'Rupiah Penjualan', satuan: 'Rp'  },
            { kode: 'pelanggan', nama: 'Pelanggan',        satuan: 'org' },
            { kode: 'daya_va',   nama: 'Daya Tersambung',  satuan: 'VA'  },
        ],
        statusStyles: {
            success: { bg: 'rgba(0,255,136,0.1)', border: 'rgba(0,255,136,0.3)', color: '#00ff88' },
            error:   { bg: 'rgba(255,68,68,0.1)',   border: 'rgba(255,68,68,0.3)',   color: '#ff4444' },
            warning: { bg: 'rgba(255,152,0,0.1)',   border: 'rgba(255,152,0,0.3)',   color: '#ff9800' },
            info:    { bg: 'rgba(33,150,243,0.1)',  border: 'rgba(33,150,243,0.3)',  color: '#2196f3' },
        },
    };

    let state = {
        isLocked: false,
        currentData: {},
        golongans: [],
    };

    // ════════════════════════════════════════════════════════════════════
    // DOM CACHE
    // ════════════════════════════════════════════════════════════════════

    const DOM = {};

    function cacheElements() {
        Object.entries(CONFIG.selectors).forEach(([key, selector]) => {
            DOM[key] = document.querySelector(selector);
        });
    }

    // ════════════════════════════════════════════════════════════════════
    // INITIALIZATION
    // ════════════════════════════════════════════════════════════════════

    function init() {
        cacheElements();
        bindEvents();
        fetchGolongans();
        
        // Auto-load current period on page load (optional)
        // loadExistingData();
    }

    function bindEvents() {
        DOM.btnLoad?.addEventListener('click', () => {
            const tahun = DOM.tahun?.value;
            const bulan = DOM.bulan?.value;
            loadExistingData(tahun, bulan);
        });

        DOM.btnSave?.addEventListener('click', saveData);
        DOM.btnFillAll?.addEventListener('click', fillAllZeros);
        DOM.btnClearAll?.addEventListener('click', clearAll);
    }

    function fetchGolongans() {
        // Extract golongan from existing chips or use default
        const chips = document.querySelectorAll('.cb-golongan, .hist-gol-chip input[type=checkbox]');
        if (chips.length > 0) {
            state.golongans = Array.from(chips).map(cb => ({
                kode: cb.value,
                nama: cb.closest('label')?.textContent?.trim() || cb.value,
            }));
        } else {
            // Default fallback
            state.golongans = [
                { kode: 'S', nama: 'Sosial' },
                { kode: 'R', nama: 'Rumah Tangga' },
                { kode: 'B', nama: 'Bisnis' },
                { kode: 'I', nama: 'Industri' },
                { kode: 'P', nama: 'Pemerintah' },
                { kode: 'M', nama: 'Meteran' },
                { kode: 'TOTAL', nama: 'TOTAL' },
            ];
        }
    }

    // ════════════════════════════════════════════════════════════════════
    // API: LOAD DATA
    // ════════════════════════════════════════════════════════════════════

    async function loadExistingData(tahun, bulan) {
        if (!tahun || !bulan) {
            showStatus('Pilih tahun dan bulan terlebih dahulu', 'warning');
            return;
        }

        setLoading(true);
        hideStatus();
        setButtonLoading(DOM.btnLoad, true);

        try {
            const response = await fetch(
                `${CONFIG.endpoints.loadData}?tahun=${tahun}&bulan=${bulan}`,
                { headers: { 'Accept': 'application/json' } }
            );

            // 🔍 Handle non-JSON responses (auth redirect, 404, 500)
            const contentType = response.headers.get('content-type');
            if (!contentType?.includes('application/json')) {
                const text = await response.text();
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                    throw new Error('Session mungkin habis. Silakan refresh halaman atau login ulang.');
                }
                throw new Error(`Response tidak valid: ${text.substring(0, 100)}`);
            }

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `HTTP ${response.status}`);
            }

            if (result.locked || !result.success) {
                showLocked(result.message || 'Data terkunci');
                state.isLocked = true;
                return;
            }

            // Success: populate form
            state.currentData = result.data || {};
            state.isLocked = false;
            
            renderTable();
            showForm(true);
            showStatus(`✓ Data dimuat: ${getBulanName(bulan)} ${tahun}`, 'success');

        } catch (error) {
            console.error('loadExistingData error:', error);
            showStatus(`✗ Gagal memuat: ${error.message}`, 'error');
        } finally {
            setLoading(false);
            setButtonLoading(DOM.btnLoad, false);
        }
    }

    // ════════════════════════════════════════════════════════════════════
    // API: SAVE DATA
    // ════════════════════════════════════════════════════════════════════

    async function saveData() {
        if (state.isLocked) {
            showStatus('Data terkunci, tidak dapat disimpan', 'error');
            return;
        }

        const tahun = DOM.tahun?.value;
        const bulan = DOM.bulan?.value;
        const entries = collectFormData();

        if (!entries.length) {
            showStatus('Tidak ada data untuk disimpan', 'warning');
            return;
        }

        setButtonLoading(DOM.btnSave, true);

        try {
            const csrfToken = getCsrfToken();
            if (!csrfToken) throw new Error('CSRF token tidak ditemukan');

            const response = await fetch(CONFIG.endpoints.saveData, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    tahun: parseInt(tahun),
                    bulan: parseInt(bulan),
                    data: entries,
                }),
            });

            // Handle non-JSON error response
            const contentType = response.headers.get('content-type');
            if (!contentType?.includes('application/json')) {
                const text = await response.text();
                if (text.trim().startsWith('<!DOCTYPE')) {
                    throw new Error('Session habis. Silakan login ulang.');
                }
            }

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `HTTP ${response.status}`);
            }

            if (result.success) {
                showStatus(`✓ ${result.message} (Baru: ${result.created}, Update: ${result.updated})`, 'success');
                highlightSavedInputs();
                return true;
            } else {
                throw new Error(result.message || 'Gagal menyimpan data');
            }

        } catch (error) {
            console.error('saveData error:', error);
            showStatus(`✗ Gagal: ${error.message}`, 'error');
            return false;
        } finally {
            setButtonLoading(DOM.btnSave, false);
        }
    }

    // ════════════════════════════════════════════════════════════════════
    // FORM RENDERING
    // ════════════════════════════════════════════════════════════════════

    function renderTable() {
        const { tableHead, tableBody } = DOM;
        if (!tableHead || !tableBody) return;

        // Build header
        tableHead.innerHTML = `
            <tr>
                <th class="col-metrik">Metrik</th>
                <th class="col-satuan">Satuan</th>
                ${state.golongans.map(g => 
                    `<th class="col-golongan">${g.nama}</th>`
                ).join('')}
            </tr>
        `;

        // Build body rows
        tableBody.innerHTML = CONFIG.metrics.map(metric => {
            const existingRows = state.currentData[metric.kode] || [];
            
            return `
                <tr>
                    <td class="cell-metrik">${metric.nama}</td>
                    <td class="cell-satuan">${metric.satuan}</td>
                    ${state.golongans.map(gol => {
                        const row = existingRows.find(r => r.golongan_kode === gol.kode);
                        const value = row?.nilai ?? '';
                        const hasValue = value !== '' && value !== null;
                        
                        return `
                            <td class="cell-input">
                                <input type="number"
                                    step="any"
                                    class="data-input ${hasValue ? 'has-value' : ''}"
                                    data-metrik="${metric.kode}"
                                    data-golongan="${gol.kode}"
                                    value="${value}"
                                    placeholder="0"
                                    ${state.isLocked ? 'disabled' : ''}>
                            </td>
                        `;
                    }).join('')}
                </tr>
            `;
        }).join('');

        // Add focus/blur handlers for visual feedback
        document.querySelectorAll('.data-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--accent-orange, #ff9800)';
                this.style.background = '#252525';
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

    function collectFormData() {
        const entries = [];
        
        document.querySelectorAll('.data-input').forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value) && value !== null) {
                entries.push({
                    metrik_kode: input.dataset.metrik,
                    golongan_kode: input.dataset.golongan,
                    nilai: value,
                });
            }
        });
        
        return entries;
    }

    // ════════════════════════════════════════════════════════════════════
    // UI HELPERS
    // ════════════════════════════════════════════════════════════════════

    function setLoading(show) {
        if (DOM.loading) DOM.loading.style.display = show ? 'flex' : 'none';
        if (DOM.entryForm) DOM.entryForm.style.display = show ? 'none' : 'block';
    }

    function showForm(show) {
        if (DOM.cardForm) DOM.cardForm.style.display = show ? 'block' : 'none';
        if (DOM.btnSave) DOM.btnSave.style.display = show && !state.isLocked ? 'inline-flex' : 'none';
    }

    function showLocked(message) {
        showForm(false);
        if (DOM.status) {
            DOM.status.textContent = `🔒 ${message}`;
            Object.assign(DOM.status.style, CONFIG.statusStyles.warning);
            DOM.status.style.display = 'block';
        }
    }

    function showStatus(message, type = 'info') {
        if (!DOM.status) return;
        
        const style = CONFIG.statusStyles[type] || CONFIG.statusStyles.info;
        DOM.status.textContent = message;
        DOM.status.style.cssText = `
            display: block;
            background: ${style.bg};
            border: 1px solid ${style.border};
            color: ${style.color};
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-top: 10px;
        `;

        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                if (DOM.status?.textContent === message) {
                    DOM.status.style.display = 'none';
                }
            }, 5000);
        }
    }

    function hideStatus() {
        if (DOM.status) DOM.status.style.display = 'none';
    }

    function setButtonLoading(button, loading) {
        if (!button) return;
        
        if (loading) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml || button.innerHTML;
        }
    }

    function highlightSavedInputs() {
        document.querySelectorAll('.data-input.has-value').forEach(input => {
            input.style.borderColor = 'var(--accent-green, #00ff88)';
            input.style.background = 'rgba(0,255,136,0.05)';
            setTimeout(() => {
                input.style.borderColor = '#333';
                input.style.background = '';
            }, 2000);
        });
    }

    function fillAllZeros() {
        if (state.isLocked) return;
        document.querySelectorAll('.data-input').forEach(input => {
            input.value = '0';
            input.classList.add('has-value');
        });
        showStatus('Semua field diisi dengan 0', 'info');
    }

    function clearAll() {
        if (state.isLocked) return;
        document.querySelectorAll('.data-input').forEach(input => {
            input.value = '';
            input.classList.remove('has-value');
            input.style.background = '';
        });
        showStatus('Form dikosongkan', 'info');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('[name="csrf-token"]')?.content;
    }

    function getBulanName(num) {
        const names = ['','Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];
        return names[+num] || '';
    }

    // ════════════════════════════════════════════════════════════════════
    // BOOTSTRAP
    // ════════════════════════════════════════════════════════════════════

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for debugging/testing
    window.FactEntry = { load: loadExistingData, save: saveData, state };

})();