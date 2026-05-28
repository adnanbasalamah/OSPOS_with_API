<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\TokenBlacklist;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\API;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): RequestInterface|ResponseInterface|null
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'error' => ['code' => 'ERR_MISSING_TOKEN', 'message' => 'Missing or invalid authorization header']]);
        }

        $token = substr($authHeader, 7);
        $apiConfig = config(API::class);

        try {
            $decoded = JWT::decode($token, new Key($apiConfig->jwt_secret, $apiConfig->jwt_algorithm));
        } catch (\Exception $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'error' => ['code' => 'ERR_INVALID_TOKEN', 'message' => 'Invalid or expired token']]);
        }

        try {
            $blacklist = model(TokenBlacklist::class);
            if ($blacklist->isBlacklisted($token)) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['success' => false, 'error' => ['code' => 'ERR_TOKEN_REVOKED', 'message' => 'Token has been revoked']]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Blacklist check failed: ' . $e->getMessage());
        }

        $request->setHeader('X-User-Id', (string) $decoded->sub);
        $request->setHeader('X-User-Name', $decoded->username ?? '');

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
