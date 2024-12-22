<?php

namespace App\Services;

use App\Mail\DisabledMachineEmail;
use App\Mail\EnableMachineEmail;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendDisabledMachineEmail(string $email, string $clientName): void
    {
        Mail::to($email)->send(new DisabledMachineEmail($clientName));
    }

    public function sendEnableMachineEmail(string $email, string $clientName): void
    {
        Mail::to($email)->send(new EnableMachineEmail($clientName));
    }
}
