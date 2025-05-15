@extends('layouts.backend.v1.auth')

@section('content')
    <body class="register-page bg-body-secondary">
        <div class="register-box">
            <div class="register-logo">
                <a href="{{ route('register') }}"><b>{{ config('app.name') }}</b></a>
            </div>
            <div class="card">
                <div class="card-body register-card-body">
                    <p class="login-box-msg">Register</p>
                    
                    {{-- Alert untuk menampilkan error --}}
                    <div id="registerAlertContainer" class="alert alert-danger mb-3" style="display: none;">
                        <ul id="registerErrors" class="mb-0"></ul>
                    </div>

                    <form id="registerForm">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="name" placeholder="Full name" required />
                            <div class="input-group-text"><span class="bi bi-person"></span></div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" name="email" placeholder="Email" required />
                            <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required />
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <span class="bi bi-eye"></span>
                            </button>
                            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" name="password_confirmation" id="passwordConfirmation" placeholder="Retype password" required />
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                <span class="bi bi-eye"></span>
                            </button>
                            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required />
                                    <label class="form-check-label" for="terms">
                                        I agree to the terms and conditions
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" id="registerButton">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                        Register
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <p class="mb-0 mt-3">
                        <a href="{{ route('login') }}" class="text-center">I already have an account</a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Scripts --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
            integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
            integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
        </script>
        <script src="{{ get_template_url('js/adminlte.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        {{-- Register Script --}}
        <script>
            $(document).ready(function() {
                const $registerForm = $('#registerForm');
                const $registerButton = $('#registerButton');
                const $registerSpinner = $registerButton.find('.spinner-border');
                const $registerAlertContainer = $('#registerAlertContainer');
                const $registerErrors = $('#registerErrors');
                const $password = $('#password');
                const $passwordConfirmation = $('#passwordConfirmation');
                const $togglePassword = $('#togglePassword');
                const $togglePasswordConfirmation = $('#togglePasswordConfirmation');

                // Toggle password visibility for main password
                $togglePassword.on('click', function() {
                    const type = $password.attr('type') === 'password' ? 'text' : 'password';
                    $password.attr('type', type);
                    // Toggle eye icon
                    $(this).find('span').toggleClass('bi-eye bi-eye-slash');
                });

                // Toggle password visibility for confirmation password
                $togglePasswordConfirmation.on('click', function() {
                    const type = $passwordConfirmation.attr('type') === 'password' ? 'text' : 'password';
                    $passwordConfirmation.attr('type', type);
                    // Toggle eye icon
                    $(this).find('span').toggleClass('bi-eye bi-eye-slash');
                });

                $registerForm.on('submit', function(e) {
                    e.preventDefault();
                    
                    // Reset error display
                    $registerAlertContainer.hide();
                    $registerErrors.empty();
                    
                    // Show loading state
                    $registerButton.prop('disabled', true);
                    $registerSpinner.show();

                    $.ajax({
                        url: '/api/v1/register',
                        method: 'POST',
                        data: $(this).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message with SweetAlert2
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Registrasi Berhasil!',
                                    text: 'Akun Anda berhasil dibuat. Anda akan diarahkan ke halaman login.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    willClose: () => {
                                        // Redirect to login page after delay
                                        window.location.href = '/login';
                                    }
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Registrasi Gagal',
                                    text: response.message || 'Terjadi kesalahan saat registrasi',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            
                            if (xhr.status === 422) {
                                // Handle validation errors
                                $.each(response.errors, function(key, errors) {
                                    $.each(errors, function(index, error) {
                                        $registerErrors.append('<li>' + error + '</li>');
                                    });
                                });
                                $registerAlertContainer.show();
                            } else if (xhr.status === 429) {
                                // Handle rate limit error with SweetAlert2
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Terlalu Banyak Percobaan',
                                    text: 'Silakan coba lagi dalam 1 menit.',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                // Handle other errors with SweetAlert2
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Registrasi Gagal',
                                    text: response.message || 'Terjadi kesalahan saat registrasi',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        complete: function() {
                            // Reset button state
                            $registerButton.prop('disabled', false);
                            $registerSpinner.hide();
                        }
                    });
                });

                // Scrollbar initialization
                const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
                const Default = {
                    scrollbarTheme: 'os-theme-light',
                    scrollbarAutoHide: 'leave',
                    scrollbarClickScroll: true,
                };

                const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
                if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
                    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                        scrollbars: {
                            theme: Default.scrollbarTheme,
                            autoHide: Default.scrollbarAutoHide,
                            clickScroll: Default.scrollbarClickScroll,
                        },
                    });
                }
            });
        </script>
    </body>
@endsection
