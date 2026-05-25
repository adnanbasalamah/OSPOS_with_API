<?php

namespace App\Controllers\api\v1;

use App\Libraries\Sale_lib;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Sales extends ResourceController
{
    public function paymentTypes(): ResponseInterface
    {
        helper('locale');
        $payments = get_payment_options();
        $saleLib = new Sale_lib();

        if ($saleLib->get_mode() === 'sale_work_order') {
            $payments[lang('Sales.cash_deposit')] = lang('Sales.cash_deposit');
            $payments[lang('Sales.credit_deposit')] = lang('Sales.credit_deposit');
        }

        $result = [];
        foreach ($payments as $id => $name) {
            $result[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return $this->respond([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
