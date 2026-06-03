@extends('app')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Users Management</h1>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" id="search-input" placeholder="Search name or email..."
                    class="w-full sm:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button id="btn-add-user" class="hidden px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors"
                onclick="navigateTo('/admin/users/create')">
                Tambah User
            </button>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        <th class="px-5 py-3 text-left font-medium">Name</th>
                        <th class="px-5 py-3 text-left font-medium">Email</th>
                        <th class="px-5 py-3 text-left font-medium">Role</th>
                        <th class="px-5 py-3 text-left font-medium">Tier</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Active</th>
                        <th class="px-5 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <tr><td class="px-5 py-4 text-gray-500 dark:text-gray-400" colspan="7">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div id="pagination-container" class="flex items-center justify-between mt-4">
        <div id="pagination-info" class="text-sm text-gray-600 dark:text-gray-400"></div>
        <div id="pagination-buttons" class="flex gap-2"></div>
    </div>
</div>

{{-- Reset Password Modal --}}
<div id="reset-password-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeResetModal()"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Reset Password</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Reset password for: <span id="reset-user-name" class="font-medium text-gray-900 dark:text-gray-100"></span></p>
        <input type="password" id="new-password-input" placeholder="New password (min 5 chars)"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 text-sm mb-4 focus:ring-2 focus:ring-indigo-500">
        <div class="flex gap-3 justify-end">
            <button onclick="closeResetModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
            <button onclick="submitResetPassword()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">Reset</button>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Delete User</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Are you sure you want to delete: <span id="delete-user-name" class="font-medium text-gray-900 dark:text-gray-100"></span>? This action cannot be undone.</p>
        <div class="flex gap-3 justify-end">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
            <button onclick="submitDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let currentPage = 1;
    let searchQuery = '';
    let resetUserId = null;
    let deleteUserId = null;

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function roleBadge(role) {
        const cls = role === 'SUPERADMIN' ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
            : role === 'ADMIN' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300'
            : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
        return `<span class="px-2 py-1 rounded text-xs font-medium ${cls}">${role}</span>`;
    }

    function tierBadge(tier) {
        const cls = tier === 'PREMIUM' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300'
            : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
        return `<span class="px-2 py-1 rounded text-xs font-medium ${cls}">${tier}</span>`;
    }

    function statusBadge(status) {
        const cls = status === 'ACTIVE' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300'
            : status === 'SUSPENDED' ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300'
            : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300';
        return `<span class="px-2 py-1 rounded text-xs font-medium ${cls}">${status}</span>`;
    }

    function activeBadge(active) {
        return active
            ? '<span class="px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">Yes</span>'
            : '<span class="px-2 py-1 rounded text-xs font-medium bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300">No</span>';
    }

    async function loadUsers() {
        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="7">Loading...</td></tr>';

        try {
            let url = '/admin/users?page=' + currentPage;
            if (searchQuery) url += '&search=' + encodeURIComponent(searchQuery);
            const data = await apiFetch(url);

            if (data.data && data.data.length) {
                tbody.innerHTML = data.data.map(u => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100 font-medium">${escHtml(u.name)}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">${escHtml(u.email)}</td>
                        <td class="px-5 py-4">${roleBadge(u.role)}</td>
                        <td class="px-5 py-4">${tierBadge(u.membership_tier)}</td>
                        <td class="px-5 py-4">${statusBadge(u.membership_status)}</td>
                        <td class="px-5 py-4">${activeBadge(u.is_active)}</td>
                        <td class="px-5 py-4">
                            <div class="flex gap-2">
                                <button onclick="navigateTo('/admin/users/${u.id}/edit')" class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">Edit</button>
                                <button onclick="openResetModal(${u.id}, '${escHtml(u.name)}')" class="px-2 py-1 text-xs bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 rounded hover:bg-amber-200 dark:hover:bg-amber-800 transition-colors">Reset PW</button>
                                ${window.SKB.isSuperAdmin ? `<button onclick="openDeleteModal(${u.id}, '${escHtml(u.name)}')" class="px-2 py-1 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800 transition-colors">Delete</button>` : ''}
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="7">No users found</td></tr>';
            }

            // Pagination
            const pinfo = document.getElementById('pagination-info');
            const pbuttons = document.getElementById('pagination-buttons');
            if (data.total) {
                const from = (data.current_page - 1) * data.per_page + 1;
                const to = Math.min(data.current_page * data.per_page, data.total);
                pinfo.textContent = `Showing ${from}-${to} of ${data.total} users`;

                pbuttons.innerHTML = '';
                const totalPages = data.last_page;
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = i === data.current_page
                        ? 'px-3 py-1 bg-indigo-600 text-white rounded text-sm font-medium'
                        : 'px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors';
                    btn.onclick = () => { currentPage = i; loadUsers(); };
                    pbuttons.appendChild(btn);
                }
            } else {
                pinfo.textContent = '';
                pbuttons.innerHTML = '';
            }
        } catch (err) {
            console.error('Load users error:', err);
            tbody.innerHTML = `<tr><td class="px-5 py-4 text-red-500" colspan="7">Error: ${escHtml(err.message)}</td></tr>`;
        }
    }

    // Show add button only for SUPERADMIN
    if (window.SKB.isSuperAdmin) {
        document.getElementById('btn-add-user').classList.remove('hidden');
    }

    // Search
    const searchInput = document.getElementById('search-input');
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = searchInput.value.trim();
            currentPage = 1;
            loadUsers();
        }, 300);
    });

    // Reset Password Modal
    window.openResetModal = function(userId, userName) {
        resetUserId = userId;
        document.getElementById('reset-user-name').textContent = userName;
        document.getElementById('new-password-input').value = '';
        document.getElementById('reset-password-modal').classList.remove('hidden');
    };

    window.closeResetModal = function() {
        document.getElementById('reset-password-modal').classList.add('hidden');
        resetUserId = null;
    };

    window.submitResetPassword = async function() {
        const newPw = document.getElementById('new-password-input').value;
        if (newPw.length < 5) {
            alert('Password must be at least 5 characters');
            return;
        }
        try {
            await apiFetch('/admin/users/' + resetUserId + '/reset-password', {
                method: 'POST',
                body: JSON.stringify({ new_password: newPw }),
            });
            alert('Password berhasil direset!');
            closeResetModal();
        } catch (err) {
            alert('Error: ' + err.message);
        }
    };

    // Delete Modal
    window.openDeleteModal = function(userId, userName) {
        deleteUserId = userId;
        document.getElementById('delete-user-name').textContent = userName;
        document.getElementById('delete-modal').classList.remove('hidden');
    };

    window.closeDeleteModal = function() {
        document.getElementById('delete-modal').classList.add('hidden');
        deleteUserId = null;
    };

    window.submitDelete = async function() {
        try {
            await apiFetch('/admin/users/' + deleteUserId, { method: 'DELETE' });
            alert('Akun berhasil dihapus!');
            closeDeleteModal();
            loadUsers();
        } catch (err) {
            alert('Error: ' + err.message);
        }
    };

    loadUsers();
})();
</script>
@endpush