/**
 * CrudHelper — utilitas reusable untuk operasi CRUD tabel edit di dashboard.
 *
 * Pola: cek existing record by unique key → PUT jika ada, POST jika tidak.
 * Data tahun lalu → input disabled + row semi-transparent.
 */
const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content
const TAHUN_INI = new Date().getFullYear()

/**
 * Render container tabel edit generik.
 * @param {string}   containerId  - id elemen container (div)
 * @param {Array}    rows         - array baris: [{ label, fields: [{key,value,color}] }]
 * @param {boolean}  isReadOnly   - jika true, semua input disabled
 * @param {Function} onSave       - async (rowIndex, fieldValues) => void
 * @param {string[]} headers      - header kolom
 * @param {Function} [onAfterSave] - (rowIndex, fieldValues) => void — dipanggil setelah save berhasil, untuk live-update kalkulasi
 */
export function renderEditTable(containerId, rows, isReadOnly, onSave, headers = [], onAfterSave = null) {
    const container = document.getElementById(containerId)
    if (!container) return

    const disabledStyle = isReadOnly
        ? 'opacity:0.45; pointer-events:none;'
        : ''

    const headHtml = headers.length
        ? `<thead style="position:sticky;top:0;background:#252525;z-index:1;">
            <tr style="font-size:0.65rem;text-transform:uppercase;">
                ${headers.map(h => `<th style="padding:8px 10px;color:#888;text-align:left;">${h}</th>`).join('')}
                <th style="padding:8px 10px;color:#aaa;text-align:center;">Aksi</th>
            </tr>
           </thead>`
        : ''

    const bodyHtml = rows.map((row, ri) => {
        const cells = row.fields.map((f, fi) => `
            <td style="padding:4px 6px;">
                <input
                    type="number"
                    data-row="${ri}"
                    data-field="${f.key}"
                    value="${f.value ?? ''}"
                    placeholder="—"
                    ${isReadOnly ? 'disabled' : ''}
                    style="width:100px;padding:4px 6px;border-radius:4px;border:1px solid ${isReadOnly ? '#2a2a2a' : '#333'};
                           background:#1a1a1a;color:${isReadOnly ? '#444' : (f.color ?? '#ccc')};
                           font-size:0.75rem;text-align:right;"
                />
            </td>`).join('')

        return `<tr style="border-bottom:1px solid #2a2a2a;${disabledStyle}" data-row="${ri}">
            <td style="padding:8px 10px;color:#888;font-size:0.75rem;white-space:nowrap;">${row.label}</td>
            ${cells}
            <td style="padding:4px 6px;text-align:center;">
                <button
                    id="btnSaveRow_${containerId}_${ri}"
                    data-row="${ri}"
                    ${isReadOnly ? 'disabled' : ''}
                    style="padding:3px 10px;border-radius:4px;border:none;
                           background:${isReadOnly ? '#2a2a2a' : '#ff9800'};
                           color:${isReadOnly ? '#444' : '#000'};
                           font-size:0.7rem;cursor:${isReadOnly ? 'not-allowed' : 'pointer'};font-weight:600;">
                    ${isReadOnly ? '🔒' : 'Simpan'}
                </button>
            </td>
        </tr>`
    }).join('')

    container.innerHTML = `
        <table style="width:100%;border-collapse:collapse;">
            ${headHtml}
            <tbody>${bodyHtml}</tbody>
        </table>`

    if (isReadOnly) return

    // Pasang event listener simpan per baris
    rows.forEach((row, ri) => {
        const btn = document.getElementById(`btnSaveRow_${containerId}_${ri}`)
        if (!btn) return
        btn.addEventListener('click', async () => {
            btn.textContent = '⏳'
            btn.disabled    = true
            const inputs    = container.querySelectorAll(`input[data-row="${ri}"]`)
            const values    = {}
            inputs.forEach(inp => {
                values[inp.dataset.field] = inp.value === '' ? null : parseFloat(inp.value)
            })
            try {
                await onSave(ri, values)
                btn.textContent      = '✓ OK'
                btn.style.background = '#00ff88'
                if (onAfterSave) onAfterSave(ri, values)
            } catch {
                btn.textContent      = '✗ Error'
                btn.style.background = '#f44336'
            } finally {
                btn.disabled = false
                setTimeout(() => {
                    btn.textContent      = 'Simpan'
                    btn.style.background = '#ff9800'
                }, 2000)
            }
        })
    })
}

/**
 * Upsert satu record: GET dulu → PUT jika ada, POST jika tidak.
 * @param {string} endpoint      - base API path, e.g. '/api/v1/fact/p2tl'
 * @param {object} uniqueParams  - query params untuk lookup, e.g. { tahun, bulan, tipe }
 * @param {object} payload       - data untuk store/update
 * @param {string} [idField='id'] - nama field ID di response
 */
export async function upsert(endpoint, uniqueParams, payload) {
    const qs     = new URLSearchParams({ ...uniqueParams, per_page: 1 }).toString()
    const csrf   = CSRF()
    const heads  = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }

    const checkRes = await fetch(`${endpoint}?${qs}`, { headers: { Accept: 'application/json' } })
    const check    = await checkRes.json()
    const existing = check.data?.data?.[0] ?? check.data?.[0]

    if (existing?.id) {
        const res = await fetch(`${endpoint}/${existing.id}`, {
            method: 'PUT', headers: heads, body: JSON.stringify(payload),
        })
        if (!res.ok) throw new Error(`PUT failed: ${res.status}`)
        return res.json()
    } else {
        const res = await fetch(endpoint, {
            method: 'POST', headers: heads, body: JSON.stringify(payload),
        })
        if (!res.ok) throw new Error(`POST failed: ${res.status}`)
        return res.json()
    }
}

/**
 * Delete by filter params.
 * @param {string} endpoint  - e.g. '/api/v1/fact/p2tl'
 * @param {object} params    - { tahun, tipe?, ... }
 */
export async function deleteByFilter(endpoint, params) {
    const qs   = new URLSearchParams(params).toString()
    const csrf = CSRF()
    const res  = await fetch(`${endpoint}?${qs}`, {
        method:  'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
    })
    if (!res.ok) throw new Error(`DELETE failed: ${res.status}`)
    return res.status === 204 ? null : res.json()
}

/**
 * Bind tombol modal standard (Muat + Hapus) ke elemen DOM.
 * @param {object} cfg - { muatBtn, hapusBtn, modalMuat, modalHapus, onMuat, onHapus }
 */
export function bindCrudModal({ muatBtnId, hapusBtnId, modalMuatId, modalHapusId, onMuat, onHapus }) {
    const btnMuat    = document.getElementById(muatBtnId)
    const btnHapus   = document.getElementById(hapusBtnId)
    const modalMuat  = document.getElementById(modalMuatId)
    const modalHapus = document.getElementById(modalHapusId)

    const bind = (btn, modal, confirmBtnId, cancelBtnId, handler) => {
        if (!btn || btn._crudBound) return
        btn._crudBound = true
        btn.addEventListener('click', () => { if (modal) modal.style.display = 'flex' })
        document.getElementById(cancelBtnId)?.addEventListener('click', () => { if (modal) modal.style.display = 'none' })
        document.getElementById(confirmBtnId)?.addEventListener('click', async () => {
            if (modal) modal.style.display = 'none'
            await handler()
        })
    }

    bind(btnMuat,  modalMuat,  `btnKonfirmasiMuat${muatBtnId.replace('btnMuat','')}`,
                               `btnBatalMuat${muatBtnId.replace('btnMuat','')}`,  onMuat)
    bind(btnHapus, modalHapus, `btnKonfirmasiHapus${hapusBtnId.replace('btnHapus','')}`,
                               `btnBatalHapus${hapusBtnId.replace('btnHapus','')}`, onHapus)
}

/**
 * Batch create 12 record kosong untuk satu tahun.
 * @param {string}   endpoint  - API base path
 * @param {Function} makeRow   - (bulan: number) => object payload
 */
export async function batchCreate(endpoint, makeRow) {
    const csrf  = CSRF()
    const heads = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }
    const results = []
    for (let bulan = 1; bulan <= 12; bulan++) {
        try {
            const res = await fetch(endpoint, {
                method: 'POST', headers: heads,
                body: JSON.stringify(makeRow(bulan)),
            })
            results.push({ bulan, ok: res.ok })
        } catch {
            results.push({ bulan, ok: false })
        }
    }
    return results
}

export { TAHUN_INI }