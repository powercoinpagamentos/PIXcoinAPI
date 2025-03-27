<?php
declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

readonly class AddEmployee
{
    public function run(array $data): JsonResponse
    {
        $parentCustomer = Cliente::find($data['parent_id']);

        $data = array_merge($data, [
            'pessoa_id' => $parentCustomer->pessoa_id,
            'data_inclusao' => now(),
            'ativo' => 1,
        ]);

        Cliente::create($data);

        return new JsonResponse(['message' => 'Funcionários criado']);
    }
}
