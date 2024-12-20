<?php

namespace App\Services;

use App\Services\Interfaces\IDiscord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Discord implements IDiscord
{
    public function notificar(string $uri, string $title, string $details)
    {
        $client = new Client();

        $embeds = [
            [
                'title' => $title,
                'color' => 5174599,
                'footer' => [
                    'text' => '📅 ' . date('d/m/Y H:i:s'),
                ],
                'fields' => [
                    [
                        'name' => '',
                        'value' => $details,
                    ],
                ],
            ],
        ];

        try {
            $response = $client->post($uri, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ['embeds' => $embeds],
            ]);
            return $response->getStatusCode();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse()->getBody()->getContents();
            }

            return $e->getMessage();
        }
    }
}
