<?php

declare(strict_types=1);

namespace Tests\Support;

use Firebase\JWT\JWT;

trait JWTTokenTrait
{
    protected function getJwtSecret(): string
    {
        $secret = getenv('JWT_SECRET');
        if ($secret === false || $secret === '') {
            $secret = 'kasirbaru_jwt_secret_change_this_to_a_random_64_char_string';
        }
        return $secret;
    }

    protected function generateToken(array $overrides = []): string
    {
        $payload = array_merge([
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
            'username' => 'admin',
        ], $overrides);

        return JWT::encode($payload, $this->getJwtSecret(), 'HS256');
    }
}
