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

    // ---------- Payment Types Tests ----------

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

    // ---------- Sales Create Tests ----------

    public function testCreateWithoutTokenReturns401(): void
    {
        $response = $this->post('api/v1/sales', []);
        $response->assertStatus(401);
    }

    public function testCreateEmptyItemsReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [],
            'payments' => [],
        ]);

        $response->assertStatus(400);
    }

    public function testCreateMissingItemsReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'payments' => [],
        ]);

        $response->assertStatus(400);
    }

    public function testCreateMissingPaymentsReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [],
        ]);

        $response->assertStatus(400);
    }

    public function testCreateWithInvalidItemIdReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [
                ['item_id' => 999999, 'quantity' => 1],
            ],
            'payments' => [
                ['payment_type' => 'Cash', 'payment_amount' => 100],
            ],
        ]);

        $response->assertStatus(400);
    }

    public function testCreateSuccessReturns201(): void
    {
        $token = $this->generateToken();

        $itemId = $this->createTestItem();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [
                ['item_id' => $itemId, 'quantity' => 1],
            ],
            'payments' => [
                ['payment_type' => 'Cash', 'payment_amount' => 100],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJSONFragment(['status' => 'success']);
    }

    public function testCreateSuccessReturnsSaleId(): void
    {
        $token = $this->generateToken();

        $itemId = $this->createTestItem();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [
                ['item_id' => $itemId, 'quantity' => 1],
            ],
            'payments' => [
                ['payment_type' => 'Cash', 'payment_amount' => 100],
            ],
        ]);

        $body = $response->getJSON();
        $data = json_decode($body, true);

        $this->assertArrayHasKey('sale_id', $data['data']);
        $this->assertArrayHasKey('sale_id_display', $data['data']);
        $this->assertArrayHasKey('sale_time', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertArrayHasKey('item_count', $data['data']);
        $this->assertGreaterThan(0, $data['data']['sale_id']);
    }

    private function createTestItem(): int
    {
        $db = \Config\Database::connect('tests');
        $name = 'TEST_SALE_ITEM_' . time();

        $db->table('items')->insert([
            'name' => $name,
            'category' => 'Test',
            'stock_type' => 0,
            'deleted' => 0,
            'unit_price' => 50.00,
            'cost_price' => 25.00,
        ]);
        $itemId = $db->insertID();

        $db->table('item_quantities')->insert([
            'item_id' => $itemId,
            'location_id' => 1,
            'quantity' => 100,
        ]);

        return $itemId;
    }
}
