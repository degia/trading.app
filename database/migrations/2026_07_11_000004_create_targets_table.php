<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_log_id')->nullable()->constrained('daily_logs')->nullOnDelete();
            $table->enum('target_type', ['5pct', '10pct']);
            $table->decimal('target_amount', 12, 2);
            $table->decimal('running_amount', 12, 2);
            $table->decimal('target_closing', 12, 2);
            $table->decimal('status', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
