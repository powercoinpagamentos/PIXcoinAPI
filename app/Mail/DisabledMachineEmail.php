<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisabledMachineEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clientName;

    public function __construct(string $clientName)
    {
        $this->clientName = $clientName;
    }

    public function build(): DisabledMachineEmail
    {
        return $this->subject('Comunicado de Inadimplência')
            ->view('emails.disabled_machine_email')
            ->with(['clientName' => $this->clientName]);
    }
}
