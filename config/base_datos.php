<?php

declare(strict_types=1);

use App\Nucleo\Entorno;

return [
    'host' => Entorno::obtener('DB_HOST', '127.0.0.1'),
    'port' => (int) Entorno::obtener('DB_PORT', '3306'),
    'database' => Entorno::obtener('DB_DATABASE', 'md_tecnologia_digital_cell'),
    'username' => Entorno::obtener('DB_USERNAME', ''),
    'password' => Entorno::obtener('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
];
