<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArduinoController extends Controller
{
    public function executeCommand($maquinaId, $command): JsonResponse
    {
        $server = 'broker.hivemq.com';
        $port = 1883;
        $clientId = 'pixcoin-' . uniqid();

        $mqtt = new phpMQTT($server, $port, $clientId);

        if ($mqtt->connect()) {
            $topic = "comandos/$maquinaId";
            $message = "comando:$command";

            $mqtt->publish($topic, $message, 0);
            $mqtt->close();

            Log::info("[ArduinoController]: Envio de comando: $command para a máquina: $maquinaId");
            return response()->json(['status' => 'sucesso', 'message' => 'Comando enviado']);
        }

        return response()->json(['status' => 'error', 'message' => 'Falha na conexão MQTT'], 500);
    }
    public function getArduinoCode(string $machineId)
    {
        $firmwarePath = storage_path("app/public/$machineId/pixcoin.ino.bin");

        if (!file_exists($firmwarePath)) {
            return response()->json(['error' => 'Firmware não encontrado'], 404);
        }

        $fileSize = filesize($firmwarePath);

        if ($fileSize < 1000000 || $fileSize > 1500000) {
            return response()->json(['error' => 'Tamanho do firmware inválido'], 422);
        }

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        Log::info("[ArduinoController]: Obtenção de código para a máquina: $machineId");

        return response()->download($firmwarePath, 'pixcoin.ino.bin', $headers)
            ->deleteFileAfterSend(true);
    }
}
