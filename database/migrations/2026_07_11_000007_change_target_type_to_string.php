<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->string('target_type', 20)->change();
        });

        DB::table('targets')->where('target_type', '5pct')->update(['target_type' => 'target_1']);
        DB::table('targets')->where('target_type', '10pct')->update(['target_type' => 'target_2']);
    }

    public function down(): void
    {
        DB::table('targets')->where('target_type', 'target_1')->update(['target_type' => '5pct']);
        DB::table('targets')->where('target_type', 'target_2')->update(['target_type' => '10pct']);

        Schema::table('targets', function (Blueprint $table) {
            $table->enum('target_type', ['5pct', '10pct'])->change();
        });
    }
};
