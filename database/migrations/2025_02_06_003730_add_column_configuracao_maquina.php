<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracaoMaquina', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo');
            $table->string('operacao');
            $table->string('urlServidor');
            $table->string('webhook01');
            $table->string('webhook02');
            $table->string('rotaConsultaStatusMaq');
            $table->string('rotaConsultaAdimplencia');
            $table->string('idMaquina');
            $table->string('idCliente');
            $table->float('valor1');
            $table->float('valor2');
            $table->float('valor3');
            $table->float('valor4');
            $table->string('textoEmpresa');
            $table->string('corPrincipal');
            $table->string('corSecundaria');
            $table->float('minValue');
            $table->float('maxValue');
            $table->string('identificadorMaquininha');
            $table->string('serialMaquininha');
            $table->string('macaddressMaquininha');
            $table->string('operadora');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracaoMaquina');
    }
};
