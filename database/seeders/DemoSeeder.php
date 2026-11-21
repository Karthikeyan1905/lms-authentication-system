<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo school
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'greenvalley'],
            [
                'name' => 'Green Valley School',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'Asia/Kolkata',
                ],
            ]
        );

        // Super Admin
        User::updateOrCreate(
            ['email' => 'super@lms.test'],
            [
                'tenant_id' => null,
                'name' => 'Super Admin',
                'password' => 'Super@1234',
                'role' => 'super_admin',
                'status' => 'active',
                'failed_attempts' => 0,
                'mfa_enabled' => true,
            ]
        );

        // School Admin
        User::updateOrCreate(
            ['email' => 'admin@gv.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'School Admin',
                'password' => 'Admin@1234',
                'role' => 'school_admin',
                'status' => 'active',
                'failed_attempts' => 0,
                'mfa_enabled' => true,
            ]
        );

        // Teacher
        User::updateOrCreate(
            ['email' => 'teacher@gv.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Teacher',
                'password' => 'Teach@1234',
                'role' => 'teacher',
                'status' => 'active',
                'failed_attempts' => 0,
                'mfa_enabled' => false,
            ]
        );

        // Student
        User::updateOrCreate(
            ['email' => 'student@gv.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Student',
                'password' => 'Stud@1234',
                'role' => 'student',
                'status' => 'active',
                'failed_attempts' => 0,
                'mfa_enabled' => false,
            ]
        );

        $this->command->info('Demo school and users created successfully!');
    }
}