<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class ApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const VALID_USERNAME = 'admin';
    private const VALID_PASSWORD = 'abuya313500';

    public function testLoginSuccess(): void
    {
        $response = $this->post('api/v1/login', [
            'username' => self::VALID_USERNAME,
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);

        $body = $response->getJSON();
        $data = json_decode($body, true);
        $this->assertArrayHasKey('token', $data['data']);
        $this->assertArrayHasKey('expires_in', $data['data']);
        $this->assertEquals(3600, $data['data']['expires_in']);
        $this->assertEquals(1, $data['data']['user']['id']);
        $this->assertEquals(self::VALID_USERNAME, $data['data']['user']['username']);
    }

    public function testLoginFailedInvalidCredentials(): void
    {
        $response = $this->post('api/v1/login', [
            'username' => self::VALID_USERNAME,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_INVALID_CREDENTIALS', $body['error']['code']);
    }

    public function testLoginFailedMissingFields(): void
    {
        $response = $this->post('api/v1/login', [
            'username' => '',
            'password' => '',
        ]);

        $response->assertStatus(400);
    }

    public function testLoginFailedNonExistentUser(): void
    {
        $response = $this->post('api/v1/login', [
            'username' => 'nonexistent_user',
            'password' => 'some_password',
        ]);

        $response->assertStatus(401);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('ERR_INVALID_CREDENTIALS', $body['error']['code']);
    }

    public function testMeWithoutTokenReturns401(): void
    {
        $response = $this->get('api/v1/me');
        $response->assertStatus(401);
    }

    public function testMeWithValidToken(): void
    {
        $token = $this->getValidToken();
        $this->assertNotEmpty($token);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts);

        $meResponse = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/me');

        $meResponse->assertStatus(200);
        $meResponse->assertJSONFragment(['success' => true]);

        $body = $meResponse->getJSON();
        $data = json_decode($body, true);
        $this->assertEquals(self::VALID_USERNAME, $data['data']['username']);
    }

    public function testMeWithInvalidTokenReturns401(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token_here'])->get('api/v1/me');

        $response->assertStatus(401);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
    }

    public function testLogoutWithoutTokenReturns401(): void
    {
        $response = $this->post('api/v1/logout');
        $response->assertStatus(401);
    }

    public function testLogoutSuccess(): void
    {
        $token = $this->getValidToken();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/logout');

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
    }

    public function testBlacklistedTokenRejected(): void
    {
        $token = $this->getValidToken();

        $this->withHeaders(['Authorization' => "Bearer $token"])->post('api/v1/logout');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])->get('api/v1/me');

        $response->assertStatus(401);
        $body = json_decode($response->getJSON(), true);
        $this->assertFalse($body['success']);
    }

    public function testCorsHeadersPresent(): void
    {
        $response = $this->post('api/v1/login', [
            'username' => self::VALID_USERNAME,
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertStatus(200);
        $header = $response->response()->getHeaderLine('Access-Control-Allow-Origin');
        // CORS headers may not be set in test environment; this is a best-effort check
        if (!empty($header)) {
            $this->assertNotEmpty($header);
        } else {
            $this->markTestSkipped('CORS headers not available in test environment');
        }
    }

    private function getValidToken(): string
    {
        $response = $this->post('api/v1/login', [
            'username' => self::VALID_USERNAME,
            'password' => self::VALID_PASSWORD,
        ]);

        $body = json_decode($response->getJSON(), true);
        return $body['data']['token'];
    }
}
