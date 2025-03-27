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
        Schema::table('maquinas', function (Blueprint $table) {
            $table->boolean('bonusPlay')->nullable()->default(false);
            $table->integer('moves')->nullable()->default(0);
            $table->integer('moves_count')->nullable()->default(0);
            $table->integer('bonus')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maquinas', function (Blueprint $table) {
            $table->dropColumn([
                'bonusPlay',
                'moves',
                'moves_count',
                'bonus',
            ]);
        });
    }
};
