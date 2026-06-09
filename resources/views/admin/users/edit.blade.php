@extends('app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <button onclick="navigateTo('/admin/users')" class="btn-ghost mb-4">
                &larr; Back to Users
            </button>
            <h1 class="text-2xl font-bold text-[var(--text-primary)]">Edit User</h1>
        </div>

        <div id="edit-form-container" class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-6">
            <div id="loading-message" class="text-[var(--text-secondary)] text-center py-8">Loading user data...</div>
            <form id="edit-user-form" class="hidden space-y-5">
                <x-form-field 
                    id="input-name" 
                    label="Name" 
                    type="text" 
                />

                {{-- Role --}}
                <div id="role-field">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Role</label>
                    <select id="input-role" class="input-field">
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="SUPERADMIN">SUPERADMIN</option>
                    </select>
                </div>

                {{-- Tier --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Membership Tier</label>
                    <select id="input-tier" class="input-field">
                        <option value="FREE">FREE</option>
                        <option value="PREMIUM">PREMIUM</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Membership Status</label>
                    <select id="input-status" class="input-field">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="EXPIRED">EXPIRED</option>
                        <option value="SUSPENDED">SUSPENDED</option>
                    </select>
                </div>

                {{-- Expiry Date --}}
                <x-form-field 
                    id="input-expiry" 
                    label="Membership Expiry" 
                    type="date" 
                    helpText="Leave empty for no expiry"
                />

                {{-- Active Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-[var(--text-secondary)]">Active</label>
                    <button id="toggle-active" type="button" onclick="toggleActiveState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" style="background-color: var(--text-muted);">
                        <span id="toggle-active-dot"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-0"></span>
                    </button>
                    <span id="active-label" class="text-sm text-[var(--text-secondary)]">No</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <button type="button" onclick="navigateTo('/admin/users')" class="btn-secondary">Cancel</button>
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
                        document.getElementById('loading-message').classList.add('text-[var(--danger)]');
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
                    document.getElementById('loading-message').classList.add('text-[var(--danger)]');
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
                    toggle.style.backgroundColor = 'var(--accent)';
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes';
                } else {
                    toggle.style.backgroundColor = 'var(--text-muted)';
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
                    showToast('User berhasil diupdate!', 'success');
                    navigateTo('/admin/users');
                } catch (err) {
                    showToast(err.message, 'error');
                }
            });

            loadUser();
        })();
    </script>
@endpush