/**
 * HistorisEntry.js — Entry Panel Handler (2026+)
 * Path: public/js/Dashboard/pages/HistorisEntry.js
 */
'use strict';

(function () {

    /* ─────────────────────────────────────────────────────────
       STATE & CONFIG
    ───────────────────────────────────────────────────────── */
    const State = {
        isLocked:    false,
        panelOpen:   false,
        currentData: null,
    };

    const Config = {
        endpoints: {
            loadData: '/fact-historis/existing-data',
            saveData: '/fact-historis',
        },
        metrics: [
            { kode: 'daya_va',         nama: 'Daya Tersambung',           satuan: 'VA',         autoConvertVA: true },
            { kode: 'delta_gwh',       nama: 'Delta GWh',                 satuan: 'GWh'         },
            { kode: 'growth',          nama: 'Growth',                    satuan: '%'           },
            { kode: 'komposisi',       nama: 'Komposisi',                 satuan: '%'           },
            { kode: 'kwh_jual',        nama: 'KWH Jual',                  satuan: 'KWH'         },
            { kode: 'mwh_jual',        nama: 'MWH Jual',                  satuan: 'MWH'         },
            { kode: 'pelanggan',       nama: 'Pelanggan',                 satuan: 'pelanggan'   },
            { kode: 'pertumbuhan_yoy', nama: 'Pertumbuhan YoY Kumulatif', satuan: '%'           },
            { kode: 'rpkwh',           nama: 'RP/KWH Jual',               satuan: 'Rp/KWh'     },
            { kode: 'rupiah',          nama: 'Rupiah Penjualan',          satuan: 'Rp'          },
        ],
        /* Kode golongan → class badge CSS */
        golBadgeClass: {
            S: 'h-s', R: 'h-r', B: 'h-b',
            I: 'h-i', P: 'h-p', M: 'h-m', TOTAL: 'h-t',
        },
        statusStyles: {
            success: 'background:rgba(0,200,83,0.08);border:1px solid rgba(0,200,83,0.3);color:#69f0ae;',
            error:   'background:rgba(244,67,54,0.08);border:1px solid rgba(244,67,54,0.3);color:#ef9a9a;',
            warning: 'background:rgba(255,152,0,0.08);border:1px solid rgba(255,152,0,0.3);color:#ffcc80;',
            info:    'background:rgba(33,150,243,0.08);border:1px solid rgba(33,150,243,0.3);color:#90caf9;',
        },
        bulanNames: ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
    };

    /* ─────────────────────────────────────────────────────────
       DOM CACHE
    ───────────────────────────────────────────────────────── */
    const DOM = {};

    function cacheElements() {
        [
            'inputTahun','inputBulan','btnLoadData','entrySaveBtn',
            'btnFillAll','btnClearAll','entryStatus','entryLocked',
            'entryTableWrap','entryThead','entryTbody',
            'entryPanel','entryChevron','entryToggleBtn',
        ].forEach(id => { DOM[id] = document.getElementById(id); });
    }

    /* ─────────────────────────────────────────────────────────
       UTILITIES
    ───────────────────────────────────────────────────────── */
    const Utils = {
        getCsrfToken: () =>
            document.querySelector('meta[name="csrf-token"]')?.content ?? '',

        getBulanName: num => Config.bulanNames[+num] || '',

        parseInteger: (val, fallback = null) => {
            const n = parseInt(val, 10);
            return isNaN(n) ? fallback : n;
        },

        isValidDate: (tahun, bulan) =>
            Number.isInteger(tahun) && Number.isInteger(bulan) &&
            tahun >= 1900 && tahun <= 2100 && bulan >= 1 && bulan <= 12,

        getSatuan: metrikKode =>
            Config.metrics.find(m => m.kode === metrikKode)?.satuan ?? null,

        /**
         * Konversi nilai VA ke KVA atau MVA untuk tampilan.
         * Aturan: >= 1.000.000 VA → MVA, sisanya → KVA.
         * Mengembalikan { nilai: string, satuan: 'KVA'|'MVA' }
         * atau null jika input tidak valid.
         */
        convertVADisplay(vaValue) {
            const num = parseFloat(vaValue);
            if (isNaN(num) || vaValue === '' || vaValue === null || vaValue === undefined) {
                return null;
            }
            if (num >= 1_000_000) {
                return {
                    nilai:  (num / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 3 }),
                    satuan: 'MVA',
                };
            }
            return {
                nilai:  (num / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }),
                satuan: 'KVA',
            };
        },

        /**
         * Dari semua input daya_va yang terisi, tentukan label satuan
         * dominan untuk kolom: MVA jika ada satu pun ≥ 1.000.000, else KVA.
         */
        dominantVASatuan() {
            const inputs = DOM.entryTbody?.querySelectorAll(
                '.data-input[data-metrik-kode="daya_va"]'
            ) ?? [];
            const hasMVA = [...inputs].some(inp =>
                inp.value && parseFloat(inp.value) >= 1_000_000
            );
            return hasMVA ? 'MVA' : 'KVA';
        },
    };

    /* ─────────────────────────────────────────────────────────
       UI HELPERS
    ───────────────────────────────────────────────────────── */
    const UI = {
        setStatus(message, type = 'info') {
            if (!DOM.entryStatus) return;
            const base = 'display:block;padding:8px 14px;border-radius:7px;font-size:0.78rem;margin-bottom:10px;';
            DOM.entryStatus.style.cssText =
                (Config.statusStyles[type] ?? Config.statusStyles.info) + base;
            DOM.entryStatus.textContent = message;

            if (type === 'success') {
                setTimeout(() => {
                    if (DOM.entryStatus?.textContent === message)
                        DOM.entryStatus.style.display = 'none';
                }, 4000);
            }
        },

        setLoading(loading) {
            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = loading ? 'none' : 'block';
            if (DOM.entryLocked)   DOM.entryLocked.style.display = 'none';
            if (DOM.entrySaveBtn)  DOM.entrySaveBtn.style.display =
                loading || State.isLocked ? 'none' : 'inline-flex';
            if (DOM.btnLoadData) {
                DOM.btnLoadData.disabled  = loading;
                DOM.btnLoadData.innerHTML = loading
                    ? '<i class="fas fa-spinner fa-spin"></i> Loading...'
                    : '<i class="fas fa-rotate-right"></i> Muat Data';
            }
        },

        togglePanel() {
            if (!DOM.entryPanel) return;
            State.panelOpen = !State.panelOpen;
            DOM.entryPanel.hidden = !State.panelOpen;
            DOM.entryToggleBtn?.setAttribute('aria-expanded', String(State.panelOpen));
            DOM.entryChevron?.classList.toggle('open', State.panelOpen);
        },

        showLocked(message) {
            if (DOM.entryLocked) {
                DOM.entryLocked.innerHTML =
                    `<i class="fas fa-lock"></i> <strong>Periode Terkunci</strong><br>
                     ${message || 'Data sudah tidak dapat diedit.'}`;
                DOM.entryLocked.style.display = 'block';
            }
            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = 'none';
            if (DOM.entrySaveBtn)   DOM.entrySaveBtn.style.display = 'none';
            State.isLocked = true;
        },

        highlightSaved() {
            document.querySelectorAll('#entryTbody .data-input.has-value').forEach(inp => {
                inp.style.borderColor = '#00c853';
                inp.style.background  = 'rgba(0,200,83,0.05)';
                setTimeout(() => {
                    inp.style.borderColor = '';
                    inp.style.background  = '';
                }, 2000);
            });
        },
    };

    /* ─────────────────────────────────────────────────────────
       TABLE RENDERER
       Synced dengan historis-components.css:
         - col.c-metrik / col.c-satuan / col.c-data
         - td.td-metrik / td.td-satuan / td.td-val
         - input.data-input  (bukan .entry-input)
         - data-metrik-kode  (bukan data-metrik / data-metrik-nama)
         - th class .h-s/.h-r/.h-b/... untuk badge golongan

       Khusus daya_va (autoConvertVA: true):
         - Input tetap menerima dan menyimpan nilai mentah dalam VA.
         - td.td-satuan berisi <span id="satuan-daya_va"> yang
           menampilkan "KVA" atau "MVA" secara dinamis.
         - Di bawah setiap input, teks hint kecil menampilkan
           nilai hasil konversi (mis. "12,50 KVA") secara real-time.
         - Tooltip input menampilkan nilai VA asli.
    ───────────────────────────────────────────────────────── */
    function buildEntryTable(data) {
        if (!DOM.entryThead || !DOM.entryTbody) return;

        /* Ambil golongan dari chips yang ada di DOM */
        let golongans = [...document.querySelectorAll('.cb-golongan')].map(cb => ({
            kode: cb.value,
            nama: cb.closest('label')?.querySelector('.chip-text')?.textContent?.trim()
                  ?? cb.value,
        })).filter(g => g.kode);

        if (!golongans.length) {
            golongans = [
                { kode: 'S',     nama: 'Sosial'       },
                { kode: 'R',     nama: 'Rumah Tangga' },
                { kode: 'B',     nama: 'Bisnis'       },
                { kode: 'I',     nama: 'Industri'     },
                { kode: 'P',     nama: 'Publik'       },
                { kode: 'M',     nama: 'Multiguna'    },
                { kode: 'TOTAL', nama: 'Total'        },
            ];
        }

        /* ── colgroup (lebar kolom terkontrol) ── */
        const existingColgroup = DOM.entryTbody.closest('table')?.querySelector('colgroup');
        if (existingColgroup) existingColgroup.remove();

        const table = DOM.entryTbody.closest('table');
        if (table) {
            const cg = document.createElement('colgroup');
            cg.innerHTML =
                `<col class="c-metrik"><col class="c-satuan">` +
                golongans.map(() => `<col class="c-data">`).join('');
            table.prepend(cg);
        }

        /* ── HEADER ── */
        DOM.entryThead.innerHTML = `
            <tr>
                <th class="h-metrik">Metrik</th>
                <th class="h-satuan">Satuan</th>
                ${golongans.map(g => {
                    const badgeCls = Config.golBadgeClass[g.kode] ?? 'h-t';
                    return `<th class="${badgeCls}">
                        <span class="gol-badge">${g.kode === 'TOTAL' ? '∑' : g.kode}</span>
                        ${g.nama}
                    </th>`;
                }).join('')}
            </tr>
        `;

        /* ── BODY ── */
        DOM.entryTbody.innerHTML = Config.metrics.map(metric => {

            const existingRows = data?.[metric.kode] ?? [];

            /* ── DAYA VA: input biasa, kolom satuan & hint dikonversi otomatis ── */
            if (metric.autoConvertVA) {

                /* Label satuan awal berdasarkan data yang sudah ada */
                const sampleVal   = existingRows.find(r => r.nilai != null)?.nilai ?? '';
                const initSatuan  = sampleVal !== ''
                    ? (Utils.convertVADisplay(sampleVal)?.satuan ?? 'KVA')
                    : 'KVA';

                const cells = golongans.map(gol => {
                    const row      = existingRows.find(r => r.golongan_kode === gol.kode);
                    const nilai    = row?.nilai ?? '';
                    const hasValue = nilai !== '';
                    const conv     = hasValue ? Utils.convertVADisplay(nilai) : null;

                    return `
                        <td class="td-val">
                            <input type="number" step="any"
                                class="data-input${hasValue ? ' has-value' : ''}"
                                data-metrik-kode="${metric.kode}"
                                data-golongan="${gol.kode}"
                                value="${hasValue ? nilai : ''}"
                                placeholder="0"
                                title="${hasValue ? `${Number(nilai).toLocaleString('id-ID')} VA` : ''}"
                                ${State.isLocked ? 'disabled' : ''}>
                            <span class="va-hint"
                                  data-golongan="${gol.kode}"
                                  style="display:block;font-size:0.72rem;
                                         color:var(--text-muted,#9e9e9e);
                                         margin-top:2px;text-align:right;
                                         min-height:1em;pointer-events:none;
                                         user-select:none;letter-spacing:0.01em;">
                                ${conv ? `${conv.nilai} ${conv.satuan}` : ''}
                            </span>
                        </td>`;
                }).join('');

                return `
                    <tr>
                        <td class="td-metrik">${metric.nama}</td>
                        <td class="td-satuan">
                            <span id="satuan-daya_va">${initSatuan}</span>
                        </td>
                        ${cells}
                    </tr>`;
            }

            /* ── NORMAL: semua metrik lainnya ── */
            const cells = golongans.map(gol => {
                const row      = existingRows.find(r => r.golongan_kode === gol.kode);
                const nilai    = row?.nilai ?? '';
                const hasValue = nilai !== '' && nilai !== null && nilai !== undefined;

                return `
                    <td class="td-val">
                        <input type="number" step="any"
                            class="data-input${hasValue ? ' has-value' : ''}"
                            data-metrik-kode="${metric.kode}"
                            data-golongan="${gol.kode}"
                            value="${hasValue ? nilai : ''}"
                            placeholder="0"
                            ${State.isLocked ? 'disabled' : ''}>
                    </td>`;
            }).join('');

            return `
                <tr>
                    <td class="td-metrik">${metric.nama}</td>
                    <td class="td-satuan">${metric.satuan}</td>
                    ${cells}
                </tr>`;
        }).join('');

        /* ── Input listeners ── */
        DOM.entryTbody.querySelectorAll('.data-input').forEach(input => {
            input.addEventListener('input', function () {
                this.classList.toggle('has-value', this.value !== '');

                /* Real-time konversi untuk baris daya_va */
                if (this.dataset.metrikKode === 'daya_va') {
                    const conv = this.value !== '' ? Utils.convertVADisplay(this.value) : null;

                    /* Update hint di bawah input ini */
                    const hint = this.closest('td')?.querySelector('.va-hint');
                    if (hint) {
                        hint.textContent = conv ? `${conv.nilai} ${conv.satuan}` : '';
                    }

                    /* Update tooltip */
                    this.title = this.value
                        ? `${Number(this.value).toLocaleString('id-ID')} VA`
                        : '';

                    /* Update label kolom satuan secara dominan */
                    const satuanSpan = document.getElementById('satuan-daya_va');
                    if (satuanSpan) satuanSpan.textContent = Utils.dominantVASatuan();
                }
            });
        });
    }

    /* ─────────────────────────────────────────────────────────
       DATA LOADER
    ───────────────────────────────────────────────────────── */
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
            const res = await fetch(
                `${Config.endpoints.loadData}?tahun=${tahun}&bulan=${bulan}`,
                { headers: { Accept: 'application/json' } }
            );

            const ct = res.headers.get('content-type') ?? '';
            if (!ct.includes('application/json')) {
                const text = await res.text();
                if (text.trim().startsWith('<'))
                    throw new Error('Session habis. Silakan refresh atau login ulang.');
                throw new Error(`Response tidak valid (${ct})`);
            }

            const result = await res.json();

            if (!res.ok || result.locked || !result.success) {
                UI.showLocked(result.message || 'Data terkunci');
                return;
            }

            State.isLocked    = false;
            State.currentData = result.data ?? {};

            buildEntryTable(State.currentData);

            if (DOM.entryTableWrap) DOM.entryTableWrap.style.display = 'block';
            if (DOM.entrySaveBtn)   DOM.entrySaveBtn.style.display   = 'inline-flex';

            const suffix = result.reason ? ` • ${result.reason}` : '';
            UI.setStatus(`✓ ${Utils.getBulanName(bulan)} ${tahun} dimuat${suffix}`, 'success');

        } catch (err) {
            console.error('[HistorisEntry] loadEntryData:', err);
            UI.setStatus(`Gagal memuat: ${err.message}`, 'error');
        } finally {
            UI.setLoading(false);
        }
    }

    /* ─────────────────────────────────────────────────────────
       DATA SAVER
       daya_va tetap disimpan dalam VA (satuan asli).
       Konversi KVA/MVA hanya untuk tampilan, tidak ikut dikirim.
    ───────────────────────────────────────────────────────── */
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
        document.querySelectorAll('#entryTbody .data-input').forEach(input => {
            const nilai = parseFloat(input.value);
            if (!isNaN(nilai) && input.dataset.metrikKode && input.dataset.golongan) {
                entries.push({
                    metrik_kode:   input.dataset.metrikKode,
                    golongan_kode: input.dataset.golongan,
                    nilai,
                    satuan: Utils.getSatuan(input.dataset.metrikKode),
                });
            }
        });

        if (!entries.length) {
            UI.setStatus('Tidak ada data untuk disimpan', 'warning');
            return;
        }

        const btn          = DOM.entrySaveBtn;
        const originalHtml = btn?.innerHTML;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        }

        try {
            const csrf = Utils.getCsrfToken();
            if (!csrf) throw new Error('CSRF token tidak ditemukan');

            const res = await fetch(Config.endpoints.saveData, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept:         'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ tahun, bulan, data: entries }),
            });

            const result = await res.json();
            if (!res.ok) throw new Error(result.message ?? `HTTP ${res.status}`);

            if (result.success) {
                UI.setStatus(`✓ ${result.message}`, 'success');
                UI.highlightSaved();
                return true;
            }
            throw new Error(result.message ?? 'Gagal menyimpan data');

        } catch (err) {
            console.error('[HistorisEntry] saveEntryData:', err);
            UI.setStatus(`Gagal: ${err.message}`, 'error');
            return false;
        } finally {
            if (btn && originalHtml) { btn.disabled = false; btn.innerHTML = originalHtml; }
        }
    }

    /* ─────────────────────────────────────────────────────────
       FORM UTILITIES
    ───────────────────────────────────────────────────────── */
    function fillAllInputs(value = 0) {
        if (State.isLocked) return;
        document.querySelectorAll('#entryTbody .data-input').forEach(input => {
            input.value = value;
            input.classList.toggle('has-value', value !== '');
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
        UI.setStatus('Semua field diisi dengan 0', 'info');
    }

    function clearAllInputs() {
        if (State.isLocked) return;
        document.querySelectorAll('#entryTbody .data-input').forEach(input => {
            input.value = '';
            input.classList.remove('has-value');
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
        UI.setStatus('Form dikosongkan', 'info');
    }

    /* ─────────────────────────────────────────────────────────
       EVENT BINDING
    ───────────────────────────────────────────────────────── */
    function bindEvents() {
        DOM.entryToggleBtn?.addEventListener('click', UI.togglePanel.bind(UI));
        DOM.btnLoadData?.addEventListener('click',   e => { e?.preventDefault(); loadEntryData(); });
        DOM.entrySaveBtn?.addEventListener('click',  e => { e?.preventDefault(); saveEntryData(); });
        DOM.btnFillAll?.addEventListener('click',    () => fillAllInputs(0));
        DOM.btnClearAll?.addEventListener('click',   clearAllInputs);
    }

    /* ─────────────────────────────────────────────────────────
       PUBLIC API
    ───────────────────────────────────────────────────────── */
    window.HistorisEntry = {
        load:        loadEntryData,
        save:        saveEntryData,
        buildTable:  buildEntryTable,
        togglePanel: () => UI.togglePanel(),
        setStatus:   (msg, type) => UI.setStatus(msg, type),
        getState:    () => ({ ...State }),
        utils:       Utils,
    };

    /* Backward compat */
    window.entryBuildTable  = buildEntryTable;
    window.loadEntryData    = loadEntryData;
    window.saveEntryData    = saveEntryData;
    window.toggleEntryPanel = () => UI.togglePanel();

    /* ─────────────────────────────────────────────────────────
       INIT
    ───────────────────────────────────────────────────────── */
    function init() {
        cacheElements();
        bindEvents();

        /* Sync chip checked state dari server-rendered HTML */
        document.querySelectorAll('.cb-golongan').forEach(cb => {
            cb.closest('.hist-gol-chip')?.classList.toggle('checked', cb.checked);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();