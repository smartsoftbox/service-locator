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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade'); // Usługa, do której subskrypcja się odnosi
            $table->enum('plan', ['free', 'premium'])->default('free'); // Typ subskrypcji: darmowa lub premium
            $table->timestamp('started_at'); // Data rozpoczęcia subskrypcji
            $table->timestamp('expires_at')->nullable(); // Data wygaśnięcia subskrypcji (może być NULL dla darmowych planów)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
