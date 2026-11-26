
@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">Reset Password</h3>

            <div id="resetMessage" class="mb-3"></div>

            <form id="resetPasswordForm">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        class="form-control"
                        value="{{ old('email', $email) }}"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input
                        type="password"
                        id="password"
                        class="form-control"
                        minlength="8"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        class="form-control"
                        minlength="8"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('resetPasswordForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const message = document.getElementById('resetMessage');
    const button = this.querySelector('button[type="submit"]');

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;

    button.disabled = true;
    button.textContent = 'Resetting...';
    message.innerHTML = '';

    try {
        const response = await fetch('/api/reset-password', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                token: @json($token),
                password: password,
                password_confirmation: password_confirmation
            })
        });

        const data = await response.json();

        if (response.ok) {
            message.innerHTML = `
                <div class="alert alert-success">
                    ${data.message}
                    <br>
                    <a href="/login">Go to Login</a>
                </div>
            `;
            this.reset();
        } else {
            let errorText = data.message || 'Unable to reset password.';

            if (data.errors) {
                errorText = Object.values(data.errors).flat().join('<br>');
            }

            message.innerHTML = `
                <div class="alert alert-danger">${errorText}</div>
            `;
        }
    } catch (error) {
        message.innerHTML = `
            <div class="alert alert-danger">
                Something went wrong. Please try again.
            </div>
        `;
    } finally {
        button.disabled = false;
        button.textContent = 'Reset Password';
    }
});
</script>
@endsection
