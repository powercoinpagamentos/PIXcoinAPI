<?php

namespace App\Services\Interfaces;

interface IDiscord
{
    public function notificar(string $uri, string $title, string $details);
}
