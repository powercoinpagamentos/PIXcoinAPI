<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\ConfiguracaoMaquina;
use Illuminate\Http\JsonResponse;

readonly class GetLittleMachine
{
    public function __construct(private string $code)
    {
    }

    public function run(): JsonResponse
    {
        $littleMachine = $this->getLittleMachine();
        if (empty($littleMachine)) {
            return new JsonResponse(['mensagem' => 'Maquina não encontrada'], 404);
        }

        return new JsonResponse(['maquina' => $littleMachine]);
    }

    private function getLittleMachine(): ?ConfiguracaoMaquina
    {
        return ConfiguracaoMaquina::query()
            ->where('codigo', $this->code)
            ->first();
    }
}
