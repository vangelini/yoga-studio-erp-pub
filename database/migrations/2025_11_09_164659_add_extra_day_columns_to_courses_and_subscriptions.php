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
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'allows_extra_day')) {
                $table->boolean('allows_extra_day')
                    ->default(false)
                    ->after('annual_price');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'extra_course_id')) {
                $table->foreignId('extra_course_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('courses')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('subscriptions', 'extra_course_plan_amount')) {
                $table->decimal('extra_course_plan_amount', 10, 2)
                    ->nullable()
                    ->after('plan_amount');
            }

            if (!Schema::hasColumn('subscriptions', 'extra_course_snapshot')) {
                $table->json('extra_course_snapshot')
                    ->nullable()
                    ->after('extra_course_plan_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'extra_course_snapshot')) {
                $table->dropColumn('extra_course_snapshot');
            }

            if (Schema::hasColumn('subscriptions', 'extra_course_plan_amount')) {
                $table->dropColumn('extra_course_plan_amount');
            }

            if (Schema::hasColumn('subscriptions', 'extra_course_id')) {
                $table->dropConstrainedForeignId('extra_course_id');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'allows_extra_day')) {
                $table->dropColumn('allows_extra_day');
            }
        });
    }
};
