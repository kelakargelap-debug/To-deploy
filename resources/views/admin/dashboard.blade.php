@extends('app')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="mb-8">
        <h2 class="text-display-lg" style="color: var(--md-on-surface);">Admin Dashboard</h2>
        <p class="text-body-lg mt-1" style="color: var(--md-on-surface-variant);">Ringkasan sistem Tryout</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8" id="stats-cards">
        <div class="stat-card">
            <span class="stat-card-label">Total Users</span>
            <div class="stat-card-value" id="stat-total-users">-</div>
        </div>
        <div class="stat-card">
            <span class="stat-card-label">Premium Users</span>
            <div class="stat-card-value" id="stat-premium-users">-</div>
        </div>
        <div class="stat-card">
            <span class="stat-card-label">Free Users</span>
            <div class="stat-card-value" id="stat-free-users">-</div>
        </div>
        <div class="stat-card">
            <span class="stat-card-label">Total Tryouts</span>
            <div class="stat-card-value" id="stat-total-tryouts">-</div>
        </div>
        <div class="stat-card">
            <span class="stat-card-label">Total Attempts</span>
            <div class="stat-card-value" id="stat-total-attempts">-</div>
        </div>
    </div>

    {{-- Tabbed Section --}}
    <div class="card p-0 mb-8 overflow-hidden">
        <div class="flex" style="border-bottom: 1px solid var(--md-outline-variant);">
            <button class="px-6 py-4 text-label-md transition-colors duration-200"
                id="tab-users" onclick="switchTab('users')"
                style="color: var(--md-primary); border-bottom: 2px solid var(--md-primary);">
                Latest Users
            </button>
            <button class="px-6 py-4 text-label-md transition-colors duration-200"
                id="tab-attempts" onclick="switchTab('attempts')"
                style="color: var(--md-on-surface-variant);">
                Latest Attempts
            </button>
        </div>

        {{-- Users Tab --}}
        <div id="content-users" class="block">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tier</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="latest-users-body">
                        <tr><td colspan="5" class="text-center" style="color: var(--md-outline);">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Attempts Tab --}}
        <div id="content-attempts" class="hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Tryout</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody id="latest-attempts-body">
                        <tr><td colspan="5" class="text-center" style="color: var(--md-outline);">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // Tab switching
    window.switchTab = function(tab) {
        var tabs = ['users', 'attempts'];
        tabs.forEach(function(t) {
            var btn = document.getElementById('tab-' + t);
            var content = document.getElementById('content-' + t);
            if (t === tab) {
                content.classList.remove('hidden');
                content.classList.add('block');
                btn.style.color = 'var(--md-primary)';
                btn.style.borderBottom = '2px solid var(--md-primary)';
            } else {
                content.classList.remove('block');
                content.classList.add('hidden');
                btn.style.color = 'var(--md-on-surface-variant)';
                btn.style.borderBottom = 'none';
            }
        });
    };

    async function loadDashboard() {
        try {
            const data = await apiFetch('/admin/stats');

            // Stats cards
            document.getElementById('stat-total-users').textContent = data.totalUsers;
            document.getElementById('stat-premium-users').textContent = data.premiumUsers;
            document.getElementById('stat-free-users').textContent = data.freeUsers;
            document.getElementById('stat-total-tryouts').textContent = data.totalTryouts;
            document.getElementById('stat-total-attempts').textContent = data.totalAttempts;

            // Latest users
            const usersBody = document.getElementById('latest-users-body');
            if (data.latestUsers && data.latestUsers.length) {
                usersBody.innerHTML = data.latestUsers.map(u => `
                    <tr>
                        <td><span class="font-medium" style="color: var(--md-on-surface);">${escHtml(u.name)}</span></td>
                        <td>${escHtml(u.email)}</td>
                        <td><span class="badge ${roleBadgeClass(u.role)}">${u.role}</span></td>
                        <td><span class="badge ${tierBadgeClass(u.membershipTier)}">${u.membershipTier}</span></td>
                        <td style="color: var(--md-on-surface-variant);">${formatDate(u.createdAt)}</td>
                    </tr>
                `).join('');
            } else {
                usersBody.innerHTML = '<tr><td colspan="5" class="text-center" style="color: var(--md-outline);">No users found</td></tr>';
            }

            // Latest attempts
            const attemptsBody = document.getElementById('latest-attempts-body');
            if (data.latestAttempts && data.latestAttempts.length) {
                attemptsBody.innerHTML = data.latestAttempts.map(a => `
                    <tr>
                        <td><span class="font-medium" style="color: var(--md-on-surface);">${escHtml(a.userName)}</span></td>
                        <td>${escHtml(a.tryoutTitle)}</td>
                        <td><span class="font-mono font-bold">${a.score ?? '-'}</span></td>
                        <td><span class="badge ${statusBadgeClass(a.status)}">${a.status}</span></td>
                        <td style="color: var(--md-on-surface-variant);">${a.submittedAt ? formatDate(a.submittedAt) : '-'}</td>
                    </tr>
                `).join('');
            } else {
                attemptsBody.innerHTML = '<tr><td colspan="5" class="text-center" style="color: var(--md-outline);">No attempts found</td></tr>';
            }
        } catch (err) {
            console.error('Dashboard load error:', err);
            document.getElementById('stats-cards').innerHTML = `
                <div class="col-span-full text-center py-8" style="color: var(--md-error);">
                    <span class="material-symbols-outlined text-3xl mb-2">error</span>
                    <p>Failed to load dashboard data: ${escHtml(err.message)}</p>
                </div>`;
        }
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function roleBadgeClass(role) {
        if (role === 'SUPERADMIN' || role === 'ADMIN') return 'badge-info';
        return 'badge-free';
    }

    function tierBadgeClass(tier) {
        if (tier === 'PREMIUM') return 'badge-premium';
        return 'badge-free';
    }

    function statusBadgeClass(status) {
        if (status === 'SUBMITTED' || status === 'COMPLETED') return 'badge-success';
        if (status === 'EXPIRED') return 'badge-danger';
        return 'badge-warning';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    loadDashboard();
})();
</script>
@endpush