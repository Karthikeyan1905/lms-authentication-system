<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Services\OtpService;

class AuthController extends Controller
{
    public function register(Request $request)
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

            'tenant_slug' => 'required|string|exists:tenants,slug',
        ]);

        // Step 2: Find the active school
        $tenant = Tenant::where('slug', $validated['tenant_slug'])
            ->where('status', 'active')
            ->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'The selected school is not active.',
            ], 422);
        }

        // Step 3: Create the student account
        $user = User::create([
            'tenant_id' => $tenant->id,
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

            event(new Registered($user));

        // Step 4: Return success response
        return response()->json([
            'message' => 'Student registered successfully. Please verify your email.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
            ],
        ], 201);
    }
    public function login(Request $request, OtpService $otpService)
{
    // Step 1: Validate input
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Step 2: Find user
    $user = User::where('email', $validated['email'])->first();

    if (!$user) {
        return response()->json([
            'message' => 'Invalid email or password.'
        ], 401);
    }

    // Step 3: Check account status
    if ($user->status !== 'active') {
        return response()->json([
            'message' => 'Invalid email or password.'
        ], 401);
    }

    // Step 4: Check account lock
    if ($user->locked_until && $user->locked_until->isFuture()) {
        return response()->json([
            'message' => 'Account locked. Try again later.',
            'locked_until' => $user->locked_until,
        ], 423);
    }

    // Step 5: Reset expired lock
    if ($user->locked_until && $user->locked_until->isPast()) {
        $user->failed_attempts = 0;
        $user->locked_until = null;
        $user->save();
    }

    // Step 6: Verify password
    if (!\Illuminate\Support\Facades\Hash::check(
        $validated['password'],
        $user->password
    )) {
        $user->failed_attempts++;

        if ($user->failed_attempts >= 5) {
            $user->locked_until = now()->addMinutes(30);
        }

        $user->save();

        return response()->json([
            'message' => $user->locked_until
                ? 'Account locked for 30 minutes.'
                : 'Invalid email or password.',
            'failed_attempts' => $user->failed_attempts,
        ], $user->locked_until ? 423 : 401);
    }

    // Step 7: Check email verification
    if (!$user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Please verify your email before logging in.'
        ], 403);
    }

    // Step 8: Reset failed login attempts
    $user->failed_attempts = 0;
    $user->locked_until = null;
    $user->save();

    // Step 9: Determine whether MFA is required
    $mfaRequired = in_array($user->role, [
        'super_admin',
        'school_admin'
    ], true) || (
        $user->role === 'student' && $user->mfa_enabled
    );

    // Step 10: Generate and send OTP when MFA is required
    if ($mfaRequired) {

        $otpService->generate($user);

        return response()->json([
            'message' => 'OTP sent to your registered email address.',
            'mfa_required' => true,
            'user_id' => $user->id,
        ], 200);
    }

    // Step 11: Generate access token when MFA is not required
    $token = $user->createToken('lms-access-token')->plainTextToken;

    // Step 12: Return successful login response
    return response()->json([
        'message' => 'Login successful.',
        'mfa_required' => false,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'tenant_id' => $user->tenant_id,
        ],
    ], 200);
}


        public function verifyOtp(Request $request, OtpService $otpService)
        {
            // Step 1: Validate input
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'otp' => 'required|string|digits:6',
            ]);

            // Step 2: Find user
            $user = User::find($validated['user_id']);

            // Step 3: Check account status
            if (!$user || $user->status !== 'active') {
                return response()->json([
                    'message' => 'Invalid account.'
                ], 401);
            }

            // Step 4: Verify OTP
            $verified = $otpService->verify(
                $user,
                $validated['otp']
            );

            if (!$verified) {
                return response()->json([
                    'message' => 'Invalid or expired OTP.'
                ], 422);
            }

            // Step 5: Generate access token
            $token = $user->createToken('lms-access-token')->plainTextToken;

            // Step 6: Return successful login response
            return response()->json([
                'message' => 'OTP verified successfully. Login successful.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'tenant_id' => $user->tenant_id,
                ],
            ], 200);
        }


        public function forgotPassword(Request $request)
        {
            // Step 1: Validate email
            $request->validate([
                'email' => 'required|email',
            ]);

            // Step 2: Send password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Step 3: Return response
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link sent successfully.'
                ], 200);
            }

            return response()->json([
            'message' => 'Unable to send password reset link.',
            'debug_status' => $status,
        ], 400);
        }

        public function resetPassword(Request $request)
        {
            // Step 1: Validate the input
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/',
                ],
            ]);
            // Step 2: Verify token and reset password
            $status = Password::reset(
                $request->only(
                    'email',
                    'password',
                    'password_confirmation',
                    'token'
                ),
                function ($user) use ($request) {

                    // Step 3: Assign the new password
                    $user->password = $request->password;

                    // Step 4: Generate a new remember token
                    $user->setRememberToken(Str::random(60));

                    // Step 5: Save the updated user
                    $user->save();

                    AuditLog::create([
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'event' => 'password_reset',
                    'ip_address' => $request->ip(),
                    'meta' => [
                        'method' => 'forgot_password',
                    ],
                ]);
                }
            );

            // Step 6: Return the result
            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password reset successfully.'
                ], 200);
            }

            return response()->json([
                'message' => 'Unable to reset password.',
                'status' => $status,
            ], 400);
        }
        public function logout(Request $request)
        {
            // Delete the current access token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout successful.'
            ], 200);
        }
        public function me(Request $request)
        {
            $user = $request->user();

            return response()->json([
                'message' => 'User profile retrieved successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'tenant_id' => $user->tenant_id,
                ],
            ], 200);
        }
    }
