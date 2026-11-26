
@extends('layouts.app')

@section('title', 'Add Course')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Course</h2>

        <a href="/school-admin-dashboard" class="btn btn-secondary">
            Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form id="addCourseForm">

                <div class="mb-3">
                    <label for="title" class="form-label">
                        Course Title
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="course_code" class="form-label">
                        Course Code
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="course_code"
                        name="course_code"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="4"
                    ></textarea>
                </div>

                <div class="mb-3">
                    <label for="duration" class="form-label">
                        Duration (Days)
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="duration"
                        name="duration"
                        min="1"
                    >
                </div>

                <div id="courseMessage" class="mb-3"></div>

                <button type="submit" class="btn btn-primary">
                    Add Course
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

    const form = document.getElementById('addCourseForm');
    const courseMessage = document.getElementById('courseMessage');

    form.addEventListener('submit', async function (event) {

        event.preventDefault();

        const courseData = {
            title: document.getElementById('title').value,
            course_code: document.getElementById('course_code').value,
            description: document.getElementById('description').value,
            duration: document.getElementById('duration').value
                ? parseInt(document.getElementById('duration').value)
                : null
        };

        courseMessage.innerHTML = '';

        try {

            const response = await fetch('/api/school-admin/courses', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(courseData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || 'Unable to create course.'
                );
            }

            courseMessage.innerHTML = `
                <div class="alert alert-success">
                    ${data.message}
                </div>
            `;

            form.reset();

        } catch (error) {

            console.error(error);

            courseMessage.innerHTML = `
                <div class="alert alert-danger">
                    ${error.message}
                </div>
            `;

        }

    });

});
</script>
@endsection
