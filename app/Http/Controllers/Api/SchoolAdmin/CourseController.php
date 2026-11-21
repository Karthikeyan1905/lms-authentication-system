<?php

namespace App\Http\Controllers\Api\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function store(Request $request)
    {
        // Step 1: Validate course details
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
        ]);

        // Step 2: Get logged-in School Admin
        $admin = $request->user();

        // Step 3: Create course under the admin's school
        $course = Course::create([
            'tenant_id' => $admin->tenant_id,
            'title' => $validated['title'],
            'course_code' => $validated['course_code'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'status' => 'active',
        ]);

        // Step 4: Return response
        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course,
        ], 201);
    }

    public function index()
{
    // Step 1: Retrieve all courses
    $courses = Course::select(
        'id',
        'title',
        'course_code',
        'description',
        'duration',
        'status',
        'tenant_id',
        'created_at'
    )
    ->latest()
    ->paginate(10);

    // Step 2: Return course list
    return response()->json([
        'message' => 'Courses retrieved successfully.',
        'courses' => $courses,
    ], 200);
}
}