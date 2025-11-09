<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'extra_day_discount_percent')) {
                $table->decimal('extra_day_discount_percent', 5, 2)
                    ->default(0)
                    ->after('allows_extra_day');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'extra_day_discount_percent')) {
                $table->dropColumn('extra_day_discount_percent');
            }
        });
    }
};
