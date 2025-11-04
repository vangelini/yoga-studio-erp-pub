<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->indexExists('membership_subscriptions', 'membership_user_season_unique')) {
            Schema::table('membership_subscriptions', function (Blueprint $table) {
                $table->unique(['user_id', 'season_start_year'], 'membership_user_season_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('membership_subscriptions', 'membership_user_season_unique')) {
            Schema::table('membership_subscriptions', function (Blueprint $table) {
                $table->dropUnique('membership_user_season_unique');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
