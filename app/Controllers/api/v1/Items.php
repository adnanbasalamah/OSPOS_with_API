<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Items extends ResourceController
{
    public function create(): ResponseInterface
    {
        $json = $this->request->getJSON(true) ?? $this->request->getPost();

        $itemNumber = $json['item_number'] ?? null;
        $name = $json['name'] ?? null;
        $category = $json['category'] ?? null;

        if (empty($itemNumber) || empty($name) || empty($category)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_VALIDATION_FAILED',
                        'message' => 'item_number, name, and category are required.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $db = db_connect();

        $check = $db->table('items')
            ->where('item_number', $itemNumber)
            ->where('deleted', 0)
            ->get()
            ->getNumRows();

        if ($check > 0) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_DUPLICATE_ITEM_NUMBER',
                        'message' => "Item number '$itemNumber' already exists.",
                    ],
                ])
                ->setStatusCode(409);
        }

        $itemData = [
            'item_number' => $itemNumber,
            'name' => $name,
            'category' => $category,
            'cost_price' => $json['cost_price'] ?? 0.00,
            'unit_price' => $json['unit_price'] ?? 0.00,
            'reorder_level' => $json['reorder_level'] ?? 0,
            'supplier_id' => $json['supplier_id'] ?? null,
            'description' => $json['description'] ?? '',
            'deleted' => 0,
            'stock_type' => 0,
        ];

        $db->table('items')->insert($itemData);
        $itemId = (int)$db->insertID();

        $quantity = (int)($json['quantity'] ?? 0);
        $db->table('item_quantities')->insert([
            'item_id' => $itemId,
            'location_id' => 1,
            'quantity' => $quantity,
        ]);

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'item_id' => $itemId,
                    'message' => 'ERR_ITEM_CREATED',
                ],
            ])
            ->setStatusCode(201);
    }

    public function index(): ResponseInterface
    {
        $term = $this->request->getVar('term');
        $category = $this->request->getVar('category');
        $locationId = (int)($this->request->getVar('location_id') ?? 1);
        $limit = $this->request->getVar('limit') ?? 25;

        $db = db_connect();
        $builder = $db->table('items');

        $builder->select('items.item_id, items.item_number, items.name, items.category, items.cost_price, items.unit_price, items.pic_filename');
        $builder->select('suppliers.person_id AS supplier_id, suppliers.company_name AS supplier_name');
        $builder->select('item_quantities.quantity');

        $builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $builder->join('item_quantities', "item_quantities.item_id = items.item_id AND item_quantities.location_id = {$locationId}", 'left');

        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', 0);

        if (!empty($term)) {
            $builder->groupStart();
            $builder->like('items.name', $term);
            $builder->orWhere('items.item_number', $term);
            $builder->groupEnd();
        }

        if (!empty($category)) {
            $builder->where('items.category', $category);
        }

        $builder->orderBy('items.name', 'ASC');
        $builder->limit((int)$limit);

        $query = $builder->get();
        $items = $query->getResultArray();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $items,
            ])
            ->setStatusCode(200);
    }
}
