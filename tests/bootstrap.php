<?php

declare(strict_types=1);

$obtenerVariable = static function (string $nombre): string {
    $valor = getenv($nombre);

    return $valor === false ? '' : trim($valor);
};

$rutaBase = dirname(__DIR__);
$valoresArchivoEntorno = [];
$rutaArchivoEntorno = $rutaBase . '/.env';

if (is_file($rutaArchivoEntorno)) {
    foreach (file($rutaArchivoEntorno, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }

        [$nombre, $valor] = explode('=', $linea, 2);
        $valor = trim($valor);

        if (
            (str_starts_with($valor, '"') && str_ends_with($valor, '"'))
            || (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
        ) {
            $valor = substr($valor, 1, -1);
        }

        $valoresArchivoEntorno[trim($nombre)] = $valor;
    }
}

$baseDatosPruebas = $obtenerVariable('DB_TEST_DATABASE');
$baseDatosAplicacion = $obtenerVariable('DB_APP_DATABASE_GUARD');
$baseDatosConfigurada = $valoresArchivoEntorno['DB_DATABASE'] ?? 'md_tecnologia_digital_cell';
$contrasenaPruebasConfigurada = getenv('DB_TEST_PASSWORD') !== false
    || (
        $obtenerVariable('DB_TEST_PASSWORD_EMPTY') === '1'
        && array_key_exists('DB_PASSWORD', $valoresArchivoEntorno)
        && $valoresArchivoEntorno['DB_PASSWORD'] === ''
    );
$configuracionIncompleta = $baseDatosPruebas === ''
    || $baseDatosAplicacion === ''
    || $obtenerVariable('DB_TEST_HOST') === ''
    || $obtenerVariable('DB_TEST_PORT') === ''
    || getenv('DB_TEST_USERNAME') === false
    || !$contrasenaPruebasConfigurada
    || getenv('DB_HOST') === false
    || getenv('DB_PORT') === false
    || getenv('DB_DATABASE') === false
    || getenv('DB_USERNAME') === false;

if (
    $configuracionIncompleta
    || preg_match('/\A[a-zA-Z0-9_]+_test\z/', $baseDatosPruebas) !== 1
    || strcasecmp($baseDatosPruebas, $baseDatosAplicacion) === 0
    || strcasecmp($baseDatosPruebas, $baseDatosConfigurada) === 0
    || $obtenerVariable('DB_DATABASE') !== $baseDatosPruebas
    || $obtenerVariable('DB_TEST_HOST') !== $obtenerVariable('DB_HOST')
    || $obtenerVariable('DB_TEST_PORT') !== $obtenerVariable('DB_PORT')
    || $obtenerVariable('DB_TEST_USERNAME') !== $obtenerVariable('DB_USERNAME')
    || $obtenerVariable('DB_TEST_PASSWORD') !== $obtenerVariable('DB_PASSWORD')
) {
    throw new RuntimeException(
        'Configuración de pruebas insegura o incompleta. Define una base exclusiva terminada en _test, distinta de la aplicación, antes de iniciar PHPUnit.'
    );
}

if (PHP_SAPI === 'cli') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

require $rutaBase . '/bootstrap/aplicacion.php';
