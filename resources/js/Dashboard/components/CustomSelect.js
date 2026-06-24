/**
 * CustomSelect.js
 *
 * Menggantikan semua native <select> di dashboard dengan custom dropdown
 * yang scrollable (max 5 item visible). Support optgroup, dynamic options,
 * dan semua event listener yang sudah ada (change event tetap terpanggil).
 *
 * Auto-init: panggil CustomSelect.initAll() setelah DOM ready.
 * Re-init setelah select diisi JS: CustomSelect.upgrade(selectEl)
 */

const CustomSelect = (() => {

    const ITEM_HEIGHT  = 30   // px per item
    const VISIBLE_ROWS = 5    // baris visible sebelum scroll

    /** Buat elemen custom dropdown dari native <select> */
    function upgrade(select) {
        // Jangan upgrade dua kali
        if (select._csUpgraded) return
        select._csUpgraded = true

        // Sembunyikan native select tapi tetap di DOM
        // (change event tetap bisa di-listen dari kode lama)
        select.style.display = 'none'

        const wrapper = document.createElement('div')
        wrapper.className = 'cs-wrapper'
        wrapper.setAttribute('data-cs-for', select.id || '')

        const trigger = document.createElement('button')
        trigger.type      = 'button'
        trigger.className = 'cs-trigger'
        trigger.setAttribute('aria-haspopup', 'listbox')
        trigger.setAttribute('aria-expanded', 'false')

        const triggerLabel = document.createElement('span')
        triggerLabel.className = 'cs-trigger-label'

        const triggerArrow = document.createElement('span')
        triggerArrow.className   = 'cs-arrow'
        triggerArrow.textContent = '▾'
        triggerArrow.setAttribute('aria-hidden', 'true')

        trigger.append(triggerLabel, triggerArrow)

        const dropdown = document.createElement('div')
        dropdown.className = 'cs-dropdown'
        dropdown.setAttribute('role', 'listbox')
        dropdown.style.display = 'none'

        wrapper.append(trigger, dropdown)
        select.insertAdjacentElement('afterend', wrapper)

        /** Rebuild list dari current state of native select */
        function buildList() {
            dropdown.innerHTML = ''

            Array.from(select.children).forEach(child => {
                if (child.tagName === 'OPTGROUP') {
                    const groupLabel = document.createElement('div')
                    groupLabel.className   = 'cs-group-label'
                    groupLabel.textContent = child.label
                    dropdown.appendChild(groupLabel)

                    Array.from(child.children).forEach(opt => {
                        dropdown.appendChild(makeItem(opt))
                    })
                } else if (child.tagName === 'OPTION') {
                    dropdown.appendChild(makeItem(child))
                }
            })

            syncLabel()
        }

        function makeItem(opt) {
            const item = document.createElement('div')
            item.className = 'cs-item'
            item.setAttribute('role', 'option')
            item.setAttribute('data-value', opt.value)
            item.textContent = opt.textContent.trim()
            if (opt.selected) item.classList.add('cs-selected')

            item.addEventListener('click', () => {
                select.value = opt.value
                // Trigger change event agar kode lama tetap bekerja
                select.dispatchEvent(new Event('change', { bubbles: true }))
                syncSelected()
                close()
            })
            return item
        }

        function syncLabel() {
            const sel = select.options[select.selectedIndex]
            triggerLabel.textContent = sel ? sel.textContent.trim() : '—'
        }

        function syncSelected() {
            syncLabel()
            dropdown.querySelectorAll('.cs-item').forEach(item => {
                item.classList.toggle('cs-selected', item.dataset.value === select.value)
            })
        }

        function open() {
            // Tutup dropdown lain yang terbuka
            document.querySelectorAll('.cs-dropdown[data-open]').forEach(d => {
                if (d !== dropdown) {
                    d.removeAttribute('data-open')
                    d.style.display = 'none'
                    d.closest('.cs-wrapper')
                        ?.querySelector('.cs-trigger')
                        ?.setAttribute('aria-expanded', 'false')
                }
            })

            dropdown.style.display = 'block'
            dropdown.setAttribute('data-open', '')
            trigger.setAttribute('aria-expanded', 'true')

            // Scroll ke item selected
            const sel = dropdown.querySelector('.cs-selected')
            if (sel) sel.scrollIntoView({ block: 'nearest' })

            // Max-height: VISIBLE_ROWS items
            const maxH = ITEM_HEIGHT * VISIBLE_ROWS
            dropdown.style.maxHeight = maxH + 'px'
            dropdown.style.overflowY = 'auto'

            // Posisi: buka ke atas kalau tidak cukup ruang di bawah
            const rect = wrapper.getBoundingClientRect()
            const spaceBelow = window.innerHeight - rect.bottom
            if (spaceBelow < maxH && rect.top > maxH) {
                dropdown.style.bottom = '100%'
                dropdown.style.top    = 'auto'
            } else {
                dropdown.style.top    = '100%'
                dropdown.style.bottom = 'auto'
            }
        }

        function close() {
            dropdown.removeAttribute('data-open')
            dropdown.style.display = 'none'
            trigger.setAttribute('aria-expanded', 'false')
        }

        trigger.addEventListener('click', e => {
            e.stopPropagation()
            dropdown.hasAttribute('data-open') ? close() : open()
        })

        // Keyboard navigation
        trigger.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault()
                dropdown.hasAttribute('data-open') ? close() : open()
            }
            if (e.key === 'Escape') close()
        })

        // MutationObserver: rebuild jika options berubah (JS-populated selects)
        const observer = new MutationObserver(() => {
            buildList()
        })
        observer.observe(select, { childList: true, subtree: true, characterData: true })

        // Sync jika value diubah secara programatik
        select.addEventListener('change', () => {
            // Hanya sync tampilan, jangan re-dispatch event
            queueMicrotask(() => syncSelected())
        })

        buildList()
    }

    /** Upgrade semua select di dalam container (default: document) */
    function initAll(container = document) {
        container.querySelectorAll('select').forEach(sel => {
            // Skip select di modal yang belum visible — akan di-init saat dibuka
            upgrade(sel)
        })
    }

    // Tutup semua dropdown saat klik di luar
    document.addEventListener('click', () => {
        document.querySelectorAll('.cs-dropdown[data-open]').forEach(d => {
            d.removeAttribute('data-open')
            d.style.display = 'none'
            d.closest('.cs-wrapper')
                ?.querySelector('.cs-trigger')
                ?.setAttribute('aria-expanded', 'false')
        })
    })

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.cs-dropdown[data-open]').forEach(d => {
                d.removeAttribute('data-open')
                d.style.display = 'none'
            })
        }
    })

    return { upgrade, initAll }
})()

export default CustomSelect
