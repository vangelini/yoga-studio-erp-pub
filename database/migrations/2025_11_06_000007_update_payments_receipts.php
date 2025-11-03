<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'receipt_number')) {
                $table->unsignedInteger('receipt_number')->nullable()->after('method');
            }
            if (!Schema::hasColumn('payments', 'receipt_year')) {
                $table->unsignedInteger('receipt_year')->nullable()->after('receipt_number');
            }
            if (!Schema::hasColumn('payments', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('receipt_year');
            }
        });

        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','waived') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'status_reason')) {
                $table->dropColumn('status_reason');
            }
            if (Schema::hasColumn('payments', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
            if (Schema::hasColumn('payments', 'receipt_year')) {
                $table->dropColumn('receipt_year');
            }
            if (Schema::hasColumn('payments', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
        });

        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid') NOT NULL DEFAULT 'pending'");
    }
};
