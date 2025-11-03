<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'plan_type')) {
                $table->string('plan_type', 20)->default('monthly')->after('auto_renew');
            }

            if (!Schema::hasColumn('subscriptions', 'plan_amount')) {
                $table->decimal('plan_amount', 10, 2)->default(0)->after('plan_type');
            }

            if (!Schema::hasColumn('subscriptions', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        if (Schema::hasColumn('subscriptions', 'plan_type') && Schema::hasColumn('subscriptions', 'plan_amount')) {
            $courses = DB::table('courses')
                ->select('id', 'price', 'monthly_price')
                ->get()
                ->keyBy('id');

            DB::table('subscriptions')
                ->orderBy('id')
                ->chunkById(100, function ($subscriptions) use ($courses) {
                    foreach ($subscriptions as $subscription) {
                        $course = $courses->get($subscription->course_id);
                        $amount = 0;

                        if ($course) {
                            $amount = $course->monthly_price ?? $course->price ?? 0;
                        }

                        DB::table('subscriptions')
                            ->where('id', $subscription->id)
                            ->update([
                                'plan_type' => 'monthly',
                                'plan_amount' => $amount,
                            ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'end_date')) {
                $table->dropColumn('end_date');
            }

            if (Schema::hasColumn('subscriptions', 'plan_amount')) {
                $table->dropColumn('plan_amount');
            }

            if (Schema::hasColumn('subscriptions', 'plan_type')) {
                $table->dropColumn('plan_type');
            }
        });
    }
};
