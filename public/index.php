<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $ruta = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    if (is_string($ruta) && $ruta !== '/' && is_file(__DIR__ . $ruta)) {
        return false;
    }
}

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\ManejadorExcepciones;
use App\Middleware\EncabezadosSeguridadMiddleware;
use App\Nucleo\Enrutamiento\Enrutador;

$contenedor = require __DIR__ . '/../bootstrap/aplicacion.php';

$solicitud = Solicitud::capture();

try {
    $respuesta = $contenedor->obtener(EncabezadosSeguridadMiddleware::class)->manejar(
        $solicitud,
        fn (Solicitud $solicitud) => $contenedor->obtener(Enrutador::class)->despachar($solicitud)
    );
} catch (Throwable $excepcion) {
    $respuesta = $contenedor->obtener(ManejadorExcepciones::class)->renderizar($excepcion);
}

$respuesta->enviar();
