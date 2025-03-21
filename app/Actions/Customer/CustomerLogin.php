<?php

namespace App\Actions\Customer;

use App\Models\Cliente;
use App\Services\Interfaces\IDiscord;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Random\RandomException;

readonly class CustomerLogin
{
    public function __construct(private string $email, private string $password)
    {
    }

    public function run(): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return response()->json(['error' => 'Email ou senha inválidos'], 400);
        }

        if (!Hash::check($this->password, $customer->senha)) {
            return response()->json(['error' => 'Email ou senha inválidos'], 400);
        }

        $this->updateLastAccess($customer);

        $this->notifierDiscord();

        return response()->json([
            'email' => $customer->email,
            'id' => $customer->id,
            'name' => $customer->nome,
            'lastLogin' => $customer->ultimo_acesso->setTimezone('America/Sao_Paulo')->format('Y-m-d\TH:i:sP'),
            'token' => $this->generateJWT($customer->id),
            'type' => 'pessoa',
            'key' => 'CLIENT',
            'ativo' => (bool)$customer->ativo,
            'vencimento' => $customer->dataVencimento,
            'aviso' => $customer->aviso,
        ]);
    }

    private function getCustomer(): ?Cliente
    {
        return Cliente::where('email', $this->email)->first();
    }

    private function updateLastAccess(Cliente $customer): void
    {
        $customer->ultimo_acesso = now();
        $customer->save();
    }

    /**
     * @throws RandomException|\DateMalformedStringException
     */
    private function generateJWT(string $userId): string
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(env('JWT_SECRET'))
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
            'O cliente de email ' . $this->email . ' acabou de realizar login.',
        );
    }
}
