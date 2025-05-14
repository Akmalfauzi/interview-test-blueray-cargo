{{-- Modal Form User --}}
<div class="modal fade" id="userFormModal" tabindex="-1" aria-labelledby="userFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userFormModalLabel">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm" novalidate>
                <div class="modal-body">
                    {{-- Alert untuk error --}}
                    <div id="userFormAlertContainer" class="alert alert-danger d-none" role="alert">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-circle-fill fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1">Terjadi Kesalahan!</h5>
                                <ul id="userFormErrors" class="mb-0 ps-0"></ul>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input untuk ID --}}
                    <input type="hidden" id="userId" name="id">

                    {{-- Form fields --}}
                    <div class="row g-3">
                        {{-- Nama --}}
                        <div class="col-md-6">
                            <label for="userName" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="userName" 
                                   name="name" 
                                   required 
                                   autocomplete="off">
                            <div class="invalid-feedback">
                                Nama user harus diisi
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label for="userEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control" 
                                   id="userEmail" 
                                   name="email" 
                                   required 
                                   autocomplete="off">
                            <div class="invalid-feedback">
                                Email harus valid
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="col-md-6">
                            <label for="userPassword" class="form-label">
                                Password 
                                <span class="text-danger password-required">*</span>
                                <small class="text-muted" id="passwordHelp">(minimal 8 karakter)</small>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="userPassword" 
                                       name="password" 
                                       minlength="8" 
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Password minimal 8 karakter
                            </div>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="col-md-6">
                            <label for="userPasswordConfirmation" class="form-label">
                                Konfirmasi Password 
                                <span class="text-danger password-required">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="userPasswordConfirmation" 
                                       name="password_confirmation" 
                                       minlength="8" 
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePasswordConfirmation">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Konfirmasi password harus sama
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="col-md-12">
                            <label for="userRoles" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" 
                                    id="userRoles" 
                                    name="roles[]" 
                                    required 
                                    multiple>
                                <option value="">Pilih Role...</option>
                            </select>
                            <div class="invalid-feedback">
                                Role harus dipilih
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveUserButton">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span class="button-text">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.required:after {
    content: " *";
    color: #dc3545;
}

/* Password toggle button */
.input-group .btn-outline-secondary {
    border-color: #ced4da;
    color: #6c757d;
}

.input-group .btn-outline-secondary:hover {
    background-color: #e9ecef;
    border-color: #ced4da;
    color: #495057;
}

.input-group .btn-outline-secondary:focus {
    box-shadow: none;
}

.input-group .btn-outline-secondary i {
    font-size: 1rem;
}

/* Form validation styles */
.was-validated .form-control:invalid,
.was-validated .form-select:invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.was-validated .form-control:valid,
.was-validated .form-select:valid {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const passwordInput = document.getElementById('userPassword');
    const passwordConfirmationInput = document.getElementById('userPasswordConfirmation');
    const passwordRequiredStars = document.querySelectorAll('.password-required');
    const userForm = document.getElementById('userForm');
    const userIdInput = document.getElementById('userId');

    // Function to toggle password required state
    function togglePasswordRequired(isRequired) {
        passwordRequiredStars.forEach(star => {
            star.style.display = isRequired ? 'inline' : 'none';
        });
        
        if (isRequired) {
            passwordInput.setAttribute('required', '');
            passwordConfirmationInput.setAttribute('required', '');
        } else {
            passwordInput.removeAttribute('required');
            passwordConfirmationInput.removeAttribute('required');
        }
    }

    // Set initial state based on form mode
    if (userIdInput.value) {
        togglePasswordRequired(false);
    } else {
        togglePasswordRequired(true);
    }

    // Watch for changes in userId to determine form mode
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                togglePasswordRequired(!userIdInput.value);
            }
        });
    });

    observer.observe(userIdInput, { attributes: true });

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }

    if (togglePasswordConfirmation && passwordConfirmationInput) {
        togglePasswordConfirmation.addEventListener('click', function() {
            const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmationInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }

    // Password confirmation validation
    if (passwordInput && passwordConfirmationInput) {
        function validatePassword() {
            // Only validate if either password field has a value
            if (passwordInput.value || passwordConfirmationInput.value) {
                if (passwordInput.value !== passwordConfirmationInput.value) {
                    passwordConfirmationInput.setCustomValidity('Password tidak cocok');
                } else {
                    passwordConfirmationInput.setCustomValidity('');
                }
            } else {
                // If both are empty and we're in edit mode, clear validation
                if (userIdInput.value) {
                    passwordConfirmationInput.setCustomValidity('');
                }
            }
        }

        passwordInput.addEventListener('input', validatePassword);
        passwordConfirmationInput.addEventListener('input', validatePassword);
    }

    // Password strength validation
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            // Only validate if there's a value and we're in add mode
            if (this.value && !userIdInput.value) {
                let isValid = true;
                let message = '';

                if (this.value.length < 8) {
                    isValid = false;
                    message = 'Password minimal 8 karakter';
                } else if (!/[A-Za-z]/.test(this.value) || !/[0-9]/.test(this.value)) {
                    isValid = false;
                    message = 'Password harus mengandung huruf dan angka';
                }

                this.setCustomValidity(message);
            } else if (!this.value && userIdInput.value) {
                // Clear validation in edit mode if empty
                this.setCustomValidity('');
            }
        });
    }

    // Form validation
    if (userForm) {
        userForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
});
</script> 