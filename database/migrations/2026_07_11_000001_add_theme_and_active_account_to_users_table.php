<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('theme_preference', ['dark', 'light'])->default('dark')->after('email');
            $table->foreignId('active_account_id')->nullable()->after('theme_preference');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_preference');
            $table->dropForeign(['active_account_id']);
            $table->dropColumn('active_account_id');
        });
    }
};
