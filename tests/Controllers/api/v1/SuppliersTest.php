<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class SuppliersTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testSuppliersWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/suppliers');
        $response->assertStatus(401);
    }

    public function testSuppliersReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/suppliers');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
    }

    public function testSuppliersWithSearch(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/suppliers', ['search' => 'Toko']);

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
    }

    public function testSuppliersReturnsRequiredFields(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/suppliers');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);

        if (!empty($body['data'])) {
            $supplier = $body['data'][0];
            $this->assertArrayHasKey('id', $supplier);
            $this->assertArrayHasKey('name', $supplier);
            $this->assertArrayHasKey('first_name', $supplier);
            $this->assertArrayHasKey('last_name', $supplier);
        }
    }
}
