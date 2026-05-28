<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Stock extends ResourceController
{
    public function outOfStock(): ResponseInterface
    {
        $db = db_connect();

        $items = $db->table('items')
            ->select('items.item_id, items.name, items.item_number, item_quantities.quantity, stock_locations.location_name')
            ->join('item_quantities', 'item_quantities.item_id = items.item_id')
            ->join('stock_locations', 'stock_locations.location_id = item_quantities.location_id')
            ->where('items.deleted', 0)
            ->where('item_quantities.quantity', 0)
            ->get()
            ->getResultArray();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $items,
            ])
            ->setStatusCode(200);
    }

    public function belowMinimum(): ResponseInterface
    {
        $db = db_connect();
        $supplierId = $this->request->getVar('supplier_id');
        $category = $this->request->getVar('category');

        $builder = $db->table('items');
        $builder->select('items.item_id, items.name, items.item_number, items.category, items.reorder_level');
        $builder->select('suppliers.company_name AS supplier_name');
        $builder->selectSum('item_quantities.quantity', 'quantity');
        $builder->join('item_quantities', 'item_quantities.item_id = items.item_id');
        $builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $builder->where('items.deleted', 0);
        $builder->groupBy('items.item_id');
        $builder->having('quantity <= items.reorder_level');

        if (!empty($supplierId)) {
            $builder->where('items.supplier_id', (int)$supplierId);
        }

        if (!empty($category)) {
            $builder->where('items.category', $category);
        }

        $items = $builder->get()->getResultArray();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $items,
            ])
            ->setStatusCode(200);
    }

    public function update($sku = null): ResponseInterface
    {
        if (empty($sku)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_MISSING_SKU',
                        'message' => 'SKU is required.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $json = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];
        $quantity = $json['quantity'] ?? $this->request->getVar('quantity');
        $locationId = $json['location_id'] ?? $this->request->getVar('location_id') ?? 1;

        if ($quantity === null) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_MISSING_QUANTITY',
                        'message' => 'Quantity is required.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $db = db_connect();

        $item = $db->table('items')
            ->where('item_number', $sku)
            ->where('deleted', 0)
            ->get()
            ->getRowArray();

        if (!$item) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_ITEM_NOT_FOUND',
                        'message' => "Item with SKU '$sku' not found.",
                    ],
                ])
                ->setStatusCode(404);
        }

        $itemId = (int)$item['item_id'];

        $current = $db->table('item_quantities')
            ->where('item_id', $itemId)
            ->where('location_id', (int)$locationId)
            ->get()
            ->getRowArray();

        $oldQuantity = $current ? (int)$current['quantity'] : 0;

        $db->table('item_quantities')
            ->set('quantity', (int)$quantity)
            ->where('item_id', $itemId)
            ->where('location_id', (int)$locationId)
            ->update();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'item_id' => $itemId,
                    'sku' => $sku,
                    'location_id' => (int)$locationId,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => (int)$quantity,
                ],
            ])
            ->setStatusCode(200);
    }
    public function bySku($sku = null): ResponseInterface
    {
        if (empty($sku)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_MISSING_SKU',
                        'message' => 'SKU is required.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $db = db_connect();

        $item = $db->table('items')
            ->where('item_number', $sku)
            ->where('deleted', 0)
            ->get()
            ->getRowArray();

        if (!$item) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_ITEM_NOT_FOUND',
                        'message' => "Item with SKU '$sku' not found.",
                    ],
                ])
                ->setStatusCode(404);
        }

        $quantities = $db->table('item_quantities')
            ->select('item_quantities.location_id, stock_locations.location_name, item_quantities.quantity')
            ->join('stock_locations', 'stock_locations.location_id = item_quantities.location_id')
            ->where('item_quantities.item_id', $item['item_id'])
            ->get()
            ->getResultArray();

        $totalStock = 0;
        $stockLocations = [];

        foreach ($quantities as $q) {
            $totalStock += (int)$q['quantity'];
            $stockLocations[] = [
                'location_id' => (int)$q['location_id'],
                'location_name' => $q['location_name'],
                'quantity' => (int)$q['quantity'],
            ];
        }

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'item_id' => (int)$item['item_id'],
                    'name' => $item['name'],
                    'item_number' => $item['item_number'],
                    'reorder_level' => (int)$item['reorder_level'],
                    'total_stock' => $totalStock,
                    'stock_locations' => $stockLocations,
                ],
            ])
            ->setStatusCode(200);
    }
}
