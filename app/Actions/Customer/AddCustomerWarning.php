<?php
declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

readonly class AddCustomerWarning
{
    public function __construct(
        private string  $clientId,
        private ?string $message,
        private ?string $showForAll
    )
    {
    }

    public function run(): JsonResponse
    {
        $client = $this->getClient();
        if (empty($client)) {
            return new JsonResponse(['message' => 'Cliente não encontrado'], 404);
        }

        $this->updateCustomer();

        return new JsonResponse(['message' => 'Mensagem adicionada ao cliente ' . $client->nome], 200);
    }

    private function getClient(): ?Cliente
    {
        return Cliente::find($this->clientId);
    }

    private function updateCustomer(): void
    {
        if ($this->showForAll !== 'null') {
            Cliente::query()
                ->update(['aviso' => $this->message ?? null]);
            return;
        }

        Cliente::query()
            ->where('id', $this->clientId)
            ->update(['aviso' => $this->message ?? null]);
    }
}
