/**
 * HistorisImport.js v6.0 (ULTIMATE - Name Attribute Fallback)
 * Import Excel → isi tabel entry
 * 
 * ✅ WORKS WITH: name="entries[metrik_code][golongan_code]" pattern
 * ✅ NO changes needed to Blade or HistorisEntry.js
 * ✅ Auto-wait for dynamic table + comprehensive fallbacks
 * 
 * @push('scripts') in historis.blade.php
 */

(function () {
    'use strict';

    /* ══════════════════════════════════════════════════════
       CONFIGURATION
    ══════════════════════════════════════════════════════ */
    const CONFIG = {
        debug: true,
        allowedExts: ['xlsx', 'xls', 'csv'],
        maxWaitMs: 15000,       // 15 detik tunggu tabel
        pollIntervalMs: 250,    // Cek setiap 250ms
        namePattern: /^entries\[([^\]]+)\]\[([^\]]+)\]/i, // entries[metrik][gol]
    };

    const log = {
        i: (...a) => CONFIG.debug && console.log('[HistorisImport]', ...a),
        w: (...a) => CONFIG.debug && console.warn('[HistorisImport]', ...a),
        e: (...a) => console.error('[HistorisImport]', ...a),
        table: (d) => CONFIG.debug && console.table(d),
    };

    /* ══════════════════════════════════════════════════════
       ELEMENT CACHE
    ══════════════════════════════════════════════════════ */
    const $ = (id) => document.getElementById(id);
    const els = {
        fileInput: $('excelFileInput'),
        btnPick: $('btnPickExcel'),
        wrap: $('entryTableWrap'),
        panel: $('entryPanel'),
        overlay: $('tableDragOverlay'),
        status: $('importStatusBar'),
        statusTxt: $('importStatusText'),
        progTrack: $('importProgressTrack'),
        progFill: $('importProgressFill'),
        btnClear: $('btnClearImport'),
    };

    /* ══════════════════════════════════════════════════════
       MAPPINGS: Excel Header → DB Code
    ══════════════════════════════════════════════════════ */
    const METRIK = {
        'daya tersambung': 'daya_va',
        'delta gwh': 'delta_gwh',
        'growth': 'growth',
        'growth kwh': 'growth',
        'komposisi': 'komposisi',
        'kwh jual': 'kwh_jual',
        'mwh jual': 'mwh_jual',
        'pelanggan': 'pelanggan',
        'pertumbuhan yoy kumulatif': 'pertumbuhan_yoy',
        'pertumbuhan': 'pertumbuhan_yoy',
        'rp/kwh jual': 'rpkwh',
        'tarif': 'rpkwh',
        'rp/kwh': 'rpkwh',
        'rupiah pendapatan': 'rupiah',
        'rupiah penjualan': 'rupiah',
        'pendapatan': 'rupiah',
        'daya (va)': 'daya_va',
        'delta (gwh)': 'delta_gwh',
        'kwh': 'kwh_jual',
        'mwh': 'mwh_jual',
        'penjualan': 'rupiah',
    };

    const GOL = {
        'S': ['sosial', 's (sosial)', 's'],
        'R': ['rumah tangga', 'rumahtangga', 'rumah', 'residensial', 'r', 'rt'],
        'B': ['bisnis', 'b (bisnis)', 'b'],
        'I': ['industri', 'i (industri)', 'i'],
        'P': ['publik', 'pemerintah', 'publik/ pemerintah', 'publik/pemerintah', 'p'],
        'M': ['multiguna', 'multiguna v prabayar', 'meteran', 'm'],
        'TOTAL': ['total', 'jumlah', 'grand total'],
    };

    /* ══════════════════════════════════════════════════════
       INIT
    ══════════════════════════════════════════════════════ */
    function init() {
        // Bind UI events
        els.btnPick?.addEventListener('click', () => els.fileInput?.click());
        
        els.fileInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) onFileSelect(file);
        });
        
        els.btnClear?.addEventListener('click', reset);

        // Drag & drop support
        if (els.wrap) {
            ['dragenter', 'dragover'].forEach(ev => 
                els.wrap.addEventListener(ev, (e) => {
                    if (!e.dataTransfer?.types?.includes('Files')) return;
                    e.preventDefault(); e.stopPropagation();
                    els.overlay.style.display = 'flex';
                })
            );
            
            els.wrap.addEventListener('dragleave', (e) => {
                if (!els.wrap.contains(e.relatedTarget)) {
                    els.overlay.style.display = 'none';
                }
            });
            
            els.wrap.addEventListener('drop', (e) => {
                e.preventDefault(); e.stopPropagation();
                els.overlay.style.display = 'none';
                const file = e.dataTransfer.files[0];
                if (file && isValidFile(file)) {
                    onFileSelect(file);
                } else {
                    status('error', '⚠️ Format file harus .xlsx, .xls, atau .csv');
                }
            });
        }

        // Expose debug utils
        if (CONFIG.debug) exposeDebug();
        
        log.i('🚀 Initialized - Ultimate Mode (Name Attribute Fallback)');
    }

    /* ══════════════════════════════════════════════════════
       FILE VALIDATION
    ══════════════════════════════════════════════════════ */
    function isValidFile(file) {
        if (!file) return false;
        const ext = file.name.split('.').pop().toLowerCase();
        const validExt = CONFIG.allowedExts.includes(ext);
        const validSize = file.size <= 50 * 1024 * 1024; // 50MB
        
        if (!validExt) log.w('❌ Ekstensi tidak valid:', ext);
        if (!validSize) log.w('❌ File terlalu besar:', file.size);
        
        return validExt && validSize;
    }

    /* ══════════════════════════════════════════════════════
       MAIN FILE HANDLER
    ══════════════════════════════════════════════════════ */
    async function onFileSelect(file) {
        if (!isValidFile(file)) {
            return status('error', '⚠️ Format/tipe file tidak valid');
        }

        log.i('📁 Processing file:', file.name);
        status('loading', `🔄 Membaca <strong>${esc(file.name)}</strong>...`, true);
        progress(0, 15, 200);

        // ⏳ WAIT FOR INPUTS - Flexible discovery
        log.i('⏳ Waiting for table inputs to appear...');
        const inputs = await waitForInputs();
        
        if (!inputs || inputs.length === 0) {
            log.e('❌ CRITICAL: No inputs found after waiting');
            runDiagnostics();
            status('error', '❌ Input tabel tidak ditemukan.<br><small>Klik "Muat Data" dulu, lalu coba lagi.</small>');
            return;
        }

        log.i(`✅ Found ${inputs.length} inputs, proceeding with import...`);
        progress(15, 35, 300);

        // Read and parse Excel
        const reader = new FileReader();
        
        reader.onload = (e) => {
            try {
                parseExcel(e.target.result, file.name, inputs);
            } catch (err) {
                log.e('❌ Parse error:', err);
                status('error', `❌ Error: ${err.message || 'Format tidak valid'}`);
            }
        };
        
        reader.onerror = () => {
            log.e('❌ FileReader error');
            status('error', '❌ Gagal membaca file');
        };
        
        reader.readAsArrayBuffer(file);
    }

    /* ══════════════════════════════════════════════════════
       INPUT DISCOVERY - ULTIMATE FLEXIBILITY
    ══════════════════════════════════════════════════════ */
    function waitForInputs() {
        return new Promise((resolve) => {
            // Multiple selector strategies, from most specific to most generic
            const selectors = [
                // Standard attributes (if HistorisEntry.js uses them)
                '#entryTbody .data-input',
                'tbody .data-input',
                '.entry-table .data-input',
                'input[data-metrik-kode]',
                'input[name*="metrik"]',
                
                // Laravel-style name pattern: entries[xxx][yyy]
                '#entryTbody input[name^="entries["]',
                'tbody input[name^="entries["]',
                
                // Generic fallbacks
                '.entry-table input[type="number"]',
                '#entryTbody input[type="number"]',
                'table input[type="number"]',
            ];

            const tryFind = () => {
                for (const sel of selectors) {
                    try {
                        const found = document.querySelectorAll(sel);
                        if (found && found.length > 0) {
                            log.i(`✅ Found ${found.length} inputs via selector: "${sel}"`);
                            return Array.from(found);
                        }
                    } catch (e) {
                        log.w('⚠️ Selector failed:', sel, e.message);
                    }
                }
                return null;
            };

            // Try immediately first
            const existing = tryFind();
            if (existing && existing.length > 0) {
                return resolve(existing);
            }

            // Setup observers for dynamic content
            let observer = null;
            let timeout = null;
            let pollInterval = null;

            const cleanup = () => {
                if (observer) { observer.disconnect(); observer = null; }
                if (timeout) { clearTimeout(timeout); timeout = null; }
                if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
            };

            const checkAndResolve = () => {
                const found = tryFind();
                if (found && found.length > 0) {
                    cleanup();
                    resolve(found);
                }
            };

            // Polling strategy
            pollInterval = setInterval(checkAndResolve, CONFIG.pollIntervalMs);
            
            // MutationObserver for DOM changes
            try {
                observer = new MutationObserver((mutations) => {
                    // Only check if nodes were added
                    if (mutations.some(m => m.addedNodes.length > 0)) {
                        checkAndResolve();
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            } catch (e) {
                log.w('⚠️ MutationObserver not supported, using polling only');
            }

            // Timeout fallback
            timeout = setTimeout(() => {
                log.w('⏰ Timeout reached, doing final check...');
                cleanup();
                const final = tryFind();
                resolve(final || []);
            }, CONFIG.maxWaitMs);
        });
    }

    /* ══════════════════════════════════════════════════════
       DIAGNOSTICS - Run when inputs not found
    ══════════════════════════════════════════════════════ */
    function runDiagnostics() {
        log.w('🔍 Running diagnostics...');
        
        // Check if table wrapper exists and is visible
        if (els.wrap) {
            log.w('📦 entryTableWrap:', {
                exists: true,
                display: els.wrap.style.display,
                hidden: els.wrap.hidden,
                classList: Array.from(els.wrap.classList || [])
            });
        } else {
            log.w('❌ entryTableWrap element not found');
        }
        
        // Check tbody
        const tbody = $('entryTbody');
        if (tbody) {
            log.w('📋 #entryTbody:', {
                exists: true,
                innerHTML: tbody.innerHTML.substring(0, 200) + '...'
            });
        } else {
            log.w('❌ #entryTbody element not found');
        }
        
        // Check for ANY inputs in the page
        const allInputs = document.querySelectorAll('input');
        log.w(`🔢 Total inputs on page: ${allInputs.length}`);
        
        if (allInputs.length > 0) {
            const samples = Array.from(allInputs).slice(0, 5).map((inp, i) => ({
                '#': i,
                type: inp.type,
                name: inp.name,
                class: inp.className,
                dataset: inp.dataset ? Object.keys(inp.dataset) : [],
                id: inp.id,
                parent: inp.closest('tbody, table, form')?.id || inp.closest('tbody, table, form')?.className || 'unknown'
            }));
            log.table(samples);
        }
        
        // Check for common Laravel patterns
        const laravelInputs = document.querySelectorAll('input[name^="entries["]');
        log.w(`🔍 Inputs with name="entries[...]": ${laravelInputs.length}`);
        
        if (laravelInputs.length > 0) {
            const nameSamples = Array.from(laravelInputs).slice(0, 3).map(inp => inp.name);
            log.w('📝 Sample name attributes:', nameSamples);
        }
    }

    /* ══════════════════════════════════════════════════════
       EXCEL PARSING
    ══════════════════════════════════════════════════════ */
    function parseExcel(buf, fname, inputs) {
        try {
            progress(35, 60, 400);
            
            const wb = XLSX.read(buf, { type: 'array' });
            const sheetName = wb.SheetNames[0];
            const ws = wb.Sheets[sheetName];
            const rows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

            log.i(`📊 Loaded sheet "${sheetName}" with ${rows.length} rows`);
            
            // Debug: Show first few rows
            if (CONFIG.debug && rows.length > 0) {
                log.i('📋 Header row:', rows[0]?.slice(0, 10));
                if (rows[1]) log.i('📋 First data row:', rows[1]?.slice(0, 10));
            }

            // Detect corrupted structure (all data in one cell)
            if (rows[1]?.[0]?.toString().length > 100 && (!rows[2] || !rows[2][0]?.toString().trim())) {
                log.e('❌ Excel structure error: All metrics concatenated in first cell!');
                log.w('💡 Fix: Ensure each metric is on a separate row in Column A');
                status('error', '❌ Format Excel salah.<br><small>Pastikan tiap metrik di baris terpisah (Kolom A).</small>');
                return;
            }

            const result = fillData(rows, inputs);
            
            progress(60, 100, 300, () => {
                if (result.success > 0) {
                    let msg = `✅ <strong>${esc(fname)}</strong>: ${result.success} data berhasil diisi`;
                    if (result.skipped > 0 && CONFIG.debug) {
                        msg += `<br><small>${result.skipped} baris dilewati (cek console)</small>`;
                    }
                    status('success', msg);
                } else if (result.skipped > 0) {
                    status('warning', `⚠️ File dibaca tapi tidak ada data cocok.<br><small>Cek console untuk detail.</small>`);
                } else {
                    status('error', '❌ Tidak ada data yang bisa diproses.<br><small>Pastikan template Excel sesuai.</small>');
                }
            });
            
        } catch (err) {
            log.e('❌ Excel parse error:', err);
            status('error', `❌ Error: ${err.message || 'Format file tidak valid'}`);
        }
    }

    /* ══════════════════════════════════════════════════════
       DATA FILLING - With Name Attribute Parsing
    ══════════════════════════════════════════════════════ */
    function fillData(rows, inputs) {
        if (!rows || rows.length < 2) {
            log.w('⚠️ No data rows to process');
            return { success: 0, skipped: 0, errors: ['No data rows'] };
        }

        // Parse headers (first row)
        const headers = rows[0].map(h => String(h || '').trim().toLowerCase());
        log.i('🗂️ Excel headers:', headers);
        
        // Build column map for golongan
        const colMap = buildColMap(headers);
        log.i('🗺️ Golongan column mapping:', colMap);
        
        // Debug: Show sample input metadata
        if (CONFIG.debug && inputs.length > 0) {
            const sample = inputs.slice(0, 3).map(inp => {
                // Try to extract metrik/gol from name attribute
                const nameMatch = inp.name?.match(CONFIG.namePattern);
                return {
                    name: inp.name || '(no name)',
                    class: inp.className || '(no class)',
                    dataMetrik: inp.dataset?.metrikKode || inp.dataset?.metrik || '(no data-metrik)',
                    dataGol: inp.dataset?.golongan || '(no data-gol)',
                    parsed: nameMatch ? { metrik: nameMatch[1], gol: nameMatch[2] } : '(no name pattern match)'
                };
            });
            log.table(sample);
        }

        let stats = { success: 0, skipped: 0, errors: [] };

        // Process each data row (skip header at index 0)
        for (let r = 1; r < rows.length; r++) {
            const row = rows[r];
            
            // Skip empty rows
            if (!row || row.every(c => !c || String(c).trim() === '')) continue;

            // Column 0 = metric name from Excel
            const rawMetric = String(row[0] || '').trim().toLowerCase();
            if (!rawMetric) continue;

            // Resolve Excel metric name → DB code
            const metricCode = METRIK[rawMetric];
            if (!metricCode) {
                log.w(`⚠️ Unknown metric: "${rawMetric}"`);
                stats.skipped++;
                continue;
            }
            log.i(`✅ Mapped: "${rawMetric}" → "${metricCode}"`);

            // Process each golongan column
            for (const [golCode, colIdx] of Object.entries(colMap)) {
                if (colIdx >= row.length) continue;
                
                const rawVal = row[colIdx];
                if (!rawVal || rawVal === '' || rawVal === null || rawVal === undefined) continue;

                // Parse number (handle various formats)
                const num = parseNum(rawVal);
                if (isNaN(num)) {
                    log.w(`⚠️ Invalid number: "${rawVal}" for ${metricCode}/${golCode}`);
                    continue;
                }

                // Find matching input
                const inp = findMatch(inputs, metricCode, golCode);
                if (inp) {
                    inp.value = num;
                    inp.classList.add('has-value');
                    
                    // Trigger Laravel validation events
                    inp.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
                    inp.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    stats.success++;
                    log.i(`🎯 Set ${metricCode}/${golCode} = ${num}`);
                } else {
                    log.w(`⚠️ No input found for ${metricCode}/${golCode}`);
                }
            }
        }

        log.i(`📊 Import complete: ${stats.success} filled, ${stats.skipped} skipped`);
        return stats;
    }

    /* ══════════════════════════════════════════════════════
       BUILD COLUMN MAP: Excel Header → Golongan Code
    ══════════════════════════════════════════════════════ */
    function buildColMap(headers) {
        const map = {};
        
        for (const [golCode, aliases] of Object.entries(GOL)) {
            const colIdx = headers.findIndex(h => aliases.includes(h));
            if (colIdx !== -1) {
                map[golCode] = colIdx;
                log.i(`🗂️ Golongan "${golCode}" → column ${colIdx} ("${headers[colIdx]}")`);
            } else {
                log.w(`⚠️ Golongan "${golCode}" not found in headers. Searched:`, aliases);
            }
        }
        
        return map;
    }

    /* ══════════════════════════════════════════════════════
       FIND MATCHING INPUT - Multiple Strategies
    ══════════════════════════════════════════════════════ */
    function findMatch(inputs, metrikCode, golCode) {
        // Strategy 1: data attributes (most reliable if present)
        for (const inp of inputs) {
            const iMetrik = (inp.dataset?.metrikKode || inp.dataset?.metrik || '').toLowerCase();
            const iGol = (inp.dataset?.golongan || '').toUpperCase();
            
            if (iMetrik === metrikCode.toLowerCase() && iGol === golCode.toUpperCase()) {
                return inp;
            }
        }
        
        // Strategy 2: Parse name attribute: entries[metrik][gol]
        for (const inp of inputs) {
            const nameMatch = inp.name?.match(CONFIG.namePattern);
            if (nameMatch) {
                const [, nameMetrik, nameGol] = nameMatch;
                if (nameMetrik.toLowerCase() === metrikCode.toLowerCase() && 
                    nameGol.toUpperCase() === golCode.toUpperCase()) {
                    return inp;
                }
            }
        }
        
        // Strategy 3: Fuzzy match on name (less reliable but catches variations)
        for (const inp of inputs) {
            if (!inp.name) continue;
            const name = inp.name.toLowerCase();
            if (name.includes(metrikCode.toLowerCase()) && name.includes(golCode.toLowerCase())) {
                return inp;
            }
        }
        
        return null;
    }

    /* ══════════════════════════════════════════════════════
       NUMBER PARSING - Handle Various Formats
    ══════════════════════════════════════════════════════ */
    function parseNum(v) {
        if (typeof v === 'number') return v;
        if (v === null || v === undefined) return NaN;
        
        let s = String(v).trim();
        if (!s) return NaN;
        
        // Remove currency symbols, spaces, non-numeric chars except .,-
        s = s.replace(/[^0-9.,\-]/g, '');
        if (!s) return NaN;
        
        // Handle European format: 1.234,56 → 1234.56
        if (s.includes(',') && s.includes('.')) {
            const lastComma = s.lastIndexOf(',');
            const lastDot = s.lastIndexOf('.');
            if (lastComma > lastDot) {
                // Comma is decimal separator
                s = s.replace(/\./g, '').replace(',', '.');
            } else {
                // Dot is decimal separator
                s = s.replace(/,/g, '');
            }
        } else if (s.includes(',')) {
            // Could be decimal (123,45) or thousand (1,234)
            const parts = s.split(',');
            if (parts[parts.length - 1].length <= 2) {
                // Likely decimal separator
                s = s.replace(',', '.');
            } else {
                // Likely thousand separator
                s = s.replace(/,/g, '');
            }
        }
        
        return parseFloat(s);
    }

    /* ══════════════════════════════════════════════════════
       UI HELPERS
    ══════════════════════════════════════════════════════ */
    function status(type, html, showProg = false) {
        if (!els.status) return;
        
        els.status.style.display = 'block';
        els.statusTxt.innerHTML = html;
        els.status.className = `import-status-bar import-status-${type}`;
        
        if (els.progTrack) {
            els.progTrack.style.display = showProg ? 'block' : 'none';
        }
        if (!showProg && els.progFill) {
            els.progFill.style.width = '0%';
        }
    }

    function progress(from, to, dur, cb) {
        if (!els.progFill) { 
            cb?.(); 
            return; 
        }
        
        const start = performance.now();
        
        const step = (now) => {
            const p = Math.min((now - start) / dur, 1);
            const value = from + (to - from) * p;
            els.progFill.style.width = `${value}%`;
            
            if (p < 1) {
                requestAnimationFrame(step);
            } else {
                cb?.();
            }
        };
        
        requestAnimationFrame(step);
    }

    function reset() {
        if (els.status) els.status.style.display = 'none';
        if (els.fileInput) els.fileInput.value = '';
        if (els.progFill) els.progFill.style.width = '0%';
    }

    function esc(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /* ══════════════════════════════════════════════════════
       DEBUG UTILS - Console Access
    ══════════════════════════════════════════════════════ */
    function exposeDebug() {
        window.HistorisDebug = {
            // Scan page for inputs
            scan: () => {
                console.group('🔎 HistorisImport - Full Scan');
                
                console.log('📦 entryTableWrap:', {
                    exists: !!els.wrap,
                    visible: els.wrap?.style.display !== 'none' && !els.wrap?.hidden
                });
                
                console.log('📋 #entryTbody:', {
                    exists: !!$('entryTbody'),
                    childCount: $('entryTbody')?.childElementCount
                });
                
                console.log('🔢 All inputs:', document.querySelectorAll('input').length);
                console.log('✅ .data-input:', document.querySelectorAll('.data-input').length);
                console.log('✅ [data-metrik-kode]:', document.querySelectorAll('input[data-metrik-kode]').length);
                console.log('✅ name^="entries[":', document.querySelectorAll('input[name^="entries["]').length);
                
                // Show sample inputs
                const samples = document.querySelectorAll('input').length > 0 
                    ? Array.from(document.querySelectorAll('input')).slice(0, 5).map(inp => ({
                        name: inp.name,
                        class: inp.className,
                        dataset: inp.dataset,
                        type: inp.type
                    }))
                    : [];
                if (samples.length) console.table(samples);
                
                console.groupEnd();
            },
            
            // Force trigger import with current file
            forceImport: (file) => {
                if (!file) {
                    file = els.fileInput?.files?.[0];
                    if (!file) {
                        log.w('⚠️ No file selected. Pick a file first.');
                        return;
                    }
                }
                log.i('🔄 Force import:', file.name);
                onFileSelect(file);
            },
            
            // Test number parsing
            testParse: (val) => {
                const result = parseNum(val);
                log.i(`parseNum("${val}") = ${result}`);
                return result;
            },
            
            // Show current config
            config: () => CONFIG,
        };
        
        log.i('🔧 Debug utils available: window.HistorisDebug');
        log.i('   → HistorisDebug.scan() - Diagnose input discovery');
        log.i('   → HistorisDebug.forceImport() - Retry import');
        log.i('   → HistorisDebug.testParse("1.234,56") - Test number parsing');
    }

    /* ══════════════════════════════════════════════════════
       STARTUP
    ══════════════════════════════════════════════════════ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already ready
        init();
    }

})();