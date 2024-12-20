<?php

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

function validarToken(string $jwt): bool
{
    $config = Configuration::forSymmetricSigner(
        new Sha256(),
        InMemory::plainText(env('SECRET_PESSOA'))
    );

    try {
        $token = $config->parser()->parse($jwt);

        $constraints = $config->validationConstraints();
        $config->validator()->assert($token, ...$constraints);

        return true;
    } catch (RequiredConstraintsViolated | InvalidTokenStructure $e) {
        return false;
    }
}
