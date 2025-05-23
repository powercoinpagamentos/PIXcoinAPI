<?php

namespace App\Actions\Admin;

use App\Models\Pessoa;
use DateMalformedStringException;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();

        try {
            $user = $this->retrieveAdmin();

            if (!$user || !Hash::check($this->password, $user->senha)) {
                DB::rollBack();
                DB::disconnect();
                return response()->json(['error' => 'Email ou senha inválidos'], 400);
            }

            $this->updateLastAccess($user);

            DB::commit();

            return response()->json([
                'email' => $user->email,
                'id' => $user->id,
                'name' => $user->nome,
                'lastLogin' => $user->ultimo_acesso->setTimezone('America/Sao_Paulo')->format('Y-m-d\TH:i:sP'),
                'token' => $this->generateJWT($user->id),
                'type' => 'pessoa',
                'key' => 'ADMIN'
            ]);

        } catch (RandomException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao gerar token'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro no processo de login'], 500);
        } finally {
            DB::disconnect();
        }
    }

    private function retrieveAdmin(): ?Pessoa
    {
        return Pessoa::where('email', $this->email)->first();
    }

    private function updateLastAccess(Pessoa $user): void
    {
        $user->timestamps = false;
        $user->ultimo_acesso = now();
        $user->save();
        $user->timestamps = true;
    }

    private function generateJWT(string $userId): string
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(config('app.jwt_secret'))
        );

        $now = new DateTimeImmutable();

        return $config->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor(config('app.frontend_url'))
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+24 hour'))
            ->withClaim('userId', $userId)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }
}
