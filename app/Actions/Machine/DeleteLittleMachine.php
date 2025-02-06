<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\ConfiguracaoMaquina;
use Illuminate\Http\JsonResponse;

readonly class DeleteLittleMachine
{
    public function __construct(private string $code)
    {
    }

    public function run(): JsonResponse
    {
        ConfiguracaoMaquina::query()
            ->where('codigo', $this->code)
            ->delete();

        return new JsonResponse(['mensagem' => 'Maquina excluída com sucesso']);
    }
}
