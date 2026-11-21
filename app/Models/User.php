<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmail;
use App\Models\Scopes\TenantScope;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'role',
        'status',
        'failed_attempts',
        'locked_until',
        'mfa_enabled',
        'mfa_method',
        'onboarded_at',
        'otp_failed_attempts',
        'otp_locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'locked_until' => 'datetime',
        'otp_failed_attempts' => 'integer',
        'otp_locked_until' => 'datetime',
    ];
}

    // A user belongs to one school
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

}
