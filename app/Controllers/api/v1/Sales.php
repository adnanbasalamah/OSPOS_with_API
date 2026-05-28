<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use App\Libraries\Sale_lib;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Sale;
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

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $result,
            ])
            ->setStatusCode(200);
    }

    public function create(): ResponseInterface
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];

        if (empty($input['items']) || !is_array($input['items'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_VALIDATION_FAILED',
                        'message' => 'Item penjualan wajib diisi.',
                    ],
                ])
                ->setStatusCode(400);
        }

        if (empty($input['payments']) || !is_array($input['payments'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_VALIDATION_FAILED',
                        'message' => 'Pembayaran wajib diisi.',
                    ],
                ])
                ->setStatusCode(400);
        }

        $locationId = (int)($input['sale_location'] ?? 1);
        $employeeId = (int)$this->request->getHeaderLine('X-User-Id');
        $customerId = (int)($input['customer_id'] ?? NEW_ENTRY);
        $comment = $input['comment'] ?? '';

        $itemModel = model(Item::class);
        $itemQuantityModel = model(Item_quantity::class);

        $outOfStock = [];
        $items = [];

        foreach ($input['items'] as $i => $itemInput) {
            $itemId = (int)($itemInput['item_id'] ?? 0);
            $quantity = (float)($itemInput['quantity'] ?? 0);

            if ($itemId <= 0 || $quantity <= 0) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'error' => [
                            'code' => 'ERR_VALIDATION_FAILED',
                            'message' => 'Item #' . ($i + 1) . ': item_id dan quantity wajib valid.',
                        ],
                    ])
                    ->setStatusCode(400);
            }

            $itemInfo = $itemModel->get_info($itemId);
            if (empty($itemInfo) || !isset($itemInfo->item_id)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'error' => [
                            'code' => 'ERR_ITEM_NOT_FOUND',
                            'message' => 'Item #' . ($i + 1) . ' (ID: ' . $itemId . ') tidak ditemukan.',
                        ],
                    ])
                    ->setStatusCode(400);
            }

            if ($itemInfo->stock_type == HAS_STOCK) {
                $stockQty = $itemQuantityModel->get_item_quantity($itemId, $locationId);
                $available = (float)($stockQty->quantity ?? 0);
                if ($available < $quantity) {
                    $outOfStock[] = [
                        'item_id' => $itemId,
                        'name' => $itemInfo->name ?? '',
                        'requested' => $quantity,
                        'available' => $available,
                    ];
                }
            }

            $price = (float)($itemInput['price'] ?? $itemInfo->unit_price ?? 0);
            $discount = (float)($itemInput['discount'] ?? 0);
            $discountType = (int)($itemInput['discount_type'] ?? PERCENT);
            $costPrice = (float)($itemInfo->cost_price ?? 0);

            $items[] = [
                'item_id' => $itemId,
                'line' => $i,
                'description' => $itemInput['description'] ?? $itemInfo->description ?? '',
                'serialnumber' => $itemInput['serialnumber'] ?? '',
                'quantity' => $quantity,
                'discount' => $discount,
                'discount_type' => $discountType,
                'cost_price' => $costPrice,
                'price' => $price,
                'item_location' => $locationId,
                'print_option' => (int)($itemInput['print_option'] ?? PRINT_YES),
            ];
        }

        if (!empty($outOfStock)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_OUT_OF_STOCK',
                        'message' => 'Stok tidak mencukupi untuk beberapa item.',
                    ],
                    'data' => [
                        'out_of_stock' => $outOfStock,
                    ],
                ])
                ->setStatusCode(400);
        }

        $payments = [];
        foreach ($input['payments'] as $paymentInput) {
            if (empty($paymentInput['payment_type']) || !isset($paymentInput['payment_amount'])) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'error' => [
                            'code' => 'ERR_VALIDATION_FAILED',
                            'message' => 'Setiap pembayaran harus memiliki payment_type dan payment_amount.',
                        ],
                    ])
                    ->setStatusCode(400);
            }

            $payments[] = [
                'payment_type' => $paymentInput['payment_type'],
                'payment_amount' => (float)$paymentInput['payment_amount'],
                'cash_refund' => (float)($paymentInput['cash_refund'] ?? 0),
                'cash_adjustment' => (int)($paymentInput['cash_adjustment'] ?? CASH_ADJUSTMENT_FALSE),
            ];
        }

        $saleModel = model(Sale::class);
        $saleStatus = (string)COMPLETED;
        $salesTaxes = [[], []];
        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            $customerId,
            $employeeId,
            $comment,
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        if ($saleId <= 0) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_SAVE_FAILED',
                        'message' => 'Gagal menyimpan transaksi penjualan.',
                    ],
                ])
                ->setStatusCode(500);
        }

        $total = 0;
        foreach ($items as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            if ($item['discount_type'] == PERCENT) {
                $lineTotal -= ($lineTotal * $item['discount'] / 100);
            } else {
                $lineTotal -= $item['discount'];
            }
            $total += $lineTotal;
        }

        $paymentTotal = 0;
        foreach ($payments as $payment) {
            $paymentTotal += (float)$payment['payment_amount'] - (float)$payment['cash_refund'];
        }

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'sale_id' => $saleId,
                    'sale_id_display' => 'POS ' . $saleId,
                    'sale_time' => date('Y-m-d H:i:s'),
                    'total' => round($total, 2),
                    'amount_due' => round(max(0, $total - $paymentTotal), 2),
                    'item_count' => count($items),
                ],
            ])
            ->setStatusCode(201);
    }

    public function show($id = null): ResponseInterface
    {
        $saleModel = model(Sale::class);
        $saleInfo = $saleModel->get_info((int)$id)->getRow();

        if (empty($saleInfo) || !isset($saleInfo->sale_id)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error' => [
                        'code' => 'ERR_SALE_NOT_FOUND',
                        'message' => 'Transaksi penjualan tidak ditemukan.',
                    ],
                ])
                ->setStatusCode(404);
        }

        $customerModel = model(Customer::class);
        $customerInfo = $customerModel->get_info((int)$saleInfo->customer_id);

        $employeeModel = model(Employee::class);
        $employeeInfo = $employeeModel->get_info((int)$saleInfo->employee_id);

        $itemsResult = $saleModel->get_sale_items_ordered((int)$id);
        $items = [];
        foreach ($itemsResult->getResult() as $item) {
            $subtotal = (float)$item->quantity_purchased * (float)$item->item_unit_price;
            if ((int)$item->discount_type === PERCENT) {
                $subtotal -= ($subtotal * (float)$item->discount / 100);
            } else {
                $subtotal -= (float)$item->discount;
            }

            $items[] = [
                'item_id' => (int)$item->item_id,
                'name' => $item->name ?? '',
                'item_number' => $item->item_number ?? '',
                'quantity' => (float)$item->quantity_purchased,
                'price' => (float)$item->item_unit_price,
                'discount' => (float)$item->discount,
                'discount_type' => (int)$item->discount_type,
                'subtotal' => round(max(0, $subtotal), 2),
            ];
        }

        $paymentsResult = $saleModel->get_sale_payments((int)$id);
        $payments = [];
        foreach ($paymentsResult->getResult() as $payment) {
            $payments[] = [
                'payment_type' => $payment->payment_type ?? '',
                'payment_amount' => (float)($payment->payment_amount ?? 0),
                'cash_refund' => (float)($payment->cash_refund ?? 0),
            ];
        }

        $total = 0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        $total = round($total, 2);

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'sale_id' => (int)$saleInfo->sale_id,
                    'sale_id_display' => 'POS ' . $saleInfo->sale_id,
                    'sale_time' => $saleInfo->sale_time ?? '',
                    'customer' => [
                        'person_id' => (int)$saleInfo->customer_id,
                        'name' => trim(($customerInfo->first_name ?? '') . ' ' . ($customerInfo->last_name ?? '')),
                        'phone_number' => $customerInfo->phone_number ?? '',
                    ],
                    'employee' => [
                        'person_id' => (int)$saleInfo->employee_id,
                        'name' => trim(($employeeInfo->first_name ?? '') . ' ' . ($employeeInfo->last_name ?? '')),
                    ],
                    'items' => $items,
                    'payments' => $payments,
                    'total' => $total,
                    'comment' => $saleInfo->comment ?? '',
                ],
            ])
            ->setStatusCode(200);
    }
}
