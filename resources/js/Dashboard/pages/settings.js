/* ============================================================
   SETTINGS ENGINE — PLN Commercial Dashboard
   Persisted via localStorage
   Keys: pln_theme | pln_accent | pln_font | pln_anim
   ============================================================ */

const ACCENT_MAP = {
    orange: '#ff9800',
    green:  '#00ff88',
    blue:   '#3b82f6',
    cyan:   '#06b6d4',
    violet: '#8b5cf6',
    rose:   '#f43f5e',
};

/* ── Theme ── */
function setTheme(mode) {
    localStorage.setItem('pln_theme', mode);
    applyTheme(mode);
    document.getElementById('btnDark')?.classList.toggle('active',  mode === 'dark');
    document.getElementById('btnLight')?.classList.toggle('active', mode === 'light');
    toast(mode === 'dark' ? 'Tema Gelap aktif 🌙' : 'Tema Terang aktif ☀️', 'success');
}
function applyTheme(mode) {
    document.body.classList.toggle('light-mode', mode === 'light');
}

/* ── Accent ── */
function setAccent(name, hex) {
    localStorage.setItem('pln_accent', name);
    applyAccent(hex);
    document.querySelectorAll('.accent-swatch').forEach(s =>
        s.classList.toggle('selected', s.dataset.color === name)
    );
    toast('Warna aksen diperbarui!', 'success');
}
function applyAccent(hex) {
    document.documentElement.style.setProperty('--accent-orange', hex);
}

/* ── Font Size ── */
function setFontSize(px) {
    localStorage.setItem('pln_font', px);
    document.documentElement.style.fontSize = px + 'px';
    toast('Ukuran teks: ' + px + 'px', 'info');
}

/* ── Animation ── */
function setAnimation(on) {
    localStorage.setItem('pln_anim', on ? '1' : '0');
    const existing = document.getElementById('noAnimStyle');
    if (!on) {
        if (!existing) {
            const s = document.createElement('style');
            s.id = 'noAnimStyle';
            s.textContent = '*,*::before,*::after{animation:none!important;transition:none!important}';
            document.head.appendChild(s);
        }
    } else {
        if (existing) existing.remove();
    }
    toast(on ? 'Animasi diaktifkan ✨' : 'Animasi dinonaktifkan', 'info');
}

/* ── Save Name ── */
function saveName() {
    const val = document.getElementById('inputName')?.value?.trim();
    if (!val) { toast('Nama tidak boleh kosong.', 'warning'); return; }
    toast('Fitur simpan nama segera tersedia!', 'warning');
}

/* ── Toast ── */
function toast(msg, icon = 'success') {
    if (typeof Swal === 'undefined') return;
    Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 2500, timerProgressBar: true,
    }).fire({ icon, title: msg });
}

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', () => {

    // Theme
    const theme = localStorage.getItem('pln_theme') || 'dark';
    applyTheme(theme);
    document.getElementById('btnDark')?.classList.toggle('active',  theme === 'dark');
    document.getElementById('btnLight')?.classList.toggle('active', theme === 'light');

    // Accent
    const accent = localStorage.getItem('pln_accent') || 'orange';
    const hex    = ACCENT_MAP[accent] || ACCENT_MAP.orange;
    applyAccent(hex);
    document.querySelectorAll('.accent-swatch').forEach(s =>
        s.classList.toggle('selected', s.dataset.color === accent)
    );

    // Font
    const font = localStorage.getItem('pln_font');
    if (font) {
        document.documentElement.style.fontSize = font + 'px';
        const sel = document.getElementById('fontSizeSelect');
        if (sel) sel.value = font;
    }

    // Animation
    if (localStorage.getItem('pln_anim') === '0') {
        const s = document.createElement('style');
        s.id = 'noAnimStyle';
        s.textContent = '*,*::before,*::after{animation:none!important;transition:none!important}';
        document.head.appendChild(s);
        const tog = document.getElementById('animToggle');
        if (tog) tog.checked = false;
    }
});