<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class CategoriesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testCategoriesWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/categories');
        $response->assertStatus(401);
    }

    public function testCategoriesReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/categories');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
    }

    public function testCategoriesWithSearch(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/categories', ['search' => 'Pakaian']);

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
    }

    public function testCategoriesReturnsStringArray(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/categories');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);

        if (!empty($body['data'])) {
            $this->assertIsString($body['data'][0]);
        }
    }
}
