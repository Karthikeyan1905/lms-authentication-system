<?php

namespace App\Http\Controllers\Api\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

    class StudentController extends Controller
    {
        public function index(Request $request)
        {
            // Get students belonging to the logged-in admin's school
            $students = User::where('role', 'student')
                ->select(
                    'id',
                    'name',
                    'email',
                    'phone',
                    'status',
                    'tenant_id',
                    'created_at'
                )
                ->latest()
                ->paginate(10);

            return response()->json([
                'message' => 'Students retrieved successfully.',
                'students' => $students,
            ], 200);
        }

        public function show($id)
    {
        // Step 1: Find t   he student by ID
        $student = User::where('role', 'student')
            ->where('id', $id)
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'status',
                'tenant_id',
                'email_verified_at',
                'created_at'
            )
            ->first();

        // Step 2: Check whether the student exists
        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        // Step 3: Return student details
        return response()->json([
            'message' => 'Student details retrieved successfully.',
            'student' => $student,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // Step 1: Find the student
        $student = User::where('role', 'student')
            ->where('id', $id)
            ->first();

        // Step 2: Check whether the student exists
        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        // Step 3: Validate updated details
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $student->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        // Step 4: Update student details
        $student->update($validated);

        // Step 5: Return updated details
        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'status' => $student->status,
                'tenant_id' => $student->tenant_id,
            ],
        ], 200);
    }


    public function store(Request $request)
    {
        // Step 1: Validate student details
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/',
            ],
        ]);

        // Step 2: Get the logged-in School Admin
        $admin = $request->user();

        // Step 3: Create the student account
        $student = User::create([
            'tenant_id' => $admin->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'student',
            'status' => 'active',
            'failed_attempts' => 0,
            'mfa_enabled' => false,
            'mfa_method' => 'email',
        ]);

        // Step 4: Send email verification notification
        event(new Registered($student));

        // Step 5: Return response
        return response()->json([
            'message' => 'Student created successfully. Email verification required.',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'role' => $student->role,
                'tenant_id' => $student->tenant_id,
            ],
        ], 201);
    }

    }