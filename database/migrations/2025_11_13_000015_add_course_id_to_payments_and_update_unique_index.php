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
            if (!Schema::hasColumn('payments', 'course_id')) {
                $table->foreignId('course_id')
                    ->nullable()
                    ->after('payable_id')
                    ->constrained('courses')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('payments', 'course_id_key')) {
                $table->unsignedBigInteger('course_id_key')
                    ->storedAs('COALESCE(course_id, 0)')
                    ->after('course_id');
            }
        });

        if (!$this->indexExists('payments', 'payments_user_id_index')) {
            DB::statement('CREATE INDEX payments_user_id_index ON payments(user_id)');
        }

        $this->dropIndexIfExists('payments', 'payments_user_year_type_unique');
        $this->dropIndexIfExists('payments', 'payments_user_year_type_course_unique');

        Schema::table('payments', function (Blueprint $table) {
            $table->unique(['user_id', 'receipt_year', 'type', 'course_id_key', 'due_date'], 'payments_user_year_type_course_unique');
        });
    }

    public function down(): void
    {
        if ($this->indexExists('payments', 'payments_user_year_type_course_unique')) {
            DB::statement('ALTER TABLE `payments` DROP INDEX `payments_user_year_type_course_unique`');
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'course_id_key')) {
                $table->dropColumn('course_id_key');
            }

            if (Schema::hasColumn('payments', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
        });

        if (!$this->indexExists('payments', 'payments_user_year_type_unique')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unique(['user_id', 'receipt_year', 'type'], 'payments_user_year_type_unique');
            });
        }

        if ($this->indexExists('payments', 'payments_user_id_index')) {
            DB::statement('ALTER TABLE `payments` DROP INDEX `payments_user_id_index`');
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $index));
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();

        return $result;
    }
};
