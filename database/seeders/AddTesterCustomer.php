<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Pessoa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddTesterCustomer extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cliente = Cliente::where('email', 'rcbrinq@hotmail.com')->first();
        if (!$cliente) {
            $pessoa = Pessoa::where('email', 'krynevictor0736@gmail.com')->first();
            Cliente::create([
                'id' => Str::uuid(),
                'nome' => 'Renato',
                'email' => 'rcbrinq@hotmail.com',
                'senha' => Hash::make('08825brc'),
                'data_inclusao' => now(),
                'ultimo_acesso' => null,
                'pessoa_id' => $pessoa->id
            ]);
        }
    }
}
