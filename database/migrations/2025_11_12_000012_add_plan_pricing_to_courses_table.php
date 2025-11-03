<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'monthly_price')) {
                $table->decimal('monthly_price', 8, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn('courses', 'quarterly_price')) {
                $table->decimal('quarterly_price', 8, 2)->nullable()->after('monthly_price');
            }

            if (!Schema::hasColumn('courses', 'annual_price')) {
                $table->decimal('annual_price', 8, 2)->nullable()->after('quarterly_price');
            }
        });

        if (Schema::hasColumn('courses', 'price')) {
            DB::table('courses')
                ->whereNull('monthly_price')
                ->update([
                    'monthly_price' => DB::raw('price'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'annual_price')) {
                $table->dropColumn('annual_price');
            }

            if (Schema::hasColumn('courses', 'quarterly_price')) {
                $table->dropColumn('quarterly_price');
            }

            if (Schema::hasColumn('courses', 'monthly_price')) {
                $table->dropColumn('monthly_price');
            }
        });
    }
};
