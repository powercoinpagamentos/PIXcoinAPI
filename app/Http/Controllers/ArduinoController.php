<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use GuzzleHttp\Client;
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
            // Evita loop de chamadas internas
            if (request()->query('ignore')) {
                return $this->serveFirmwareLocally($machineId);
            }

            // Chamada para o servidor local (PHP embutido rodando em localhost:8000)
            $client = new Client();
            $response = $client->get("http://localhost:8000/api/update-firmware/{$machineId}?ignore=true", [
                'stream' => true,
            ]);

            // Repassa os headers
            $headers = [];
            foreach ($response->getHeaders() as $name => $values) {
                $headers[$name] = implode(", ", $values);
            }

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->withHeaders($headers);

        } catch (\Exception $e) {
            Log::error("[ArduinoController]: Falha no proxy localh1ost: " . $e->getMessage());
            return response()->json(['error' => 'Falha no proxy'], 500);
        }
    }

    private function serveFirmwareLocally(string $machineId)
    {
        $firmwarePath = storage_path("app/public/{$machineId}/pixcoin.ino.bin");

        if (!file_exists($firmwarePath)) {
            Log::warning("[ArduinoController]: Arquivo não encontrado na maquina: $machineId");
            return response()->json(['error' => 'Firmware não encontrado'], 404, ['Content-Length' => 1]);
        }

        $fileSize = filesize($firmwarePath);
        if ($fileSize < 1_000_000 || $fileSize > 1_500_000) {
            Log::warning("[ArduinoController]: Tamanho inválido do firmware: $machineId");
            return response()->json(['error' => 'Tamanho do firmware inválido'], 422, ['Content-Length' => 1]);
        }

        Log::info("[ArduinoController]: Servindo firmware local para máquina: $machineId");

        return response(file_get_contents($firmwarePath), 200)
            ->withHeaders([
                'Content-Type' => 'application/x-binary',
                'Content-Length' => $fileSize,
                'Content-Disposition' => 'attachment; filename="pixcoin.ino.bin"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }
}
