@extends('app')

@section('content')
<div class="max-w-md mx-auto w-full">
    <x-page-header title="Ubah Password" subtitle="Perbarui password akun kamu" />

    <div class="card animate-fade-in-up">
        <form id="change-password-form" onsubmit="handleChangePassword(event)">
            <!-- Current Password -->
            <x-form-field 
                id="current_password" 
                label="Password Saat Ini" 
                type="password" 
                placeholder="Masukkan password saat ini" 
                required="true" 
            />

            <!-- New Password -->
            <x-form-field 
                id="new_password" 
                label="Password Baru" 
                type="password" 
                placeholder="Minimal 5 karakter" 
                required="true" 
                minlength="5"
            />

            <!-- Confirm Password -->
            <x-form-field 
                id="confirm_password" 
                label="Konfirmasi Password Baru" 
                type="password" 
                placeholder="Ulangi password baru" 
                required="true" 
                minlength="5"
            />

            <!-- Message Display -->
            <div id="password-message" class="hidden mb-6"></div>

            <!-- Submit Button -->
            <button type="submit" id="submit-btn" class="w-full btn-primary">
                Simpan Password
            </button>
        </form>
    </div>
</div>

<script>
function handleChangePassword(e) {
    e.preventDefault();

    var currentPassword = document.getElementById('current_password').value;
    var newPassword = document.getElementById('new_password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    var messageEl = document.getElementById('password-message');
    var submitBtn = document.getElementById('submit-btn');

    // Validate confirmation match
    if (newPassword !== confirmPassword) {
        messageEl.classList.remove('hidden');
        messageEl.innerHTML = '<x-alert type="danger" message="Password baru dan konfirmasi tidak cocok." />';
        return;
    }

    // Validate minimum length
    if (newPassword.length < 5) {
        messageEl.classList.remove('hidden');
        messageEl.innerHTML = '<x-alert type="danger" message="Password baru minimal 5 karakter." />';
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
        messageEl.innerHTML = '<x-alert type="success" message="' + (result.message || 'Password berhasil diubah!') + '" />';
        document.getElementById('change-password-form').reset();
    }).catch(function (err) {
        messageEl.classList.remove('hidden');
        messageEl.innerHTML = '<x-alert type="danger" message="' + (err.message || 'Gagal mengubah password.') + '" />';
    }).finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Password';
    });
}
</script>
@endsection