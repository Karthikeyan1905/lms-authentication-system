<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        // Step 1: Retrieve courses from the student's school
        $courses = Course::select(
            'id',
            'title',
            'course_code',
            'description',
            'duration',
            'status'
        )
            ->where('status', 'active')
            ->latest()
            ->paginate(10);

        // Step 2: Return the course list
        return response()->json([
            'message' => 'Courses retrieved successfully.',
            'courses' => $courses,
        ], 200);
    }


public function show($id)
{
    // Step 1: Find the active course by ID
    $course = Course::select(
        'id',
        'title',
        'course_code',
        'description',
        'duration',
        'status'
    )
        ->where('status', 'active')
        ->find($id);

    // Step 2: Check whether the course exists
    if (!$course) {
        return response()->json([
            'message' => 'Course not found.',
        ], 404);
    }

    // Step 3: Return course details
    return response()->json([
        'message' => 'Course retrieved successfully.',
        'course' => $course,
    ], 200);
}

}
