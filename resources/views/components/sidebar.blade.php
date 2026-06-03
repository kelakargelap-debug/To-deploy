@auth
    <aside id="sidebar"
        class="fixed left-0 top-0 h-full z-30 transition-all duration-200 flex flex-col overflow-hidden @if(session('sidebar_collapsed', false)) sidebar-collapsed @else sidebar-expanded @endif"
        style="background: var(--bg-surface); border-right: 1px solid var(--border-subtle);">

        {{-- Logo area --}}
        <div class="flex items-center gap-3 px-4 h-11 border-b shrink-0" style="border-color: var(--border-subtle);">
            <div class="w-7 h-7 rounded-md flex items-center justify-center shrink-0"
                style="background: var(--accent); color: white; font-size: 0.8rem; font-weight: 600;">
                S
            </div>
            <span class="text-sm font-medium sidebar-text" style="color: var(--text-primary); white-space: nowrap;">SKB
                Tryout</span>
        </div>

        {{-- User info (collapsed: hidden) --}}
        <div class="px-4 py-3 border-b shrink-0 sidebar-text" style="border-color: var(--border-subtle);">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs font-semibold"
                    style="background: var(--accent-subtle); color: var(--accent);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-medium truncate" style="color: var(--text-primary);">{{ Auth::user()->name }}
                    </p>
                    <p class="text-[11px] truncate" style="color: var(--text-muted);">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-2 px-3">
            <p class="text-[10px] font-semibold uppercase tracking-wider px-3 py-1.5 sidebar-text"
                style="color: var(--text-muted);">Menu</p>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                </svg>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <a href="{{ route('tryouts') }}"
                class="nav-link {{ request()->routeIs('tryout*') && !request()->routeIs('tryout-result') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2" />
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                </svg>
                <span class="sidebar-text">Tryout</span>
            </a>

            <a href="{{ route('materials') }}" class="nav-link {{ request()->routeIs('material*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h8a4 4 0 014 4v14" />
                    <path d="M22 3H14a4 4 0 00-4 4v14" />
                </svg>
                <span class="sidebar-text">Materi</span>
            </a>

            <a href="{{ route('my-attempts') }}" class="nav-link {{ request()->routeIs('my-attempts') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                    <rect x="9" y="3" width="6" height="4" rx="1" />
                    <path d="M9 14l2 2 4-4" />
                </svg>
                <span class="sidebar-text">Nilai Saya</span>
            </a>

            <a href="{{ route('change-password') }}"
                class="nav-link {{ request()->routeIs('change-password') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
                <span class="sidebar-text">Ubah Password</span>
            </a>

            <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span class="sidebar-text">Profil</span>
            </a>

            @if(Auth::user()->isAdmin())
                <p class="text-[10px] font-semibold uppercase tracking-wider px-3 py-1.5 mt-2 sidebar-text"
                    style="color: var(--text-muted);">Admin</p>

                <a href="{{ route('admin-dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin-dashboard') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    <span class="sidebar-text">Dashboard Admin</span>
                </a>

                <a href="{{ route('admin-users') }}" class="nav-link {{ request()->routeIs('admin-user*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 00-3-3.87" />
                        <path d="M16 3.13a4 4 0 010 7.75" />
                    </svg>
                    <span class="sidebar-text">Kelola User</span>
                </a>

                <a href="{{ route('admin-categories') }}"
                    class="nav-link {{ request()->routeIs('admin-categories') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" />
                    </svg>
                    <span class="sidebar-text">Kelola Kategori</span>
                </a>

                <a href="{{ route('admin-tryouts') }}"
                    class="nav-link {{ request()->routeIs('admin-tryout*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2" />
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                    </svg>
                    <span class="sidebar-text">Kelola Tryout</span>
                </a>

                <a href="{{ route('admin-materials') }}"
                    class="nav-link {{ request()->routeIs('admin-material*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h8a4 4 0 014 4v14" />
                        <path d="M22 3H14a4 4 0 00-4 4v14" />
                    </svg>
                    <span class="sidebar-text">Kelola Materi</span>
                </a>
            @endif
        </nav>

        {{-- Footer: collapse toggle + logout --}}
        <div class="border-t p-3 shrink-0" style="border-color: var(--border-subtle);">
            <button onclick="toggleSidebar()"
                class="w-full flex items-center gap-3 px-2 py-1.5 rounded-md text-[13px] font-medium transition-colors"
                style="color: var(--text-secondary);">
                <svg class="w-[18px] h-[18px] shrink-0 transition-transform duration-200 @if(session('sidebar_collapsed', false)) rotate-180 @endif"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                <span class="sidebar-text">Tutup Sidebar</span>
            </button>
        </div>
    </aside>

    <style>
        .sidebar-collapsed .sidebar-text {
            display: none !important;
        }

        .sidebar-collapsed .nav-link {
            justify-content: center;
            padding: 0.5rem;
        }

        .sidebar-collapsed .flex-1 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
    </style>
@endauth