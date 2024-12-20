<?php

namespace App\Actions\Admin;

use App\Models\Pessoa;
use App\Services\Interfaces\IDiscord;
use DateMalformedStringException;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Random\RandomException;

readonly class AdminLogin
{
    public function __construct(private string $email, private string $password)
    {
    }

    public function run(): JsonResponse
    {
        $user = $this->retrieveAdmin();
        if (!$user) {
            return response()->json(['error' => 'Email ou senha inválidos'], 400);
        }

        if (!Hash::check($this->password, $user->senha)) {
            return response()->json(['error' => 'Email ou senha inválidos'], 400);
        }

        $this->updateLastAccess($user);

        $this->notifierDiscord();

        return response()->json([
            'email' => $user->email,
            'id' => $user->id,
            'name' => $user->nome,
            'lastLogin' => $user->ultimo_acesso->setTimezone('America/Sao_Paulo')->format('Y-m-d\TH:i:sP'),
            'token' => $this->generateJWT($user->id),
            'type' => 'pessoa',
            'key' => 'ADMIN'
        ]);
    }

    private function retrieveAdmin(): ?Pessoa
    {
        return Pessoa::where('email', $this->email)->first();
    }

    private function updateLastAccess(Pessoa $user): void
    {
        $user->ultimo_acesso = now();
        $user->save();
    }

    /**
     * @throws RandomException|DateMalformedStringException
     */
    private function generateJWT(string $userId): string
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(env('SECRET_PESSOA'))
        );

        $now = new DateTimeImmutable();
        $token = $config->builder()
            ->issuedBy('pixcoin')
            ->permittedFor('pixcoin_frontend')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+24 hour'))
            ->withClaim('userId', $userId)
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

    private function notifierDiscord(): void
    {
        /** @var IDiscord $discordAPI */
        $discordAPI = resolve(IDiscord::class);

        $discordAPI->notificar(
            env('NOTIFICACOES_LOGINS'),
            'Novo login efetuado!',
            'O admin de email ' . $this->email . ' acabou de realizar login.',
        );
    }
}
