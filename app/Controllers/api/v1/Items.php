<?php

namespace App\Controllers\api\v1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Items extends ResourceController
{
    public function index(): ResponseInterface
    {
        $term = $this->request->getVar('term');
        $category = $this->request->getVar('category');
        $locationId = $this->request->getVar('location_id') ?? 1;
        $limit = $this->request->getVar('limit') ?? 25;

        $db = db_connect();
        $builder = $db->table('items');

        $builder->select('items.item_id, items.item_number, items.name, items.category, items.cost_price, items.unit_price, items.pic_filename');
        $builder->select('suppliers.person_id AS supplier_id, suppliers.company_name AS supplier_name');
        $builder->select('item_quantities.quantity');

        $builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $builder->join('item_quantities', "item_quantities.item_id = items.item_id AND item_quantities.location_id = $locationId", 'left');

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

        return $this->respond([
            'status' => 'success',
            'data' => $items,
        ]);
    }
}
