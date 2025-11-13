<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->boolean('can_manage_courses')->default(false)->after('can_host_private');
            $table->boolean('can_manage_payments')->default(false)->after('can_manage_courses');
            $table->boolean('can_manage_students')->default(false)->after('can_manage_payments');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'can_manage_courses',
                'can_manage_payments',
                'can_manage_students',
            ]);
        });
    }
};
