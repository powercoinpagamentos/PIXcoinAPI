<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\ConfiguracaoMaquina;
use Illuminate\Http\JsonResponse;

readonly class CreateLittleMachine
{
    public function __construct(private array $data)
    {
    }

    public function run(): JsonResponse
    {
        $littleMachine = $this->createLittleMachine();
        return new JsonResponse(['mensagem' => 'Maquininha cadastrada com sucesso!', 'novaMaquina' => $littleMachine]);
    }

    private function createLittleMachine(): ConfiguracaoMaquina
    {
        return ConfiguracaoMaquina::create($this->data);
    }
}
