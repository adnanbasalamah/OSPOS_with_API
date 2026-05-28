<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use App\Models\Customer;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Customers extends ResourceController
{
    public function show($id = null): ResponseInterface
    {
        $customer = model(Customer::class);
        $info = $customer->get_info((int)$id);

        if (!$info || !isset($info->person_id) || (int)$info->person_id < 1) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_CUSTOMER_NOT_FOUND',
                        'message' => 'Customer not found.',
                    ],
                ])
                ->setStatusCode(404);
        }

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'person_id' => (int)$info->person_id,
                    'first_name' => $info->first_name ?? '',
                    'last_name' => $info->last_name ?? '',
                    'email' => $info->email ?? '',
                    'phone_number' => $info->phone_number ?? '',
                ],
            ])
            ->setStatusCode(200);
    }

    public function index(): ResponseInterface
    {
        $term = $this->request->getVar('term');
        $limit = $this->request->getVar('limit') ?? 25;
        $customer = model(Customer::class);

        if (!empty($term)) {
            $suggestions = $customer->get_search_suggestions($term, (int)$limit, true);
            $customers = [];
            foreach ($suggestions as $s) {
                $info = $customer->get_info((int)$s['value']);
                if ($info && isset($info->person_id)) {
                    $customers[] = [
                        'person_id' => (int)$info->person_id,
                        'first_name' => $info->first_name ?? '',
                        'last_name' => $info->last_name ?? '',
                        'email' => $info->email ?? '',
                        'phone_number' => $info->phone_number ?? '',
                        'company_name' => $info->company_name ?? '',
                        'discount' => (float)($info->discount ?? 0),
                        'discount_type' => (int)($info->discount_type ?? 0),
                        'taxable' => (int)($info->taxable ?? 1),
                        'balance' => (float)($info->balance ?? 0),
                    ];
                }
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $customers,
                ])
                ->setStatusCode(200);
        }

        $result = $customer->get_all((int)$limit, 0);
        $allCustomers = [];
        foreach ($result->getResult() as $row) {
            $allCustomers[] = [
                'person_id' => (int)$row->person_id,
                'first_name' => $row->first_name ?? '',
                'last_name' => $row->last_name ?? '',
                'email' => $row->email ?? '',
                'phone_number' => $row->phone_number ?? '',
                'company_name' => $row->company_name ?? '',
                'discount' => (float)($row->discount ?? 0),
                'discount_type' => (int)($row->discount_type ?? 0),
                'taxable' => (int)($row->taxable ?? 1),
                'balance' => (float)($row->balance ?? 0),
            ];
        }

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $allCustomers,
            ])
            ->setStatusCode(200);
    }

    public function create(): ResponseInterface
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];

        if (empty($input['first_name']) || empty($input['last_name'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_VALIDATION_FAILED',
                        'message' => 'Nama depan dan belakang wajib diisi.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $person_data = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'gender' => $input['gender'] ?? null,
            'email' => $input['email'] ?? '',
            'phone_number' => $input['phone_number'] ?? '',
            'address_1' => $input['address_1'] ?? '',
            'address_2' => $input['address_2'] ?? '',
            'city' => $input['city'] ?? '',
            'state' => $input['state'] ?? '',
            'zip' => $input['zip'] ?? '',
            'country' => $input['country'] ?? '',
            'comments' => $input['comments'] ?? '',
        ];

        $customer_data = [
            'company_name' => $input['company_name'] ?? null,
            'account_number' => $input['account_number'] ?? null,
            'tax_id' => $input['tax_id'] ?? '',
            'taxable' => $input['taxable'] ?? 1,
            'discount' => $input['discount'] ?? 0.00,
            'discount_type' => $input['discount_type'] ?? 0,
        ];

        $customer = model(Customer::class);
        if ($customer->save_customer($person_data, $customer_data)) {
            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'person_id' => $person_data['person_id'],
                        'message' => 'Pelanggan berhasil ditambahkan.',
                    ],
                ])
                ->setStatusCode(201);
        }

        return $this->response
            ->setJSON([
                'success' => false,
                'error' => [
                    'code' => 'ERR_SAVE_FAILED',
                    'message' => 'Gagal menyimpan data pelanggan.',
                ],
            ])
            ->setStatusCode(500);
    }
}
