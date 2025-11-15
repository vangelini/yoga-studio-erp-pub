<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('pricing_mode')->default('block')->after('annual_price');
            $table->unsignedInteger('max_enrollments')->nullable()->after('pricing_mode');
            $table->json('lesson_pricing')->nullable()->after('max_enrollments');
        });

        Schema::table('course_schedules', function (Blueprint $table) {
            $table->unsignedInteger('capacity')->nullable()->after('time');
        });

        Schema::create('subscription_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('course_schedule_id')->nullable()->constrained('course_schedules')->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_lessons');

        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['pricing_mode', 'max_enrollments', 'lesson_pricing']);
        });
    }
};
