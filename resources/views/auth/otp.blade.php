
@extends('layouts.app')

@section('title', 'OTP Verification')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center mb-4">OTP Verification</h3>

                    <p class="text-center">
                        Enter the OTP sent to your registered email.
                    </p>

                    <form id="otpForm">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP</label>
                            <input
                                type="text"
                                id="otp"
                                class="form-control"
                                maxlength="6"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Verify OTP
                        </button>
                    </form>

                    <div id="otpMessage" class="mt-3 text-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.getElementById('otpForm').addEventListener('submit', async function (event) {
        event.preventDefault();

        const otp = document.getElementById('otp').value;
        const userId = sessionStorage.getItem('lms_user_id');
        const message = document.getElementById('otpMessage');

        if (!userId) {
            message.textContent = 'User session not found. Please login again.';
            return;
        }

        message.textContent = 'Verifying OTP...';

        try {
            const response = await fetch('/api/verify-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    otp: otp
                })
            });

            const data = await response.json();

            if (!response.ok) {
                message.textContent = data.message || 'OTP verification failed.';
                return;
            }

            // Store token and user information.
            sessionStorage.setItem('lms_token', data.token);
            sessionStorage.setItem('lms_user', JSON.stringify(data.user));

            // Remove temporary user ID.
            sessionStorage.removeItem('lms_user_id');

            message.textContent = 'OTP verified successfully!';

            // Redirect based on role.
            if (data.user.role === 'student') {
                window.location.href = '/student-dashboard';
            } else if (data.user.role === 'school_admin') {
                window.location.href = '/school-admin-dashboard';
            } else if (data.user.role === 'super_admin') {
                window.location.href = '/super-admin-dashboard';
            }

        } catch (error) {
            message.textContent = 'Unable to connect to the server.';
            console.error(error);
        }
    });
</script>
@endsection

