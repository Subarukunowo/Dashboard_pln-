/**
 * HistorisEntry.js — Entry Panel Handler (2026+)
 * Path: public/js/Dashboard/pages/HistorisEntry.js
 * 
 * Handles: load data, build table, save data, UI toggles
 * Best Practice: IIFE pattern + explicit public API exposure
 */
'use strict';

(function() {
    // ─────────────────────────────────────────────────────────────────
    // 📦 STATE & CONFIG
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
        // ✅ LENGKAP: Semua metrik dengan satuan sesuai database
        metrics: [
            { kode: 'daya_va',         nama: 'Daya Tersambung',         satuan: 'VA' },
            { kode: 'delta_gwh',       nama: 'Delta GWh',               satuan: 'GWh' },
            { kode: 'growth',          nama: 'Growth',                  satuan: '%' },
            { kode: 'komposisi',       nama: 'Komposisi',               satuan: '%' },
            { kode: 'kwh_jual',        nama: 'KWH Jual',                satuan: 'KWH' },
            { kode: 'mwh_jual',        nama: 'MWH Jual',                satuan: 'MWH' },
            { kode: 'pelanggan',       nama: 'Pelanggan',               satuan: 'pelanggan' },
            { kode: 'pertumbuhan_yoy', nama: 'Pertumbuhan YoY Kumulatif', satuan: '%' },
            { kode: 'rpkwh',           nama: 'RP/KWH Jual',             satuan: 'Rp/KWh' },
            { kode: 'rupiah',          nama: 'Rupiah Penjualan',        satuan: 'Rp' },
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
    // 🎯 DOM CACHE
    // ─────────────────────────────────────────────────────────────────
    
    const DOM = {};
    
    function cacheElements() {
        const selectors = [
            'inputTahun', 'inputBulan', 'btnLoadData', 'entrySaveBtn',
            'btnFillAll', 'btnClearAll', 'entryStatus', 'entryLocked',
            'entryTableWrap', 'entryThead', 'entryTbody', 'entryPanel', 'entryChevron'
        ];
        selectors.forEach(id => { DOM[id] = document.getElementById(id); });
    }

    // ─────────────────────────────────────────────────────────────────
    // 🔧 UTILITIES
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
            
        // ✅ Helper: Cari satuan berdasarkan metrik kode
        getSatuan: (metrikKode) => {
            const metric = Config.metrics.find(m => m.kode === metrikKode);
            return metric ? metric.satuan : null;
        },
    };

    // ─────────────────────────────────────────────────────────────────
    // 🎨 UI HANDLERS
    // ─────────────────────────────────────────────────────────────────
    
    const UI = {
        setStatus: (message, type = 'info') => {
            if (!DOM.entryStatus) {
                console.log(`[Status] ${type.toUpperCase()}:`, message);
                return;
            }
            DOM.entryStatus.style.cssText = Config.statusStyles[type] || Config.statusStyles.info;
            DOM.entryStatus.textContent = message;
            DOM.entryStatus.style.display = 'block';
            
            if (type === 'success') {
                setTimeout(() => { 
                    if (DOM.entryStatus?.textContent === message) {
                        DOM.entryStatus.style.display = 'none'; 
                    }
                }, 4000);
            }
        },

        setLoading: (loading) => {
            if (DOM.entryTableWrap) {
                DOM.entryTableWrap.style.display = loading ? 'none' : 'block';
            }
            if (DOM.entryLocked) {
                DOM.entryLocked.style.display = 'none';
            }
            if (DOM.entrySaveBtn) {
                DOM.entrySaveBtn.style.display = loading || State.isLocked ? 'none' : 'inline-flex';
            }
            if (DOM.btnLoadData) {
                DOM.btnLoadData.disabled = loading;
                DOM.btnLoadData.innerHTML = loading 
                    ? '<i class="fas fa-spinner fa-spin"></i> Loading...' 
                    : '<i class="fas fa-download"></i> Muat Data';
            }
        },

        togglePanel: () => {
            if (!DOM.entryPanel) return;
            State.panelOpen = !State.panelOpen;
            DOM.entryPanel.style.display = State.panelOpen ? 'block' : 'none';
            if (DOM.entryChevron) {
                DOM.entryChevron.style.transform = State.panelOpen ? 'rotate(180deg)' : 'rotate(0)';
            }
        },

        showLocked: (message) => {
            UI.setStatus(message || 'Data terkunci', 'error');
            if (DOM.entryLocked) {
                DOM.entryLocked.innerHTML = `<i class="fas fa-lock"></i> ${message || 'Data terkunci'}`;
                DOM.entryLocked.style.display = 'block';
            }
            State.isLocked = true;
        },

        highlightSaved: () => {
            document.querySelectorAll('.entry-input.has-value').forEach(input => {
                input.style.borderColor = 'var(--accent-green, #00ff88)';
                input.style.background = 'rgba(0,255,136,0.05)';
                setTimeout(() => {
                    input.style.borderColor = '';
                    input.style.background = '';
                }, 2000);
            });
        },
    };

    // ─────────────────────────────────────────────────────────────────
    // 📊 TABLE RENDERER ⭐ PUBLIC API
    // ─────────────────────────────────────────────────────────────────
    
    function buildEntryTable(data) {
        if (!DOM.entryThead || !DOM.entryTbody) {
            console.error('❌ Table elements not found:', { thead: !!DOM.entryThead, tbody: !!DOM.entryTbody });
            return;
        }

        // Get golongan from hidden chips
        const golongans = [...document.querySelectorAll('.cb-golongan')].map(cb => ({
            kode: cb.value,
            nama: cb.closest('label')?.textContent?.trim() || cb.value,
        })).filter(g => g.kode);

        if (golongans.length === 0) {
            console.warn('⚠️ No golongan found, using defaults');
            golongans.push(
                { kode: 'S', nama: 'Sosial' }, { kode: 'R', nama: 'Rumah Tangga' },
                { kode: 'B', nama: 'Bisnis' }, { kode: 'I', nama: 'Industri' },
                { kode: 'P', nama: 'Pemerintah' }, { kode: 'M', nama: 'Meteran' },
                { kode: 'TOTAL', nama: 'TOTAL' }
            );
        }

        // Render header
        DOM.entryThead.innerHTML = `
            <tr>
                <th style="text-align:left;padding:8px 10px;">Metrik</th>
                <th style="padding:8px 6px;">Satuan</th>
                ${golongans.map(g => 
                    `<th style="text-align:center;min-width:110px;">${g.nama}</th>`
                ).join('')}
            </tr>
        `;

        // Render body
        DOM.entryTbody.innerHTML = Config.metrics.map(metric => {
            const existingRows = data?.[metric.kode] || [];
            
            return `
                <tr>
                    <td style="padding:8px 10px;color:#ccc;">${metric.nama}</td>
                    <td style="padding:8px 6px;color:var(--text-muted);font-size:0.75rem;">${metric.satuan}</td>
                    ${golongans.map(gol => {
                        const row = existingRows.find(r => r.golongan_kode === gol.kode);
                        const value = row?.nilai ?? '';
                        const hasValue = value !== '' && value !== null && value !== undefined;
                        
                        return `
                            <td style="padding:4px 6px;">
                                <input type="number" step="any" 
                                    class="entry-input ${hasValue ? 'has-value' : ''}"
                                    data-metrik="${metric.kode}"
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

        // Attach input listeners
        document.querySelectorAll('.entry-input').forEach(input => {
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

        console.log('✅ Table rendered:', { metrics: Config.metrics.length, golongans: golongans.length });
    }

    // ─────────────────────────────────────────────────────────────────
    // 📥 DATA LOADER ⭐ PUBLIC API
    // ─────────────────────────────────────────────────────────────────
    
    async function loadEntryData() {
        console.log('🔄 loadEntryData called');
        
        if (!DOM.inputTahun || !DOM.inputBulan) {
            UI.setStatus('Form tidak ditemukan. Refresh halaman.', 'error');
            return;
        }

        const tahun = Utils.parseInteger(DOM.inputTahun.value);
        const bulan = Utils.parseInteger(DOM.inputBulan.value);

        if (!Utils.isValidDate(tahun, bulan)) {
            UI.setStatus('Pilih tahun dan bulan terlebih dahulu', 'warning');
            return;
        }

        console.log('📅 Loading:', { tahun, bulan: `${bulan} (${Utils.getBulanName(bulan)})` });
        
        UI.setStatus('Memuat data...', 'info');
        UI.setLoading(true);

        try {
            const url = `${Config.endpoints.loadData}?tahun=${tahun}&bulan=${bulan}`;
            console.log('🌐 Fetching:', url);
            
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType?.includes('application/json')) {
                const text = await response.text();
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                    throw new Error('Session habis. Silakan refresh halaman atau login ulang.');
                }
                throw new Error(`Response tidak valid (Content-Type: ${contentType})`);
            }

            const result = await response.json();
            console.log('📦 Response:', { ok: response.ok, success: result.success, locked: result.locked });

            if (!response.ok || result.locked || !result.success) {
                UI.showLocked(result.message || 'Data terkunci');
                return;
            }

            State.isLocked = false;
            State.currentData = result.data || {};
            
            buildEntryTable(State.currentData);
            
            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = 'block';
            if (DOM.entrySaveBtn) DOM.entrySaveBtn.style.display = 'inline-flex';
            
            const deadline = result.reason ? ` • ${result.reason}` : '';
            UI.setStatus(`✓ ${Utils.getBulanName(bulan)} ${tahun} dimuat${deadline}`, 'success');

        } catch (error) {
            console.error('❌ loadEntryData error:', error);
            UI.setStatus(`Gagal memuat: ${error.message}`, 'error');
        } finally {
            UI.setLoading(false);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 📤 DATA SAVER ⭐ PUBLIC API — ✅ INCLUDE SATUAN
    // ─────────────────────────────────────────────────────────────────
    
    async function saveEntryData() {
        console.log('💾 saveEntryData called');
        
        if (State.isLocked) {
            UI.setStatus('Data terkunci, tidak dapat disimpan', 'error');
            return;
        }

        if (!DOM.inputTahun || !DOM.inputBulan) {
            UI.setStatus('Form tidak ditemukan', 'error');
            return;
        }

        const tahun = Utils.parseInteger(DOM.inputTahun.value);
        const bulan = Utils.parseInteger(DOM.inputBulan.value);

        if (!Utils.isValidDate(tahun, bulan)) {
            UI.setStatus('Pilih tahun dan bulan terlebih dahulu', 'warning');
            return;
        }

        // ✅ Collect form data DENGAN satuan dari Config.metrics
        const entries = [];
        document.querySelectorAll('.entry-input').forEach(input => {
            const nilai = parseFloat(input.value);
            if (!isNaN(nilai) && input.dataset.metrik && input.dataset.golongan) {
                // ✅ Ambil satuan berdasarkan metrik_kode
                const satuan = Utils.getSatuan(input.dataset.metrik);
                
                entries.push({
                    metrik_kode: input.dataset.metrik,
                    golongan_kode: input.dataset.golongan,
                    nilai: nilai,
                    satuan: satuan,  // ✅ SATUAN DITAMBAHKAN (tidak null lagi!)
                });
            }
        });

        if (entries.length === 0) {
            UI.setStatus('Tidak ada data untuk disimpan', 'warning');
            return;
        }

        console.log('📤 Saving:', { 
            tahun, 
            bulan, 
            entriesCount: entries.length,
            sample: entries.slice(0, 3)  // Log sample untuk verifikasi
        });
        
        const btn = DOM.entrySaveBtn;
        const originalHtml = btn?.innerHTML;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        }

        try {
            const csrf = Utils.getCsrfToken();
            if (!csrf) throw new Error('CSRF token tidak ditemukan');

            const response = await fetch(Config.endpoints.saveData, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ 
                    tahun: tahun, 
                    bulan: bulan, 
                    data: entries  // Sekarang include satuan
                }),
            });

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
                UI.setStatus(`✓ ${result.message}`, 'success');
                UI.highlightSaved();
                return true;
            } else {
                throw new Error(result.message || 'Gagal menyimpan data');
            }

        } catch (error) {
            console.error('❌ saveEntryData error:', error);
            UI.setStatus(`Gagal: ${error.message}`, 'error');
            return false;
        } finally {
            if (btn && originalHtml) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 🧹 FORM UTILITIES
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
    // 🔗 EVENT BINDING
    // ─────────────────────────────────────────────────────────────────
    
    function bindEvents() {
        DOM.btnLoadData?.addEventListener('click', (e) => {
            e?.preventDefault();
            loadEntryData();
        });
        
        DOM.entrySaveBtn?.addEventListener('click', (e) => {
            e?.preventDefault();
            saveEntryData();
        });
        
        DOM.btnFillAll?.addEventListener('click', () => fillAllInputs(0));
        DOM.btnClearAll?.addEventListener('click', clearAllInputs);
        
        console.log('✅ Events bound');
    }

    // ─────────────────────────────────────────────────────────────────
    // 🚀 PUBLIC API EXPOSURE ⭐ CRITICAL FOR APP.BLADE.PHP OVERRIDE
    // ─────────────────────────────────────────────────────────────────
    
    window.HistorisEntry = {
        load: loadEntryData,
        save: saveEntryData,
        buildTable: buildEntryTable,
        togglePanel: UI.togglePanel,
        setStatus: UI.setStatus,
        getState: () => ({ ...State }),
        utils: Utils,
    };
    
    // Backward compatibility
    window.entryBuildTable = buildEntryTable;
    window.loadEntryData = loadEntryData;
    window.saveEntryData = saveEntryData;
    window.toggleEntryPanel = UI.togglePanel;

    // ─────────────────────────────────────────────────────────────────
    // 🎬 INITIALIZATION
    // ─────────────────────────────────────────────────────────────────
    
    function init() {
        console.log('🔌 HistorisEntry.js initializing...');
        cacheElements();
        bindEvents();
        console.log('✅ HistorisEntry.js ready');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();