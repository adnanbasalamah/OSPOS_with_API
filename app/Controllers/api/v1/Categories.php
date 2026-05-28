<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Categories extends ResourceController
{
    public function index(): ResponseInterface
    {
        $search = $this->request->getVar('search');

        $db = db_connect();
        $builder = $db->table('items');
        $builder->distinct();
        $builder->select('category');
        $builder->where('deleted', 0);

        if (!empty($search)) {
            $builder->like('category', $search);
        }

        $builder->orderBy('category', 'ASC');

        $rows = $builder->get()->getResultArray();
        $categories = array_column($rows, 'category');

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $categories,
            ])
            ->setStatusCode(200);
    }
}
