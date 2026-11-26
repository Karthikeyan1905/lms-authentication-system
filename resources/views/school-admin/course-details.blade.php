@extends('layouts.app')

@section('title', 'Course Details')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Course Details</h2>

        <a href="/school-admin-dashboard" class="btn btn-secondary">
            Back to Dashboard
        </a>
    </div>

    <div id="courseMessage" class="mb-3"></div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered">
                <tr>
                    <th>Course ID</th>
                    <td id="courseId">Loading...</td>
                </tr>

                <tr>
                    <th>Course Title</th>
                    <td id="courseTitle">Loading...</td>
                </tr>

                <tr>
                    <th>Course Code</th>
                    <td id="courseCode">Loading...</td>
                </tr>

                <tr>
                    <th>Description</th>
                    <td id="courseDescription">Loading...</td>
                </tr>

                <tr>
                    <th>Duration</th>
                    <td id="courseDuration">Loading...</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td id="courseStatus">Loading...</td>
                </tr>

                <tr>
                    <th>Created Date</th>
                    <td id="courseCreatedAt">Loading...</td>
                </tr>
            </table>

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

    const courseId = {{ $id }};

    const courseMessage = document.getElementById('courseMessage');

    try {

        const response = await fetch('/api/school-admin/courses/' + courseId, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const data = await response.json();

        if (!response.ok || !data.course) {
            throw new Error(data.message || 'Unable to load course details.');
        }

        const course = data.course;

        document.getElementById('courseId').textContent = course.id;
        document.getElementById('courseTitle').textContent = course.title;
        document.getElementById('courseCode').textContent = course.course_code;
        document.getElementById('courseDescription').textContent =
            course.description || 'N/A';
        document.getElementById('courseDuration').textContent =
            course.duration ? course.duration + ' days' : 'N/A';
        document.getElementById('courseStatus').textContent = course.status;
        document.getElementById('courseCreatedAt').textContent =
            course.created_at || 'N/A';

        courseMessage.innerHTML = `
            <div class="alert alert-success">
                Course details loaded successfully.
            </div>
        `;

    } catch (error) {

        console.error(error);

        courseMessage.innerHTML = `
            <div class="alert alert-danger">
                ${error.message}
            </div>
        `;

    }
});
</script>
@endsection
