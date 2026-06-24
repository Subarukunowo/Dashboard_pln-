<nav class="dash-sidebar" id="dashSidebar">

    <div class="sidebar-section-label">{{ __('sidebar.umum') }}</div>
    <div class="sidebar-item active" data-section="all">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
        </svg>
        {{ __('sidebar.tampilkan_semua') }}
    </div>

    <div class="sidebar-section-label">{{ __('sidebar.produksi_biaya') }}</div>
    <div class="sidebar-item" data-section="bpp">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
        {{ __('sidebar.bpp') }}
    </div>
    <div class="sidebar-item" data-section="losses">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
        {{ __('sidebar.losses') }}
    </div>

    <div class="sidebar-section-label">{{ __('sidebar.penjualan') }}</div>
    <div class="sidebar-item" data-section="penjualan">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        {{ __('sidebar.kinerja_penjualan') }}
    </div>
    <div class="sidebar-item" data-section="babin">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>a
        {{ __('sidebar.babin') }}
    </div>
    <div class="sidebar-item" data-section="jamnyala">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        {{ __('sidebar.jam_nyala') }}
    </div>

    <div class="sidebar-section-label">{{ __('sidebar.pendapatan') }}</div>
    <div class="sidebar-item" data-section="goltarif">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>
        </svg>
        {{ __('sidebar.goltarif') }}
    </div>
    <div class="sidebar-item" data-section="pendapatan">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/>
        </svg>
        {{ __('sidebar.pendapatan_batam') }}
    </div>
    <div class="sidebar-item" data-section="rpkwh">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        {{ __('sidebar.rpkwh') }}
    </div>

    <div class="sidebar-section-label">{{ __('sidebar.pelanggan') }}</div>
    <div class="sidebar-item" data-section="pelanggan">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        {{ __('sidebar.pelanggan') }}
    </div>
    <div class="sidebar-item" data-section="kva">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <line x1="8" y1="21" x2="16" y2="21"/>
            <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
        {{ __('sidebar.kva') }}
    </div>

    <div class="sidebar-section-label">{{ __('sidebar.keuangan') }}</div>
    <div class="sidebar-item" data-section="tunggakan">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        {{ __('sidebar.tunggakan') }}
    </div>
    <div class="sidebar-item" data-section="p2tl">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        {{ __('sidebar.p2tl') }}
    </div>

    <div class="sidebar-section-label">{{ __('sidebar.analisis') }}</div>
    <a href="{{ route('historis.index') }}"
       class="sidebar-item {{ Request::is('historis*') ? 'active' : '' }}"
       style="text-decoration:none; color:inherit;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-6"/>
        </svg>
        {{ __('sidebar.historis') }}
    </a>
    <a href="{{ route('stats') }}"
       class="sidebar-item {{ Request::is('stats*') ? 'active' : '' }}"
       style="text-decoration:none; color:inherit;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6"  y1="20" x2="6"  y2="14"/>
        </svg>
        Database Stats
    </a>

</nav>