@extends('app')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Users Management" subtitle="Kelola pengguna aplikasi">
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" id="search-input" placeholder="Search name or email..." class="input-field pl-10 sm:w-64">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button id="btn-add-user" class="hidden btn-primary" onclick="navigateTo('/admin/users/create')">
                Tambah User
            </button>
        </div>
    </x-page-header>

    {{-- Users Table --}}
    <x-data-table>
        <table class="w-full">
            <thead class="bg-[var(--bg-surface-hover)] border-b border-[var(--border-default)]">
                <tr>
                    <th class="py-3 px-4 text-left font-label-sm uppercase text-[var(--text-secondary)]">Name</th>
                    <th class="py-3 px-4 text-left font-label-sm uppercase text-[var(--text-secondary)]">Email</th>
                    <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Role</th>
                    <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Tier</th>
                    <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Status</th>
                    <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Active</th>
                    <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <tr><td colspan="7" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </x-data-table>

    {{-- Pagination --}}
    <div id="pagination-container" class="flex items-center justify-between mt-4">
        <div id="pagination-info" class="text-sm text-[var(--text-secondary)]"></div>
        <div id="pagination-buttons" class="flex gap-2"></div>
    </div>
</div>

{{-- Reset Password Modal --}}
<x-modal id="reset-password-modal" title="Reset Password" size="sm">
    <p class="text-sm text-[var(--text-secondary)] mb-4">Reset password for: <span id="reset-user-name" class="font-bold text-[var(--text-primary)]"></span></p>
    <x-form-field 
        id="new-password-input" 
        label="New Password" 
        type="password" 
        placeholder="New password (min 5 chars)" 
    />
    <div class="flex gap-3 justify-end mt-4">
        <button data-modal-close="reset-password-modal" class="btn-secondary">Cancel</button>
        <button onclick="submitResetPassword()" class="btn-primary">Reset</button>
    </div>
</x-modal>

{{-- Delete Confirmation Modal --}}
<x-modal id="delete-modal" title="Delete User" size="sm">
    <p class="text-sm text-[var(--text-secondary)] mb-4">Are you sure you want to delete: <span id="delete-user-name" class="font-bold text-[var(--text-primary)]"></span>? This action cannot be undone.</p>
    <div class="flex gap-3 justify-end">
        <button data-modal-close="delete-modal" class="btn-secondary">Cancel</button>
        <button onclick="submitDelete()" class="btn-danger">Delete</button>
    </div>
</x-modal>
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
        const cls = (role === 'SUPERADMIN' || role === 'ADMIN') ? 'badge-info' : 'badge-free';
        return `<span class="badge ${cls}">${role}</span>`;
    }

    function tierBadge(tier) {
        const cls = tier === 'PREMIUM' ? 'badge-premium' : 'badge-free';
        return `<span class="badge ${cls}">${tier}</span>`;
    }

    function statusBadge(status) {
        const cls = status === 'ACTIVE' ? 'badge-success'
            : status === 'SUSPENDED' ? 'badge-danger'
            : 'badge-warning';
        return `<span class="badge ${cls}">${status}</span>`;
    }

    function activeBadge(active) {
        return active
            ? '<span class="badge badge-success">Yes</span>'
            : '<span class="badge badge-danger">No</span>';
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
                    <tr class="border-b border-[var(--border-default)] hover:bg-[var(--bg-surface-hover)]">
                        <td class="py-4 px-4 align-middle text-left"><span class="font-medium text-[var(--text-primary)]">${escHtml(u.name)}</span></td>
                        <td class="py-4 px-4 align-middle text-left">${escHtml(u.email)}</td>
                        <td class="py-4 px-4 align-middle text-center">${roleBadge(u.role)}</td>
                        <td class="py-4 px-4 align-middle text-center">${tierBadge(u.membership_tier)}</td>
                        <td class="py-4 px-4 align-middle text-center">${statusBadge(u.membership_status)}</td>
                        <td class="py-4 px-4 align-middle text-center">${activeBadge(u.is_active)}</td>
                        <td class="py-4 px-4 align-middle">
                            <div class="flex gap-2 justify-center">
                                <button onclick="navigateTo('/admin/users/${u.id}/edit')" class="btn-ghost btn-sm text-[var(--info)]">Edit</button>
                                <button onclick="openResetModal(${u.id}, '${escHtml(u.name)}')" class="btn-ghost btn-sm text-[var(--warning)]">Reset PW</button>
                                ${window.SKB.isSuperAdmin ? `<button onclick="openDeleteModal(${u.id}, '${escHtml(u.name)}')" class="btn-ghost btn-sm text-[var(--danger)]">Delete</button>` : ''}
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-[var(--text-muted)]">No users found</td></tr>';
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
                        ? 'btn-primary px-3 py-1 text-sm'
                        : 'btn-secondary px-3 py-1 text-sm';
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
    try {
        if (window.SKB && window.SKB.isSuperAdmin) {
            document.getElementById('btn-add-user').classList.remove('hidden');
        }
    } catch(e) { console.error('Error checking admin status', e); }

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
        openModal('reset-password-modal');
    };

    window.submitResetPassword = async function() {
        const newPw = document.getElementById('new-password-input').value;
        if (newPw.length < 5) {
            showToast('Password must be at least 5 characters', 'warning');
            return;
        }
        try {
            await apiFetch('/admin/users/' + resetUserId + '/reset-password', {
                method: 'POST',
                body: JSON.stringify({ new_password: newPw }),
            });
            showToast('Password berhasil direset!', 'success');
            closeModal('reset-password-modal');
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    // Delete Modal
    window.openDeleteModal = function(userId, userName) {
        deleteUserId = userId;
        document.getElementById('delete-user-name').textContent = userName;
        openModal('delete-modal');
    };

    window.submitDelete = async function() {
        try {
            await apiFetch('/admin/users/' + deleteUserId, { method: 'DELETE' });
            showToast('Akun berhasil dihapus!', 'success');
            closeModal('delete-modal');
            loadUsers();
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    loadUsers();
})();
</script>
@endpush