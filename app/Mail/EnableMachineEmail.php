<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnableMachineEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clientName;

    public function __construct(string $clientName)
    {
        $this->clientName = $clientName;
    }

    public function build(): EnableMachineEmail
    {
        return $this->subject('Comunicado de Liberação')
            ->view('emails.enable_machine_email')
            ->with(['clientName' => $this->clientName]);
    }
}
