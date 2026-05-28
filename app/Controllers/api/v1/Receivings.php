<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Receivings extends ResourceController
{
    public function index(): ResponseInterface
    {
        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'message' => 'Receivings API is active',
                ],
            ])
            ->setStatusCode(200);
    }

    public function items(): ResponseInterface
    {
        $term = $this->request->getVar('term');
        $locationId = (int)($this->request->getVar('location_id') ?? 1);

        if (empty($term)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_MISSING_TERM',
                        'message' => 'Search term is required.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $db = db_connect();

        $builder = $db->table('items');
        $builder->select('items.item_id, items.name, items.item_number, items.cost_price, items.unit_price, items.category, items.description');
        $builder->select('items.supplier_id, suppliers.company_name AS supplier_name');
        $builder->select('item_quantities.quantity');
        $builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $builder->join('item_quantities', "item_quantities.item_id = items.item_id AND item_quantities.location_id = {$locationId}", 'left');
        $builder->where('items.deleted', 0);
        $builder->groupStart();
        $builder->like('items.name', $term);
        $builder->orLike('items.item_number', $term);
        $builder->groupEnd();
        $builder->orderBy('items.name', 'ASC');

        $items = $builder->get()->getResultArray();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $items,
            ])
            ->setStatusCode(200);
    }

    public function stockLocations(): ResponseInterface
    {
        $db = db_connect();

        $locations = $db->table('stock_locations')
            ->where('deleted', 0)
            ->get()
            ->getResultArray();

        $result = array_map(function ($loc) {
            return [
                'location_id' => (int)$loc['location_id'],
                'location_name' => $loc['location_name'],
            ];
        }, $locations);

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $result,
            ])
            ->setStatusCode(200);
    }

    public function complete(): ResponseInterface
    {
        $json = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];

        $items = $json['items'] ?? [];

        if (empty($items)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_EMPTY_CART',
                        'message' => 'Items cannot be empty.',
                    ],
                ])
                ->setStatusCode(400);
        }

        foreach ($items as $item) {
            $costPrice = (float)($item['cost_price'] ?? 0);
            $retailPrice = (float)($item['retail_price'] ?? $item['unit_price'] ?? 0);

            if ($costPrice >= $retailPrice) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'error' => [
                            'code' => 'ERR_INVALID_PRICE_COMPARISON',
                            'message' => "cost_price ($costPrice) must be less than retail_price ($retailPrice) for item {$item['item_id']}.",
                        ],
                    ])
                    ->setStatusCode(400);
            }
        }

        $db = db_connect();
        $db->transStart();

        $receivingData = [
            'receiving_time' => date('Y-m-d H:i:s'),
            'supplier_id' => $json['supplier_id'] ?? null,
            'employee_id' => $json['employee_id'] ?? 1,
            'comment' => $json['comment'] ?? '',
            'payment_type' => $json['payment_type'] ?? 'Cash',
            'reference' => $json['reference'] ?? '',
        ];

        $db->table('receivings')->insert($receivingData);
        $receivingId = (int)$db->insertID();

        foreach ($items as $item) {
            $itemId = (int)($item['item_id'] ?? 0);
            $quantity = (float)($item['quantity'] ?? 1);
            $stockLocationId = (int)($json['stock_location'] ?? $item['stock_location'] ?? 1);

            $db->table('receivings_items')->insert([
                'receiving_id' => $receivingId,
                'item_id' => $itemId,
                'line' => $item['line'] ?? 0,
                'description' => $item['description'] ?? '',
                'serialnumber' => $item['serialnumber'] ?? '',
                'quantity_purchased' => $quantity,
                'receiving_quantity' => $item['receiving_quantity'] ?? 1,
                'discount' => $item['discount'] ?? 0,
                'discount_type' => $item['discount_type'] ?? 0,
                'item_cost_price' => $item['cost_price'] ?? 0,
                'item_unit_price' => $item['retail_price'] ?? $item['unit_price'] ?? 0,
                'item_location' => $stockLocationId,
            ]);

            $currentQty = $db->table('item_quantities')
                ->where('item_id', $itemId)
                ->where('location_id', $stockLocationId)
                ->get()
                ->getRowArray();

            if ($currentQty) {
                $db->table('item_quantities')
                    ->set('quantity', (int)$currentQty['quantity'] + (int)$quantity)
                    ->where('item_id', $itemId)
                    ->where('location_id', $stockLocationId)
                    ->update();
            } else {
                $db->table('item_quantities')->insert([
                    'item_id' => $itemId,
                    'location_id' => $stockLocationId,
                    'quantity' => $quantity,
                ]);
            }

            $db->table('inventory')->insert([
                'trans_date' => date('Y-m-d H:i:s'),
                'trans_items' => $itemId,
                'trans_user' => $json['employee_id'] ?? 1,
                'trans_location' => $stockLocationId,
                'trans_comment' => 'RECV ' . $receivingId,
                'trans_inventory' => $quantity,
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_TRANSACTION_FAILED',
                        'message' => 'Failed to save receiving.',
                    ],
                ])
                ->setStatusCode(500);
        }

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'receiving_id' => 'RECV ' . $receivingId,
                    'message' => 'Receiving saved successfully.',
                ],
            ])
            ->setStatusCode(201);
    }
}
