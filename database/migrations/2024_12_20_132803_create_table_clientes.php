<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha');
            $table->string('mercado_pago_token')->nullable();
            $table->string('pagbank_email')->nullable();
            $table->string('pagbank_token')->nullable();
            $table->uuid('pessoa_id');
            $table->timestamp('data_inclusao')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('ultimo_acesso')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('data_vencimento')->nullable();
            $table->foreign('pessoa_id')->references('id')->on('pessoas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
