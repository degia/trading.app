<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->enum('status', ['profit', 'loss', 'day_off', 'pending']);
            $table->decimal('balance', 12, 2);
            $table->decimal('daily_percent', 5, 2)->nullable();
            $table->decimal('profit_amount', 12, 2)->default(0);
            $table->decimal('loss_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
