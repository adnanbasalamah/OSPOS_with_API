<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Profit_loss extends Report
{
    private array $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = config(OSPOS::class)->settings;
    }

    public function getDataColumns(): array
    {
        return [
            ['item_name' => lang('Reports.profit_loss_report')]
        ];
    }

    public function getData(array $inputs): array
    {
        $start_date = $inputs['start_date'];
        $end_date = $inputs['end_date'];

        $saldo_barang = $this->calculateSaldoBarang();
        $revenue = $this->calculateRevenue($start_date, $end_date);
        $cogs = $this->calculateCogs($start_date, $end_date);
        $biaya_operasional = $this->calculateBiayaOperasional($start_date, $end_date);

        $cash_in = $this->calculatePaymentInflow('Cash', $start_date, $end_date);
        $cash_out_purchases = $this->calculatePurchaseOutflow('Cash', $start_date, $end_date);
        $cash_out_expenses = $this->calculateExpenseOutflow('Cash', $start_date, $end_date);

        $bank_in = $this->calculatePaymentInflow('Bank', $start_date, $end_date);
        $bank_out_purchases = $this->calculatePurchaseOutflow('Bank', $start_date, $end_date);
        $bank_out_expenses = $this->calculateExpenseOutflow('Bank', $start_date, $end_date);

        $saldo_awal_kas = (float)($this->config['balance_cash_initial'] ?? 0);
        $saldo_awal_bank = (float)($this->config['balance_bank_initial'] ?? 0);

        $saldo_kas = $saldo_awal_kas + $cash_in - $cash_out_purchases - $cash_out_expenses;
        $saldo_bank = $saldo_awal_bank + $bank_in - $bank_out_purchases - $bank_out_expenses;
        $rugi_laba = $revenue - $cogs - $biaya_operasional;

        return [
            [
                'label' => lang('Reports.stock_balance'),
                'value' => $saldo_barang,
                'type'  => 'currency'
            ],
            [
                'label' => lang('Reports.cash_balance'),
                'value' => $saldo_kas,
                'type'  => 'currency'
            ],
            [
                'label' => lang('Reports.bank_balance'),
                'value' => $saldo_bank,
                'type'  => 'currency'
            ],
            [
                'label' => lang('Reports.revenue'),
                'value' => $revenue,
                'type'  => 'currency'
            ],
            [
                'label' => lang('Reports.cogs'),
                'value' => $cogs,
                'type'  => 'currency'
            ],
            [
                'label' => lang('Reports.total_operational_expenses'),
                'value' => $biaya_operasional,
                'type'  => 'currency'
            ],
            [
                'label' => lang('Reports.profit_loss'),
                'value' => $rugi_laba,
                'type'  => 'currency'
            ]
        ];
    }

    public function getSummaryData(array $inputs): array
    {
        return [];
    }

    private function calculateSaldoBarang(): float
    {
        $builder = $this->db->table('items AS items');
        $builder->select('COALESCE(SUM(items.cost_price * item_quantities.quantity), 0) AS total', false);
        $builder->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id');
        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', 0);

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    private function getCompletedSaleIds(string $start_date, string $end_date): array
    {
        $builder = $this->db->table('sales');
        $builder->select('sale_id');
        $builder->where('sale_status', 0);
        $builder->where('DATE(sale_time) >=', $start_date);
        $builder->where('DATE(sale_time) <=', $end_date);

        $result = $builder->get()->getResultArray();

        return array_column($result, 'sale_id');
    }

    private function calculateRevenue(string $start_date, string $end_date): float
    {
        $sale_ids = $this->getCompletedSaleIds($start_date, $end_date);

        if (empty($sale_ids)) {
            return 0.0;
        }

        $builder = $this->db->table('sales_payments');
        $builder->select('COALESCE(SUM(payment_amount), 0) AS total', false);
        $builder->whereIn('sale_id', $sale_ids);

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    private function calculateCogs(string $start_date, string $end_date): float
    {
        $sale_ids = $this->getCompletedSaleIds($start_date, $end_date);

        if (empty($sale_ids)) {
            return 0.0;
        }

        $builder = $this->db->table('sales_items');
        $builder->select('COALESCE(SUM(item_cost_price * quantity_purchased), 0) AS total', false);
        $builder->whereIn('sale_id', $sale_ids);

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    private function calculateBiayaOperasional(string $start_date, string $end_date): float
    {
        $builder = $this->db->table('expenses');
        $builder->select('COALESCE(SUM(amount), 0) AS total', false);
        $builder->where('deleted', 0);
        $builder->where('DATE(date) >=', $start_date);
        $builder->where('DATE(date) <=', $end_date);

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    private function calculatePaymentInflow(string $type, string $start_date, string $end_date): float
    {
        $sale_ids = $this->getCompletedSaleIds($start_date, $end_date);

        if (empty($sale_ids)) {
            return 0.0;
        }

        $builder = $this->db->table('sales_payments');
        $builder->select('COALESCE(SUM(payment_amount), 0) AS total', false);
        $builder->whereIn('sale_id', $sale_ids);

        if ($type === 'Cash') {
            $builder->where("payment_type IN ('Cash')", null, false);
        } else {
            $builder->where("payment_type NOT IN ('Cash')", null, false);
        }

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    private function calculatePurchaseOutflow(string $type, string $start_date, string $end_date): float
    {
        $builder = $this->db->table('receivings AS receivings');
        $builder->select('COALESCE(SUM(receivings_items.item_cost_price * receivings_items.quantity_purchased), 0) AS total', false);
        $builder->join('receivings_items AS receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');

        if ($type === 'Cash') {
            $builder->where("receivings.payment_type IN ('Cash')", null, false);
        } else {
            $builder->where("receivings.payment_type NOT IN ('Cash')", null, false);
        }

        $builder->where('DATE(receivings.receiving_time) >=', $start_date);
        $builder->where('DATE(receivings.receiving_time) <=', $end_date);

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    private function calculateExpenseOutflow(string $type, string $start_date, string $end_date): float
    {
        $builder = $this->db->table('expenses');
        $builder->select('COALESCE(SUM(amount), 0) AS total', false);
        $builder->where('deleted', 0);

        if ($type === 'Cash') {
            $builder->where("payment_type IN ('Cash')", null, false);
        } else {
            $builder->where("payment_type NOT IN ('Cash')", null, false);
        }

        $builder->where('DATE(date) >=', $start_date);
        $builder->where('DATE(date) <=', $end_date);

        return (float)($builder->get()->getRow()->total ?? 0);
    }
}
