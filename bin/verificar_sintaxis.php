<?php

declare(strict_types=1);

/**
 * Recorre los directorios clave del proyecto para detectar archivos PHP con
 * errores de sintaxis usando el linter nativo de PHP (-l).
 */

$rutaRaiz = dirname(__DIR__);
$directoriosPermitidos = [
    'app', 
    'bin', 
    'bootstrap', 
    'config', 
    'public', 
    'resources', 
    'routes', 
    'tests'
];

/**
 * Recopila de forma recursiva todos los archivos .PHP dentro de los directorios dados.
 */
function obtenerArchivosPhp(string $rutaRaiz, array $directorios): array {
    $archivosPhp = [];

    foreach ($directorios as $directorio) {
        $rutaDirectorio = $rutaRaiz . DIRECTORY_SEPARATOR . $directorio;
        
        if (!is_dir($rutaDirectorio)) {
            continue;
        }

        $recorrido = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rutaDirectorio, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($recorrido as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php') {
                $archivosPhp[] = $archivo->getPathname();
            }
        }
    }

    sort($archivosPhp, SORT_STRING);
    return $archivosPhp;
}

/**
 * Ejecuta el linter de PHP (-l) en cada archivo para verificar errores de sintaxis.
 */
function verificarErroresSintaxis(array $archivos): array {
    $erroresSintaxis = [];

    foreach ($archivos as $rutaArchivo) {
        $salida = [];
        $codigoSalida = 0;
        
        $comando = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($rutaArchivo) . ' 2>&1';
        exec($comando, $salida, $codigoSalida);

        if ($codigoSalida !== 0) {
            $erroresSintaxis[] = implode(PHP_EOL, $salida);
        }
    }

    return $erroresSintaxis;
}

// Ejecución principal del script
$archivos = obtenerArchivosPhp($rutaRaiz, $directoriosPermitidos);
$erroresSintaxis = verificarErroresSintaxis($archivos);

// Manejo de resultados
if ($erroresSintaxis !== []) {
    fwrite(STDERR, implode(PHP_EOL . PHP_EOL, $erroresSintaxis) . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, count($archivos) . " archivos PHP sin errores de sintaxis.\n");