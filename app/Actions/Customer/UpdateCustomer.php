<?php

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

readonly class UpdateCustomer
{
    public function __construct(private string $customerId, private array $customerData)
    {
    }

    public function run(): JsonResponse
    {
        $updated = $this->updateCustomer();

        if ($updated) {
            return response()->json(['message' => 'Cliente atualizado com sucesso!']);
        }

        return response()->json(['error' => 'Falha ao atualizar o cliente!'], 500);
    }

    private function updateCustomer(): int
    {
        return Cliente::query()
            ->where('id', $this->customerId)
            ->update($this->customerData);
    }
}
