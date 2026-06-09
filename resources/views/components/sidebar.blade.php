@auth
    <aside id="sidebar"
        class="fixed left-0 top-0 h-full z-30 transition-all duration-300 ease-in-out flex flex-col overflow-hidden @if(session('sidebar_collapsed', false)) sidebar-collapsed @else sidebar-expanded @endif"
        style="background: var(--md-surface-container-low); border-right: 1px solid var(--md-outline-variant);">

        {{-- Logo area --}}
        <div class="flex items-center gap-3 px-4 h-16 border-b shrink-0" style="border-color: var(--md-outline-variant);">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                style="background: var(--md-primary); color: var(--md-on-primary); font-size: 0.8rem; font-weight: 700;">
                S
            </div>
            <div class="sidebar-text">
                <h2 class="text-headline-sm font-bold" style="color: var(--md-primary); white-space: nowrap;">SKB Tryout</h2>
                <p class="text-label-sm" style="color: var(--md-outline); white-space: nowrap;">Examination Portal</p>
            </div>
        </div>

        {{-- User info --}}
        <div class="px-4 py-3 border-b shrink-0" style="border-color: var(--md-outline-variant);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 text-sm font-semibold"
                    style="background: var(--md-surface-variant); color: var(--md-primary);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 sidebar-text">
                    <p class="text-label-md font-medium truncate" style="color: var(--md-on-surface);">{{ Auth::user()->name }}</p>
                    <p class="text-label-sm truncate" style="color: var(--md-on-surface-variant);">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-3 px-2">
            <p class="text-label-sm uppercase tracking-wider px-3 py-1.5 sidebar-text"
                style="color: var(--md-outline);">Menu</p>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('dashboard')) data-weight="fill" @endif>dashboard</span>
                <span class="sidebar-text text-label-md">Dashboard</span>
            </a>

            <a href="{{ route('tryouts') }}"
                class="nav-link {{ request()->routeIs('tryout*') && !request()->routeIs('tryout-result') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('tryout*') && !request()->routeIs('tryout-result')) data-weight="fill" @endif>quiz</span>
                <span class="sidebar-text text-label-md">Tryout</span>
            </a>

            <a href="{{ route('materials') }}" class="nav-link {{ request()->routeIs('material*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('material*')) data-weight="fill" @endif>book</span>
                <span class="sidebar-text text-label-md">Materi</span>
            </a>

            <a href="{{ route('my-attempts') }}" class="nav-link {{ request()->routeIs('my-attempts') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('my-attempts')) data-weight="fill" @endif>analytics</span>
                <span class="sidebar-text text-label-md">Nilai Saya</span>
            </a>

            <a href="{{ route('change-password') }}"
                class="nav-link {{ request()->routeIs('change-password') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('change-password')) data-weight="fill" @endif>lock</span>
                <span class="sidebar-text text-label-md">Ubah Password</span>
            </a>

            <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('profile')) data-weight="fill" @endif>person</span>
                <span class="sidebar-text text-label-md">Profil</span>
            </a>

            @if(Auth::user()->isAdmin())
                <p class="text-label-sm uppercase tracking-wider px-3 py-1.5 mt-3 sidebar-text"
                    style="color: var(--md-outline);">Admin</p>

                <a href="{{ route('admin-dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin-dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('admin-dashboard')) data-weight="fill" @endif>admin_panel_settings</span>
                    <span class="sidebar-text text-label-md">Dashboard Admin</span>
                </a>

                <a href="{{ route('admin-users') }}" class="nav-link {{ request()->routeIs('admin-user*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('admin-user*')) data-weight="fill" @endif>group</span>
                    <span class="sidebar-text text-label-md">Kelola User</span>
                </a>

                <a href="{{ route('admin-categories') }}"
                    class="nav-link {{ request()->routeIs('admin-categories') ? 'active' : '' }}">
                    <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('admin-categories')) data-weight="fill" @endif>category</span>
                    <span class="sidebar-text text-label-md">Kelola Kategori</span>
                </a>

                <a href="{{ route('admin-tryouts') }}"
                    class="nav-link {{ request()->routeIs('admin-tryout*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('admin-tryout*')) data-weight="fill" @endif>assignment</span>
                    <span class="sidebar-text text-label-md">Kelola Tryout</span>
                </a>

                <a href="{{ route('admin-materials') }}"
                    class="nav-link {{ request()->routeIs('admin-material*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined text-xl shrink-0" @if(request()->routeIs('admin-material*')) data-weight="fill" @endif>history_edu</span>
                    <span class="sidebar-text text-label-md">Kelola Materi</span>
                </a>
            @endif
        </nav>

        {{-- Footer: collapse toggle --}}
        <div class="border-t p-3 shrink-0" style="border-color: var(--md-outline-variant);">
            <button onclick="toggleSidebar()"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-label-md font-medium transition-colors duration-200 hover:bg-[var(--md-surface-container-high)]"
                style="color: var(--md-on-surface-variant);">
                <span class="material-symbols-outlined text-xl shrink-0 transition-transform duration-300 @if(session('sidebar_collapsed', false)) rotate-180 @endif">chevron_left</span>
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
            padding: 0.625rem;
        }

        .sidebar-collapsed .flex-1 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
    </style>
@endauth