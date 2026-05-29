<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class Dashboard_model extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'sale_id';

    public function getTotalTransactions(string $dateFrom, string $dateTo): int
    {
        $builder = $this->db->table('sales');
        $builder->select('COUNT(*) AS count')
            ->where('sale_status', COMPLETED)
            ->where('DATE(sale_time) >=', $dateFrom)
            ->where('DATE(sale_time) <=', $dateTo);

        return (int)($builder->get()->getRow()->count ?? 0);
    }

    public function getTotalRevenue(string $dateFrom, string $dateTo): float
    {
        $saleIds = $this->getCompletedSaleIds($dateFrom, $dateTo);

        if (empty($saleIds)) {
            return 0.0;
        }

        $builder = $this->db->table('sales_payments');
        $builder->select('COALESCE(SUM(payment_amount), 0) AS total', false)
            ->whereIn('sale_id', $saleIds);

        return (float)($builder->get()->getRow()->total ?? 0);
    }

    public function getHourlyRevenue(string $dateFrom, string $dateTo): array
    {
        $builder = $this->db->table('sales AS s');
        $builder->select('HOUR(s.sale_time) AS hour, COALESCE(SUM(sp.payment_amount), 0) AS revenue', false)
            ->join('sales_payments AS sp', 's.sale_id = sp.sale_id')
            ->where('s.sale_status', COMPLETED)
            ->where('DATE(s.sale_time) >=', $dateFrom)
            ->where('DATE(s.sale_time) <=', $dateTo)
            ->groupBy('HOUR(s.sale_time)')
            ->orderBy('hour');

        $results = $builder->get()->getResultArray();
        $revenueByHour = [];

        foreach ($results as $row) {
            $revenueByHour[(int)$row['hour']] = (float)$row['revenue'];
        }

        $hourly = [];
        for ($hour = 6; $hour <= 22; $hour++) {
            $hourly[] = [
                'hour' => $hour,
                'revenue' => $revenueByHour[$hour] ?? 0.0,
            ];
        }

        return $hourly;
    }

    private function getCompletedSaleIds(string $dateFrom, string $dateTo): array
    {
        $builder = $this->db->table('sales');
        $builder->select('sale_id')
            ->where('sale_status', COMPLETED)
            ->where('DATE(sale_time) >=', $dateFrom)
            ->where('DATE(sale_time) <=', $dateTo);

        $result = $builder->get()->getResultArray();
        return array_column($result, 'sale_id');
    }
}
