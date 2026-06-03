@extends('app')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Admin Dashboard</h1>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8" id="stats-cards">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1" id="stat-total-users">-</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Premium Users</div>
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1" id="stat-premium-users">-</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Free Users</div>
            <div class="text-3xl font-bold text-gray-600 dark:text-gray-300 mt-1" id="stat-free-users">-</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tryouts</div>
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1" id="stat-total-tryouts">-</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Attempts</div>
            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1" id="stat-total-attempts">-</div>
        </div>
    </div>

    {{-- Latest Users --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest Users</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        <th class="px-5 py-3 text-left font-medium">Name</th>
                        <th class="px-5 py-3 text-left font-medium">Email</th>
                        <th class="px-5 py-3 text-left font-medium">Role</th>
                        <th class="px-5 py-3 text-left font-medium">Tier</th>
                        <th class="px-5 py-3 text-left font-medium">Created</th>
                    </tr>
                </thead>
                <tbody id="latest-users-body" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <tr><td class="px-5 py-4 text-gray-500 dark:text-gray-400" colspan="5">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Latest Attempts --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest Attempts</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        <th class="px-5 py-3 text-left font-medium">User</th>
                        <th class="px-5 py-3 text-left font-medium">Tryout</th>
                        <th class="px-5 py-3 text-left font-medium">Score</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Submitted</th>
                    </tr>
                </thead>
                <tbody id="latest-attempts-body" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <tr><td class="px-5 py-4 text-gray-500 dark:text-gray-400" colspan="5">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
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
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100">${escHtml(u.name)}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">${escHtml(u.email)}</td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium ${roleBadgeClass(u.role)}">${u.role}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium ${tierBadgeClass(u.membershipTier)}">${u.membershipTier}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400">${formatDate(u.createdAt)}</td>
                    </tr>
                `).join('');
            } else {
                usersBody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="5">No users found</td></tr>';
            }

            // Latest attempts
            const attemptsBody = document.getElementById('latest-attempts-body');
            if (data.latestAttempts && data.latestAttempts.length) {
                attemptsBody.innerHTML = data.latestAttempts.map(a => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100">${escHtml(a.userName)}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">${escHtml(a.tryoutTitle)}</td>
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100 font-semibold">${a.score ?? '-'}</td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium ${statusBadgeClass(a.status)}">${a.status}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400">${a.submittedAt ? formatDate(a.submittedAt) : '-'}</td>
                    </tr>
                `).join('');
            } else {
                attemptsBody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="5">No attempts found</td></tr>';
            }
        } catch (err) {
            console.error('Dashboard load error:', err);
            document.getElementById('stats-cards').innerHTML = `
                <div class="col-span-full text-center py-8 text-red-500 dark:text-red-400">
                    Failed to load dashboard data: ${escHtml(err.message)}
                </div>`;
        }
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function roleBadgeClass(role) {
        if (role === 'SUPERADMIN') return 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300';
        if (role === 'ADMIN') return 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300';
        return 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
    }

    function tierBadgeClass(tier) {
        if (tier === 'PREMIUM') return 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300';
        return 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
    }

    function statusBadgeClass(status) {
        if (status === 'SUBMITTED') return 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300';
        if (status === 'EXPIRED') return 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300';
        return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300';
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