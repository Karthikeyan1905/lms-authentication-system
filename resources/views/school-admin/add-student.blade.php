
@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<div class="container mt-4">

    <a href="/school-admin-dashboard" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Add New Student</h3>

            <div id="studentMessage" class="mb-3"></div>

            <form id="addStudentForm">

                <div class="mb-3">
                    <label class="form-label">Student Name</label>
                    <input type="text" id="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" id="phone" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Create Student
                </button>

            </form>

        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const token = sessionStorage.getItem('lms_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    document.getElementById('addStudentForm').addEventListener('submit', async function (event) {

        event.preventDefault();

        const message = document.getElementById('studentMessage');

        message.textContent = 'Creating student...';

        const studentData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value
        };

        try {
            const response = await fetch('/api/school-admin/students', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(studentData)
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    message.textContent = Object.values(data.errors).flat().join(' ');
                } else {
                    message.textContent = data.message || 'Unable to create student.';
                }

                message.className = 'text-danger mb-3';
                return;
            }

            message.textContent = data.message;
            message.className = 'text-success mb-3';

            document.getElementById('addStudentForm').reset();

        } catch (error) {
            console.error(error);

            message.textContent = 'Unable to connect to the server.';
            message.className = 'text-danger mb-3';
        }

    });

});
</script>
@endsection
