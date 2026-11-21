<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function profile(Request $request)
    {
        // Get the currently authenticated student
        $student = $request->user();

        // Return student profile details
        return response()->json([
            'message' => 'Student profile retrieved successfully.',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'role' => $student->role,
                'status' => $student->status,
                'tenant_id' => $student->tenant_id,
                'email_verified_at' => $student->email_verified_at,
                'created_at' => $student->created_at,
            ],
        ], 200);
    }

    public function dashboard(Request $request)
        {
            // Get the currently logged-in student
            $student = $request->user();

            // Return dashboard information
            return response()->json([
                'message' => 'Welcome to Student Dashboard.',

                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'role' => $student->role,
                ],

                'dashboard' => [
                    'total_courses' => 0,
                    'attendance_percentage' => 0,
                    'pending_assignments' => 0,
                ],
            ], 200);
        }
}