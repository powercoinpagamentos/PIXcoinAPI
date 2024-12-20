<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Maquina;
use App\Models\Pessoa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CreateTestMachine extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maquina = Maquina::where('maquininha_serial', 'SERIAL_TESTE')->first();

        if (!$maquina) {
            $cliente = Cliente::where('email', 'rcbrinq@hotmail.com')->first();
            $pessoa = Pessoa::where('email', 'krynevictor0736@gmail.com')->first();

            Maquina::create([
                'id' => Str::uuid(),
                'pessoa_id' => $pessoa->id,
                'cliente_id' => $cliente->id,
                'nome' => 'Máquina 01 PIXCOIN',
                'descricao' => 'Máquina 01 pixCOIN',
                'store_id' => 60709386,
                'maquininha_serial' => 'SERIAL_TESTE',
                'estoque' => 0,
                'valor_do_pix' => '0',
                'valor_do_pulso' => '1',
                'data_inclusao' => now(),
                'ultimo_pagamento_recebido' => now(),
                'ultima_requisicao' => now(),
                'disabled' => false,
            ]);
        }
    }
}
