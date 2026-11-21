<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('courses', function (Blueprint $table) {
        $table->id();

        // School / Tenant relationship
        $table->foreignId('tenant_id')
            ->constrained('tenants')
            ->cascadeOnDelete();

        // Course information
        $table->string('title');
        $table->string('course_code');
        $table->text('description')->nullable();

        // Course details
        $table->unsignedInteger('duration')->nullable();
        $table->string('status')->default('active');

        $table->timestamps();

        // Course code must be unique within each school
        $table->unique(['tenant_id', 'course_code']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
