<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('residenza_citta')->nullable()->after('telephone');
            $table->string('residenza_provincia', 100)->nullable()->after('residenza_citta');
            $table->string('residenza_stato', 150)->nullable()->after('residenza_provincia');
            $table->string('residenza_via')->nullable()->after('residenza_stato');
            $table->string('residenza_numero_civico', 20)->nullable()->after('residenza_via');
            $table->string('codice_fiscale', 32)->nullable()->after('residenza_numero_civico');
            $table->string('luogo_nascita')->nullable()->after('codice_fiscale');
            $table->date('data_nascita')->nullable()->after('luogo_nascita');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'residenza_citta',
                'residenza_provincia',
                'residenza_stato',
                'residenza_via',
                'residenza_numero_civico',
                'codice_fiscale',
                'luogo_nascita',
                'data_nascita',
            ]);
        });
    }
};
