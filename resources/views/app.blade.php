<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="@if(session('dark_mode', false)) dark @else light @endif" @if(session('dark_mode', false)) data-theme="dark"
    @endif>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKB Tryout — Platform Tryout Online</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=geist-mono:400,500" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.SKB = {
            @auth
                user: {!! Auth::user()->toJson() !!},
                isAdmin: {{ Auth::user()->isAdmin() ? 'true' : 'false' }},
                isSuperAdmin: {{ Auth::user()->isSuperAdmin() ? 'true' : 'false' }},
            @endauth
            @guest
                user: null,
                isAdmin: false,
                isSuperAdmin: false,
            @endguest
            darkMode: {{ session('dark_mode', false) ? 'true' : 'false' }},
            sidebarCollapsed: {{ session('sidebar_collapsed', false) ? 'true' : 'false' }},
        };

        // ---- apiFetch with CSRF session auth ----
        async function apiFetch(url, options = {}) {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                ...options.headers,
            };
            var fullUrl = url.startsWith('/api') ? url : '/api' + url;
            var response = await fetch(fullUrl, { ...options, headers });

            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                throw new Error('Unauthorized');
            }
            if (!response.ok) {
                var err = await response.json().catch(function () { return { message: 'Request failed' }; });
                throw new Error(err.message || 'Request failed');
            }
            return response.json();
        }

        // ---- Dark mode toggle ----
        async function toggleDarkMode() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("toggle-dark-mode") }}';
            form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + '">';
            document.body.appendChild(form);
            form.submit();
        }

        // ---- Sidebar toggle ----
        async function toggleSidebar() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("toggle-sidebar") }}';
            form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + '">';
            document.body.appendChild(form);
            form.submit();
        }

        // ---- Mobile sidebar ----
        function toggleMobileSidebar() {
            var overlay = document.getElementById('mobile-sidebar-overlay');
            var drawer = document.getElementById('mobile-sidebar-drawer');
            // Clone sidebar content
            var sidebar = document.getElementById('sidebar');
            if (sidebar && !drawer.querySelector('.nav-link')) {
                drawer.innerHTML = sidebar.innerHTML;
            }
            overlay.classList.toggle('hidden');
        }

        function closeMobileSidebar() {
            document.getElementById('mobile-sidebar-overlay').classList.add('hidden');
        }
    </script>
</head>

<body class="font-sans antialiased min-h-screen" style="background: var(--bg-canvas); color: var(--text-primary);">
    <div id="app">
        {{-- SIDEBAR — fixed left --}}
        <x-sidebar />

        {{-- MAIN AREA — offset by sidebar width --}}
        <div id="main-area"
            class="flex flex-col min-h-screen transition-all duration-200 @if(session('sidebar_collapsed', false)) main-collapsed @else main-expanded @endif">

            {{-- TOP HEADER BAR --}}
            @auth
                <header class="top-header flex items-center justify-between px-5">
                    <div class="flex items-center gap-3">
                        {{-- Mobile hamburger --}}
                        <button id="mobile-menu-btn"
                            class="md:hidden p-1 rounded-md hover:bg-[var(--bg-subtle)] text-[var(--text-secondary)]"
                            onclick="toggleMobileSidebar()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                        </button>
                        <span class="text-sm font-medium text-[var(--text-primary)]">SKB Tryout</span>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- User name + role --}}
                        <span class="text-[13px] text-[var(--text-secondary)] hidden sm:block">
                            {{ Auth::user()->name }}
                            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[11px] font-medium
                                @if(Auth::user()->role === 'SUPERADMIN') bg-[var(--danger-subtle)] text-[var(--danger)]
                                @elseif(Auth::user()->role === 'ADMIN') bg-[var(--accent-subtle)] text-[var(--accent)]
                                @else bg-[var(--bg-subtle)] text-[var(--text-secondary)]
                                @endif">
                                {{ Auth::user()->role }}
                            </span>
                        </span>

                        {{-- Dark mode toggle --}}
                        <button onclick="toggleDarkMode()"
                            class="p-1.5 rounded-md hover:bg-[var(--bg-subtle)] text-[var(--text-secondary)]" title="Mode">
                            <svg id="dark-mode-icon-sun"
                                class="w-[18px] h-[18px] @if(!session('dark_mode', false)) hidden @endif"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="5" />
                                <line x1="12" y1="1" x2="12" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="23" />
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                                <line x1="1" y1="12" x2="3" y2="12" />
                                <line x1="21" y1="12" x2="23" y2="12" />
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                            </svg>
                            <svg id="dark-mode-icon-moon"
                                class="w-[18px] h-[18px] @if(session('dark_mode', false)) hidden @endif" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                        </button>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="p-1.5 rounded-md hover:bg-[var(--danger-subtle)] text-[var(--text-secondary)] hover:text-[var(--danger)]"
                                title="Keluar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </header>
            @endauth

            {{-- MAIN CONTENT --}}
            <main id="main-content" class="flex-1 @auth p-6 sm:p-8 @endauth">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div id="mobile-sidebar-overlay" class="hidden fixed inset-0 z-50 md:hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeMobileSidebar()"></div>
        <div id="mobile-sidebar-drawer" class="relative w-64 bg-[var(--bg-surface)] h-full overflow-y-auto">
            {{-- Cloned sidebar content injected via JS --}}
        </div>
    </div>

    @stack('scripts')
</body>

</html>