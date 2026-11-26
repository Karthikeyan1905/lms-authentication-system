
@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container mt-4">

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Student Dashboard</h2>
            <p class="text-muted">Welcome to your LMS portal.</p>
        </div>

        <button class="btn btn-danger" id="logoutBtn">
            Logout
        </button>
    </div>

    <!-- Dashboard Statistics -->
    <div class="row g-3">

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Total Courses</h5>
                    <h2 id="totalCourses">0</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Attendance</h5>
                    <h2 id="attendance">0%</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Pending Assignments</h5>
                    <h2 id="pendingAssignments">0</h2>
                </div>
            </div>
        </div>

    </div>

    <!-- Student Information -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5>Student Information</h5>

            <p>Name: <span id="studentName">Loading...</span></p>
            <p>Email: <span id="studentEmail">Loading...</span></p>
            <p>Role: <span id="studentRole">Loading...</span></p>
        </div>
    </div>

    <!-- My Courses -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="mb-3">My Courses</h5>

            <div id="coursesMessage">Loading courses...</div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                           <th>Course Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="coursesTable"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection



@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

    // =================================
    // 1. GET LOGIN TOKEN
    // =================================

    const token = sessionStorage.getItem('lms_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    // =================================
    // 2. LOGOUT FUNCTIONALITY
    // =================================

    document.getElementById('logoutBtn').addEventListener('click', function () {

        sessionStorage.removeItem('lms_token');
        sessionStorage.removeItem('lms_user');
        sessionStorage.removeItem('lms_user_id');

        window.location.href = '/login';

    });

    try {

        // =================================
        // 3. STUDENT DASHBOARD API
        // =================================

        const response = await fetch('/api/student/dashboard', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message || 'Unable to load dashboard.');
            return;
        }

        // Display student information
        document.getElementById('studentName').textContent =
            data.student.name;

        document.getElementById('studentEmail').textContent =
            data.student.email;

        document.getElementById('studentRole').textContent =
            data.student.role;

        // Display dashboard statistics
        document.getElementById('totalCourses').textContent =
            data.dashboard.total_courses;

        document.getElementById('attendance').textContent =
            data.dashboard.attendance_percentage + '%';

        document.getElementById('pendingAssignments').textContent =
            data.dashboard.pending_assignments;


        // =================================
        // 4. STUDENT COURSES API
        // =================================

        const coursesResponse = await fetch('/api/student/courses', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const coursesData = await coursesResponse.json();

        const coursesTable = document.getElementById('coursesTable');
        const coursesMessage = document.getElementById('coursesMessage');

        if (!coursesResponse.ok) {
            coursesMessage.textContent =
                coursesData.message || 'Unable to load courses.';
            return;
        }

        // Get course records from paginated response
        const courses = coursesData.courses.data;

        coursesTable.innerHTML = '';

        if (courses.length === 0) {

            coursesMessage.textContent = 'No courses available.';

        } else {

            coursesMessage.textContent = '';

            courses.forEach(course => {

                const row = document.createElement('tr');

                const titleCell = document.createElement('td');
                titleCell.textContent = course.title;

                const descriptionCell = document.createElement('td');
                descriptionCell.textContent = course.description || '-';

                const statusCell = document.createElement('td');
                statusCell.textContent = course.status;

                const actionCell = document.createElement('td');

                const viewButton = document.createElement('button');
                viewButton.textContent = 'View Details';
                viewButton.className = 'btn btn-primary btn-sm';

                viewButton.addEventListener('click', function () {
                    window.location.href = '/student-courses/' + course.id;
                });

                actionCell.appendChild(viewButton);

                row.appendChild(titleCell);
                row.appendChild(descriptionCell);
                row.appendChild(statusCell);
                row.appendChild(actionCell);

                coursesTable.appendChild(row);

            });
        }

    } catch (error) {

        console.error(error);
        alert('Unable to connect to the server.');

    }

});
</script>
@endsection
