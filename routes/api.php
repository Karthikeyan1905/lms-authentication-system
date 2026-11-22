
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Models\AuditLog;
use App\Http\Controllers\Api\SchoolAdmin\StudentController;
use App\Http\Controllers\Api\Student\StudentController as StudentProfileController;
use App\Http\Controllers\Api\SchoolAdmin\CourseController;
use App\Http\Controllers\Api\Student\CourseController as StudentCourseController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'LMS API is working!',
    ]);
});


Route::middleware(['auth:sanctum', 'tenant.access'])
    ->get('/tenant-check', function () {
        return response()->json([
            'message' => 'Tenant access granted successfully.',
        ]);
    });

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {

    // Step 1: Find the registered user
    $user = \App\Models\User::findOrFail($id);

    // Step 2: Validate the email hash
    if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
        return response()->json([
            'message' => 'Invalid verification link.',
        ], 403);
    }

    // Step 3: Mark email as verified
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();

        // Step 4: Create audit log
        AuditLog::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'event' => 'email_verified',
            'ip_address' => $request->ip(),
            'meta' => [
                'method' => 'email_verification',
            ],
        ]);
    }

    // Step 5: Return success response
    return response()->json([
        'message' => 'Email verified successfully.',
    ]);
})->middleware('signed:relative')->name('verification.verify');

Route::get('/password/reset/{token}', function (Request $request, $token) {
    return response()->json([
        'message' => 'Password reset link opened successfully.',
        'token' => $token,
        'email' => $request->query('email'),
    ]);
})->name('password.reset');

Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])
    ->get('/admin-dashboard', function (Request $request) {
        return response()->json([
            'message' => 'Welcome to the Admin Dashboard.',
            'user' => $request->user()->name,
            'role' => $request->user()->role,
        ]);
    });

// Super Admin APIs
Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('super-admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Super Admin Dashboard',
            ]);
        });
    });

// School Admin APIs
Route::middleware(['auth:sanctum', 'tenant.access', 'role:school_admin'])
    ->prefix('school-admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'School Admin Dashboard',
            ]);
        });

        // Student APIs
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::put('/students/{id}', [StudentController::class, 'update']);

        // Course APIs
        Route::post('/courses', [CourseController::class, 'store']);
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
    });

// Teacher APIs
Route::middleware(['auth:sanctum', 'tenant.access', 'role:teacher'])
    ->prefix('teacher')
    ->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Teacher Dashboard',
            ]);
        });
    });

// Student APIs
Route::middleware(['auth:sanctum', 'tenant.access', 'role:student'])
    ->prefix('student')
    ->group(function () {
        Route::get('/dashboard', [StudentProfileController::class, 'dashboard']);
        Route::get('/profile', [StudentProfileController::class, 'profile']);

        Route::get('/courses', [StudentCourseController::class, 'index']);
        Route::get('/courses/{id}', [StudentCourseController::class, 'show']);
    });

// Parent APIs
Route::middleware(['auth:sanctum', 'tenant.access', 'role:parent'])
    ->prefix('parent')
    ->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Parent Dashboard',
            ]);
        });
    });

// Authentication APIs
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
