<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class CustomersTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testCustomersWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/customers');
        $response->assertStatus(401);
    }

    public function testCustomersSearchByName(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/customers', ['term' => 'Adnan']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
    }

    public function testCustomersSearchByPhone(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/customers', ['term' => '0812']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
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
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_VALIDATION_FAILED', $body['error']['code']);
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
        $response->assertJSONFragment(['success' => true]);
    }

    public function testGetCustomerInfoReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/customers/128');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('person_id', $body['data']);
        $this->assertArrayHasKey('first_name', $body['data']);
        $this->assertArrayHasKey('last_name', $body['data']);
    }

    public function testGetCustomerInfoNotFoundReturns404(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/customers/99999');

        $response->assertStatus(404);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_CUSTOMER_NOT_FOUND', $body['error']['code']);
    }
}
