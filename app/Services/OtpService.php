<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\AuditLog;

class OtpService
{
    public function generate(User $user): string
    {
        // Step 1: Generate a random 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Step 2: Store the hashed OTP in database
        OtpCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);

        // Step 3: Send OTP to user's email
        Mail::to($user->email)->send(new OtpMail($otp));

        // Step 4: Return OTP
        return $otp;
    }


        

public function verify(User $user, string $otp): bool
{
    // Step 1: Check whether the user is already locked
   if ($user->otp_locked_until) {

    // Lockout is still active
    if (now()->lessThan($user->otp_locked_until)) {
        return false;
    }

    // Lockout has expired - reset failed attempts
    $user->update([
        'otp_failed_attempts' => 0,
        'otp_locked_until' => null,
    ]);
        AuditLog::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'event' => 'otp_lockout_expired',
            'ip_address' => request()->ip(),
            'meta' => [
                'message' => 'OTP lockout expired successfully.',
            ],
        ]);
    // Refresh user data
    $user->refresh();
}

    // Step 2: Get the latest unused OTP
    $record = OtpCode::where('user_id', $user->id)
        ->whereNull('used_at')
        ->latest()
        ->first();

    if (!$record) {
        return false;
    }

    // Step 3: Check OTP expiration
    if (now()->greaterThan($record->expires_at)) {
        return false;
    }

    // Step 4: Check OTP attempts
    if ($user->otp_failed_attempts >= 3) {
        return false;
    }

    // Step 5: Verify the submitted OTP
    if (!Hash::check($otp, $record->code)) {

    // Step 1: Increase the user's failed OTP attempts
    $user->increment('otp_failed_attempts');

    // Step 2: Increase attempts for this OTP record
    $record->increment('attempts');

    // Step 3: Get the latest failed attempt count
    $failedAttempts = $user->fresh()->otp_failed_attempts;

    // Step 4: Save failed OTP event in audit_logs
    AuditLog::create([
        'user_id' => $user->id,
        'tenant_id' => $user->tenant_id,
        'event' => 'otp_verification_failed',
        'ip_address' => request()->ip(),
        'meta' => [
            'failed_attempts' => $failedAttempts,
        ],
    ]);

    // Step 5: Lock account after 3 failures
    if ($failedAttempts >= 3) {

    // Step 1: Lock the user for 30 minutes
    $user->update([
        'otp_locked_until' => now()->addMinutes(30),
    ]);

    // Step 2: Record the lockout event
    AuditLog::create([
        'user_id' => $user->id,
        'tenant_id' => $user->tenant_id,
        'event' => 'otp_lockout',
        'ip_address' => request()->ip(),
        'meta' => [
            'failed_attempts' => $failedAttempts,
            'lockout_minutes' => 30,
        ],
    ]);
}

    return false;
}
// Step 7: Mark OTP as used
$record->update([
    'used_at' => now(),
]);

// Step 8: Reset failed attempts after success
$user->update([
    'otp_failed_attempts' => 0,
    'otp_locked_until' => null,
]);

// Step 9: Record successful OTP verification
AuditLog::create([
    'user_id' => $user->id,
    'tenant_id' => $user->tenant_id,
    'event' => 'otp_verified',
    'ip_address' => request()->ip(),
    'meta' => [
        'method' => 'email',
        'status' => 'success',
    ],
]);

return true;


}
}