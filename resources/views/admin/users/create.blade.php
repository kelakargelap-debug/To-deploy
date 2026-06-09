@extends('app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div id="access-denied" class="hidden text-center py-12">
        <p class="text-[var(--danger)] text-lg font-medium">Access Denied</p>
        <p class="text-[var(--text-secondary)] mt-2">Only Super Admin can create new users.</p>
        <button onclick="navigateTo('/admin/users')" class="btn-secondary mt-4">Back to Users</button>
    </div>

    <div id="create-form-wrapper">
        <div class="mb-6">
            <button onclick="navigateTo('/admin/users')" class="btn-ghost mb-4">
                &larr; Back to Users
            </button>
            <h1 class="text-2xl font-bold text-[var(--text-primary)]">Create User</h1>
        </div>

        <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-6">
            <form id="create-user-form" class="space-y-5">
                <x-form-field 
                    id="input-name" 
                    label="Name" 
                    type="text" 
                    required="true" 
                />

                <x-form-field 
                    id="input-email" 
                    label="Email" 
                    type="email" 
                    required="true" 
                />

                <x-form-field 
                    id="input-password" 
                    label="Password" 
                    type="password" 
                    required="true" 
                    helpText="Minimum 5 characters"
                />

                {{-- Role --}}
                <div>
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

                {{-- Expiry --}}
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
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" style="background-color: var(--accent);">
                        <span id="toggle-active-dot" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-5"></span>
                    </button>
                    <span id="active-label" class="text-sm text-[var(--text-secondary)]">Yes</span>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="submit" class="btn-primary">Create User</button>
                    <button type="button" onclick="navigateTo('/admin/users')" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let isActive = true;

    // SUPERADMIN only
    if (!window.SKB.isSuperAdmin) {
        document.getElementById('create-form-wrapper').classList.add('hidden');
        document.getElementById('access-denied').classList.remove('hidden');
        return;
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

    document.getElementById('create-user-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = {
            name: document.getElementById('input-name').value,
            email: document.getElementById('input-email').value,
            password: document.getElementById('input-password').value,
            role: document.getElementById('input-role').value,
            membership_tier: document.getElementById('input-tier').value,
            membership_status: document.getElementById('input-status').value,
        };

        const expiry = document.getElementById('input-expiry').value;
        if (expiry) {
            payload.membership_expiry = expiry;
        }

        try {
            await apiFetch('/admin/users', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            showToast('User berhasil dibuat!', 'success');
            navigateTo('/admin/users');
        } catch (err) {
            showToast(err.message, 'error');
        }
    });
})();
</script>
@endpush