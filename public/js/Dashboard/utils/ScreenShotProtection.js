/**
 * ScreenshotProtection.js
 * Path: public/js/Dashboard/utils/ScreenshotProtection.js
 *
 * Cara pakai:
 *   // ES Module (dashboard.js):
 *   import ScreenshotProtection from '../utils/ScreenshotProtection.js'
 *   ScreenshotProtection.init()
 *
 *   // Global fallback:
 *   window.ScreenshotProtection?.init()
 */

'use strict';

const ScreenshotProtection = (() => {

    const CFG = {
        blurDuration: 2500,
        targets: ['#main-content', '.historis-container', '.dash-content'],
    };

    let blurTimeout = null;
    let toastEl     = null;
    let initialized = false;
    let lastHidden  = 0;

    // ── Helpers ───────────────────────────────────────────────────
    function getTargets() {
        const els = [];
        CFG.targets.forEach(sel => document.querySelectorAll(sel).forEach(el => els.push(el)));
        return els.length ? els : [document.body];
    }

    function showToast() {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = '_sp_toast';
            Object.assign(toastEl.style, {
                position: 'fixed', bottom: '20px', right: '20px', zIndex: '999999',
                background: 'rgba(20,20,20,0.95)', border: '1px solid rgba(255,152,0,0.5)',
                color: '#ff9800', padding: '10px 16px', borderRadius: '8px',
                fontSize: '0.8rem', fontWeight: '600', fontFamily: 'Inter, system-ui, sans-serif',
                boxShadow: '0 4px 20px rgba(0,0,0,0.6)', pointerEvents: 'none', opacity: '0',
                transition: 'opacity 0.2s'
            });
            toastEl.innerHTML = '<i class="fa-solid fa-ban" style="margin-right:6px;"></i>Screenshot tidak diizinkan';
            document.body.appendChild(toastEl);
        }
        toastEl.style.display = 'block';
        requestAnimationFrame(() => toastEl.style.opacity = '1');
        setTimeout(() => { toastEl.style.opacity = '0'; }, CFG.blurDuration - 400);
    }

    function hideToast() {
        if (toastEl) toastEl.style.display = 'none';
    }

    // ── Blur / Unblur (INSTANT & AGGRESSIVE) ──────────────────────
    function applyBlur() {
        console.log('[SP] 🔒 Blur triggered');
        const targets = getTargets();
        targets.forEach(el => {
            el.style.setProperty('filter', 'blur(20px) brightness(0.2)', 'important');
            el.style.setProperty('transition', 'none', 'important');
        });
        // Fallback: blur body langsung
        document.body.style.setProperty('filter', 'blur(15px) brightness(0.3)', 'important');
        showToast();
        clearTimeout(blurTimeout);
        blurTimeout = setTimeout(removeBlur, CFG.blurDuration);
    }

    function removeBlur() {
        console.log('[SP] 🔓 Blur removed');
        getTargets().forEach(el => {
            el.style.removeProperty('filter');
            el.style.removeProperty('transition');
        });
        document.body.style.removeProperty('filter');
        hideToast();
    }

    // ── CSS Inject ────────────────────────────────────────────────
    function injectCSS() {
        if (document.getElementById('_sp_css')) return;
        const s = document.createElement('style');
        s.id = '_sp_css';
        s.textContent = `
            canvas, img, video { -webkit-user-drag: none !important; user-select: none !important; }
            @media print {
                body, body * { visibility: hidden !important; filter: blur(40px) !important; }
                #_sp_print_warn { display: block !important; visibility: visible !important; }
            }
        `;
        document.head.appendChild(s);

        const warn = document.createElement('div');
        warn.id = '_sp_print_warn';
        Object.assign(warn.style, {
            display: 'none', position: 'fixed', top: '40%', left: '0', width: '100%',
            textAlign: 'center', fontSize: '2rem', color: '#ff3333', fontWeight: 'bold',
            zIndex: '999999', textShadow: '0 2px 10px rgba(0,0,0,0.8)'
        });
        warn.textContent = '⛔ PRINT / SCREENSHOT DILARANG';
        document.body.appendChild(warn);
    }

    // ── Event Bindings ────────────────────────────────────────────
    function bindKeys() {
        const handler = (e) => {
            const k = e.key.toLowerCase();
            // PrintScreen (best effort)
            if (k === 'printscreen' || e.key === 'PrintScreen') {
                e.preventDefault(); e.stopImmediatePropagation();
                applyBlur(); return;
            }
            // Win + Shift + S
            if (e.metaKey && e.shiftKey && k === 's') {
                e.preventDefault(); e.stopImmediatePropagation();
                applyBlur(); return;
            }
            // Win + G / Alt+PrtScr / Shift+PrtScr
            if (e.metaKey && (k === 'g' || k === 'printscreen')) {
                e.preventDefault(); e.stopImmediatePropagation();
                applyBlur();
            }
        };
        document.addEventListener('keydown', handler, { capture: true, passive: false });
        document.addEventListener('keyup', (e) => {
            if (e.key === 'PrintScreen') applyBlur();
        }, { capture: true });
    }

    function bindVisibility() {
        // Layer 1: visibilitychange (sangat efektif untuk Win+Shift+S)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                lastHidden = Date.now();
            } else {
                const delta = Date.now() - lastHidden;
                if (delta > 10 && delta < 600) {
                    console.log(`[SP] 👁️ visibility (${delta}ms)`);
                    applyBlur();
                }
            }
        }, { capture: true });

        // Layer 2: window blur/focus (overlay Windows selalu trigger ini)
        window.addEventListener('blur', () => { lastHidden = Date.now(); }, { capture: true });
        window.addEventListener('focus', () => {
            if (lastHidden > 0) {
                const delta = Date.now() - lastHidden;
                if (delta > 10 && delta < 1000) {
                    console.log(`[SP]  focus (${delta}ms)`);
                    applyBlur();
                }
                lastHidden = 0;
            }
        }, { capture: true });
    }

    function bindPointerLock() {
        document.addEventListener('pointerlockchange', () => {
            if (!document.pointerLockElement && lastHidden > 0 && Date.now() - lastHidden < 1000) {
                applyBlur();
            }
        });
    }

    function patchDisplayMedia() {
        if (!navigator.mediaDevices?.getDisplayMedia) return;
        const orig = navigator.mediaDevices.getDisplayMedia.bind(navigator.mediaDevices);
        navigator.mediaDevices.getDisplayMedia = async (c) => {
            applyBlur();
            return orig(c);
        };
    }

    // ─ Public API ────────────────────────────────────────────────
    function init() {
        if (initialized) return;
        initialized = true;
        const run = () => {
            injectCSS();
            bindKeys();
            bindVisibility();
            bindPointerLock();
            patchDisplayMedia();
            console.log('[SP] ✅ Active');
        };
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run, { once: true }) : run();
    }

    function testBlur() {
        console.log('[SP] 🧪 Manual test started');
        applyBlur();
        setTimeout(removeBlur, 2000);
    }

    return { init, applyBlur, removeBlur, testBlur };

})();

// ── Exports ───────────────────────────────────────────────────────
export default ScreenshotProtection;

if (typeof window !== 'undefined') {
    window.ScreenshotProtection = ScreenshotProtection;
}