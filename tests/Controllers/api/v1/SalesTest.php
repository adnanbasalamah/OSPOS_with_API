<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class SalesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    // ---------- Payment Types Tests ----------

    public function testPaymentTypesWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/sales/payment-types');
        $response->assertStatus(401);
    }

    public function testPaymentTypesReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/sales/payment-types');

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
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
        $response->assertJSONFragment(['success' => true]);
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

    // ---------- Sales Detail Tests ----------

    public function testDetailWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/sales/1');
        $response->assertStatus(401);
    }

    public function testDetailNotFoundReturns404(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/sales/9999999');
        $response->assertStatus(404);
    }

    public function testDetailSuccessReturnsSaleData(): void
    {
        $token = $this->generateToken();
        $itemId = $this->createTestItem();

        $createResponse = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [
                ['item_id' => $itemId, 'quantity' => 2, 'price' => 5000],
            ],
            'payments' => [
                ['payment_type' => 'Cash', 'payment_amount' => 10000],
            ],
        ]);
        $createBody = json_decode($createResponse->getJSON(), true);
        $saleId = $createBody['data']['sale_id'];

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get("api/v1/sales/$saleId");

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);

        $this->assertEquals($saleId, $body['data']['sale_id']);
        $this->assertEquals('POS ' . $saleId, $body['data']['sale_id_display']);
        $this->assertArrayHasKey('sale_time', $body['data']);
        $this->assertArrayHasKey('customer', $body['data']);
        $this->assertArrayHasKey('employee', $body['data']);
        $this->assertArrayHasKey('items', $body['data']);
        $this->assertArrayHasKey('payments', $body['data']);
        $this->assertArrayHasKey('total', $body['data']);
        $this->assertIsArray($body['data']['items']);
        $this->assertIsArray($body['data']['payments']);
        $this->assertCount(1, $body['data']['items']);
        $this->assertCount(1, $body['data']['payments']);
    }

    public function testDetailItemHasRequiredFields(): void
    {
        $token = $this->generateToken();
        $itemId = $this->createTestItem();

        $createResponse = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [
                ['item_id' => $itemId, 'quantity' => 1, 'price' => 5000],
            ],
            'payments' => [
                ['payment_type' => 'Cash', 'payment_amount' => 5000],
            ],
        ]);
        $createBody = json_decode($createResponse->getJSON(), true);
        $saleId = $createBody['data']['sale_id'];

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get("api/v1/sales/$saleId");
        $body = json_decode($response->getJSON(), true);

        $item = $body['data']['items'][0];
        $this->assertArrayHasKey('item_id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('quantity', $item);
        $this->assertArrayHasKey('price', $item);
        $this->assertArrayHasKey('subtotal', $item);

        $payment = $body['data']['payments'][0];
        $this->assertArrayHasKey('payment_type', $payment);
        $this->assertArrayHasKey('payment_amount', $payment);
    }

    public function testDetailCustomerEmployeeAreObjects(): void
    {
        $token = $this->generateToken();
        $itemId = $this->createTestItem();

        $createResponse = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/sales', [
            'items' => [
                ['item_id' => $itemId, 'quantity' => 1],
            ],
            'payments' => [
                ['payment_type' => 'Cash', 'payment_amount' => 50],
            ],
        ]);
        $createBody = json_decode($createResponse->getJSON(), true);
        $saleId = $createBody['data']['sale_id'];

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get("api/v1/sales/$saleId");
        $body = json_decode($response->getJSON(), true);

        $this->assertArrayHasKey('person_id', $body['data']['customer']);
        $this->assertArrayHasKey('name', $body['data']['customer']);
        $this->assertArrayHasKey('person_id', $body['data']['employee']);
        $this->assertArrayHasKey('name', $body['data']['employee']);
    }
}
