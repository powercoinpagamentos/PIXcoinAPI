<?php

namespace Database\Seeders;

use App\Models\Pagamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CreatePaymentTest extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pagamento = Pagamento::where('operadora', 'TESTE')->first();
        if (!$pagamento) {
            Pagamento::create([
                'id' => Str::uuid(),
                'maquina_id' => 'fdd2096d-bfa4-44d0-be38-527466baece1',
                'valor' => '10',
                'mercadoPagoId' => '94976984449',
                'estornado' => 0,
                'motivo_estorno' => '',
                'tipo' => 'credit_card',
                'taxas' => '',
                'cliente_id' => '2a7e1906-0d5f-437e-a456-d8f7d950f654',
                'operadora' => '',
                'data' => now(),
            ]);
        }
    }
}
