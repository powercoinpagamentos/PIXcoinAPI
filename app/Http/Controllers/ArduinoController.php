<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Bluerhinos\phpMQTT;

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
}
