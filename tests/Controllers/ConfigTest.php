<?php

namespace Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

class ConfigTest extends CIUnitTestCase
{
    public function testBalanceConfigExistsInDatabase(): void
    {
        $db = Database::connect();
        $builder = $db->table('app_config');

        $cashRow = $builder->getWhere(['key' => 'balance_cash_initial'])->getRow();
        $bankRow = $builder->getWhere(['key' => 'balance_bank_initial'])->getRow();

        $this->assertNotNull($cashRow, 'balance_cash_initial should exist in app_config');
        $this->assertEquals('0', $cashRow->value);
        $this->assertNotNull($bankRow, 'balance_bank_initial should exist in app_config');
        $this->assertEquals('0', $bankRow->value);
    }

    public function testBalanceConfigCanBeUpdatedViaBatchSave(): void
    {
        $model = model(\App\Models\Appconfig::class);

        $result = $model->batch_save([
            'balance_cash_initial' => '2500000',
            'balance_bank_initial' => '5000000',
        ]);

        $this->assertTrue($result);

        $settings = config(\Config\OSPOS::class)->settings;
        $this->assertEquals('2500000', $settings['balance_cash_initial']);
        $this->assertEquals('5000000', $settings['balance_bank_initial']);

        $model->batch_save(['balance_cash_initial' => '0', 'balance_bank_initial' => '0']);
    }
}
