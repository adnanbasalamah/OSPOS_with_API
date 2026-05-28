<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class ItemsCreateTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testCreateItemWithoutTokenReturns401(): void
    {
        $response = $this->post('api/v1/items', [
            'item_number' => 'TEST001',
            'name' => 'Test Item',
            'category' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function testCreateItemMissingRequiredFieldsReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->post('api/v1/items', ['name' => 'No Number']);

        $response->assertStatus(400);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_VALIDATION_FAILED', $body['error']['code']);
    }

    public function testCreateItemDuplicateItemNumberReturns409(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->post('api/v1/items', [
                'item_number' => 'BRG001',
                'name' => 'Duplicate Test',
                'category' => 'Test',
            ]);

        $response->assertStatus(409);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_DUPLICATE_ITEM_NUMBER', $body['error']['code']);
    }

    public function testCreateItemSuccessReturns201(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->post('api/v1/items', [
                'item_number' => 'API_TEST_' . time(),
                'name' => 'API Test Item',
                'category' => 'Test',
                'cost_price' => 5000,
                'unit_price' => 10000,
                'quantity' => 10,
                'reorder_level' => 2,
            ]);

        $response->assertStatus(201);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('item_id', $body['data']);
        $this->assertEquals('ERR_ITEM_CREATED', $body['data']['message']);
    }
}
