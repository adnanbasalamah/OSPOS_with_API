<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class StockTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testStockBySkuWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/stock/by-sku/BRG001');
        $response->assertStatus(401);
    }

    public function testStockBySkuNotFoundReturns404(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/stock/by-sku/NONEXISTENT_SKU');

        $response->assertStatus(404);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_ITEM_NOT_FOUND', $body['error']['code']);
    }

    public function testStockBySkuValidReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/stock/by-sku/BRG001');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('item_id', $body['data']);
        $this->assertArrayHasKey('name', $body['data']);
        $this->assertArrayHasKey('item_number', $body['data']);
        $this->assertArrayHasKey('reorder_level', $body['data']);
        $this->assertArrayHasKey('total_stock', $body['data']);
        $this->assertArrayHasKey('stock_locations', $body['data']);
        $this->assertIsArray($body['data']['stock_locations']);
    }

    public function testOutOfStockWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/stock/out-of-stock');
        $response->assertStatus(401);
    }

    public function testOutOfStockReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/stock/out-of-stock');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
    }

    public function testBelowMinimumWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/stock/below-minimum');
        $response->assertStatus(401);
    }

    public function testBelowMinimumReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/stock/below-minimum');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
    }

    public function testBelowMinimumWithSupplierFilter(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/stock/below-minimum', ['supplier_id' => 1]);

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
    }

    public function testUpdateStockWithoutTokenReturns401(): void
    {
        $response = $this->withBodyFormat('json')->patch('api/v1/stock/update/BRG001', ['quantity' => 10]);
        $response->assertStatus(401);
    }

    public function testUpdateStockSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->withBodyFormat('json')
            ->patch('api/v1/stock/update/BRG001', ['quantity' => 15]);

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('item_id', $body['data']);
        $this->assertArrayHasKey('sku', $body['data']);
        $this->assertArrayHasKey('old_quantity', $body['data']);
        $this->assertArrayHasKey('new_quantity', $body['data']);
        $this->assertEquals(15, $body['data']['new_quantity']);
    }

    public function testUpdateStockMissingQuantityReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->withBodyFormat('json')
            ->patch('api/v1/stock/update/BRG001', []);

        $response->assertStatus(400);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_MISSING_QUANTITY', $body['error']['code']);
    }

    public function testUpdateStockItemNotFoundReturns404(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->withBodyFormat('json')
            ->patch('api/v1/stock/update/NONEXISTENT_SKU', ['quantity' => 5]);

        $response->assertStatus(404);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_ITEM_NOT_FOUND', $body['error']['code']);
    }
}
