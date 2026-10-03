<?php

declare(strict_types=1);

use App\Nucleo\Entorno;

return [
    'name' => Entorno::obtener('APP_NAME', 'MD Technology Digital Cell'),
    'env' => Entorno::obtener('APP_ENV', 'production'),
    'debug' => Entorno::leerBooleano('APP_DEBUG', false),
    'url' => Entorno::obtener('APP_URL', ''),
    'timezone' => Entorno::obtener('APP_TIMEZONE', 'America/Lima'),
    'currency' => Entorno::obtener('CURRENCY', 'S/'),
    'address' => Entorno::obtener('SITE_ADDRESS', 'Jr. Amazonas 563, Bagua 01721'),
];
