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
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('status_reason');
            $table->enum('status', ['pending', 'paid'])->default('pending')->change();
        });
    }
};
