<?php

// Activa el modo estricto para los tipos escalares en las llamadas desde este archivo.
declare(strict_types=1);

// Evita mostrar errores al usuario y activa su registro para revisión.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Atiende archivos estáticos directamente cuando se usa el servidor integrado de PHP.
if (PHP_SAPI === 'cli-server') {
    // Extrae la ruta solicitada, sin los parámetros de consulta.
    $ruta = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

    // Delega al servidor integrado la entrega de archivos existentes.
    if (is_string($ruta) && $ruta !== '/' && is_file(__DIR__ . $ruta)) {
        return false;
    }
}

// Permite utilizar los nombres cortos de las clases necesarias.
use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\ManejadorExcepciones;
use App\Middleware\EncabezadosSeguridadMiddleware;
use App\Nucleo\Enrutamiento\Enrutador;

// Inicializa la aplicación y obtiene su contenedor de dependencias.
$contenedor = require __DIR__ . '/../bootstrap/aplicacion.php';

// Construye un objeto con los datos de la petición HTTP actual.
$solicitud = Solicitud::capturarActual();

try {
    // Procesa la petición y aplica encabezados de seguridad a la respuesta.
    $respuesta = $contenedor->obtener(
        EncabezadosSeguridadMiddleware::class)->manejar(
        $solicitud,
        
        // Continúa el procesamiento mediante el enrutador.
        fn (Solicitud $solicitud) => $contenedor->obtener(Enrutador::class)->despachar($solicitud)
    );
} catch (Throwable $excepcion) {
    // Convierte los errores o excepciones del bloque try en una respuesta controlada.
    $respuesta = $contenedor->obtener(ManejadorExcepciones::class)->renderizar($excepcion);
}

// Envía el estado HTTP, los encabezados y el contenido de la respuesta.
$respuesta->enviar();