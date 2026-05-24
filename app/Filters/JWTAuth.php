<?php

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
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Missing or invalid authorization header']);
        }

        $token = substr($authHeader, 7);
        $apiConfig = config(API::class);

        try {
            $decoded = JWT::decode($token, new Key($apiConfig->jwt_secret, $apiConfig->jwt_algorithm));

            $blacklist = model(TokenBlacklist::class);
            if ($blacklist->isBlacklisted($token)) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['status' => 'error', 'message' => 'Token has been revoked']);
            }

            $request->setHeader('X-User-Id', (string) $decoded->sub);
            $request->setHeader('X-User-Name', $decoded->username ?? '');
        } catch (\Exception $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Invalid or expired token']);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
