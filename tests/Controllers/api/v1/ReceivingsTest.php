<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class ReceivingsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testHealthCheckWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/receivings');
        $response->assertStatus(401);
    }

    public function testHealthCheckReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/receivings');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertEquals('Receivings API is active', $body['data']['message']);
    }

    public function testSearchItemsWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/receivings/items', ['term' => 'test']);
        $response->assertStatus(401);
    }

    public function testSearchItemsMissingTermReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/receivings/items');

        $response->assertStatus(400);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_MISSING_TERM', $body['error']['code']);
    }

    public function testSearchItemsWithTermReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/receivings/items', ['term' => 'IKHWAN']);

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertIsArray($body['data']);
    }

    public function testStockLocationsWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/receivings/stock-locations');
        $response->assertStatus(401);
    }

    public function testStockLocationsReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/receivings/stock-locations');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertIsArray($body['data']);
        if (!empty($body['data'])) {
            $this->assertArrayHasKey('location_id', $body['data'][0]);
            $this->assertArrayHasKey('location_name', $body['data'][0]);
        }
    }

    public function testCompleteReceivingWithoutTokenReturns401(): void
    {
        $response = $this->withBodyFormat('json')
            ->post('api/v1/receivings', ['items' => [['item_id' => 4]]]);
        $response->assertStatus(401);
    }

    public function testCompleteReceivingEmptyCartReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->withBodyFormat('json')
            ->post('api/v1/receivings', ['items' => []]);

        $response->assertStatus(400);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_EMPTY_CART', $body['error']['code']);
    }

    public function testCompleteReceivingInvalidPriceReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->withBodyFormat('json')
            ->post('api/v1/receivings', [
                'items' => [
                    [
                        'item_id' => 4,
                        'cost_price' => 10000,
                        'unit_price' => 5000,
                        'quantity' => 5,
                    ],
                ],
                'supplier_id' => 19,
                'employee_id' => 1,
                'stock_location' => 1,
            ]);

        $response->assertStatus(400);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_INVALID_PRICE_COMPARISON', $body['error']['code']);
    }

    public function testCompleteReceivingSuccessReturns201(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->withBodyFormat('json')
            ->post('api/v1/receivings', [
                'items' => [
                    [
                        'item_id' => 4,
                        'cost_price' => 5500,
                        'unit_price' => 6500,
                        'quantity' => 5,
                    ],
                ],
                'supplier_id' => 19,
                'employee_id' => 1,
                'comment' => 'Test receiving',
                'reference' => 'REF-TEST-' . time(),
                'payment_type' => 'Cash',
                'stock_location' => 1,
            ]);

        $response->assertStatus(201);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertStringStartsWith('RECV ', $body['data']['receiving_id']);
        $this->assertArrayHasKey('message', $body['data']);
    }
}
