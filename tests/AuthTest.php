<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private const JWT_SECRET = 'kasirbaru_jwt_secret_change_this_to_a_random_64_char_string';

    public function testJwtTokenGenerationAndValidation(): void
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
            'username' => 'admin',
        ];

        $token = JWT::encode($payload, self::JWT_SECRET, 'HS256');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);

        $decoded = JWT::decode($token, new Key(self::JWT_SECRET, 'HS256'));
        $this->assertEquals(1, $decoded->sub);
        $this->assertEquals('admin', $decoded->username);
        $this->assertEquals('kasirbaru', $decoded->iss);
    }

    public function testJwtExpiredTokenRejected(): void
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() - 10,
            'sub' => 1,
        ];

        $token = JWT::encode($payload, self::JWT_SECRET, 'HS256');

        $this->expectException(\Firebase\JWT\ExpiredException::class);
        JWT::decode($token, new Key(self::JWT_SECRET, 'HS256'));
    }

    public function testJwtInvalidSignatureRejected(): void
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
        ];

        $token = JWT::encode($payload, 'wrong_secret_key_that_is_different_and_long_enough', 'HS256');

        $this->expectException(\Firebase\JWT\SignatureInvalidException::class);
        JWT::decode($token, new Key(self::JWT_SECRET, 'HS256'));
    }
}
