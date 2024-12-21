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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('maquina_id')->nullable();
            $table->string('valor');
            $table->string('mercadoPagoId')->nullable();
            $table->boolean('estornado');
            $table->string('motivo_estorno')->nullable();
            $table->string('tipo')->nullable();
            $table->string('taxas')->nullable();
            $table->uuid('cliente_id')->nullable();
            $table->string('operadora')->nullable();
            $table->timestamp('data')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('maquina_id')->references('id')->on('maquinas');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
