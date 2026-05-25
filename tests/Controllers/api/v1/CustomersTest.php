<?php

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CustomersTest extends CIUnitTestCase
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

    public function testCustomersWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/customers');
        $response->assertStatus(401);
        $response->assertJSONFragment(['status' => 'error']);
    }

    public function testCustomersSearchByName(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/customers', ['term' => 'Adnan']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['status' => 'success']);
    }

    public function testCustomersSearchByPhone(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/customers', ['term' => '0812']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['status' => 'success']);
    }

    public function testCustomersSearchReturnsDataArray(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/customers');

        $response->assertStatus(200);
        $body = $response->getJSON();
        $data = json_decode($body, true);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testCustomersItemHasRequiredFields(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/customers', ['term' => 'Adnan']);

        $response->assertStatus(200);
        $body = $response->getJSON();
        $data = json_decode($body, true);

        if (!empty($data['data'])) {
            $item = $data['data'][0];
            $this->assertArrayHasKey('person_id', $item);
            $this->assertArrayHasKey('first_name', $item);
            $this->assertArrayHasKey('last_name', $item);
            $this->assertArrayHasKey('phone_number', $item);
        }
    }

    public function testCreateCustomerWithoutTokenReturns401(): void
    {
        $response = $this->post('api/v1/customers', [
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);
        $response->assertStatus(401);
    }

    public function testCreateCustomerMissingRequiredFields(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/customers', [
            'first_name' => '',
            'last_name' => '',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateCustomerSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/customers', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone_number' => '08123456799',
        ]);

        $response->assertStatus(201);
        $response->assertJSONFragment(['status' => 'success']);
    }
}
