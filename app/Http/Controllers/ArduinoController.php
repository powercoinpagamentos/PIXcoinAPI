<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Bluerhinos\phpMQTT;
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

            return response()->json(['status' => 'sucesso', 'message' => 'Comando enviado']);
        }

        return response()->json(['status' => 'error', 'message' => 'Falha na conexão MQTT'], 500);
    }
    public function getArduinoCode(): StreamedResponse|JsonResponse
    {
        $firmwarePath = storage_path("app/firmware/pixcoin.ino.bin");

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
            'Expires' => '0',
            'Connection' => 'close',
            'Content-Disposition' => 'attachment; filename="pixcoin.ino.bin"',
        ];

        return response()->stream(function () use ($firmwarePath) {
            $handle = fopen($firmwarePath, 'rb');
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
            if (file_exists($firmwarePath)) {
                unlink($firmwarePath);
            }
        }, 200, $headers);
    }

}
