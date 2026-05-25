<?php

namespace App\Controllers\api\v1;

use App\Libraries\Sale_lib;
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

        return $this->respond([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function create(): ResponseInterface
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];

        if (empty($input['items']) || !is_array($input['items'])) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Item penjualan wajib diisi.',
            ], 400);
        }

        if (empty($input['payments']) || !is_array($input['payments'])) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Pembayaran wajib diisi.',
            ], 400);
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
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Item #' . ($i + 1) . ': item_id dan quantity wajib valid.',
                ], 400);
            }

            $itemInfo = $itemModel->get_info($itemId);
            if (empty($itemInfo) || !isset($itemInfo->item_id)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Item #' . ($i + 1) . ' (ID: ' . $itemId . ') tidak ditemukan.',
                ], 400);
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
            return $this->respond([
                'status' => 'error',
                'message' => 'Stok tidak mencukupi untuk beberapa item.',
                'data' => [
                    'out_of_stock' => $outOfStock,
                ],
            ], 400);
        }

        $payments = [];
        foreach ($input['payments'] as $paymentInput) {
            if (empty($paymentInput['payment_type']) || !isset($paymentInput['payment_amount'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Setiap pembayaran harus memiliki payment_type dan payment_amount.',
                ], 400);
            }

            $payments[] = [
                'payment_type' => $paymentInput['payment_type'],
                'payment_amount' => (float)$paymentInput['payment_amount'],
                'cash_refund' => (float)($paymentInput['cash_refund'] ?? 0),
                'cash_adjustment' => (int)($paymentInput['cash_adjustment'] ?? CASH_ADJUSTMENT_FALSE),
            ];
        }

        $saleModel = model(Sale::class);
        $saleStatus = COMPLETED;
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
            return $this->respond([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi penjualan.',
            ], 500);
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

        return $this->respond([
            'status' => 'success',
            'data' => [
                'sale_id' => $saleId,
                'sale_id_display' => 'POS ' . $saleId,
                'sale_time' => date('Y-m-d H:i:s'),
                'total' => round($total, 2),
                'amount_due' => round(max(0, $total - $paymentTotal), 2),
                'item_count' => count($items),
            ],
        ], 201);
    }
}
