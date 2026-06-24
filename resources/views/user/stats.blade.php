@extends('layouts.app')

@section('title', 'Database Stats — PLN Dashboard')

@push('styles')
    @vite([
        'resources/css/components/dashboard-components.css',
        'resources/css/pages/dashboard.css',
    ])
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/layouts/sidebar.css') }}" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/layouts/sidebar.css') }}">
    </noscript>
    <style>
        /* ── Stats page specific styles ── */
        .stats-page { padding: 24px; max-width: 1400px; margin: 0 auto; }

        .stats-back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.78rem; color: #888; text-decoration: none;
            padding: 6px 12px; border-radius: 6px;
            border: 1px solid #333; background: rgba(255,255,255,0.03);
            margin-bottom: 20px; transition: color .2s, border-color .2s;
        }
        .stats-back-btn:hover { color: #fff; border-color: #555; }

        .stats-page-title {
            font-size: 1.2rem; font-weight: 700; color: #fff;
            margin-bottom: 4px; letter-spacing: 0.3px;
        }
        .stats-page-sub {
            font-size: 0.75rem; color: #555; margin-bottom: 24px;
        }

        /* Summary bar */
        .stats-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 28px;
        }
        .summary-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .summary-card .s-label {
            font-size: 0.65rem; color: #555; text-transform: uppercase;
            letter-spacing: 0.6px; margin-bottom: 6px;
        }
        .summary-card .s-value {
            font-size: 1.4rem; font-weight: 700; color: #fff;
            line-height: 1;
        }
        .summary-card .s-value.green  { color: #00ff88; }
        .summary-card .s-value.yellow { color: #ff9800; }
        .summary-card .s-value.red    { color: #f44336; }
        .summary-card .s-value.blue   { color: #2196f3; }

        /* Kategori section */
        .stats-kategori-label {
            font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;
            color: #444; margin: 20px 0 10px;
            padding-bottom: 6px; border-bottom: 1px solid #1e1e1e;
        }

        /* Table cards grid */
        .stats-table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 12px;
        }

        .rt-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            padding: 16px;
            position: relative;
            transition: border-color .2s, background .2s;
        }
        .rt-card:hover { border-color: #444; background: rgba(255,255,255,0.05); }

        .rt-card.status-kosong  { border-left: 3px solid #ff9800; }
        .rt-card.status-ada_data { border-left: 3px solid #00ff88; }
        .rt-card.status-error   { border-left: 3px solid #f44336; }

        .rt-card-header {
            display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px;
        }
        .rt-card-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,0.06);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .rt-card-icon svg { width: 16px; height: 16px; stroke: #888; }

        .rt-card-title {
            font-size: 0.78rem; font-weight: 600; color: #ccc; line-height: 1.3;
        }
        .rt-card-table {
            font-size: 0.62rem; color: #444; font-family: monospace; margin-top: 2px;
        }

        .rt-card-stats {
            display: flex; gap: 16px; flex-wrap: wrap;
        }
        .rt-stat-item .rs-label {
            font-size: 0.6rem; color: #555; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .rt-stat-item .rs-value {
            font-size: 1.05rem; font-weight: 700; color: #fff; line-height: 1.2;
        }
        .rt-stat-item .rs-value.dim  { color: #888; font-size: 0.85rem; }
        .rt-stat-item .rs-value.green{ color: #00ff88; }
        .rt-stat-item .rs-value.red  { color: #f44336; }

        .rt-card-footer {
            margin-top: 12px; padding-top: 10px;
            border-top: 1px solid #1e1e1e;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;
        }
        .rt-card-years {
            display: flex; gap: 4px; flex-wrap: wrap;
        }
        .rt-year-badge {
            font-size: 0.58rem; padding: 2px 6px;
            border-radius: 4px; background: rgba(33,150,243,0.12);
            border: 1px solid rgba(33,150,243,0.25); color: #2196f3;
        }
        .rt-year-badge.current {
            background: rgba(0,255,136,0.1);
            border-color: rgba(0,255,136,0.3); color: #00ff88;
        }
        .rt-last-update {
            font-size: 0.6rem; color: #444;
        }

        /* Badge status */
        .rt-status-badge {
            position: absolute; top: 12px; right: 12px;
            font-size: 0.58rem; padding: 2px 8px; border-radius: 10px;
        }
        .rt-status-badge.ada_data {
            background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.25); color: #00ff88;
        }
        .rt-status-badge.kosong {
            background: rgba(255,152,0,0.1); border: 1px solid rgba(255,152,0,0.25); color: #ff9800;
        }
        .rt-status-badge.error {
            background: rgba(244,67,54,0.1); border: 1px solid rgba(244,67,54,0.25); color: #f44336;
        }

        /* Loading skeleton */
        .rt-skeleton {
            background: linear-gradient(90deg, #1a1a1a 25%, #222 50%, #1a1a1a 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 6px;
        }
        @keyframes shimmer { 0%{ background-position: 200% 0; } 100%{ background-position: -200% 0; } }

        .stats-error-banner {
            background: rgba(244,67,54,0.08); border: 1px solid rgba(244,67,54,0.2);
            border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;
            font-size: 0.78rem; color: #f44336; display: none;
        }

        /* Refresh button */
        .stats-refresh-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.72rem; padding: 6px 14px; border-radius: 6px;
            border: 1px solid #2196f3; background: rgba(33,150,243,0.08);
            color: #2196f3; cursor: pointer; transition: background .2s;
        }
        .stats-refresh-btn:hover { background: rgba(33,150,243,0.18); }
        .stats-refresh-btn svg { width: 13px; height: 13px; }
        .stats-refresh-btn.loading svg { animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .stats-generated-at {
            font-size: 0.62rem; color: #444; margin-left: auto;
        }
    </style>
@endpush

@section('content')

<div class="stats-page">

    <a href="{{ route('dashboard') }}" class="stats-back-btn">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Kembali ke Dashboard
    </a>

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:6px;">
        <div>
            <div class="stats-page-title">📊 Database Stats</div>
            <div class="stats-page-sub">Jumlah record per tabel RT — data langsung dari database</div>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="stats-generated-at" id="statsGeneratedAt">—</span>
            <button class="stats-refresh-btn" id="btnRefreshStats" onclick="loadStats()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Error banner --}}
    <div class="stats-error-banner" id="statsErrorBanner"></div>

    {{-- Summary cards --}}
    <div class="stats-summary-grid" id="statsSummaryGrid">
        @foreach(['Total Tabel','Ada Data','Tabel Kosong','Error','Total Record'] as $sk)
        <div class="summary-card">
            <div class="s-label">{{ $sk }}</div>
            <div class="rt-skeleton" style="height:28px; width:60px;"></div>
        </div>
        @endforeach
    </div>

    {{-- Table list — diisi JS --}}
    <div id="statsTableContainer">
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap:12px;">
            @for($i = 0; $i < 14; $i++)
            <div class="rt-card">
                <div style="height:14px; width:55%; margin-bottom:8px;" class="rt-skeleton"></div>
                <div style="height:10px; width:80%; margin-bottom:16px;" class="rt-skeleton"></div>
                <div style="height:28px; width:40%;" class="rt-skeleton"></div>
            </div>
            @endfor
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    // ── Icon SVG paths (Feather-compatible) ──────────────────────────────────
    const ICONS = {
        'tag':           '<polyline points="20.59 13.41 13.41 20.59 6.41 13.59 13.59 6.41 20.59 13.41 20.59 13.41"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
        'layers':        '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'zap':           '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'trending-down': '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>',
        'activity':      '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'globe':         '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'clock':         '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'dollar-sign':   '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'bar-chart-2':   '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'percent':       '<line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
        'users':         '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'file-text':     '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'alert-triangle':'<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'upload':        '<polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>',
    };

    function svgIcon(name) {
        const d = ICONS[name] || ICONS['bar-chart-2'];
        return `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${d}</svg>`;
    }

    // ── Number formatter ─────────────────────────────────────────────────────
    function fmt(n) {
        if (n === null || n === undefined) return '—';
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + ' jt';
        if (n >= 1_000) return n.toLocaleString('id-ID');
        return String(n);
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
             + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    // ── Render summary bar ───────────────────────────────────────────────────
    function renderSummary(summary) {
        const grid = document.getElementById('statsSummaryGrid');
        const tahunIni = new Date().getFullYear();

        const items = [
            { label: 'Total Tabel',    value: summary.total_tabel,        cls: 'blue'   },
            { label: 'Ada Data',       value: summary.tabel_ada_data,      cls: 'green'  },
            { label: 'Tabel Kosong',   value: summary.tabel_kosong,        cls: 'yellow' },
            { label: 'Error',          value: summary.tabel_error,         cls: summary.tabel_error > 0 ? 'red' : '' },
            { label: 'Total Record',   value: fmt(summary.total_semua_record), cls: 'blue' },
        ];

        grid.innerHTML = items.map(item => `
            <div class="summary-card">
                <div class="s-label">${item.label}</div>
                <div class="s-value ${item.cls}">${item.value}</div>
            </div>
        `).join('');
    }

    // ── Render table cards grouped by kategori ───────────────────────────────
    function renderTables(tables) {
        const container = document.getElementById('statsTableContainer');
        const tahunIni  = new Date().getFullYear();

        // Group by kategori
        const groups = {};
        tables.forEach(t => {
            if (!groups[t.kategori]) groups[t.kategori] = [];
            groups[t.kategori].push(t);
        });

        let html = '';
        for (const [kat, items] of Object.entries(groups)) {
            html += `<div class="stats-kategori-label">${kat}</div>`;
            html += `<div class="stats-table-grid">`;

            items.forEach(t => {
                const isError  = t.status === 'error';
                const isEmpty  = t.status === 'kosong';
                const badgeTxt = isError ? 'Error' : isEmpty ? 'Kosong' : 'Ada Data';

                const tahunBadges = (t.tahun_tersedia || []).slice(0, 8).map(y =>
                    `<span class="rt-year-badge ${y == tahunIni ? 'current' : ''}">${y}</span>`
                ).join('');

                const recordTahunIniHtml = t.record_tahun_ini !== null
                    ? `<div class="rt-stat-item">
                           <div class="rs-label">Tahun ${tahunIni}</div>
                           <div class="rs-value ${t.record_tahun_ini > 0 ? 'green' : ''}">${fmt(t.record_tahun_ini)}</div>
                       </div>`
                    : '';

                html += `
                <div class="rt-card status-${t.status}">
                    <span class="rt-status-badge ${t.status}">${badgeTxt}</span>

                    <div class="rt-card-header">
                        <div class="rt-card-icon">${svgIcon(t.icon)}</div>
                        <div>
                            <div class="rt-card-title">${t.label}</div>
                            <div class="rt-card-table">${t.table}</div>
                        </div>
                    </div>

                    <div class="rt-card-stats">
                        <div class="rt-stat-item">
                            <div class="rs-label">Total Record</div>
                            <div class="rs-value ${isError ? 'red' : t.total_records === 0 ? 'dim' : ''}">
                                ${isError ? 'Error' : fmt(t.total_records)}
                            </div>
                        </div>
                        ${recordTahunIniHtml}
                    </div>

                    <div class="rt-card-footer">
                        <div class="rt-card-years">
                            ${tahunBadges || '<span style="font-size:0.6rem;color:#444;">—</span>'}
                        </div>
                        <div class="rt-last-update">
                            ${t.latest_updated ? 'Update: ' + fmtDate(t.latest_updated) : ''}
                        </div>
                    </div>

                    ${isError ? `<div style="font-size:0.6rem;color:#f44336;margin-top:8px;word-break:break-all;">${t.error}</div>` : ''}
                </div>`;
            });

            html += `</div>`;
        }

        container.innerHTML = html;
    }

    // ── Main load function ───────────────────────────────────────────────────
    window.loadStats = async function () {
        const btn     = document.getElementById('btnRefreshStats');
        const errBanner = document.getElementById('statsErrorBanner');
        const genAt   = document.getElementById('statsGeneratedAt');

        btn.classList.add('loading');
        errBanner.style.display = 'none';

        try {
            const res  = await fetch('/api/stats/database');
            const json = await res.json();

            if (!json.success) throw new Error(json.message || 'Gagal memuat data');

            renderSummary(json.data.summary);
            renderTables(json.data.tables);

            genAt.textContent = 'Diperbarui: ' + fmtDate(json.data.summary.generated_at);
        } catch (err) {
            errBanner.textContent = '⚠ Gagal memuat stats: ' + err.message;
            errBanner.style.display = 'block';
        } finally {
            btn.classList.remove('loading');
        }
    };

    // Auto-load on page ready
    document.addEventListener('DOMContentLoaded', loadStats);
})();
</script>
@endsection
