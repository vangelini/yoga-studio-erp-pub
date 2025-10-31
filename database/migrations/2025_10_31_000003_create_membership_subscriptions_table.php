<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('season_start_year'); // es. 2024 per stagione 2024/2025
            $table->date('starts_at');
            $table->date('ends_at');
            $table->enum('status', ['pending', 'active', 'expired'])->default('pending');
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'season_start_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_subscriptions');
    }
};
