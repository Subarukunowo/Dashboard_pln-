// /js/Dashboard/layout/UpdateStats.js

const BULAN_NAMES = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

/**
 * @param {PelangganService} pelangganService
 * @param {{ autoLoad?: boolean, year?: number, month?: number }} options
 */
export async function initStats(pelangganService, options = {}) {
    const now   = new Date();
    const tahun = options.year  ?? now.getFullYear();
    const bulan = options.month ?? now.getMonth() + 1; // 1-based

    console.log(`📊 initStats: tahun=${tahun}, bulan=${bulan}`);

    setLoading();

    try {
        // ── Sumber 1: Spreadsheet (via PelangganService) ──────────────────
        const pelangganData = await pelangganService.getData();
        if (!pelangganData) throw new Error('PelangganService returned null');

        const realisasi = pelangganData.realisasiData['JUMLAH'] ?? new Array(12).fill(0);
        // index 0 = Jan, bulan-1 = bulan ini
        const idxIni  = bulan - 1;        // e.g. April → 3
        const idxLalu = bulan - 2;        // e.g. Maret → 2

        const totalCustomer  = realisasi[idxIni]  ?? 0;
        const customerLalu   = idxLalu >= 0 ? (realisasi[idxLalu] ?? 0) : 0;
        const mom            = totalCustomer - customerLalu;
        const momPersen      = customerLalu !== 0
            ? ((mom / customerLalu) * 100).toFixed(1)
            : null;

        // ── Sumber 2: API historis (YoY dari DB) ──────────────────────────
        let yoyPersen = null;
        try {
            const res  = await fetch(`/api/historis/pelanggan-stats?tahun=${tahun}&bulan=${bulan}`);
            const json = await res.json();
            yoyPersen  = json.yoy_persen ?? null;
        } catch (e) {
            console.warn('⚠️ YoY API gagal:', e);
        }

        // ── Render ────────────────────────────────────────────────────────
        renderTotalCustomer(totalCustomer, customerLalu);
        renderMoM(mom, momPersen, bulan);
        renderYoY(yoyPersen);

    } catch (err) {
        console.error('❌ initStats error:', err);
        setError();
    }
}

// ─── Render Helpers ──────────────────────────────────────────────────────────

function renderTotalCustomer(nilai, sebelumnya) {
    const el = document.getElementById('statTotalCustomer');
    if (!el) return;

    const delta = nilai - sebelumnya;
    const arah  = delta >= 0 ? '▲' : '▼';
    const color = delta >= 0 ? 'var(--accent-green, #00ff88)' : '#f44336';

    el.innerHTML = `
        <span class="stat-main">${nilai.toLocaleString('id-ID')}</span>
        <span class="stat-sub" style="color:${color}; font-size:0.75rem; margin-left:6px;">
            ${arah} ${Math.abs(delta).toLocaleString('id-ID')} vs bln lalu
        </span>
    `;
}

function renderMoM(mom, persen, bulan) {
    const el = document.getElementById('statNewCustomer');
    if (!el) return;

    const arah  = mom >= 0 ? '▲' : '▼';
    const color = mom >= 0 ? 'var(--accent-green, #00ff88)' : '#f44336';
    const bulanLabel = BULAN_NAMES[bulan - 1] ?? '';

    el.style.color = color;
    el.innerHTML = `
        <span class="stat-main" style="color:${color};">${arah} ${Math.abs(mom).toLocaleString('id-ID')}</span>
        <span class="stat-sub" style="font-size:0.75rem; color:#aaa; margin-left:6px;">
            ${persen !== null ? `(${persen}%)` : ''} MoM ${bulanLabel}
        </span>
    `;
}

function renderYoY(persen) {
    const el = document.getElementById('statGrowthRate');
    if (!el) return;

    if (persen === null) {
        el.textContent = 'N/A';
        return;
    }

    const arah  = persen >= 0 ? '▲' : '▼';
    const color = persen >= 0 ? 'var(--accent-green, #00ff88)' : '#f44336';

    el.style.color = color;
    el.innerHTML = `
        <span class="stat-main" style="color:${color};">${arah} ${Math.abs(persen).toFixed(2)}%</span>
        <span class="stat-sub" style="font-size:0.75rem; color:#aaa; margin-left:6px;">YoY</span>
    `;
}

function setLoading() {
    ['statTotalCustomer','statNewCustomer','statGrowthRate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '<span style="color:#555; font-size:0.8rem;">Memuat...</span>';
    });
}

function setError() {
    ['statTotalCustomer','statNewCustomer','statGrowthRate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '<span style="color:#f44336; font-size:0.8rem;">Gagal memuat</span>';
    });
}