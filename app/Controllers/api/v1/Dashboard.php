<?php

declare(strict_types=1);

namespace App\Controllers\api\v1;

use App\Models\DashboardModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Dashboard extends ResourceController
{
    public function index(): ResponseInterface
    {
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        foreach (['date_from' => $dateFrom, 'date_to' => $dateTo] as $param => $value) {
            if ($value !== null && !$this->isValidDate($value)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'error' => [
                            'code' => 'ERR_VALIDATION_FAILED',
                            'message' => "Invalid date format for {$param}. Use YYYY-MM-DD.",
                        ],
                    ])
                    ->setStatusCode(400);
            }
        }

        $today = date('Y-m-d');
        $dateFrom = $dateFrom ?? $today;
        $dateTo = $dateTo ?? $dateFrom;

        $model = model(DashboardModel::class);

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'total_transactions' => $model->getTotalTransactions($dateFrom, $dateTo),
                    'total_revenue' => $model->getTotalRevenue($dateFrom, $dateTo),
                    'hourly_revenue' => $model->getHourlyRevenue($dateFrom, $dateTo),
                ],
            ])
            ->setStatusCode(200);
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}
