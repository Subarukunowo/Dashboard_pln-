/* ============================================================
   PROFILE PAGE JS — PLN Commercial Dashboard
   Features: inline avatar upload, inline email edit, save bar
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Avatar upload modal ── */
    const avatarWrap    = document.getElementById('avatarWrap');
    const avatarModal   = document.getElementById('avatarModal');
    const avatarClose   = document.getElementById('avatarClose');
    const avatarFileInput = document.getElementById('avatarFileInput');
    const avatarUploadZone = document.getElementById('avatarUploadZone');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarModalSave = document.getElementById('avatarModalSave');
    const avatarMain    = document.getElementById('profileAvatarMain');

    let pendingAvatarFile = null;

    if (avatarWrap) {
        avatarWrap.addEventListener('click', function () {
            if (avatarModal) avatarModal.classList.add('open');
        });
    }

    if (avatarClose) {
        avatarClose.addEventListener('click', closeAvatarModal);
    }

    if (avatarModal) {
        avatarModal.addEventListener('click', function (e) {
            if (e.target === avatarModal) closeAvatarModal();
        });
    }

    function closeAvatarModal() {
        if (avatarModal) avatarModal.classList.remove('open');
        pendingAvatarFile = null;
        if (avatarPreview && avatarMain) {
            avatarPreview.src = avatarMain.src;
        }
    }

    if (avatarUploadZone) {
        avatarUploadZone.addEventListener('click', function () {
            if (avatarFileInput) avatarFileInput.click();
        });

        avatarUploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            avatarUploadZone.style.borderColor = 'var(--accent)';
        });
        avatarUploadZone.addEventListener('dragleave', function () {
            avatarUploadZone.style.borderColor = '';
        });
        avatarUploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            avatarUploadZone.style.borderColor = '';
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) handleAvatarFile(file);
        });
    }

    if (avatarFileInput) {
        avatarFileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) handleAvatarFile(file);
        });
    }

    function handleAvatarFile(file) {
        pendingAvatarFile = file;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (avatarPreview) avatarPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    if (avatarModalSave) {
        avatarModalSave.addEventListener('click', function () {
            if (!pendingAvatarFile) {
                toast('Pilih foto terlebih dahulu.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', pendingAvatarFile);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            avatarModalSave.disabled = true;
            avatarModalSave.textContent = 'Menyimpan...';

            fetch('/profile/avatar', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success || data.avatar_url) {
                    const url = data.avatar_url || (avatarPreview ? avatarPreview.src : '');
                    if (avatarMain) avatarMain.src = url;
                    // update navbar avatar too if present
                    const navAvatar = document.querySelector('.nav-avatar-img');
                    if (navAvatar) navAvatar.src = url;
                    closeAvatarModal();
                    toast('Foto profil diperbarui!', 'success');
                } else {
                    toast(data.message || 'Gagal memperbarui foto.', 'error');
                }
            })
            .catch(() => {
                toast('Terjadi kesalahan. Coba lagi.', 'error');
            })
            .finally(() => {
                avatarModalSave.disabled = false;
                avatarModalSave.textContent = 'Simpan';
            });
        });
    }

    /* ── Inline email edit ── */
    const emailRow    = document.getElementById('emailEditRow');
    const emailDisplay= document.getElementById('emailDisplay');
    const emailInput  = document.getElementById('emailInput');
    const saveBar     = document.getElementById('profileSaveBar');
    const btnSave     = document.getElementById('btnSaveChanges');
    const btnCancel   = document.getElementById('btnCancelChanges');
    const originalEmail = emailDisplay ? emailDisplay.textContent.trim() : '';

    if (emailRow) {
        emailRow.addEventListener('click', function () {
            if (emailInput) {
                emailDisplay.style.display = 'none';
                emailInput.style.display = 'block';
                emailInput.focus();
                if (saveBar) saveBar.classList.add('visible');
            }
        });
    }

    if (emailInput) {
        emailInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cancelEdit();
            if (e.key === 'Enter') saveChanges();
        });
    }

    if (btnSave)   btnSave.addEventListener('click', saveChanges);
    if (btnCancel) btnCancel.addEventListener('click', cancelEdit);

    function cancelEdit() {
        if (emailInput) {
            emailInput.style.display = 'none';
            emailInput.value = originalEmail;
        }
        if (emailDisplay) emailDisplay.style.display = '';
        if (saveBar) saveBar.classList.remove('visible');
    }

    function saveChanges() {
        const newEmail = emailInput ? emailInput.value.trim() : '';
        if (!newEmail) { toast('Email tidak boleh kosong.', 'warning'); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
            toast('Format email tidak valid.', 'warning'); return;
        }

        if (btnSave) {
            btnSave.disabled = true;
            btnSave.textContent = 'Menyimpan...';
        }

        fetch('/profile/email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ email: newEmail })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (emailDisplay) {
                    emailDisplay.textContent = newEmail;
                    emailDisplay.style.display = '';
                }
                if (emailInput) emailInput.style.display = 'none';
                if (saveBar) saveBar.classList.remove('visible');
                toast('Email berhasil diperbarui!', 'success');
            } else {
                toast(data.message || 'Gagal memperbarui email.', 'error');
            }
        })
        .catch(() => toast('Terjadi kesalahan. Coba lagi.', 'error'))
        .finally(() => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.textContent = 'Simpan';
            }
        });
    }

    /* ── Toast helper ── */
    function toast(msg, icon = 'success') {
        if (typeof Swal === 'undefined') {
            alert(msg); return;
        }
        Swal.mixin({
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 2800, timerProgressBar: true,
        }).fire({ icon, title: msg });
    }

});