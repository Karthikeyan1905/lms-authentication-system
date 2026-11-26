@extends('layouts.app')

@section('title', 'School Admin Dashboard')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>School Admin Dashboard</h2>
            <p class="text-muted">Welcome to your School Admin Portal.</p>
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
                    <h5>Total Students</h5>
                    <h2 id="totalStudents">0</h2>
                </div>
            </div>
        </div>

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
                    <h5>Active Students</h5>
                    <h2 id="activeStudents">0</h2>
                </div>
            </div>
        </div>

    </div>

    <!-- School Admin Information -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5>School Admin Information</h5>

            <p>Name: <span id="adminName">Loading...</span></p>
            <p>Email: <span id="adminEmail">Loading...</span></p>
            <p>Role: <span id="adminRole">Loading...</span></p>
        </div>
    </div>

        <!-- Student Management -->
    <div class="card shadow-sm mt-4 mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

    <h4>Student Management</h4>

    <div>
        <a href="/school-admin/add-student" class="btn btn-primary">
            + Add Student
        </a>

        <button class="btn btn-primary" id="refreshStudentsBtn">
            Refresh Students
        </button>
    </div>

</div>

            <div id="studentsMessage" class="text-muted mb-3">
                Loading students...
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTable">
                    </tbody>
                </table>
            </div>

        </div>
    </div>

<!-- Course Management -->
<div class="card shadow-sm mt-4">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Course Management</h4>

    <div>
        <a href="/school-admin/add-course" class="btn btn-success me-2">
            + Add Course
        </a>

        <button class="btn btn-primary" id="refreshCoursesBtn">
            Refresh Courses
        </button>
    </div>
</div>

        <div id="coursesMessage" class="mb-3"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Course Title</th>
                        <th>Course Code</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="coursesTable">
                    <tr>
                        <td colspan="5" class="text-center">
                            Loading courses...
                        </td>
                    </tr>
                </tbody>
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
        // 3. GET SCHOOL ADMIN INFORMATION
        // =================================

        const user = JSON.parse(sessionStorage.getItem('lms_user'));

        if (user) {
            document.getElementById('adminName').textContent = user.name;
            document.getElementById('adminEmail').textContent = user.email;
            document.getElementById('adminRole').textContent = user.role;
        }

        // =================================
        // 4. GET STUDENT COUNT
        // =================================

        const studentsResponse = await fetch('/api/school-admin/students', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const studentsData = await studentsResponse.json();

        if (!studentsResponse.ok) {
            alert(studentsData.message || 'Unable to load students.');
            return;
        }

        const totalStudents = studentsData.students.total;

        document.getElementById('totalStudents').textContent =
            totalStudents;

        // Count active students on the current page only
        const activeStudents = studentsData.students.data.filter(
            student => student.status === 'active'
        ).length;

        document.getElementById('activeStudents').textContent =
            activeStudents;

            // =================================
// 5. DISPLAY STUDENTS IN TABLE
// =================================

function displayStudents(students) {

    const studentsTable = document.getElementById('studentsTable');
    const studentsMessage = document.getElementById('studentsMessage');

    studentsTable.innerHTML = '';

    if (students.length === 0) {
        studentsMessage.textContent = 'No students found.';
        return;
    }

    studentsMessage.textContent = 'Students loaded successfully.';

    students.forEach(student => {

        const row = document.createElement('tr');

        const values = [
            student.id,
            student.name,
            student.email,
            student.phone || '-',
            student.status
        ];

        values.forEach(value => {
            const cell = document.createElement('td');
            cell.textContent = value;
            row.appendChild(cell);
        });

        const actionCell = document.createElement('td');

        const viewButton = document.createElement('button');

        viewButton.textContent = 'View';
        viewButton.className = 'btn btn-sm btn-info';

        viewButton.addEventListener('click', function () {

            window.location.href = '/school-admin/students/' + student.id;

        });

        actionCell.appendChild(viewButton);
        row.appendChild(actionCell);

        studentsTable.appendChild(row);

    });
}

        // Display students from API response
        displayStudents(studentsData.students.data);
        // =================================
// 6. REFRESH STUDENT RECORDS
// =================================

document.getElementById('refreshStudentsBtn').addEventListener('click', async function () {

    const studentsMessage = document.getElementById('studentsMessage');

    studentsMessage.textContent = 'Refreshing students...';

    try {

        const response = await fetch('/api/school-admin/students', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });

        const data = await response.json();

        if (!response.ok) {
            studentsMessage.textContent = data.message || 'Unable to refresh students.';
            return;
        }

        // Update total students
        document.getElementById('totalStudents').textContent =
            data.students.total;

        // Update active students on the current page
        const activeCount = data.students.data.filter(
            student => student.status === 'active'
        ).length;

        document.getElementById('activeStudents').textContent =
            activeCount;

        // Display updated student records
        displayStudents(data.students.data);

    } catch (error) {

        console.error(error);
        studentsMessage.textContent = 'Unable to connect to the server.';

    }

});
        
        // =================================
        // 7. GET AND DISPLAY COURSES
        // =================================

        async function loadSchoolAdminCourses() {

            const coursesTable = document.getElementById('coursesTable');
            const coursesMessage = document.getElementById('coursesMessage');

            coursesTable.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        Loading courses...
                    </td>
                </tr>
            `;

            coursesMessage.textContent = '';

            try {

                const response = await fetch('/api/school-admin/courses', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.courses) {
                    throw new Error(data.message || 'Unable to load courses.');
                }

                const courses = data.courses.data || [];

                document.getElementById('totalCourses').textContent =
                    data.courses.total;

                if (courses.length === 0) {
                    coursesTable.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">
                                No courses found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                coursesTable.innerHTML = '';

                courses.forEach(course => {

                    const row = document.createElement('tr');

                    const values = [
                        course.id,
                        course.title,
                        course.course_code,
                        course.duration ?? 'N/A',
                        course.status
                    ];

                    values.forEach(value => {
                        const cell = document.createElement('td');
                        cell.textContent = value;
                        row.appendChild(cell);
                    });
                    const actionCell = document.createElement('td');

                    const viewButton = document.createElement('button');

                    viewButton.textContent = 'View Details';
                    viewButton.className = 'btn btn-sm btn-info';

                    viewButton.addEventListener('click', function () {
                        window.location.href = '/school-admin/courses/' + course.id;
                    });

                    actionCell.appendChild(viewButton);
                    row.appendChild(actionCell);
                    coursesTable.appendChild(row);

                });

                coursesMessage.textContent = 'Courses loaded successfully.';

            } catch (error) {

                console.error(error);

                coursesTable.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            ${error.message}
                        </td>
                    </tr>
                `;

                coursesMessage.textContent = error.message;

            }
        }

        // Load courses when dashboard opens
        loadSchoolAdminCourses();

        // Refresh courses when button is clicked
        document.getElementById('refreshCoursesBtn')
            .addEventListener('click', loadSchoolAdminCourses);

    } catch (error) {

        console.error(error);
        alert('Unable to connect to the server.');

    }

});
</script>
@endsection

