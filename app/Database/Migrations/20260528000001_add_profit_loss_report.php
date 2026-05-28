<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_profit_loss_report extends Migration
{
    public function up(): void
    {
        $this->db->table('permissions')->ignore(true)->insert([
            'permission_id' => 'reports_profit_loss',
            'module_id'     => 'reports'
        ]);

        $this->db->table('grants')->ignore(true)->insert([
            'permission_id' => 'reports_profit_loss',
            'person_id'     => 1
        ]);

        $config_values = [
            ['key' => 'balance_cash_initial', 'value' => '0'],
            ['key' => 'balance_bank_initial', 'value' => '0']
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($config_values);
    }

    public function down(): void
    {
        $this->db->table('permissions')->delete(['permission_id' => 'reports_profit_loss']);
        $this->db->table('app_config')->delete(['key' => 'balance_cash_initial']);
        $this->db->table('app_config')->delete(['key' => 'balance_bank_initial']);
    }
}
