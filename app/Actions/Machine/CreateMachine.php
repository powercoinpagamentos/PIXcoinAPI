<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

readonly class CreateMachine
{
    public function __construct(private array $data)
    {
    }

    public function run(): JsonResponse
    {
        $this->createMachine();

        return response()->json(['message' => 'Máquina criada com sucesso!']);
    }

    private function createMachine(): void
    {
        Maquina::create($this->data);
    }
}
