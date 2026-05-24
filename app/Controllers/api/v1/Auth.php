<?php

namespace App\Controllers\api\v1;

use App\Models\Employee;
use App\Models\TokenBlacklist;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\API;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends ResourceController
{
    public function login(): ResponseInterface
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $employee = model(Employee::class);
        $builder = $employee->db->table('employees');
        $query = $builder->getWhere(['username' => $username, 'deleted' => 0], 1);

        if ($query->getNumRows() !== 1) {
            return $this->failUnauthorized('Invalid username or password');
        }

        $row = $query->getRow();
        $valid = false;

        if ($row->hash_version === '1' && $row->password === md5($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $employee->db->table('employees')
                ->where('person_id', $row->person_id)
                ->update(['hash_version' => 2, 'password' => $password_hash]);
            $valid = true;
        } elseif ($row->hash_version === '2' && password_verify($password, $row->password)) {
            $valid = true;
        }

        if (!$valid) {
            return $this->failUnauthorized('Invalid username or password');
        }

        $apiConfig = config(API::class);
        $now = time();
        $payload = [
            'iss' => 'kasirbaru',
            'iat' => $now,
            'exp' => $now + $apiConfig->jwt_expiry,
            'sub' => $row->person_id,
            'username' => $row->username,
        ];

        $token = JWT::encode($payload, $apiConfig->jwt_secret, $apiConfig->jwt_algorithm);

        return $this->respond([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'expires_in' => $apiConfig->jwt_expiry,
                'user' => [
                    'id' => (int) $row->person_id,
                    'username' => $row->username,
                ],
            ],
        ]);
    }

    public function logout(): ResponseInterface
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->failUnauthorized('Missing or invalid authorization header');
        }

        $token = substr($authHeader, 7);

        try {
            $apiConfig = config(API::class);
            $decoded = JWT::decode($token, new Key($apiConfig->jwt_secret, $apiConfig->jwt_algorithm));

            $blacklist = model(TokenBlacklist::class);
            $blacklist->blacklist($token, $decoded->exp);

            return $this->respond([
                'status' => 'success',
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Invalid or expired token');
        }
    }

    public function me(): ResponseInterface
    {
        $userId = $this->request->getHeaderLine('X-User-Id');
        $username = $this->request->getHeaderLine('X-User-Name');

        if (empty($userId)) {
            return $this->failUnauthorized('Authentication required');
        }

        $employee = model(Employee::class);
        $info = $employee->get_info((int) $userId);

        if (!$info) {
            return $this->failNotFound('User not found');
        }

        return $this->respond([
            'status' => 'success',
            'data' => [
                'id' => (int) $info->person_id,
                'username' => $username,
                'email' => $info->email ?? '',
                'person_id' => (int) $info->person_id,
            ],
        ]);
    }
}
