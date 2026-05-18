/**
 * Excel Import Module for Fact Historis Entry
 * Handles file upload, validation, and import process
 */

(function() {
    'use strict';

    const ExcelImport = {
        /**
         * Initialize Excel Import functionality
         */
        init() {
            this.cacheElements();
            this.bindEvents();
            console.log('Excel Import module initialized');
        },

        /**
         * Cache DOM elements for better performance
         */
        cacheElements() {
            this.card = document.getElementById('excelImportCard');
            this.fileInput = document.getElementById('excelFileInput');
            this.uploadArea = document.getElementById('uploadArea');
            this.filePreview = document.getElementById('filePreview');
            this.fileName = document.getElementById('fileName');
            this.importActions = document.getElementById('importActions');
            this.btnProcess = document.getElementById('btnProcessImport');
            this.btnCancel = document.getElementById('btnCancelImport');
            this.progress = document.getElementById('importProgress');
            this.progressFill = document.getElementById('progressFill');
            this.progressText = document.getElementById('progressText');
            this.result = document.getElementById('importResult');
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        },

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Show/hide card based on period selection
            const checkPeriod = () => {
                const t = document.getElementById('inputTahun')?.value;
                const b = document.getElementById('inputBulan')?.value;
                if (this.card) {
                    this.card.style.display = (t && b) ? 'block' : 'none';
                }
            };

            document.getElementById('inputTahun')?.addEventListener('change', checkPeriod);
            document.getElementById('inputBulan')?.addEventListener('change', checkPeriod);
            checkPeriod(); // Initial check

            // File input change
            this.fileInput?.addEventListener('change', (e) => this.handleFile(e));

            // Upload area click
            this.uploadArea?.addEventListener('click', () => this.fileInput?.click());

            // Remove file button
            document.getElementById('removeFile')?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.clear();
            });

            // Process import button
            this.btnProcess?.addEventListener('click', () => this.process());

            // Cancel button
            this.btnCancel?.addEventListener('click', () => this.clear());
        },

        /**
         * Handle file selection
         * @param {Event} e - Change event
         */
        handleFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file extension
            if (!file.name.match(/\.(xlsx|xls|csv)$/i)) {
                return this.showResult('Format file tidak valid. Gunakan .xlsx, .xls, atau .csv', 'error');
            }

            // Validate file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                return this.showResult('Ukuran file melebihi 10MB', 'error');
            }

            // Show file preview
            this.fileName.textContent = file.name;
            this.filePreview.style.display = 'flex';
            this.uploadArea.style.display = 'none';
            this.importActions.style.display = 'flex';
            this.clearResult();
        },

        /**
         * Clear file selection and reset UI
         */
        clear() {
            if (this.fileInput) this.fileInput.value = '';
            if (this.filePreview) this.filePreview.style.display = 'none';
            if (this.uploadArea) this.uploadArea.style.display = 'block';
            if (this.importActions) this.importActions.style.display = 'none';
            if (this.progress) this.progress.style.display = 'none';
            this.clearResult();
        },

        /**
         * Process Excel import
         */
        async process() {
            const file = this.fileInput?.files[0];
            const tahun = document.getElementById('inputTahun')?.value;
            const bulan = document.getElementById('inputBulan')?.value;

            // Validation
            if (!file || !tahun || !bulan) {
                return this.showResult('Pilih file, tahun, dan bulan terlebih dahulu', 'error');
            }

            // Prepare FormData
            const formData = new FormData();
            formData.append('file_excel', file);
            formData.append('tahun', tahun);
            formData.append('bulan', bulan);
            if (this.csrfToken) {
                formData.append('_token', this.csrfToken);
            }

            // Show progress
            this.progress.style.display = 'block';
            this.btnProcess.disabled = true;
            this.btnCancel.disabled = true;
            this.setProgress(30, 'Mengupload file...');

            try {
                this.setProgress(60, 'Memproses data...');

                const response = await fetch('/fact-historis/import-excel', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                this.setProgress(100, 'Selesai!');

                if (data.success) {
                    this.showResult(
                        `✓ ${data.message} (${data.imported} data diimpor, Baru: ${data.created}, Update: ${data.updated})`,
                        'success'
                    );

                    // Auto-refresh data table if function exists
                    if (typeof window.loadExistingData === 'function') {
                        setTimeout(() => {
                            window.loadExistingData(tahun, bulan);
                        }, 1200);
                    }
                } else {
                    const errors = data.errors ? `<br><small>${data.errors.join('<br>')}</small>` : '';
                    this.showResult(`✗ ${data.message}${errors}`, 'error');
                }
            } catch (error) {
                console.error('Import error:', error);
                this.showResult('✗ Gagal terhubung ke server. Periksa koneksi internet Anda.', 'error');
            } finally {
                setTimeout(() => {
                    this.progress.style.display = 'none';
                    this.btnProcess.disabled = false;
                    this.btnCancel.disabled = false;
                }, 400);
            }
        },

        /**
         * Update progress bar
         * @param {number} percent - Progress percentage (0-100)
         * @param {string} text - Status text
         */
        setProgress(percent, text) {
            if (this.progressFill) {
                this.progressFill.style.width = `${percent}%`;
            }
            if (this.progressText) {
                this.progressText.textContent = text;
            }
        },

        /**
         * Show result message
         * @param {string} message - Message to display
         * @param {string} type - Message type ('success' or 'error')
         */
        showResult(message, type = 'info') {
            if (!this.result) return;

            this.result.innerHTML = message;
            this.result.className = type;
            this.result.style.display = 'block';

            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    this.result.style.display = 'none';
                }, 5000);
            }
        },

        /**
         * Clear result message
         */
        clearResult() {
            if (this.result) {
                this.result.style.display = 'none';
                this.result.textContent = '';
                this.result.className = '';
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('excelImportCard')) {
                ExcelImport.init();
            }
        });
    } else {
        if (document.getElementById('excelImportCard')) {
            ExcelImport.init();
        }
    }

    // Expose to global scope if needed
    window.ExcelImport = ExcelImport;

})();