<?php

namespace Models\Reports;

use CodeIgniter\Test\CIUnitTestCase;

class ProfitLossTest extends CIUnitTestCase
{
    public function testModelExists(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $this->assertNotNull($model);
    }

    public function testGetDataColumnsReturnsArray(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $columns = $model->getDataColumns();

        $this->assertIsArray($columns);
    }

    public function testGetDataReturnsArray(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $data = $model->getData([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertIsArray($data);

        $expectedKeys = ['stock_balance', 'cash_balance', 'bank_balance', 'revenue', 'cogs', 'total_operational_expenses', 'profit_loss'];
        $foundLabels = array_map(fn($row) => $row['label'], $data);

        foreach ($expectedKeys as $key) {
            $this->assertContains(lang("Reports.$key"), $foundLabels, "Report should contain: $key");
        }
    }

    public function testGetDataReturnsCurrencyValues(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $data = $model->getData([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        foreach ($data as $row) {
            $this->assertArrayHasKey('value', $row);
            $this->assertIsFloat($row['value']);
            $this->assertArrayHasKey('type', $row);
            $this->assertEquals('currency', $row['type']);
        }
    }

    public function testGetSummaryDataReturnsEmptyArray(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $summary = $model->getSummaryData([]);

        $this->assertIsArray($summary);
        $this->assertEmpty($summary);
    }
}
