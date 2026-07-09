/**
 * CustomerStats.js
 * Class standalone untuk mengelola Stats Cards (Total, MoM, YoY)
 * Lokasi: public/js/components/CustomerStats.js
 * 
 * ✅ Tidak mengubah file existing
 * ✅ Mudah di-import di blade manapun
 * ✅ Dependency: PelangganService
 */

export default class CustomerStats {
    /**
     * @param {PelangganService} pelangganService - Instance service yang sudah di-init
     * @param {Object} config - Optional config
     */
    constructor(pelangganService, config = {}) {
        this.service = pelangganService;
        this.config = {
            apiBaseUrl: '/api',
            autoLoad: true,
            year: null,
            month: null,
            ...config
        };
        
        // Element IDs (sesuai Stats.blade.php)
        this.elements = {
            totalCustomer: 'statTotalCustomer',
            newCustomer: 'statNewCustomer',
            growthRate: 'statGrowthRate'
        };
        
        // Colors
        this.colors = {
            positive: '#4caf50',
            negative: '#f44336',
            neutral: '#2196f3',
            muted: '#999'
        };
        
        // Cache
        this.historicalCache = new Map();
        
        // Bind methods
        this._init();
    }
    
    // ─────────────────────────────────────────────────────────────
    // 🔧 INTERNAL METHODS
    // ─────────────────────────────────────────────────────────────
    
    _init() {
        if (this.config.autoLoad) {
            this._onReady(() => this.load());
        }
    }
    
    _onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }
    
    _getElement(id) {
        return document.getElementById(id);
    }
    
    _setText(id, text) {
        const el = this._getElement(id);
        if (el) el.textContent = text;
    }
    
    _setHTML(id, html) {
        const el = this._getElement(id);
        if (el) el.innerHTML = html;
    }
    
    _setColor(id, value) {
        const el = this._getElement(id);
        if (!el) return;
        
        if (value === null || value === undefined || Number.isNaN(value)) {
            el.style.color = this.colors.muted;
        } else if (value >= 0) {
            el.style.color = this.colors.positive;
        } else {
            el.style.color = this.colors.negative;
        }
    }
    
    _formatNumber(num) {
        if (num === null || num === undefined || Number.isNaN(num)) return '---';
        return new Intl.NumberFormat('id-ID').format(Math.round(num));
    }
    
    _formatChange(value) {
        if (value === null || value === undefined || Number.isNaN(value)) return '---';
        const sign = value >= 0 ? '+' : '';
        return `${sign}${this._formatNumber(value)}`;
    }
    
    _formatPercent(value) {
        if (value === null || value === undefined || Number.isNaN(value)) return '---';
        const sign = value >= 0 ? '+' : '';
        const color = value >= 0 ? this.colors.positive : this.colors.negative;
        return `<span style="color: ${color}">${sign}${value}%</span>`;
    }
    
    // ─────────────────────────────────────────────────────────────
    // 📊 STATS CALCULATIONS (Delegated to PelangganService)
    // ─────────────────────────────────────────────────────────────
    
    /**
     * Fetch data historis dari API
     */
    async _fetchHistorical(tahun, bulan, metrik = 'pelanggan') {
        const cacheKey = `${tahun}-${bulan}-${metrik}`;
        
        if (this.historicalCache.has(cacheKey)) {
            return this.historicalCache.get(cacheKey);
        }
        
        try {
            const url = `${this.config.apiBaseUrl}/fact-historis/yoy-data?tahun=${tahun}&bulan=${bulan}&metrik=${metrik}`;
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            
            if (!response.ok) return {};
            const result = await response.json();
            const data = result.success ? (result.data || {}) : {};
            
            this.historicalCache.set(cacheKey, data);
            return data;
        } catch (e) {
            console.warn('⚠️ Gagal fetch historis:', e.message);
            return {};
        }
    }
    
    /**
     * Sum total dari data historis object
     */
    _sumHistorical(data) {
        if (!data || typeof data !== 'object') return 0;
        return Object.values(data).reduce((sum, val) => sum + (Number.parseFloat(val) || 0), 0);
    }
    
    /**
     * Hitung Growth YoY
     */
    _calculateYoy(current, previous) {
        if (!previous || previous === 0) return current > 0 ? 100 : 0;
        return Math.round(((current - previous) / previous) * 10000) / 100;
    }
    
    // ─────────────────────────────────────────────────────────────
    // 🎯 MAIN: LOAD & UPDATE STATS
    // ─────────────────────────────────────────────────────────────
    
    /**
     * Load dan update semua stats cards
     * @param {Object} options - Override config { year, month }
     */
    async load(options = {}) {
        const year = options.year || this.config.year || new Date().getFullYear();
        const month = options.month || this.config.month || (new Date().getMonth() + 1);
        
        console.log('📊 Loading customer stats...', { year, month });
        
        try {
            // 1. Get data dari Google Sheets via PelangganService
            const data = await this.service.getData();
           if (!data?.realisasiData) {
    throw new Error('Data realisasi tidak tersedia');
}
            
            const { realisasiData } = data;
            const monthIdx = this.service.getCurrentMonthIndex(realisasiData);
            const displayMonth = monthIdx + 1;
            
            // 2. Total Customer
            const total = this.service.getTotalCustomer(realisasiData, monthIdx);
            this._setText(this.elements.totalCustomer, this._formatNumber(total));
            
            // 3. New Customer (MoM)
            const mom = this.service.getNewCustomerMom(realisasiData, monthIdx);
            this._setHTML(this.elements.newCustomer, this._formatChange(mom));
            this._setColor(this.elements.newCustomer, mom);
            
            // 4. Growth Rate (YoY) - dari fact_historis
            const historical = await this._fetchHistorical(year - 1, displayMonth);
            const previousTotal = this._sumHistorical(historical);
            const growthYoy = this._calculateYoy(total, previousTotal);
            
            if (previousTotal > 0) {
                this._setHTML(this.elements.growthRate, this._formatPercent(growthYoy));
                const el = this._getElement(this.elements.growthRate);
                if (el) {
                    el.title = `Vs ${displayMonth}/${year - 1}: ${this._formatNumber(previousTotal)} pelanggan`;
                }
            } else {
                this._setText(this.elements.growthRate, 'N/A');
                const el = this._getElement(this.elements.growthRate);
                if (el) {
                    el.style.color = this.colors.neutral;
                    el.title = 'Data historis tahun lalu tidak tersedia';
                }
            }
            
            console.log('✅ Stats loaded:', { total, mom, growthYoy });
            return { success: true, data: { total, mom, growthYoy } };
            
        } catch (error) {
            console.error('❌ Error loading stats:', error);
            
            // Error state
            [this.elements.totalCustomer, this.elements.newCustomer, this.elements.growthRate].forEach(id => {
                this._setText(id, 'Error');
                this._setColor(id, null);
            });
            
            return { success: false, error: error.message };
        }
    }
    
    /**
     * Refresh stats (alias untuk load)
     */
    async refresh(options = {}) {
        return this.load(options);
    }
    
    /**
     * Update config dan reload
     */
    setConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        return this.load();
    }
    
    /**
     * Destroy instance (cleanup)
     */
    destroy() {
        this.historicalCache.clear();
        console.log('🧹 CustomerStats destroyed');
    }
}

// ─────────────────────────────────────────────────────────────
// 🌐 GLOBAL EXPOSURE (Optional - untuk non-module usage)
// ─────────────────────────────────────────────────────────────

if (typeof globalThis !== 'undefined') {
    globalThis.CustomerStats = CustomerStats;
}