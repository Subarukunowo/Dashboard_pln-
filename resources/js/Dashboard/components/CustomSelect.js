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

const ITEM_HEIGHT  = 30   // px per item
const VISIBLE_ROWS = 5    // baris visible sebelum scroll

/** Representasi satu instance custom dropdown untuk satu <select> */
class SelectInstance {
    constructor(select) {
        this.select   = select
        this.wrapper  = document.createElement('div')
        this.trigger  = document.createElement('button')
        this.dropdown = document.createElement('div')

        this._buildDom()
        this._bindEvents()
        this.buildList()
    }

    _buildDom() {
        const { select, wrapper, trigger, dropdown } = this

        select.style.display = 'none'

        wrapper.className = 'cs-wrapper'
        wrapper.dataset.csFor = select.id || ''

        trigger.type      = 'button'
        trigger.className = 'cs-trigger'
        trigger.setAttribute('aria-haspopup', 'listbox')
        trigger.setAttribute('aria-expanded', 'false')

        this.triggerLabel = document.createElement('span')
        this.triggerLabel.className = 'cs-trigger-label'

        const triggerArrow = document.createElement('span')
        triggerArrow.className   = 'cs-arrow'
        triggerArrow.textContent = '▾'
        triggerArrow.setAttribute('aria-hidden', 'true')

        trigger.append(this.triggerLabel, triggerArrow)

        dropdown.className = 'cs-dropdown'
        dropdown.setAttribute('role', 'listbox')
        dropdown.style.display = 'none'

        wrapper.append(trigger, dropdown)
        select.after(wrapper)
    }

    _bindEvents() {
        this.trigger.addEventListener('click', e => {
            e.stopPropagation()
            this.toggle()
        })

        this.trigger.addEventListener('keydown', e => this._onTriggerKeydown(e))

        this._observer = new MutationObserver(() => this.buildList())
        this._observer.observe(this.select, { childList: true, subtree: true, characterData: true })

        this.select.addEventListener('change', () => {
            queueMicrotask(() => this.syncSelected())
        })
    }

    _onTriggerKeydown(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault()
            this.toggle()
        }
        if (e.key === 'Escape') this.close()
    }

    /** Rebuild list dari current state of native select */
    buildList() {
        this.dropdown.innerHTML = ''

        Array.from(this.select.children).forEach(child => {
            if (child.tagName === 'OPTGROUP') {
                this._appendGroup(child)
            } else if (child.tagName === 'OPTION') {
                this.dropdown.appendChild(this._makeItem(child))
            }
        })

        this.syncLabel()
    }

    _appendGroup(group) {
        const groupLabel = document.createElement('div')
        groupLabel.className   = 'cs-group-label'
        groupLabel.textContent = group.label
        this.dropdown.appendChild(groupLabel)

        Array.from(group.children).forEach(opt => {
            this.dropdown.appendChild(this._makeItem(opt))
        })
    }

    _makeItem(opt) {
        const item = document.createElement('div')
        item.className = 'cs-item'
        item.setAttribute('role', 'option')
        item.dataset.value = opt.value
        item.textContent = opt.textContent.trim()
        if (opt.selected) item.classList.add('cs-selected')

        item.addEventListener('click', () => this._selectOption(opt))
        return item
    }

    _selectOption(opt) {
        this.select.value = opt.value
        // Trigger change event agar kode lama tetap bekerja
        this.select.dispatchEvent(new Event('change', { bubbles: true }))
        this.syncSelected()
        this.close()
    }

    syncLabel() {
        const sel = this.select.options[this.select.selectedIndex]
        this.triggerLabel.textContent = sel ? sel.textContent.trim() : '—'
    }

    syncSelected() {
        this.syncLabel()
        this.dropdown.querySelectorAll('.cs-item').forEach(item => {
            item.classList.toggle('cs-selected', item.dataset.value === this.select.value)
        })
    }

    toggle() {
        this.dropdown.dataset.open !== undefined ? this.close() : this.open()
    }

    open() {
        SelectInstance.closeAllExcept(this.dropdown)

        this.dropdown.style.display = 'block'
        this.dropdown.dataset.open = ''
        this.trigger.setAttribute('aria-expanded', 'true')

        // Scroll ke item selected
        const sel = this.dropdown.querySelector('.cs-selected')
        if (sel) sel.scrollIntoView({ block: 'nearest' })

        // Max-height: VISIBLE_ROWS items
        const maxH = ITEM_HEIGHT * VISIBLE_ROWS
        this.dropdown.style.maxHeight = maxH + 'px'
        this.dropdown.style.overflowY = 'auto'

        this._positionDropdown(maxH)
    }

    _positionDropdown(maxH) {
        // Posisi: buka ke atas kalau tidak cukup ruang di bawah
        const rect = this.wrapper.getBoundingClientRect()
        const spaceBelow = window.innerHeight - rect.bottom

        if (spaceBelow < maxH && rect.top > maxH) {
            this.dropdown.style.bottom = '100%'
            this.dropdown.style.top    = 'auto'
        } else {
            this.dropdown.style.top    = '100%'
            this.dropdown.style.bottom = 'auto'
        }
    }

    close() {
        delete this.dropdown.dataset.open
        this.dropdown.style.display = 'none'
        this.trigger.setAttribute('aria-expanded', 'false')
    }

    /** Tutup semua dropdown yang terbuka, kecuali satu (opsional) */
    static closeAllExcept(exceptDropdown = null) {
        document.querySelectorAll('.cs-dropdown[data-open]').forEach(d => {
            if (d !== exceptDropdown) SelectInstance._forceCloseEl(d)
        })
    }

    static _forceCloseEl(d) {
        delete d.dataset.open
        d.style.display = 'none'
        d.closest('.cs-wrapper')
            ?.querySelector('.cs-trigger')
            ?.setAttribute('aria-expanded', 'false')
    }
}

/** Buat elemen custom dropdown dari native <select> */
function upgrade(select) {
    // Jangan upgrade dua kali
    if (select._csUpgraded) return
    select._csUpgraded = true
    select._csInstance = new SelectInstance(select)
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
    SelectInstance.closeAllExcept()
})

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.cs-dropdown[data-open]').forEach(d => {
            delete d.dataset.open
            d.style.display = 'none'
        })
    }
})

const CustomSelect = { upgrade, initAll }

export default CustomSelect