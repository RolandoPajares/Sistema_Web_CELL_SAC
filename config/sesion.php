<?php

declare(strict_types=1);

use App\Nucleo\Entorno;

return [
    'nombre' => Entorno::obtener('SESSION_NAME', 'md_technology_session'),
    'save_path' => dirname(__DIR__) . '/storage/sessions',
    'secure' => Entorno::leerBooleano('SESSION_SECURE', false),
    'httponly' => true,
    'samesite' => Entorno::obtener('SESSION_SAMESITE', 'Lax'),
    'use_strict_mode' => true,
];
