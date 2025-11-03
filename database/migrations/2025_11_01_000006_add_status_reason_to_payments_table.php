<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending')->change();
            $table->string('status_reason')->nullable()->after('status');
            $table->unsignedInteger('receipt_number')->nullable()->after('method');
            $table->unsignedInteger('receipt_year')->nullable()->after('receipt_number');
            $table->string('receipt_path')->nullable()->after('receipt_year');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['status_reason', 'receipt_number', 'receipt_year', 'receipt_path']);
            $table->enum('status', ['pending', 'paid'])->default('pending')->change();
        });
    }
};
