<?php

namespace App\Actions\Machine;

use App\Models\Cliente;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

readonly class DisabledMachine
{
    public function __construct(private string $id)
    {
    }

    public function run(): JsonResponse
    {
        $data = $this->disableMachines();

        return response()->json($data);
    }

    private function disableMachines()
    {
        return DB::transaction(function () {
            $cliente = Cliente::findOrFail($this->id);

            $disabled = !$cliente->maquinas()->first()->disabled;

            $cliente->maquinas()->update(['disabled' => $disabled]);

            if ($disabled) {
                (new EmailService())->sendDisabledMachineEmail($cliente->email, $cliente->nome);
            } else {
                (new EmailService())->sendEnableMachineEmail($cliente->email, $cliente->nome);
            }

            return [
                'message' => 'Todas as máquinas foram ' . ($disabled ? 'desabilitadas' : 'habilitadas'),
            ];
        });
    }
}
