<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        try {
            if (!request()->has('ignore')) {
                Log::info("[ArduinoController]: Redirecionando para localhost por causa do Content-Length");

                $url = "http://localhost:8000/update-firmware/{$machineId}?ignore=true";

                $response = Http::withHeaders([
                    'Accept' => 'application/octet-stream',
                ])->get($url);

                return response($response->body(), $response->status())
                    ->withHeaders([
                        'Content-Type' => $response->header('Content-Type', 'application/x-binary'),
                        'Content-Length' => $response->header('Content-Length', strlen($response->body())),
                        'Content-Disposition' => 'attachment; filename="pixcoin.ino.bin"',
                        'Cache-Control' => 'no-cache, no-store, must-revalidate',
                        'Pragma' => 'no-cache',
                        'Expires' => '0',
                    ]);
            }

            $firmwarePath = storage_path("app/public/$machineId/pixcoin.ino.bin");

            if (!file_exists($firmwarePath)) {
                Log::warning("[ArduinoController]: Arquivo não encontrado na máquina: $machineId");
                return response()->json(['error' => 'Firmware não encontrado'], 404, ['Content-Length' => 1]);
            }

            $fileSize = filesize($firmwarePath);
            if ($fileSize < 1_000_000 || $fileSize > 1_500_000) {
                Log::warning("[ArduinoController]: Tamanho de file inválido na máquina: $machineId");
                return response()->json(['error' => 'Tamanho do firmware inválido'], 422, ['Content-Length' => 1]);
            }

            Log::info("[ArduinoController]: Entregando firmware diretamente para máquina: $machineId");

            return response()->stream(function () use ($firmwarePath) {
                readfile($firmwarePath);
            }, 200, [
                'Content-Type' => 'application/x-binary',
                'Content-Length' => $fileSize,
                'Content-Disposition' => 'attachment; filename="pixcoin.ino.bin"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            Log::error("[ArduinoController]: Falha ao enviar código remotamente: " . $e->getMessage());
            return response()->json(['error' => 'Falha!!'], 500);
        }
    }


}
