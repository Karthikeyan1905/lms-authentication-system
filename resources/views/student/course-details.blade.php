@extends('layouts.app')

@section('title', 'Course Details')

@section('content')
<div class="container mt-4">

    <a href="/student-dashboard" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>

    <div class="card shadow-sm">
        <div class="card-body">

            <h2>Course Details</h2>

            <div id="courseMessage">
                Loading course details...
            </div>

            <div id="courseDetails"></div>

        </div>
    </div>

</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

    // Get login token
    const token = sessionStorage.getItem('lms_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    // Get course ID from Laravel route
    const courseId = {{ $id }};

    try {

        const response = await fetch('/api/student/courses/' + courseId, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const data = await response.json();

        const courseMessage = document.getElementById('courseMessage');
        const courseDetails = document.getElementById('courseDetails');

        if (!response.ok) {
            courseMessage.textContent =
                data.message || 'Unable to load course details.';
            return;
        }

        // Hide loading message
        courseMessage.textContent = '';

        // Get course information
        const course = data.course;

        // Display course details safely
        const details = [
            ['Course Name', course.title],
            ['Course Code', course.course_code],
            ['Description', course.description || '-'],
            ['Duration', course.duration || '-'],
            ['Status', course.status]
        ];

        details.forEach(function (item) {

            const paragraph = document.createElement('p');

            const label = document.createElement('strong');
            label.textContent = item[0] + ': ';

            const value = document.createElement('span');
            value.textContent = item[1];

            paragraph.appendChild(label);
            paragraph.appendChild(value);

            courseDetails.appendChild(paragraph);

        });

    } catch (error) {

        console.error(error);

        document.getElementById('courseMessage').textContent =
            'Unable to connect to the server.';

    }

});
</script>
@endsection
