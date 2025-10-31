<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('profile_picture_url')->nullable();
            $table->text('bio')->nullable();
            $table->json('specializations')->default(DB::raw('(JSON_ARRAY())'));
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price', 8, 2)->default(0);
            $table->text('speciality_description')->nullable();
            $table->json('gallery')->default(DB::raw('(JSON_ARRAY())'));
            $table->timestamps();
        });

        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('day_of_week');
            $table->time('time');
            $table->timestamps();
        });

        Schema::create('teacher_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('slot_date');
            $table->time('slot_time');
            $table->boolean('is_booked')->default(false);
            $table->foreignId('booked_by_client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'slot_date', 'slot_time'], 'teacher_slot_unique');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('availability_id')->constrained('teacher_availability')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();

            $table->unique(['client_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('teacher_availability');
        Schema::dropIfExists('course_schedules');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('users');
    }
};
