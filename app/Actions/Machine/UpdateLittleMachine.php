<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\ConfiguracaoMaquina;
use Illuminate\Http\JsonResponse;

readonly class UpdateLittleMachine
{
    public function __construct(
        private array $machineData,
        private string $code
    )
    {
    }

    public function run(): JsonResponse
    {
        $updated = ConfiguracaoMaquina::query()
            ->where('codigo', $this->code)
            ->update($this->machineData);

        if ($updated) {
            return response()->json(['message' => 'Máquina atualizada.']);
        }

        return response()->json(['error' => 'Máquina não encontrada'], 404);
    }
}
