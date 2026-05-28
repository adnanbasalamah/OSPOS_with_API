<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class ItemsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testItemsWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/items');
        $response->assertStatus(401);
    }

    public function testItemsWithInvalidTokenReturns401(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token_here'])->get('api/v1/items');
        $response->assertStatus(401);
    }

    public function testItemsSearchByBarcode(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/items', ['term' => 'BRG001']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
    }

    public function testItemsSearchByName(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/items', ['term' => 'Baju']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
    }

    public function testItemsSearchByCategory(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/items', ['category' => 'Pakaian']);

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
    }

    public function testItemsReturnsDataArray(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/items', ['term' => 'Baju']);

        $response->assertStatus(200);
        $body = $response->getJSON();
        $data = json_decode($body, true);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testItemsItemHasRequiredFields(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/items', ['term' => 'Baju']);

        $response->assertStatus(200);
        $body = $response->getJSON();
        $data = json_decode($body, true);

        if (!empty($data['data'])) {
            $item = $data['data'][0];
            $this->assertArrayHasKey('item_id', $item);
            $this->assertArrayHasKey('item_number', $item);
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('category', $item);
            $this->assertArrayHasKey('unit_price', $item);
            $this->assertArrayHasKey('quantity', $item);
        }
    }
}
