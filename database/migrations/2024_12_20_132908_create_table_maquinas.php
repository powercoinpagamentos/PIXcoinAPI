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
        Schema::create('maquinas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id')->nullable();
            $table->uuid('cliente_id')->nullable();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->string('store_id')->nullable();
            $table->string('maquininha_serial')->nullable();
            $table->integer('estoque')->nullable();
            $table->string('valor_do_pix');
            $table->string('valorDoPulso')->default('1');
            $table->timestamp('data_inclusao')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('ultimo_pagamento_recebido')->nullable();
            $table->timestamp('ultima_requisicao')->nullable();
            $table->boolean('disabled')->default(false);
            $table->foreign('pessoa_id')->references('id')->on('pessoas');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinas');
    }
};
