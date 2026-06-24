    <!DOCTYPE html>
    <html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ __('app.meta.description') }}">
        <meta name="author" content="Sandy Mulia Kesuma">
        @php [$_apiToken, $_apiTs] = \App\Helpers\InternalApiToken::generate(); @endphp
        <meta name="api-token"     content="{{ $_apiToken }}">
        <meta name="api-timestamp" content="{{ $_apiTs }}">

        {{-- ================================================================
             INTERNAL API FETCH INTERCEPTOR
             Patch window.fetch SEBELUM semua script lain load.
             Setiap request ke /api/* otomatis mendapat HMAC header.
             Token di-refresh via /api/internal/token-refresh tiap 50 detik.
             ================================================================ --}}
        <script>
        (function () {
            var _secret_ts = '{{ $_apiTs }}';
            var _token     = document.querySelector('meta[name="api-token"]').content;
            var _csrf      = document.querySelector('meta[name="csrf-token"]').content;

            // generate() menghasilkan [token, timestamp] yang SATU PASANG.
            // Kita simpan pasangan terakhir yang valid.
            var _currentPair = { token: _token, ts: _secret_ts };

            // Refresh pasangan token+timestamp dari server tiap 50 detik
            setInterval(function () {
                fetch('/api/internal/token-refresh', {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrf }
                })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (j) {
                    if (!j) return;
                    _currentPair = { token: j.token, ts: j.timestamp };
                    var tMeta  = document.querySelector('meta[name="api-token"]');
                    var tsMeta = document.querySelector('meta[name="api-timestamp"]');
                    if (tMeta)  tMeta.setAttribute('content', j.token);
                    if (tsMeta) tsMeta.setAttribute('content', j.timestamp);
                });
            }, 50000);

            // Patch fetch global — inject header HMAC pada setiap request ke /api/*
            var _origFetch = window.fetch;
            window.fetch = function (input, init) {
                var url = (typeof input === 'string') ? input
                        : (input && input.url ? input.url : '');

                if (url && url.indexOf('/api/') !== -1 && url.indexOf('/token-refresh') === -1) {
                    init = init || {};
                    var headers = new Headers(init.headers || {});

                    // Gunakan PASANGAN token+ts yang sama — JANGAN generate ts baru
                    headers.set('X-Api-Token',     _currentPair.token);
                    headers.set('X-Api-Timestamp', _currentPair.ts);

                    if (!headers.has('Accept')) {
                        headers.set('Accept', 'application/json');
                    }

                    var method = (init.method || 'GET').toUpperCase();
                    if (['POST','PUT','PATCH','DELETE'].indexOf(method) !== -1) {
                        if (!headers.has('X-CSRF-TOKEN')) {
                            headers.set('X-CSRF-TOKEN', _csrf);
                        }
                    }

                    init = Object.assign({}, init, { headers: headers, credentials: 'same-origin' });
                }

                return _origFetch.call(this, input, init);
            };
        })();
        </script>

        <title>@yield('title', __('app.meta.title'))</title>

        @include('partials.layouts.MetaConfig')

        <link rel="preconnect" href="https://cdnjs.cloudflare.com">
        <link rel="preconnect" href="https://cdn.jsdelivr.net">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
            crossorigin="anonymous" referrerpolicy="no-referrer">

        @vite([
            'resources/css/app.css',
            'resources/css/layouts/navbar.css',
            'resources/css/pages/dashboard.css',
            'resources/css/pages/fact-entry.css',
            'resources/css/components/chatbot.css',
            'resources/css/components/app.css',
            'resources/css/components/custom-select.css',
        ])

        @stack('styles')
        @yield('head')

        {{-- Anti-Flash: theme + accent + font + animation sebelum render --}}
        <script>
        (function () {
            var theme = localStorage.getItem('pln_theme') || 'dark';
            if (theme === 'light') {
                document.documentElement.classList.add('light-mode-pre');
                document.addEventListener('DOMContentLoaded', function () {
                    document.body.classList.add('light-mode');
                });
            }
            var accents = {
                orange: '#ff9800', green: '#00ff88', blue: '#3b82f6',
                cyan:   '#06b6d4', violet:'#8b5cf6', rose: '#f43f5e'
            };
            var accent = localStorage.getItem('pln_accent') || 'orange';
            document.documentElement.style.setProperty('--accent-orange', accents[accent] || accents.orange);
            var fontSize = localStorage.getItem('pln_font');
            if (fontSize) document.documentElement.style.fontSize = fontSize + 'px';
            if (localStorage.getItem('pln_anim') === '0') {
                var s = document.createElement('style');
                s.textContent = '*:not(.loading-dot),*:not(.loading-dot)::before,*:not(.loading-dot)::after{animation:none!important;transition:none!important}';
                document.head.appendChild(s);
            }
        })();
        </script>

        <style>
            html.light-mode-pre body          { background: #f0f2f5 !important; color: #1e293b !important; }
            html.light-mode-pre .navbar       { background: #ffffff !important; border-color: #e2e8f0 !important; color: #1e293b !important; }
            html.light-mode-pre .dash-sidebar { background: #1e293b !important; border-color: #334155 !important; }
            html.light-mode-pre .dash-header  { background: #ffffff !important; border-color: #e2e8f0 !important; color: #0f172a !important; }
            .visually-hidden {
                position: absolute; width: 1px; height: 1px; padding: 0;
                margin: -1px; overflow: hidden; clip: rect(0,0,0,0);
                white-space: nowrap; border: 0;
            }
        </style>
    </head>
    <body>

        @php $locale = app()->getLocale(); @endphp

        <nav class="navbar" role="navigation" aria-label="{{ __('app.nav.aria_label') }}">
            <div class="nav-left">
                <a href="/dashboard"
                class="nav-item {{ Request::is('dashboard') || Request::is('/') ? 'active' : '' }}"
                aria-current="{{ Request::is('dashboard') || Request::is('/') ? 'page' : 'false' }}">
                    <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                    <span class="nav-label">{{ __('app.nav.dashboard') }}</span>
                </a>
                <a href="/historis"
                class="nav-item {{ Request::is('historis*') ? 'active' : '' }}"
                aria-current="{{ Request::is('historis*') ? 'page' : 'false' }}">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                    <span class="nav-label">{{ __('app.nav.historis') }}</span>
                </a>
            </div>

            <div class="nav-right">

                {{-- Language Dropdown --}}
                <div class="lang-dropdown">
                    <button class="lang-dropdown-btn"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="{{ __('app.lang.aria_label') }}"
                            type="button">
                        <img src="{{ asset('assets/favicons/' . ($locale === 'id' ? 'indonesia' : 'usa') . '.svg') }}"
                            alt="{{ strtoupper($locale) }}" class="lang-flag">
                        <span class="lang-label">{{ strtoupper($locale) }}</span>
                        <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="lang-dropdown-content" role="menu">
                        {{-- url() bukan route() agar tidak jadi URL relatif --}}
                        <a href="{{ url('/language/id') }}"
                        class="lang-option {{ $locale === 'id' ? 'active' : '' }}"
                        role="menuitem">
                            <img src="{{ asset('assets/favicons/indonesia.svg') }}" alt="ID" class="lang-flag">
                            <span>{{ __('app.lang.indonesia') }}</span>
                        </a>
                        <a href="{{ url('/language/en') }}"
                        class="lang-option {{ $locale === 'en' ? 'active' : '' }}"
                        role="menuitem">
                            <img src="{{ asset('assets/favicons/usa.svg') }}" alt="EN" class="lang-flag">
                            <span>{{ __('app.lang.english') }}</span>
                        </a>
                    </div>
                </div>

                {{-- Theme Toggle --}}
                <button id="theme-toggle"
                        class="theme-toggle-btn"
                        aria-label="{{ __('app.theme.toggle') }}"
                        title="{{ __('app.theme.toggle') }} (Ctrl+Shift+L)"
                        type="button">
                    <i class="fa-solid fa-moon theme-icon-moon" aria-hidden="true"></i>
                    <i class="fa-solid fa-sun  theme-icon-sun"  aria-hidden="true"></i>
                </button>

                {{-- Profile Dropdown --}}
                <div class="profile-dropdown">
                    <button class="profile-btn"
                            aria-label="{{ __('app.profile.menu', ['name' => auth()->user()->name]) }}"
                            aria-haspopup="true"
                            aria-expanded="false"
                            type="button">
                        <img src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}"
                            alt="" class="nav-avatar nav-avatar-img" aria-hidden="true">
                        <span class="nav-username">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-content" role="menu">
                        <a href="{{ route('profile') }}" role="menuitem">
                            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                            {{ __('app.profile.profile') }}
                        </a>
                        <a href="{{ route('settings') }}" role="menuitem">
                            <i class="fa-solid fa-gear" aria-hidden="true"></i>
                            {{ __('app.profile.settings') }}
                        </a>
                        <hr class="dropdown-divider">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="logout-item" role="menuitem">
                                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                                {{ __('app.profile.logout') }}
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </nav>

        <main class="app-main" id="main-content" role="main">
            @yield('content')
        </main>

        @if(!Request::is('historis*') && !Request::is('profile*') && !Request::is('settings*'))
            @include('partials.widgets.chatbot')
        @endif

        <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js" defer></script>
        {{-- Register datalabels plugin secara global setelah kedua script di atas load --}}
        <script>
        window.addEventListener('load', function () {
            if (window.Chart && window.ChartDataLabels) {
                Chart.register(ChartDataLabels)
            }
        })
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

        @yield('scripts')
        @stack('scripts')

        <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Theme Toggle ──────────────────────────────────────────────────────
            const themeToggle = document.getElementById('theme-toggle');
            const htmlEl      = document.documentElement;

            function applyTheme(theme) {
                if (theme === 'light') {
                    document.body.classList.add('light-mode');
                    htmlEl.classList.add('light-mode-pre');
                    localStorage.setItem('pln_theme', 'light');
                } else {
                    document.body.classList.remove('light-mode');
                    htmlEl.classList.remove('light-mode-pre');
                    localStorage.setItem('pln_theme', 'dark');
                }
            }

            applyTheme(localStorage.getItem('pln_theme') || 'dark');

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    applyTheme(localStorage.getItem('pln_theme') === 'light' ? 'dark' : 'light');
                    this.style.transition = 'transform 0.4s cubic-bezier(0.68,-0.55,0.265,1.55)';
                    this.style.transform  = 'rotate(360deg)';
                    setTimeout(() => { this.style.transform = ''; }, 400);
                });

                document.addEventListener('keydown', function (e) {
                    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'l') {
                        e.preventDefault();
                        themeToggle.click();
                    }
                });
            }

            if (!localStorage.getItem('pln_theme')) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    applyTheme(e.matches ? 'dark' : 'light');
                });
            }

            // ── Dropdown helper ───────────────────────────────────────────────────
            function initDropdown(btnSel, contentSel) {
                const btn     = document.querySelector(btnSel);
                const content = document.querySelector(contentSel);
                if (!btn || !content) return;

                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    btn.setAttribute('aria-expanded', content.classList.toggle('show'));
                });
                document.addEventListener('click', function () {
                    content.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                });
                content.addEventListener('click', e => e.stopPropagation());
            }

            initDropdown('.profile-btn',       '.dropdown-content');
            initDropdown('.lang-dropdown-btn', '.lang-dropdown-content');

            // ── Toast Notifications ───────────────────────────────────────────────
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: 3000, timerProgressBar: true,
                didOpen: toast => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            @if(session('success'))
                Toast.fire({ icon: 'success', title: '{{ addslashes(session('success')) }}' });
            @endif
            @if(session('error'))
                Toast.fire({ icon: 'error',   title: '{{ addslashes(session('error')) }}' });
            @endif
            @if(session('warning'))
                Toast.fire({ icon: 'warning', title: '{{ addslashes(session('warning')) }}' });
            @endif
            @if(session('pending'))
                Toast.fire({ icon: 'info',    title: '{{ addslashes(session('pending')) }}' });
            @endif
        });
        </script>

        @stack('global-scripts')

    </body>
    </html>