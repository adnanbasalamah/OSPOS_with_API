<?php

declare(strict_types=1);

namespace Controllers\api\v1;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\JWTTokenTrait;

class DashboardTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use JWTTokenTrait;

    public function testDashboardWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/dashboard');
        $response->assertStatus(401);
    }

    public function testDashboardWithValidTokenReturnsSuccess(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/dashboard');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('total_transactions', $body['data']);
        $this->assertArrayHasKey('total_revenue', $body['data']);
        $this->assertArrayHasKey('hourly_revenue', $body['data']);
        $this->assertIsArray($body['data']['hourly_revenue']);
    }

    public function testDashboardWithInvalidDateReturns400(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/dashboard', ['date_from' => 'invalid-date']);

        $response->assertStatus(400);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_VALIDATION_FAILED', $body['error']['code']);
    }

    public function testDashboardWithDateRangeReturnsFilteredData(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/dashboard', [
                'date_from' => '2026-01-01',
                'date_to' => '2026-12-31',
            ]);

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $this->assertTrue($body['success']);
        $this->assertIsInt($body['data']['total_transactions']);
        $this->assertIsNumeric($body['data']['total_revenue']);
        $this->assertCount(17, $body['data']['hourly_revenue']);
    }

    public function testDashboardHourlyRevenueHasCorrectStructure(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get('api/v1/dashboard');

        $response->assertStatus(200);
        $body = json_decode($response->getJSON(), true);
        $hourly = $body['data']['hourly_revenue'];

        foreach ($hourly as $entry) {
            $this->assertArrayHasKey('hour', $entry);
            $this->assertArrayHasKey('revenue', $entry);
            $this->assertIsInt($entry['hour']);
            $this->assertIsNumeric($entry['revenue']);
        }

        $hours = array_column($hourly, 'hour');
        $this->assertEquals(range(6, 22), $hours);
    }
}
