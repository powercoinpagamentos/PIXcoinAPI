<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pessoa = Pessoa::where('email', 'krynevictor0736@gmail.com')->first();
        if (!$pessoa) {
            Pessoa::create([
                'id' => Str::uuid(),
                'nome' => 'Admin',
                'email' => 'krynevictor0736@gmail.com',
                'senha' => Hash::make('08825Brcc@'),
                'data_inclusao' => now(),
                'ultimo_acesso' => null,
            ]);
        }
    }
}
