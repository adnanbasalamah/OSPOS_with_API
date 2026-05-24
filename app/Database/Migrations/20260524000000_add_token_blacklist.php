<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_token_blacklist extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'token_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token_hash');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('token_blacklist', true, ['dbprefix' => '']);
    }

    public function down(): void
    {
        $this->forge->dropTable('token_blacklist', true);
    }
}
