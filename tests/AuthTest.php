<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;
use Tests\Support\JWTTokenTrait;

class AuthTest extends TestCase
{
    use JWTTokenTrait;

    public function testJwtTokenGenerationAndValidation(): void
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
            'username' => 'admin',
        ];

        $token = JWT::encode($payload, $this->getJwtSecret(), 'HS256');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);

        $decoded = JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));
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

        $token = JWT::encode($payload, $this->getJwtSecret(), 'HS256');

        $this->expectException(\Firebase\JWT\ExpiredException::class);
        JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));
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
        JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));
    }

    public function testTokenBlacklistHashIsDeterministic(): void
    {
        $token = 'test-token-123';
        $hash1 = hash('sha256', $token);
        $hash2 = hash('sha256', $token);
        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1));
    }

    public function testTokenBlacklistDifferentTokensHashDifferently(): void
    {
        $hash1 = hash('sha256', 'token-a');
        $hash2 = hash('sha256', 'token-b');
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testJwtTokenContainsRequiredClaims(): void
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 42,
            'username' => 'testuser',
        ];

        $token = JWT::encode($payload, $this->getJwtSecret(), 'HS256');
        $decoded = JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));

        $this->assertObjectHasProperty('iss', $decoded);
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertObjectHasProperty('sub', $decoded);
        $this->assertEquals('kasirbaru', $decoded->iss);
        $this->assertIsInt($decoded->iat);
        $this->assertIsInt($decoded->exp);
        $this->assertIsInt($decoded->sub);
    }

    public function testJwtTokenTamperedPayloadDetected(): void
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
        ];

        $token = JWT::encode($payload, $this->getJwtSecret(), 'HS256');
        $parts = explode('.', $token);
        $tamperedPayload = rtrim(base64url_encode(json_encode(['sub' => 999])), '=');
        $fakeToken = $parts[0] . '.' . $tamperedPayload . '.' . $parts[2];

        $this->expectException(\Firebase\JWT\SignatureInvalidException::class);
        JWT::decode($fakeToken, new Key($this->getJwtSecret(), 'HS256'));
    }

    public function testJwtRejectsMalformedToken(): void
    {
        $this->expectException(\DomainException::class);
        JWT::decode('not.a.token', new Key($this->getJwtSecret(), 'HS256'));
    }

    public function testJwtRejectsEmptyToken(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        JWT::decode('', new Key($this->getJwtSecret(), 'HS256'));
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
