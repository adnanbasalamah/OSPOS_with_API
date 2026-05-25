<?php

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Firebase\JWT\JWT;

class SalesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const JWT_SECRET = 'kasirbaru_jwt_secret_change_this_to_a_random_64_char_string';

    private function generateToken(): string
    {
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
            'username' => 'admin',
        ];

        return JWT::encode($payload, self::JWT_SECRET, 'HS256');
    }

    public function testPaymentTypesWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/sales/payment-types');
        $response->assertStatus(401);
        $response->assertJSONFragment(['status' => 'error']);
    }

    public function testPaymentTypesReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/sales/payment-types');

        $response->assertStatus(200);
        $response->assertJSONFragment(['status' => 'success']);
    }

    public function testPaymentTypesReturnsArray(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/sales/payment-types');

        $body = $response->getJSON();
        $data = json_decode($body, true);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testPaymentTypesItemsHaveIdAndName(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/sales/payment-types');

        $body = $response->getJSON();
        $data = json_decode($body, true);

        $this->assertNotEmpty($data['data']);
        foreach ($data['data'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
        }
    }

    public function testPaymentTypesReturnsAtLeastOnePayment(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/sales/payment-types');

        $body = $response->getJSON();
        $data = json_decode($body, true);

        $this->assertNotEmpty($data['data']);
        $this->assertArrayHasKey('id', $data['data'][0]);
        $this->assertArrayHasKey('name', $data['data'][0]);
        $this->assertEquals($data['data'][0]['id'], $data['data'][0]['name']);
    }
}
