/**
 * ThemeChart.js
 * Menyediakan warna Chart.js yang responsif terhadap tema light/dark.
 *
 * Cara pakai:
 *   import { chartColors, onThemeChange, getChartDefaults } from '../utils/ThemeChart.js'
 *
 *   // Ambil warna saat ini
 *   const c = chartColors()
 *   c.grid      → warna grid line
 *   c.tick      → warna label axis
 *   c.legend    → warna teks legend
 *   c.datalabel → warna angka di atas bar/titik
 *
 *   // Re-render semua chart saat tema berubah
 *   onThemeChange(() => myModule.render(state))
 */

const DARK = {
    grid:      '#333333',
    tick:      '#888888',
    legend:    '#cccccc',
    datalabel: '#cccccc',
    tooltip_bg:'#1e1e1e',
    tooltip_fg:'#ffffff',
}

const LIGHT = {
    grid:      '#e2e8f0',
    tick:      '#64748b',
    legend:    '#1e293b',
    datalabel: '#1e293b',
    tooltip_bg:'#ffffff',
    tooltip_fg:'#1e293b',
}

/** Kembalikan palet warna sesuai tema aktif */
export function chartColors() {
    return document.body.classList.contains('light-mode') ? LIGHT : DARK
}

/** Buat Chart.js options defaults sesuai tema */
export function getChartDefaults(yFmt = null) {
    const c = chartColors()
    return {
        responsive:          true,
        maintainAspectRatio: false,
        animation:           { duration: 300 },
        plugins: {
            legend: {
                labels: { color: c.legend, font: { size: 11 } },
            },
            tooltip: {
                backgroundColor: c.tooltip_bg,
                titleColor:      c.tooltip_fg,
                bodyColor:       c.tooltip_fg,
                borderColor:     c.grid,
                borderWidth:     1,
            },
            datalabels: { display: false },
        },
        scales: {
            y: {
                grid:  { color: c.grid },
                ticks: {
                    color:    c.tick,
                    callback: yFmt ?? undefined,
                },
            },
            x: {
                grid:  { display: false },
                ticks: { color: c.tick, font: { size: 9 }, maxRotation: 45 },
            },
        },
    }
}

// ── Theme change listener registry ───────────────────────────────────────────
const _listeners = []
let   _observed  = false

function _setupObserver() {
    if (_observed) return
    _observed = true

    // Watch body classList changes (light-mode add/remove)
    const obs = new MutationObserver((mutations) => {
        const themeChanged = mutations.some(m =>
            [...m.removedClasses ?? [], ...m.addedClasses ?? [],
             // classList changes come as attributeOldValue
             m.oldValue ?? ''].some(cls => typeof cls === 'string' && cls.includes('light-mode'))
        )
        // Simpler: just fire on any body class change
        _listeners.forEach(fn => { try { fn() } catch (e) { /**/ } })
    })

    obs.observe(document.body, {
        attributes:      true,
        attributeFilter: ['class'],
    })
}

/**
 * Daftarkan callback yang dipanggil setiap kali tema berubah.
 * Mengembalikan fungsi unsubscribe.
 */
export function onThemeChange(callback) {
    _setupObserver()
    _listeners.push(callback)
    return () => {
        const i = _listeners.indexOf(callback)
        if (i !== -1) _listeners.splice(i, 1)
    }
}
