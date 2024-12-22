<?php

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

class CreateCustomer
{
    public function __construct(private array $data)
    {
    }

    public function run(): JsonResponse
    {
        $this->createCustomer();

        return response()->json(['message' => 'Cliente criado com sucesso!']);
    }

    private function createCustomer()
    {
        return Cliente::create($this->data);
    }
}
