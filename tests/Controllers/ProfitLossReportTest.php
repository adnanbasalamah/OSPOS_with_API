<?php

namespace Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use Config\OSPOS;

class ProfitLossReportTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testProfitLossRouteExists(): void
    {
        $routes = service('routes');
        $routes->loadRoutes();

        $matched = false;
        foreach ($routes->getRoutes() as $uri => $handler) {
            if (str_contains($uri, 'profit_loss')) {
                $matched = true;
                break;
            }
        }

        $this->assertTrue($matched, 'A route containing "profit_loss" should be registered');
    }

    public function testPermissionExistsInDatabase(): void
    {
        $db = Database::connect();
        $builder = $db->table('permissions');

        $row = $builder->getWhere(['permission_id' => 'reports_profit_loss'])->getRow();
        $this->assertNotNull($row, 'reports_profit_loss permission should exist');
        $this->assertEquals('reports', $row->module_id);
    }

    public function testAdminGrantExistsInDatabase(): void
    {
        $db = Database::connect();
        $builder = $db->table('grants');

        $row = $builder->getWhere([
            'permission_id' => 'reports_profit_loss',
            'person_id'     => 1
        ])->getRow();

        $this->assertNotNull($row, 'Admin should have reports_profit_loss grant');
    }

    public function testBalanceConfigDefaultsInDatabase(): void
    {
        $db = Database::connect();
        $builder = $db->table('app_config');

        $cashRow = $builder->getWhere(['key' => 'balance_cash_initial'])->getRow();
        $bankRow = $builder->getWhere(['key' => 'balance_bank_initial'])->getRow();

        $this->assertNotNull($cashRow, 'balance_cash_initial should exist');
        $this->assertEquals('0', $cashRow->value);
        $this->assertNotNull($bankRow, 'balance_bank_initial should exist');
        $this->assertEquals('0', $bankRow->value);
    }

    public function testProfitLossModelReturnsCorrectStructure(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);

        $data = $model->getData([
            'start_date' => '2026-01-01',
            'end_date'   => '2026-12-31',
        ]);

        $this->assertIsArray($data);
        $this->assertCount(7, $data);

        $expectedKeys = [
            lang('Reports.stock_balance'),
            lang('Reports.cash_balance'),
            lang('Reports.bank_balance'),
            lang('Reports.revenue'),
            lang('Reports.cogs'),
            lang('Reports.total_operational_expenses'),
            lang('Reports.profit_loss'),
        ];

        $actualLabels = array_map(fn($row) => $row['label'], $data);

        foreach ($expectedKeys as $expectedKey) {
            $this->assertContains($expectedKey, $actualLabels, "Report should contain: {$expectedKey}");
        }

        foreach ($data as $row) {
            $this->assertArrayHasKey('value', $row);
            $this->assertIsFloat($row['value']);
            $this->assertArrayHasKey('type', $row);
            $this->assertEquals('currency', $row['type']);
        }
    }

    public function testProfitLossModelGetDataColumnsReturnsArray(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $columns = $model->getDataColumns();

        $this->assertIsArray($columns);
        $this->assertNotEmpty($columns);
    }

    public function testProfitLossModelGetSummaryDataReturnsEmptyArray(): void
    {
        $model = model(\App\Models\Reports\Profit_loss::class);
        $summary = $model->getSummaryData([]);

        $this->assertIsArray($summary);
        $this->assertEmpty($summary);
    }

    public function testLanguageStringsEnExist(): void
    {
        $this->assertNotEmpty(lang('Reports.profit_loss'));
        $this->assertNotEmpty(lang('Reports.profit_loss_report'));
        $this->assertNotEmpty(lang('Reports.stock_balance'));
        $this->assertNotEmpty(lang('Reports.cash_balance'));
        $this->assertNotEmpty(lang('Reports.bank_balance'));
        $this->assertNotEmpty(lang('Reports.cogs'));
        $this->assertNotEmpty(lang('Reports.total_operational_expenses'));
    }

    public function testLanguageStringsIdExist(): void
    {
        $settings = config(OSPOS::class)->settings;
        $this->assertNotEmpty($settings['language']);

        $idStrings = [
            'Reports.profit_loss',
            'Reports.profit_loss_report',
            'Reports.stock_balance',
            'Reports.cash_balance',
            'Reports.bank_balance',
            'Reports.cogs',
            'Reports.total_operational_expenses',
        ];

        foreach ($idStrings as $key) {
            $this->assertNotEmpty(lang($key), "Language string '{$key}' should exist");
        }
    }
}