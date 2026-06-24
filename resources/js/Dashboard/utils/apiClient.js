/**
 * apiClient.js
 *
 * Wrapper fetch terpusat yang otomatis menyertakan:
 *   - X-Api-Token     : HMAC token dari meta tag (di-refresh otomatis)
 *   - X-Api-Timestamp : unix timestamp saat request dibuat
 *   - X-CSRF-TOKEN    : untuk request mutasi (POST/PUT/DELETE)
 *   - Accept          : application/json
 *
 * Token dari blade hanya valid 60 detik. JS ini memperbarui token
 * setiap 50 detik via /api/internal/token-refresh (session-auth route).
 *
 * Semua modul/service wajib pakai apiFetch() bukan fetch() langsung.
 */

/** Baca dari meta tag yang di-render blade */
const getMeta = (name) => document.querySelector(`meta[name="${name}"]`)?.content ?? ''

/** State token saat ini */
let _token     = getMeta('api-token')
let _timestamp = getMeta('api-timestamp')

/**
 * Refresh token dari server (route web, perlu session auth).
 * Dipanggil otomatis setiap 50 detik.
 */
async function refreshToken() {
    try {
        const res  = await fetch('/api/internal/token-refresh', {
            headers: {
                Accept:          'application/json',
                'X-CSRF-TOKEN':  getMeta('csrf-token'),
            },
            credentials: 'same-origin',
        })
        if (!res.ok) return
        const json = await res.json()
        _token     = json.token
        _timestamp = json.timestamp

        // Update meta tag juga (untuk modul yang baca langsung)
        const tMeta  = document.querySelector('meta[name="api-token"]')
        const tsMeta = document.querySelector('meta[name="api-timestamp"]')
        if (tMeta)  tMeta.setAttribute('content', _token)
        if (tsMeta) tsMeta.setAttribute('content', _timestamp)
    } catch {
        // Gagal refresh — token lama masih bisa dipakai sampai TTL habis
    }
}

// Perbarui token setiap 50 detik (TTL = 60 detik, beri margin 10 detik)
setInterval(refreshToken, 50_000)

/**
 * apiFetch(url, options?)
 *
 * Drop-in replacement untuk fetch() dengan HMAC header otomatis.
 *
 * @param {string} url
 * @param {RequestInit} [options]
 * @returns {Promise<Response>}
 *
 * @example
 * import { apiFetch } from '@/Dashboard/utils/apiClient'
 *
 * const res  = await apiFetch('/api/chart/penjualan?tahun=2025')
 * const data = await res.json()
 */
export async function apiFetch(url, options = {}) {
    // Selalu pakai timestamp fresh agar tidak replay-attack
    const ts      = String(Math.floor(Date.now() / 1000))
    const headers = {
        Accept:            'application/json',
        'X-Api-Token':     _token,
        'X-Api-Timestamp': ts,
        ...options.headers,
    }

    // Inject CSRF untuk request yang mengubah data
    const method = (options.method ?? 'GET').toUpperCase()
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
        headers['X-CSRF-TOKEN'] = getMeta('csrf-token')
    }

    return fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers,
    })
}

/**
 * Shorthand helpers
 */
export const apiGet    = (url, opts = {})         => apiFetch(url, { ...opts, method: 'GET' })
export const apiPost   = (url, body, opts = {})   => apiFetch(url, { ...opts, method: 'POST',   body: JSON.stringify(body), headers: { 'Content-Type': 'application/json', ...(opts.headers ?? {}) } })
export const apiPut    = (url, body, opts = {})   => apiFetch(url, { ...opts, method: 'PUT',    body: JSON.stringify(body), headers: { 'Content-Type': 'application/json', ...(opts.headers ?? {}) } })
export const apiDelete = (url, opts = {})         => apiFetch(url, { ...opts, method: 'DELETE' })

export default apiFetch
