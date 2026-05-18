/**
 * DashboardCache.js
 *
 * Strategi: Stale-While-Revalidate (SWR)
 * ─────────────────────────────────────────────────────────────────────────────
 *  - Cache data per-key di IndexedDB (tahan hard-refresh)
 *  - Saat init: kembalikan cache instan jika valid, refresh di background
 *  - sharedFetch DIHAPUS — SheetsService sudah handle dedup fetch sendiri
 *  - TTL 10 menit sinkron dengan SheetsService.TTL
 */

const DB_NAME    = 'pln_dashboard_cache'
const DB_VERSION = 3                          // bump → hapus cache lama otomatis
const STORE_NAME = 'cache_store'

const DEFAULTS = {
    REFRESH_INTERVAL_MS : 10 * 60 * 1000,    // 10 menit
    TTL_MS              : 10 * 60 * 1000,    // 10 menit — sinkron dengan SheetsService
    VERSION             : '3.0.0',
}

// ─── Hash ringan (djb2) ───────────────────────────────────────────────────────

function hashData(data) {
    try {
        const str = JSON.stringify(data)
        let h = 5381
        for (let i = 0; i < str.length; i++) {
            h = ((h << 5) + h) ^ str.charCodeAt(i)
        }
        return (h >>> 0).toString(36)
    } catch {
        return String(Date.now())
    }
}

// ─── IndexedDB helpers ────────────────────────────────────────────────────────

let _db = null

async function openDB() {
    if (_db) return _db
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION)
        req.onupgradeneeded = e => {
            const db = e.target.result
            if (db.objectStoreNames.contains(STORE_NAME)) {
                db.deleteObjectStore(STORE_NAME)
            }
            db.createObjectStore(STORE_NAME, { keyPath: 'key' })
        }
        req.onsuccess = e => { _db = e.target.result; resolve(_db) }
        req.onerror   = e => reject(e.target.error)
    })
}

async function idbGet(key) {
    try {
        const db = await openDB()
        return new Promise((resolve, reject) => {
            const tx  = db.transaction(STORE_NAME, 'readonly')
            const req = tx.objectStore(STORE_NAME).get(key)
            req.onsuccess = e => resolve(e.target.result ?? null)
            req.onerror   = e => reject(e.target.error)
        })
    } catch (err) {
        console.warn('[Cache] idbGet error:', err)
        return null
    }
}

async function idbSet(key, value) {
    try {
        const db = await openDB()
        return new Promise((resolve, reject) => {
            const tx  = db.transaction(STORE_NAME, 'readwrite')
            const req = tx.objectStore(STORE_NAME).put({ key, ...value })
            req.onsuccess = () => resolve()
            req.onerror   = e => reject(e.target.error)
        })
    } catch (err) {
        console.warn('[Cache] idbSet error:', err)
    }
}

async function idbDelete(key) {
    try {
        const db = await openDB()
        return new Promise((resolve, reject) => {
            const tx  = db.transaction(STORE_NAME, 'readwrite')
            const req = tx.objectStore(STORE_NAME).delete(key)
            req.onsuccess = () => resolve()
            req.onerror   = e => reject(e.target.error)
        })
    } catch (err) {
        console.warn('[Cache] idbDelete error:', err)
    }
}

// ─── DashboardCache ───────────────────────────────────────────────────────────

export default class DashboardCache {
    /**
     * @param {string}   key     - Nama unik dataset, misal 'kwhData'
     * @param {Function} fetcher - async () => data  (sudah di-dedup SheetsService)
     * @param {object}   options
     * @param {number}   [options.refreshIntervalMs]
     * @param {number}   [options.ttlMs]
     * @param {Function} [options.onUpdate] - callback(newData) saat data berubah
     */
    constructor(key, fetcher, options = {}) {
        this._key      = `v${DEFAULTS.VERSION}::${key}`
        this._fetcher  = fetcher
        this._interval = options.refreshIntervalMs ?? DEFAULTS.REFRESH_INTERVAL_MS
        this._ttl      = options.ttlMs             ?? DEFAULTS.TTL_MS
        this._onUpdate = options.onUpdate          ?? null
        this._hash     = null
        this._data     = null
        this._timer    = null
    }

    async init() {
        const cached = await this._read()

        if (cached && this._alive(cached)) {
            // Cache HIT — tampilkan instan, refresh di background
            this._data = cached.data
            this._hash = cached.hash
            console.info(`[Cache] HIT "${this._key}" (${this._age(cached)}m lalu)`)
            this._backgroundRefresh()
        } else {
            // Cache MISS/STALE — fetch sekarang (SheetsService handle dedup)
            const reason = cached ? 'STALE' : 'MISS'
            console.info(`[Cache] ${reason} "${this._key}" — fetching`)
            this._data = await this._fetchAndStore()
        }

        this._startTimer()
        return this._data
    }

    async forceRefresh() {
        console.info(`[Cache] FORCE REFRESH "${this._key}"`)
        this._data = await this._fetchAndStore()
        return this._data
    }

    async clear() {
        await idbDelete(this._key)
        this._data = null
        this._hash = null
    }

    get data() { return this._data }

    destroy() {
        if (this._timer) clearInterval(this._timer)
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    _startTimer() {
        if (this._timer) clearInterval(this._timer)
        this._timer = setInterval(() => this._backgroundRefresh(), this._interval)
    }

    async _backgroundRefresh() {
        try {
            const t0        = performance.now()
            const fresh     = await this._fetcher()
            const freshHash = hashData(fresh)
            const elapsed   = (performance.now() - t0).toFixed(0)

            if (freshHash === this._hash) {
                console.info(`[Cache] NO CHANGE "${this._key}" (${elapsed}ms)`)
                await this._touch()
                return
            }

            console.info(`[Cache] UPDATED "${this._key}" (${elapsed}ms)`)
            this._hash = freshHash
            this._data = fresh
            await this._write(fresh, freshHash)
            if (this._onUpdate) this._onUpdate(fresh)

        } catch (err) {
            console.warn(`[Cache] Background refresh gagal "${this._key}":`, err)
        }
    }

    async _fetchAndStore() {
        try {
            const t0      = performance.now()
            const data    = await this._fetcher()
            const hash    = hashData(data)
            const elapsed = (performance.now() - t0).toFixed(0)

            // Validasi — jangan simpan data kosong/undefined ke cache
            if (data === undefined || data === null) {
                throw new Error('Fetcher returned undefined/null')
            }

            console.info(`[Cache] FETCH "${this._key}" ${elapsed}ms`)
            this._hash = hash
            await this._write(data, hash)
            return data

        } catch (err) {
            // Fallback ke stale cache daripada crash
            const cached = await this._read()
            if (cached?.data !== undefined) {
                console.warn(`[Cache] Fetch gagal, pakai stale "${this._key}"`)
                return cached.data
            }
            throw err
        }
    }

    async _write(data, hash) {
        await idbSet(this._key, {
            data,
            hash,
            cachedAt : Date.now(),
            version  : DEFAULTS.VERSION,
        })
    }

    async _touch() {
        const cached = await this._read()
        if (cached) await idbSet(this._key, { ...cached, cachedAt: Date.now() })
    }

    async _read() {
        const entry = await idbGet(this._key)
        if (!entry || entry.version !== DEFAULTS.VERSION) return null
        return entry
    }

    _alive(cached) {
        return Date.now() - cached.cachedAt < this._ttl
    }

    _age(cached) {
        return Math.floor((Date.now() - cached.cachedAt) / 60000)
    }
}

// ─── CacheManager ─────────────────────────────────────────────────────────────

export class CacheManager {
    constructor() { this._map = new Map() }

    register(key, instance) {
        this._map.set(key, instance)
        return instance
    }

    get(key) { return this._map.get(key) }

    async refreshAll() {
        return Promise.allSettled([...this._map.values()].map(c => c.forceRefresh()))
    }

    async clearAll() {
        for (const c of this._map.values()) await c.clear()
    }

    destroyAll() {
        for (const c of this._map.values()) c.destroy()
    }
}