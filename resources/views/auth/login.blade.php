
@extends('layouts.app')

@section('title', 'Login - LMS')

@section('content')

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">

        <div class="col-md-5 col-lg-4">

            <div class="card shadow border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <h2 class="fw-bold">LMS</h2>
                        <p class="text-muted">
                            Learning Management System
                        </p>
                    </div>

                    <h4 class="mb-3">Login</h4>

                    <form id="loginForm">

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                            >
                        </div>

                      <div class="text-end mb-3">
                        <a href="#" id="forgotPasswordBtn" class="text-decoration-none">
                            Forgot Password?
                        </a>
                    </div>

                    <div id="forgotPasswordMessage" class="mb-3"></div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>

                    </form>

                    <div id="loginMessage" class="mt-3"></div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', async function (event) {
        event.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const message = document.getElementById('loginMessage');

        message.textContent = 'Logging in...';

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });

            const data = await response.json();

            if (!response.ok) {
                message.textContent = data.message || 'Login failed.';
                return;
            }

           if (data.mfa_required === true) {
            // Save the user ID temporarily for OTP verification.
            sessionStorage.setItem('lms_user_id', data.user_id);

            // Open the OTP verification page.
            window.location.href = '/otp-verification';
            return;
            }

            if (data.mfa_required === false) {
                // Store the access token for API requests.
                sessionStorage.setItem('lms_token', data.token);

                // Store user information.
                sessionStorage.setItem('lms_user', JSON.stringify(data.user));

                message.textContent = 'Login successful!';

                // Redirect based on user role.
                if (data.user.role === 'student') {
                    window.location.href = '/student-dashboard';
                } else if (data.user.role === 'school_admin') {
                    window.location.href = '/school-admin-dashboard';
                } else if (data.user.role === 'super_admin') {
                    window.location.href = '/super-admin-dashboard';
                }
            }

        } catch (error) {
            message.textContent = 'Unable to connect to the server.';
            console.error(error);
        }
    });

    document.getElementById('forgotPasswordBtn').addEventListener('click', async function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const message = document.getElementById('forgotPasswordMessage');

    if (!email) {
        message.innerHTML = `
            <div class="alert alert-warning">
                Please enter your email address first.
            </div>
        `;
        return;
    }

    message.innerHTML = 'Sending password reset link...';

    try {
        const response = await fetch('/api/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: email
            })  
        });

        const data = await response.json();

        if (response.ok) {
            message.innerHTML = `
                <div class="alert alert-success">
                    ${data.message}
                </div>
            `;
        } else {
            message.innerHTML = `
                <div class="alert alert-danger">
                    ${data.message || 'Unable to send reset link.'}
                </div>
            `;
        }

    } catch (error) {
        message.innerHTML = `
            <div class="alert alert-danger">
                Unable to connect to the server.
            </div>
        `;
        console.error(error);
    }
});
</script>
@endsection
