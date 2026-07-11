<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->decimal('target_1_pct', 5, 2)->default(5.00);
            $table->decimal('target_2_pct', 5, 2)->default(10.00);
            $table->json('off_days')->default('["saturday","sunday"]');
            $table->timestamps();

            $table->unique('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_rules');
    }
};
