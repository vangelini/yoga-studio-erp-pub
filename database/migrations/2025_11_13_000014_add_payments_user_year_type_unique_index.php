<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'receipt_year')) {
                return;
            }

            $table->unique(['user_id', 'receipt_year', 'type'], 'payments_user_year_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'receipt_year')) {
                $table->dropUnique('payments_user_year_type_unique');
            }
        });
    }
};
