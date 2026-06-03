@extends('app')

@section('content')
    <div class="p-6 max-w-3xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <button onclick="navigateTo('/admin/users')"
                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                &larr; Back
            </button>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit User</h1>
        </div>

        <div id="edit-form-container"
            class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
            <div id="loading-message" class="text-gray-500 dark:text-gray-400 text-center py-8">Loading user data...</div>
            <form id="edit-user-form" class="hidden space-y-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input type="text" id="input-name"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Role --}}
                <div id="role-field">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select id="input-role"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="SUPERADMIN">SUPERADMIN</option>
                    </select>
                </div>

                {{-- Tier --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Membership Tier</label>
                    <select id="input-tier"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="FREE">FREE</option>
                        <option value="PREMIUM">PREMIUM</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Membership Status</label>
                    <select id="input-status"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="EXPIRED">EXPIRED</option>
                        <option value="SUSPENDED">SUSPENDED</option>
                    </select>
                </div>

                {{-- Expiry Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Membership Expiry</label>
                    <input type="date" id="input-expiry"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty for no expiry</p>
                </div>

                {{-- Active Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                    <button id="toggle-active" type="button" onclick="toggleActiveState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-gray-300 dark:bg-gray-600">
                        <span id="toggle-active-dot"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-0"></span>
                    </button>
                    <span id="active-label" class="text-sm text-gray-600 dark:text-gray-400">No</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">Save
                        Changes</button>
                    <button type="button" onclick="navigateTo('/admin/users')"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const userId = {!! isset($id) ? $id : 'null' !!};
            let isActive = false;
            let userRole = '';

            async function loadUser() {
                if (!userId) {
                    document.getElementById('loading-message').textContent = 'User ID not found in URL';
                    return;
                }

                try {
                    const user = await apiFetch('/admin/users/' + userId);
                    userRole = user.role;

                    // Check if ADMIN cannot edit SUPERADMIN
                    if (user.role === 'SUPERADMIN' && !window.SKB.isSuperAdmin) {
                        document.getElementById('loading-message').textContent = 'You cannot edit a Super Admin account. Only Super Admins can do this.';
                        document.getElementById('loading-message').classList.add('text-red-500');
                        return;
                    }

                    // Fill form
                    document.getElementById('input-name').value = user.name || '';
                    document.getElementById('input-role').value = user.role || 'USER';
                    document.getElementById('input-tier').value = user.membership_tier || 'FREE';
                    document.getElementById('input-status').value = user.membership_status || 'ACTIVE';

                    if (user.membership_expiry) {
                        const d = new Date(user.membership_expiry);
                        document.getElementById('input-expiry').value = d.toISOString().split('T')[0];
                    }

                    isActive = user.is_active;
                    updateActiveToggle();

                    // Hide role dropdown for non-SUPERADMIN
                    if (!window.SKB.isSuperAdmin) {
                        document.getElementById('role-field').classList.add('hidden');
                    }

                    // Show form, hide loading
                    document.getElementById('loading-message').classList.add('hidden');
                    document.getElementById('edit-user-form').classList.remove('hidden');
                } catch (err) {
                    document.getElementById('loading-message').textContent = 'Error: ' + err.message;
                    document.getElementById('loading-message').classList.add('text-red-500');
                }
            }

            function toggleActiveState() {
                isActive = !isActive;
                updateActiveToggle();
            }

            window.toggleActiveState = toggleActiveState;

            function updateActiveToggle() {
                const toggle = document.getElementById('toggle-active');
                const dot = document.getElementById('toggle-active-dot');
                const label = document.getElementById('active-label');

                if (isActive) {
                    toggle.classList.remove('bg-gray-300', 'dark:bg-gray-600');
                    toggle.classList.add('bg-indigo-600');
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes';
                } else {
                    toggle.classList.remove('bg-indigo-600');
                    toggle.classList.add('bg-gray-300', 'dark:bg-gray-600');
                    dot.classList.remove('translate-x-5');
                    dot.classList.add('translate-x-0');
                    label.textContent = 'No';
                }
            }

            document.getElementById('edit-user-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const payload = {
                    name: document.getElementById('input-name').value,
                    membership_tier: document.getElementById('input-tier').value,
                    membership_status: document.getElementById('input-status').value,
                    is_active: isActive,
                };

                const expiry = document.getElementById('input-expiry').value;
                if (expiry) {
                    payload.membership_expiry = expiry;
                } else {
                    payload.membership_expiry = null;
                }

                // Only SUPERADMIN can change role
                if (window.SKB.isSuperAdmin) {
                    payload.role = document.getElementById('input-role').value;
                }

                try {
                    await apiFetch('/admin/users/' + userId, {
                        method: 'PATCH',
                        body: JSON.stringify(payload),
                    });
                    alert('User berhasil diupdate!');
                    navigateTo('/admin/users');
                } catch (err) {
                    alert('Error: ' + err.message);
                }
            });

            loadUser();
        })();
    </script>
@endpush