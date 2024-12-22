<?php

namespace App\Helpers;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Token\InvalidTokenStructure;

class AdminHelper
{
    public function validateAdminToken(string $jwt): string
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(env('SECRET_PESSOA'))
        );

        try {
            $token = $config->parser()->parse($jwt);

            $config->setValidationConstraints(
                new Constraint\SignedWith($config->signer(), $config->signingKey()), // Verifica assinatura
            );

            $constraints = $config->validationConstraints();
            $config->validator()->assert($token, ...$constraints);

            return $token->claims()->get('userId');
        } catch (RequiredConstraintsViolated | InvalidTokenStructure $e) {
            return '0';
        }
    }
}
