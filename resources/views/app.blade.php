<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="@if(session('dark_mode', false)) dark @else light @endif" @if(session('dark_mode', false)) data-theme="dark"
    @endif>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKB Tryout — Platform Tryout Online</title>
    <!-- Google Fonts: Manrope -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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

        // ---- Navigate With Progress ----
        function navigateTo(url) {
            window.location.href = url;
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

        // ---- Modals ----
        window.openModal = function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        };

        window.closeModal = function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        };

        // data-modal-close attribute handler
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-modal-close]');
            if (btn) {
                var modalId = btn.getAttribute('data-modal-close');
                closeModal(modalId);
            }
        });
    </script>
</head>

<body class="font-sans antialiased min-h-screen" style="background: var(--md-background); color: var(--md-on-surface);">
    <div id="app">
        {{-- SIDEBAR — fixed left --}}
        <x-sidebar />

        {{-- MAIN AREA — offset by sidebar width --}}
        <div id="main-area"
            class="flex flex-col min-h-screen transition-all duration-200 @if(session('sidebar_collapsed', false)) main-collapsed @else main-expanded @endif">

            {{-- TOP HEADER BAR --}}
            @auth
                <header class="top-header flex items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        {{-- Mobile hamburger --}}
                        <button id="mobile-menu-btn"
                            class="md:hidden p-2 rounded-full hover:bg-[var(--md-surface-container-high)] text-[var(--md-on-surface-variant)] transition-colors duration-200"
                            onclick="toggleMobileSidebar()">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                        <h1 class="text-headline-sm font-bold" style="color: var(--md-primary);">SKB Tryout</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- User name + role --}}
                        <span class="text-label-md hidden sm:flex items-center gap-2" style="color: var(--md-on-surface);">
                            {{ Auth::user()->name }}
                            <span class="px-2 py-0.5 rounded text-label-sm font-semibold
                                @if(Auth::user()->role === 'SUPERADMIN') bg-[var(--md-error-container)] text-[var(--md-on-error-container)]
                                @elseif(Auth::user()->role === 'ADMIN') bg-[var(--md-primary-fixed)] text-[var(--md-on-primary-fixed-variant)]
                                @else bg-[var(--md-surface-container)] text-[var(--md-on-surface-variant)]
                                @endif">
                                {{ Auth::user()->role }}
                            </span>
                        </span>

                        {{-- Dark mode toggle --}}
                        <button onclick="toggleDarkMode()"
                            class="p-2 rounded-full hover:bg-[var(--md-surface-container-high)] transition-colors duration-200"
                            style="color: var(--md-on-surface-variant);" title="Mode">
                            <span class="material-symbols-outlined">
                                @if(session('dark_mode', false)) light_mode @else dark_mode @endif
                            </span>
                        </button>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="p-2 rounded-full hover:bg-[var(--md-error-container)] transition-colors duration-200"
                                style="color: var(--md-on-surface-variant);"
                                title="Keluar">
                                <span class="material-symbols-outlined">logout</span>
                            </button>
                        </form>
                    </div>
                </header>
            @endauth

            {{-- MAIN CONTENT --}}
            <main id="main-content" class="flex-1 @auth p-4 sm:p-6 @endauth animate-fade-in-up" style="background: var(--md-background);">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Toast Notification Container --}}
    <x-toast />

    {{-- Mobile sidebar overlay --}}
    <div id="mobile-sidebar-overlay" class="hidden fixed inset-0 z-50 md:hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeMobileSidebar()"></div>
        <div id="mobile-sidebar-drawer" class="relative w-64 h-full overflow-y-auto" style="background: var(--md-surface-container-low);">
            {{-- Cloned sidebar content injected via JS --}}
        </div>
    </div>

    @stack('scripts')
</body>

</html>