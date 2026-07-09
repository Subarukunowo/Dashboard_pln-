// /js/Dashboard/layout/UpdateStats.js

const BULAN_NAMES = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

// ── Formatters ────────────────────────────────────────────────────────────────
const fmt = {
    number : v => Number(v).toLocaleString('id-ID'),
    kwh    : v => (v / 1_000_000).toFixed(2) + ' jt kWh',
    kwhDiff: v => {
        const abs = Math.abs(v) / 1_000_000
        return (v >= 0 ? '+' : '-') + abs.toFixed(2) + ' jt kWh'
    },
    rp     : v => {
        const abs = Math.abs(v)
        if (abs >= 1_000_000_000_000) return 'Rp ' + (v / 1_000_000_000_000).toFixed(2) + ' T'
        if (abs >= 1_000_000_000)     return 'Rp ' + (v / 1_000_000_000).toFixed(2) + ' M'
        return 'Rp ' + (v / 1_000_000).toFixed(2) + ' jt'
    },
    rpDiff : v => {
        const abs = Math.abs(v)
        const sign = v >= 0 ? '+' : '-'
        if (abs >= 1_000_000_000_000) return sign + 'Rp ' + (abs / 1_000_000_000_000).toFixed(2) + ' T'
        if (abs >= 1_000_000_000)     return sign + 'Rp ' + (abs / 1_000_000_000).toFixed(2) + ' M'
        return sign + 'Rp ' + (abs / 1_000_000).toFixed(2) + ' jt'
    },
    pct    : v => Number.parseFloat(v).toFixed(2) + '%',
    pctDiff: v => (v >= 0 ? '+' : '') + Number.parseFloat(v).toFixed(4) + '%',
    jam    : v => Number.parseFloat(v).toFixed(2) + ' jam',
    jamDiff: v => (v >= 0 ? '+' : '') + Number.parseFloat(v).toFixed(2) + ' jam',
    rpkwh  : v => 'Rp ' + Number(Math.round(v)).toLocaleString('id-ID') + '/kWh',
    rpkwhDiff: v => (v >= 0 ? '+' : '') + 'Rp ' + Number(Math.round(Math.abs(v))).toLocaleString('id-ID') + '/kWh',
    kva    : v => Number(Math.round(v)).toLocaleString('id-ID') + ' kVA',
    kvaDiff: v => (v >= 0 ? '+' : '') + Number(Math.round(Math.abs(v))).toLocaleString('id-ID') + ' kVA',
}

// ── Colors ────────────────────────────────────────────────────────────────────
const color = {
    green  : 'var(--accent-green, #00ff88)',
    red    : '#f44336',
    orange : '#ff9800',
    muted  : '#555',
    sub    : '#aaa',
    white  : 'var(--text-primary, #fff)',
}

// ── DOM helpers ───────────────────────────────────────────────────────────────
function setHTML(id, html) {
    const el = document.getElementById(id)
    if (!el) { console.warn('[Stats] DOM element not found:', id); return }
    el.innerHTML = html
}
function setText(id, text) {
    const el = document.getElementById(id)
    if (!el) { console.warn('[Stats] DOM element not found:', id); return }
    el.textContent = text
}
function setLoading(ids) { ids.forEach(id => setHTML(id, '<span style="color:#555;font-size:0.8rem;">Memuat...</span>')) }
function setError(ids)   { ids.forEach(id => setHTML(id, '<span style="color:#f44336;font-size:0.75rem;">—</span>')) }

// ── MoM helper ────────────────────────────────────────────────────────────────
/**
 * Render selisih MoM:
 * positifBagus=true  → naik hijau, turun merah (pendapatan, penjualan, dll)
 * positifBagus=false → naik merah, turun hijau (BPP, losses, tunggakan)
 */
function momHTML(selisih, pct, fmtFn, positifBagus = true, bulan) {
    if (selisih === null || selisih === undefined) return `<span style="color:${color.muted};">N/A</span>`
    const naik   = selisih > 0
    const good   = positifBagus ? naik : !naik
    const c      = selisih === 0 ? color.muted : (good ? color.green : color.red)
    const icon   = selisih > 0 ? '▲' : selisih < 0 ? '▼' : '–'
    const pctStr = pct !== null && pct !== undefined
        ? ` <span style="font-size:0.72rem;color:${color.sub};">(${pct >= 0 ? '+' : ''}${Number.parseFloat(pct).toFixed(1)}%)</span>`
        : ''
    return `<span style="color:${c};">${icon} ${fmtFn(selisih)}</span>${pctStr}`
}

// ── Fallback badge ────────────────────────────────────────────────────────────
function setFallbackBadge(isFallback, periodLabel) {
    let badge = document.getElementById('statsFallbackBadge')
    if (!badge) {
        badge = document.createElement('div')
        badge.id = 'statsFallbackBadge'
        badge.style.cssText = 'grid-column:1/-1;font-size:0.68rem;padding:4px 10px;border-radius:6px;background:rgba(255,152,0,0.1);border:1px solid rgba(255,152,0,0.25);color:#ff9800;display:none;'
        const container = document.getElementById('statsContainer')
        if (container) container.prepend(badge)
    }
    if (isFallback && periodLabel) {
        badge.textContent = '⚠ Menampilkan ' + periodLabel + ' (data tahun ini belum tersedia)'
        badge.style.display = 'block'
    } else {
        badge.style.display = 'none'
    }
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchStats(section, tahun, bulan) {
    const url = `/api/stats/${section}?tahun=${tahun}&bulan=${bulan}`
    console.log('[Stats] fetch →', url)
    const res = await fetch(url)
    console.log('[Stats] response:', res.status, res.ok ? 'OK' : 'ERROR')
    if (!res.ok) throw new Error(`/api/stats/${section} → ${res.status}`)
    const json = await res.json()
    console.log('[Stats] data received for', section, ':', json)
    return json
}

// ── Renderers ─────────────────────────────────────────────────────────────────

function renderBpp(d, t, b) {
    setHTML('statBppPembangkit', `<span style="color:${color.orange};">${fmt.rpkwh(d.realisasi)}</span>`)
    setHTML('statBppTransmisi',  `<span style="color:${color.white};">${fmt.rpkwh(d.target)}</span>`)
    setHTML('statBppMom',        momHTML(d.mom_selisih, d.mom_pct, fmt.rpkwhDiff, false))
    setText('statBppPeriod',     BULAN_NAMES[b - 1] + ' ' + t)
}

function renderLosses(d, t, b) {
    setHTML('statLossesRealisasi', `<span style="color:${color.orange};">${fmt.pct(d.realisasi)}</span>`)
    setHTML('statLossesTarget',    `<span style="color:${color.white};">${fmt.pct(d.target)}</span>`)
    setHTML('statLossesMom',       momHTML(d.mom_selisih, d.mom_pct, fmt.pctDiff, false))
    setText('statLossesPeriod',    BULAN_NAMES[b - 1] + ' ' + t)
}

function renderPenjualan(d, t, b) {
    const c = (d.capai_pct ?? 0) >= 100 ? color.green : color.orange
    setHTML('statRealisasiPenjualan', `<span style="color:${color.green};">${fmt.kwh(d.realisasi)}</span>`)
    setHTML('statTargetPenjualan',    `<span style="color:${color.orange};">${fmt.kwh(d.target)}</span>`)
    setHTML('statPenjualanMom',       momHTML(d.mom_selisih, d.mom_pct, fmt.kwhDiff, true))
    setText('statPenjualanPeriod',    'Jan–' + BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statPenjualanCapai',     `<span style="color:${c};">Capaian ${fmt.pct(d.capai_pct ?? 0)}</span>`)
}

function renderBabin(d, t, b) {
    const c = (d.capai_pct ?? 0) >= 100 ? color.green : color.orange
    setHTML('statBabinRealisasi', `<span style="color:${color.green};">${fmt.kwh(d.realisasi)}</span>`)
    setHTML('statBabinTarget',    `<span style="color:${color.orange};">${fmt.kwh(d.target)}</span>`)
    setHTML('statBabinMom',       momHTML(d.mom_selisih, d.mom_pct, fmt.kwhDiff, true))
    setText('statBabinPeriod',    BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statBabinCapai',     `<span style="color:${c};">Capaian ${fmt.pct(d.capai_pct ?? 0)}</span>`)
}

function renderJamNyala(d, t, b) {
    const c = d.realisasi >= d.target ? color.green : color.orange
    setHTML('statJamNyalaRealisasi',  `<span style="color:${c};">${fmt.jam(d.realisasi)}</span>`)
    setHTML('statJamNyalaTarget',     `<span style="color:${color.orange};">${fmt.jam(d.target)}</span>`)
    setHTML('statJamNyalaMom',        momHTML(d.mom_selisih, d.mom_pct, fmt.jamDiff, true))
    setText('statJamNyalaPeriod',     BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statJamNyalaPencapaian', d.avg_pencapaian > 0
        ? `<span style="color:${c};">Capaian ${fmt.pct(d.avg_pencapaian)}</span>` : '')
}

function renderGolTarif(d, t, b) {
    setHTML('statHjrTr',     `<span style="color:${color.green};">${fmt.rpkwh(d.realisasi)}</span>`)
    setHTML('statHjrTarget', `<span style="color:${color.orange};">${fmt.rpkwh(d.target)}</span>`)
    setHTML('statHjrMom',    momHTML(d.mom_selisih, d.mom_pct, fmt.rpkwhDiff, true))
    setText('statHjrPeriod', 'Akumulasi ' + BULAN_NAMES[b - 1] + ' ' + t)
}

function renderPendapatan(d, t, b) {
    const c = (d.capai_pct ?? 0) >= 100 ? color.green : color.orange
    setHTML('statRealisasiPendapatan', `<span style="color:${color.green};">${fmt.rp(d.realisasi)}</span>`)
    setHTML('statTargetPendapatan',    `<span style="color:${color.orange};">${fmt.rp(d.target)}</span>`)
    setHTML('statPendapatanMom',       momHTML(d.mom_selisih, d.mom_pct, fmt.rpDiff, true))
    setText('statPendapatanPeriod',    'Jan–' + BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statPendapatanCapai',     `<span style="color:${c};">Capaian ${fmt.pct(d.capai_pct ?? 0)}</span>`)
}

function renderRpkwh(d, t, b) {
    const c = (d.capai_pct ?? 0) >= 100 ? color.green : color.orange
    setHTML('statRpkwhRealisasi', `<span style="color:${color.green};">${fmt.rpkwh(d.realisasi)}</span>`)
    setHTML('statRpkwhTarget',    `<span style="color:${color.orange};">${fmt.rpkwh(d.target)}</span>`)
    setHTML('statRpkwhMom',       momHTML(d.mom_selisih, d.mom_pct, fmt.rpkwhDiff, true))
    setText('statRpkwhPeriod',    BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statRpkwhCapai',     `<span style="color:${c};">Capaian ${fmt.pct(d.capai_pct ?? 0)}</span>`)
}

function renderPelanggan(d, t, b) {
    const c = (d.capai_pct ?? 0) >= 100 ? color.green : color.orange
    setHTML('statTotalCustomer',    `<span style="color:${color.green};">${fmt.number(d.realisasi)}</span>`)
    setHTML('statTargetPelanggan',  `<span style="color:${color.orange};">${fmt.number(d.target)}</span>`)
    setHTML('statNewCustomer',      momHTML(d.mom_selisih, d.mom_pct, v => (v >= 0 ? '+' : '') + fmt.number(Math.round(Math.abs(v))), true))
    setText('statTotalCustomerPeriod', BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statPelangganCapai',   d.target > 0 ? `<span style="color:${c};">Capaian ${fmt.pct(d.capai_pct ?? 0)}</span>` : '')
}

function renderKva(d, t, b) {
    const c = (d.capai_pct ?? 0) >= 100 ? color.green : color.orange
    setHTML('statKvaRealisasi', `<span style="color:${color.green};">${fmt.kva(d.realisasi)}</span>`)
    setHTML('statKvaTarget',    `<span style="color:${color.orange};">${fmt.kva(d.target)}</span>`)
    setHTML('statKvaMom',       momHTML(d.mom_selisih, d.mom_pct, fmt.kvaDiff, true))
    setText('statKvaPeriod',    BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statKvaCapai',     d.target > 0 ? `<span style="color:${c};">Capaian ${fmt.pct(d.capai_pct ?? 0)}</span>` : '')
}

function renderTunggakan(d, t, b) {
    const ratioOk = d.target > 0 ? d.realisasi <= d.target : true
    const rc = ratioOk ? color.green : color.red
    setHTML('statJmlTunggakan',        `<span style="color:${color.orange};">${fmt.rp(d.realisasi)}</span>`)
    setHTML('statPendapatanTunggakan', `<span style="color:${color.white};">${fmt.rp(d.pendapatan)}</span>`)
    setHTML('statTunggakanMom',        momHTML(d.mom_selisih, d.mom_pct, fmt.rpDiff, false))
    setText('statTunggakanPeriod',     BULAN_NAMES[b - 1] + ' ' + t)
    setHTML('statRatioTunggakan',      `<span style="color:${rc};">Ratio ${fmt.pct((d.ratio_pct ?? 0) * 100)}</span>`)
}

function renderP2tl(d, t, b) {
    setHTML('statP2tlPemeriksaan', `<span style="color:${color.white};">${fmt.number(d.total_pemeriksaan)}</span>`)
    setHTML('statP2tlTemuan',      `<span style="color:${color.green};">${fmt.number(d.realisasi)}</span>`)
    setHTML('statP2tlTagihan',     `<span style="color:${color.orange};">${fmt.rp(d.total_tagihan_rp)}</span>`)
    setHTML('statP2tlMom',         momHTML(d.mom_selisih, d.mom_pct, v => (v >= 0 ? '+' : '') + fmt.number(Math.abs(Math.round(v))), true))
    setText('statP2tlPeriod',      'YTD Jan–' + BULAN_NAMES[b - 1] + ' ' + t)
}

// ── Renderer & ID maps ────────────────────────────────────────────────────────
const RENDERERS = {
    bpp        : renderBpp,
    losses     : renderLosses,
    penjualan  : renderPenjualan,
    babin      : renderBabin,
    jamnyala   : renderJamNyala,
    goltarif   : renderGolTarif,
    pendapatan : renderPendapatan,
    rpkwh      : renderRpkwh,
    pelanggan  : renderPelanggan,
    kva        : renderKva,
    tunggakan  : renderTunggakan,
    p2tl       : renderP2tl,
}

const SECTION_IDS = {
    bpp        : ['statBppPembangkit', 'statBppTransmisi', 'statBppMom'],
    losses     : ['statLossesRealisasi', 'statLossesTarget', 'statLossesMom'],
    penjualan  : ['statRealisasiPenjualan', 'statTargetPenjualan', 'statPenjualanMom'],
    babin      : ['statBabinRealisasi', 'statBabinTarget', 'statBabinMom'],
    jamnyala   : ['statJamNyalaRealisasi', 'statJamNyalaTarget', 'statJamNyalaMom'],
    goltarif   : ['statHjrTr', 'statHjrTarget', 'statHjrMom'],
    pendapatan : ['statRealisasiPendapatan', 'statTargetPendapatan', 'statPendapatanMom'],
    rpkwh      : ['statRpkwhRealisasi', 'statRpkwhTarget', 'statRpkwhMom'],
    pelanggan  : ['statTotalCustomer', 'statTargetPelanggan', 'statNewCustomer'],
    kva        : ['statKvaRealisasi', 'statKvaTarget', 'statKvaMom'],
    tunggakan  : ['statJmlTunggakan', 'statPendapatanTunggakan', 'statTunggakanMom'],
    p2tl       : ['statP2tlPemeriksaan', 'statP2tlTemuan', 'statP2tlTagihan'],
}

// ── Main export ───────────────────────────────────────────────────────────────
export async function initStats(section, options = {}) {
    const now   = new Date()
    const tahun = options.year  ?? now.getFullYear()
    const bulan = options.month ?? now.getMonth() + 1

    console.log(`[SP] initStats: section=${section}, tahun=${tahun}, bulan=${bulan}`)

    const ids = SECTION_IDS[section]
    if (!ids) return

    try {
        setLoading(ids)
        const data = await fetchStats(section, tahun, bulan)
        setFallbackBadge(data.is_fallback ?? false, data.period ?? null)
        RENDERERS[section]?.(data, tahun, bulan)
    } catch (err) {
        console.error(`❌ Stats error [${section}]:`, err)
        setError(ids)
        setFallbackBadge(false, null)
    }
}
