@extends('layouts.backend.v1.auth')

@section('content')

    <body class="login-page bg-body-secondary">
        <div class="login-box">
            <div class="login-logo">
                <a href="{{ route('login') }}"><b>{{ config('app.name') }}</b></a>
            </div>
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>
                    
                    {{-- Alert untuk menampilkan error --}}
                    <div id="loginAlertContainer" class="alert alert-danger mb-3" style="display: none;">
                        <ul id="loginErrors" class="mb-0"></ul>
                    </div>

                    <form id="loginForm">
                        @csrf
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
                        <div class="row">
                            <div class="col-8">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">Remember Me</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <button type="submit" class="btn btn-primary btn-block" id="loginButton">
                                    <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
                                    Sign In
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="mb-1"><a href="{{ route('forgot-password') }}">I forgot my password</a></p>
                    <p class="mb-0">
                        <a href="{{ route('register') }}" class="text-center">Register</a>
                    </p>
                </div>
            </div>
        </div>
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
        <script>
            $(document).ready(function() {
                const $loginForm = $('#loginForm');
                const $loginButton = $('#loginButton');
                const $loginSpinner = $loginButton.find('.spinner-border');
                const $loginAlertContainer = $('#loginAlertContainer');
                const $loginErrors = $('#loginErrors');
                const $password = $('#password');
                const $togglePassword = $('#togglePassword');

                // Toggle password visibility
                $togglePassword.on('click', function() {
                    const type = $password.attr('type') === 'password' ? 'text' : 'password';
                    $password.attr('type', type);
                    // Toggle eye icon
                    $(this).find('span').toggleClass('bi-eye bi-eye-slash');
                });

                // Initialize Toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                $loginForm.on('submit', function(e) {
                    e.preventDefault();
                    
                    // Reset error display
                    $loginAlertContainer.hide();
                    $loginErrors.empty();
                    
                    // Show loading state
                    $loginButton.prop('disabled', true);
                    $loginSpinner.show();

                    $.ajax({
                        url: '{{ route('api.v1.login') }}',
                        method: 'POST',
                        data: $(this).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        credentials: 'same-origin',
                        success: function(response) {
                            if (response.success) {
                                // Show success message with SweetAlert2
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Login Berhasil!',
                                    text: 'Selamat datang kembali. Anda akan diarahkan ke dashboard.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    willClose: () => {
                                        // Redirect after delay
                                        if (response.redirect) {
                                            window.location.href = response.redirect;
                                        } else {
                                            window.location.href = '/dashboard';
                                        }
                                    }
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Login Gagal',
                                    text: response.message || 'Terjadi kesalahan saat login',
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
                                        $loginErrors.append('<li>' + error + '</li>');
                                    });
                                });
                                $loginAlertContainer.show();
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
                                    title: 'Login Gagal',
                                    text: response.message || 'Terjadi kesalahan saat login',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        complete: function() {
                            // Reset button state
                            $loginButton.prop('disabled', false);
                            $loginSpinner.hide();
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
