<?php

namespace App\Models;

use CodeIgniter\Model;

class TokenBlacklist extends Model
{
    protected $table = 'token_blacklist';
    protected $primaryKey = 'id';
    protected $allowedFields = ['token_hash', 'expires_at', 'created_at'];
    protected $useTimestamps = false;

    public function blacklist(string $token, int $expiresAt): void
    {
        $this->insert([
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function isBlacklisted(string $token): bool
    {
        $hash = hash('sha256', $token);
        return $this->where('token_hash', $hash)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->countAllResults() > 0;
    }

    public function cleanExpired(): void
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }
}
