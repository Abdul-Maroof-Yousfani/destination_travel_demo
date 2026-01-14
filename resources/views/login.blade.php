@php
    $layout = request()->routeIs('admin.login') ? 'home/layouts/layout' : 'home/layouts/master';
@endphp
@extends($layout)
@section('title', 'Login')
@section('style')
  <link rel="stylesheet" href="{{ url('assets/css/login.css') }}">
@endsection
@section('content')
    @php $isAdmin = request()->routeIs('admin.login') ? true : false; @endphp
    <section class="search-bookings">
        <div class="container d-flex justify-content-center">
            <div class="booking-box">
                <h2>Login to your account @if ($isAdmin) (Admin) @endif</h2>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. name@gmail.com" required>
                    <div class="form-note">Enter your valid email address.</div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button class="loginBtn" disabled>Login</button>
                @if (!$isAdmin)
                    <p class="registerText mt-2">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
                @endif
            </div>
        </div>
    </section>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            const isAdmin = @json($isAdmin);

            function validateForm() {
                let email = $('#email').val().trim();
                let password = $('#password').val().trim();
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // basic email check

                if (emailRegex.test(email) && password.length > 0) {
                    $('.loginBtn').addClass('active').prop('disabled', false);
                } else {
                    $('.loginBtn').removeClass('active').prop('disabled', true);
                }
            }

            // Run validation whenever user types
            $('#email, #password').on('input', validateForm);

            $('.loginBtn').on('click', function (e) {
                e.preventDefault();
                let url = isAdmin ? "{{ route('admin.login.submit') }}" : "{{ route('login.submit') }}";
                let email = $('#email').val().trim();
                let password = $('#password').val().trim();

                if (!email || !password) {
                    _alert('Both fields are required.', 'error');
                    return;
                }

                $.ajax({
                    url,
                    method: 'POST',
                    data: {
                        email: email,
                        password: password,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: res => {
                        let currentUrl = window.location.href;
                        if (currentUrl.includes('/admin') || currentUrl.includes('/agent')) {
                            window.location.href = res.redirect
                        } else {
                            let goBack = localStorage.getItem('flights') || null;
                            window.location.href = (goBack ? `/flights${goBack}` : res.redirect);
                        }
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Login failed.';
                        _alert(msg, 'error');
                    }
                });
            });
        });
    </script>
@endsection