<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class API extends BaseConfig
{
    public string $jwt_secret = 'kasirbaru_jwt_secret_change_this_to_a_random_64_char_string';

    public int $jwt_expiry = 3600;

    public string $jwt_algorithm = 'HS256';

    public array $cors = [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 7200,
    ];
}
