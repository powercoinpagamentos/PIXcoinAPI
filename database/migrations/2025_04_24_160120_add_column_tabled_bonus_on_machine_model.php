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
            $table->boolean('tabledBonus')->nullable()->default(false);
            $table->integer('bonus_five')->nullable()->default(0);
            $table->integer('bonus_ten')->nullable()->default(0);
            $table->integer('bonus_twenty')->nullable()->default(0);
            $table->integer('bonus_fifty')->nullable()->default(0);
            $table->integer('bonus_hundred')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maquinas', function (Blueprint $table) {
            $table->dropColumn([
                'tabledBonus',
                'bonus_five',
                'bonus_ten',
                'bonus_twenty',
                'bonus_fifty',
                'bonus_hundred',
            ]);
        });
    }
};
