{{-- Modal Form Role Assignment --}}
<div class="modal fade" id="roleFormModal" tabindex="-1" aria-labelledby="roleFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleFormModalLabel">Atur Role User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm" novalidate>
                <div class="modal-body">
                    {{-- Alert untuk error --}}
                    <div id="roleFormAlertContainer" class="alert alert-danger d-none" role="alert">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-circle-fill fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1">Terjadi Kesalahan!</h5>
                                <ul id="roleFormErrors" class="mb-0 ps-0"></ul>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input untuk user ID --}}
                    <input type="hidden" id="roleUserId" name="user_id">

                    {{-- User info --}}
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-person-circle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading mb-1">Informasi User</h6>
                                <p class="mb-0" id="roleUserName">
                                    Memuat informasi user...
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Role selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <div class="alert alert-warning mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small">Pilih satu role untuk user ini. Role yang dipilih akan menentukan hak akses user.</p>
                                </div>
                            </div>
                        </div>
                        <div id="roleLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 mb-0 text-muted">Memuat daftar role...</p>
                        </div>
                        <div id="roleList" class="role-list d-none">
                            {{-- Role radio buttons will be rendered here --}}
                        </div>
                        <div id="noRoleResults" class="alert alert-info d-none">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>
                                <div>
                                    <p class="mb-0">Tidak ada role yang tersedia. Silakan tambahkan role terlebih dahulu.</p>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback">
                            Pilih satu role
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveRoleButton">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span class="button-text">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.role-list {
    max-height: 400px;
    overflow-y: auto;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
}

.role-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    background-color: #fff;
    transition: all 0.2s ease-in-out;
}

.role-item:last-child {
    margin-bottom: 0;
}

.role-item:hover {
    border-color: #adb5bd;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.role-item .form-check {
    margin: 0;
    flex: 1;
    padding-left: 2rem;
}

.role-item .form-check-input {
    margin-left: -2rem;
    margin-top: 0.25rem;
}

.role-item .form-check-input[type="radio"] {
    width: 1.25rem;
    height: 1.25rem;
    margin-top: 0.125rem;
}

.role-item .form-check-input[type="radio"]:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.role-item .form-check-label {
    cursor: pointer;
    user-select: none;
    width: 100%;
}

.role-item .role-name {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

.role-item .role-description {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

/* Custom scrollbar */
.role-list::-webkit-scrollbar {
    width: 8px;
}

.role-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.role-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.role-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Alert animation */
.alert {
    transition: all 0.3s ease-in-out;
}

.alert.show {
    display: block;
    animation: slideDown 0.3s ease-in-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Modal customization */
.modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

.modal-title {
    font-weight: 600;
    color: #212529;
}

/* Button styles */
.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #212529;
}

.btn-light:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #212529;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Role form submission
    const roleForm = document.getElementById('roleForm');
    const saveRoleButton = document.getElementById('saveRoleButton');
    const spinner = saveRoleButton.querySelector('.spinner-border');
    const buttonText = saveRoleButton.querySelector('.button-text');

    roleForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        // Show loading state
        saveRoleButton.disabled = true;
        spinner.classList.remove('d-none');
        buttonText.classList.add('d-none');

        try {
            const formData = new FormData(this);
            const userId = document.getElementById('roleUserId').value;
            
            const response = await fetch(`/api/v1/users/${userId}/roles`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Close modal and refresh table
                const modal = bootstrap.Modal.getInstance(document.getElementById('roleFormModal'));
                modal.hide();
                
                // Refresh user table
                if (typeof UserManagement !== 'undefined') {
                    UserManagement.fetchAndDisplayUsers(UserManagement.state.currentPage);
                }
                
                // Show success message
                window.Toast.fire({
                    icon: 'success',
                    title: result.message || 'Role user berhasil diperbarui!'
                });
            } else {
                // Show error message
                window.Toast.fire({
                    icon: 'error',
                    title: result.message || 'Gagal memperbarui role user'
                });
            }
        } catch (error) {
            console.error('Error updating user roles:', error);
            window.Toast.fire({
                icon: 'error',
                title: 'Terjadi kesalahan saat memperbarui role user: ' + error.message
            });
        } finally {
            // Reset button state
            saveRoleButton.disabled = false;
            spinner.classList.add('d-none');
            buttonText.classList.remove('d-none');
        }
    });
});
</script> 