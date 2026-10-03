<?php

declare(strict_types=1);

/**
 * Consola de Administración y Migraciones
 */

// Validación estricta: este script solo puede ejecutarse desde la terminal (CLI)
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse desde la consola.\n");
    exit(1);
}

// Inicialización de rutas, contenedor de dependencias y argumentos
$rutaRaiz = dirname(__DIR__);
$contenedor = require $rutaRaiz . '/bootstrap/aplicacion.php';
$comando = $argv[1] ?? 'ayuda';
$directorioMigraciones = $rutaRaiz . '/database/migrations';

try {
    switch ($comando) {
        // Ejecuta las migraciones pendientes en la base de datos
        case 'migrar':
            $ejecutor = $contenedor->obtener(App\Nucleo\BaseDatos\EjecutorMigraciones::class);
            $migraciones = $ejecutor->migrar($directorioMigraciones);
            
            $mensajeResultado = $migraciones === []
                ? "La base de datos ya esta actualizada.\n"
                : "Migraciones ejecutadas:\n" . implode("\n", $migraciones) . "\n";
            
            fwrite(STDOUT, $mensajeResultado);
            break;

        // Muestra el estado actual (ejecutada o pendiente) de cada migración
        case 'estado:migraciones':
            $ejecutor = $contenedor->obtener(App\Nucleo\BaseDatos\EjecutorMigraciones::class);
            
            foreach ($ejecutor->estado($directorioMigraciones) as $migracion) {
                $estado = $migracion['ejecutada'] ? 'Ejecutada' : 'Pendiente';
                fwrite(STDOUT, $estado . ' | ' . $migracion['migracion'] . "\n");
            }
            break;

        // Crea un nuevo usuario administrador mediante la consola
        case 'administrador:crear':
            if (count($argv) !== 5) {
                fwrite(STDERR, "Uso: php bin/consola.php administrador:crear \"Nombre\" correo clave\n");
                exit(2);
            }
            
            $idAdministrador = $contenedor->obtener(App\Servicios\Usuarios\CreacionAdministradorServicio::class)
                ->crear($argv[2], $argv[3], $argv[4]);
            
            fwrite(STDOUT, "Administrador creado. Identificador: {$idAdministrador}\n");
            break;

        // Muestra la ayuda y los comandos disponibles por defecto
        default:
            fwrite(STDOUT, "Comandos disponibles:\n");
            fwrite(STDOUT, "  migrar\n  estado:migraciones\n  administrador:crear \"Nombre\" correo clave\n");
            break;
    }
} catch (Throwable $error) {
    // Manejo global de excepciones para errores inesperados en consola
    fwrite(STDERR, 'Error: ' . $error->getMessage() . "\n");
    exit(1);
}