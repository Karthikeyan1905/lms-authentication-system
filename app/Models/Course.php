<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Course extends Model
{
    protected $fillable = [
        'tenant_id',
        'title',
        'course_code',
        'description',
        'duration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
        ];
    }

    // Course belongs to a school
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Automatically restrict courses to the logged-in user's school
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}