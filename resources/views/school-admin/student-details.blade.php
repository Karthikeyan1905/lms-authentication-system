
@extends('layouts.app')

@section('title', 'Student Details')

@section('content')

<div class="container mt-4">

    <a href="/school-admin-dashboard" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Student Details</h3>

            <div id="studentMessage">Loading student details...</div>

            <div id="studentDetails"></div>

        </div>
    </div>

</div>

@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', async function () {

    const token = sessionStorage.getItem('lms_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    const studentId = {{ $id }};

    try {

        const response = await fetch('/api/school-admin/students/' + studentId, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const data = await response.json();

        const message = document.getElementById('studentMessage');
        const details = document.getElementById('studentDetails');

        if (!response.ok) {
            message.textContent = data.message || 'Unable to load student details.';
            return;
        }

        message.textContent = '';

        const student = data.student;

        const studentInfo = [
            ['Student ID', student.id],
            ['Name', student.name],
            ['Email', student.email],
            ['Phone', student.phone || '-'],
            ['Status', student.status],
            ['Tenant ID', student.tenant_id],
            ['Email Verified At', student.email_verified_at || 'Not verified'],
            ['Created At', student.created_at]
        ];

        studentInfo.forEach(item => {

            const paragraph = document.createElement('p');

            const label = document.createElement('strong');
            label.textContent = item[0] + ': ';

            const value = document.createElement('span');
            value.textContent = item[1];

            paragraph.appendChild(label);
            paragraph.appendChild(value);

            details.appendChild(paragraph);

        });

    } catch (error) {

        console.error(error);

        document.getElementById('studentMessage').textContent =
            'Unable to connect to the server.';

    }

});
</script>

@endsection
