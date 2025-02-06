<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\Maquina;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class MachineIsOnline
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();
        if (!$machine) {
            return response()->json(['msg' => 'Máquina não encontrada'], 404);
        }

        if (!$machine->ultima_requisicao) {
            return response()->json(['msg' => 'MÁQUINA OFFLINE! Sem registro de última requisição.'], 400);
        }

        $timeFromLastRequest = abs($this->tempoOffline(Carbon::parse($machine->ultima_requisicao)));

        $status = $timeFromLastRequest > 5 ? 'OFFLINE' : 'ONLINE';

        return response()->json(['idMaquina' => $this->machineId, 'status' => $status]);
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::query()->find($this->machineId);
    }

    public function tempoOffline(Carbon $data): float
    {
        return Carbon::now()->diffInSeconds($data);
    }
}
