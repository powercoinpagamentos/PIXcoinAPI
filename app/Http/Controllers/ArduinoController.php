<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
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
    public function getArduinoCode(string $machineId): StreamedResponse|JsonResponse
    {
        try {
            $firmwarePath = storage_path("app/public/$machineId/pixcoin.ino.bin");

            if (!file_exists($firmwarePath)) {
                Log::warning("[ArduinoController]: Arquivo não encontrado na maquina: $machineId");
                return response()->json(['error' => 'Firmware não encontrado'], 404);
            }

            $fileSize = filesize($firmwarePath);

            if ($fileSize < 1_000_000 || $fileSize > 1_500_000) {
                Log::warning("[ArduinoController]: Tamanho de file inválido na maquina: $machineId");
                return response()->json(['error' => 'Tamanho do firmware inválido'], 422);
            }

            $headers = [
                'Content-Type' => 'application/x-binary',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Connection' => 'keep-alive',
                'Content-Disposition' => 'attachment; filename="pixcoin.ino.bin"',
            ];

            Log::info("[ArduinoController]: Obtenção de código para a máquina: $machineId");
            Log::info("[ArduinoController]: Permissões do arquivo: " . decoct(fileperms($firmwarePath) & 0777));

            $response = response()->stream(function () use ($firmwarePath) {
                if (ob_get_level()) {
                    ob_end_clean();
                }

                $handle = fopen($firmwarePath, 'rb');
                if (!$handle) {
                    Log::error("[ArduinoController]: Falha ao abrir arquivo $firmwarePath");
                    return;
                }

                while (!feof($handle)) {
                    echo fread($handle, 8192);
                    flush();
                }

                fclose($handle);
                Log::info("[ArduinoController]: Transmissão concluída para $firmwarePath");
            }, 200, $headers);

            // ⚠️ Registra uma função para deletar o arquivo após a resposta
            register_shutdown_function(function () use ($firmwarePath, $machineId) {
                if (file_exists($firmwarePath)) {
                    @unlink($firmwarePath);
                    $dir = dirname($firmwarePath);
                    if (is_dir($dir)) {
                        @rmdir($dir);
                    }
                    Log::info("[ArduinoController]: Arquivo e pasta removidos para máquina $machineId");
                }
            });

            return $response;

        } catch (\Exception $e) {
            Log::error("[ArduinoController]: Falha ao enviar o código remotamente: " . $e->getMessage());
            return response()->json(['error' => 'Falha!!'], 500);
        }
    }

}
