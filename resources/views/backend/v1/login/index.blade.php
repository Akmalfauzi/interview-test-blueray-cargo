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
                            <input type="password" class="form-control" name="password" placeholder="Password" required />
                            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" />
                                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" id="loginButton">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                        Sign In
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <p class="mb-1"><a href="{{ route('forgot-password') }}">I forgot my password</a></p>
                    <p class="mb-0">
                        <a href="{{ route('register') }}" class="text-center">Register a new membership</a>
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
        <script>
            $(document).ready(function() {
                const $loginForm = $('#loginForm');
                const $loginButton = $('#loginButton');
                const $loginSpinner = $loginButton.find('.spinner-border');
                const $loginAlertContainer = $('#loginAlertContainer');
                const $loginErrors = $('#loginErrors');

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
                            console.log(response);
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = '/dashboard';
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
                            } else if (xhr.status === 429) {
                                // Handle rate limit error
                                $loginErrors.append('<li>Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.</li>');
                            } else {
                                // Handle other errors
                                $loginErrors.append('<li>' + (response.message || 'An error occurred during login') + '</li>');
                            }
                            $loginAlertContainer.show();
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
