@extends('app')

@section('content')
<div class="p-6 max-w-md mx-auto w-full">
    <x-page-header title="Ubah Password" subtitle="Perbarui password akun kamu" />

    <div class="card">
        <form id="change-password-form" onsubmit="handleChangePassword(event)">
            <!-- Current Password -->
            <div class="mb-4">
                <label for="current-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Saat Ini</label>
                <input type="password" id="current-password" name="current_password" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan password saat ini">
            </div>

            <!-- New Password -->
            <div class="mb-4">
                <label for="new-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                <input type="password" id="new-password" name="new_password" required minlength="5"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Minimal 5 karakter">
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirm-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                <input type="password" id="confirm-password" name="confirm_password" required minlength="5"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ulangi password baru">
            </div>

            <!-- Message Display -->
            <div id="password-message" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

            <!-- Submit Button -->
            <button type="submit" id="submit-btn"
                class="w-full btn-primary py-2 px-4 rounded-lg font-medium transition-colors">
                Simpan Password
            </button>
        </form>
    </div>
</div>

<script>
function handleChangePassword(e) {
    e.preventDefault();

    var currentPassword = document.getElementById('current-password').value;
    var newPassword = document.getElementById('new-password').value;
    var confirmPassword = document.getElementById('confirm-password').value;
    var messageEl = document.getElementById('password-message');
    var submitBtn = document.getElementById('submit-btn');

    // Validate confirmation match
    if (newPassword !== confirmPassword) {
        messageEl.classList.remove('hidden');
        messageEl.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
        messageEl.textContent = 'Password baru dan konfirmasi tidak cocok.';
        return;
    }

    // Validate minimum length
    if (newPassword.length < 5) {
        messageEl.classList.remove('hidden');
        messageEl.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
        messageEl.textContent = 'Password baru minimal 5 karakter.';
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';

    apiFetch('/user/change-password', {
        method: 'POST',
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        })
    }).then(function (result) {
        messageEl.classList.remove('hidden');
        messageEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
        messageEl.textContent = result.message || 'Password berhasil diubah!';
        document.getElementById('change-password-form').reset();
    }).catch(function (err) {
        messageEl.classList.remove('hidden');
        messageEl.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
        messageEl.textContent = err.message || 'Gagal mengubah password.';
    }).finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Password';
    });
}
</script>
@endsection