<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Suppliers extends ResourceController
{
    public function index(): ResponseInterface
    {
        $search = $this->request->getVar('search');

        $db = db_connect();
        $builder = $db->table('suppliers');
        $builder->join('people', 'suppliers.person_id = people.person_id');
        $builder->where('suppliers.deleted', 0);

        if (!empty($search)) {
            $builder->like('suppliers.company_name', $search);
        }

        $builder->select('suppliers.person_id AS id, suppliers.company_name AS name, people.first_name, people.last_name');
        $builder->orderBy('suppliers.company_name', 'ASC');

        $suppliers = $builder->get()->getResultArray();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $suppliers,
            ])
            ->setStatusCode(200);
    }
}
