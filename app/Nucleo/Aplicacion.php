<?php

declare(strict_types=1);

namespace App\Nucleo;

final class Aplicacion
{
    private static ?Contenedor $contenedor = null;

    /**
     * Registra el contenedor de servicios que resolverá las dependencias de la aplicación.
     */
    public static function establecerContenedor(Contenedor $contenedor): void
    {
        self::$contenedor = $contenedor;
    }

    /**
     * Devuelve el contenedor de servicios configurado para la aplicación.
     */
    public static function contenedor(): Contenedor
    {
        if (self::$contenedor === null) {
            throw new \RuntimeException('El contenedor de la aplicación no está disponible.');
        }

        return self::$contenedor;
    }

    /** Resuelve una dependencia a través del contenedor principal de la aplicación. */
    public static function obtener(string $abstracto): mixed
    {
        return self::contenedor()->obtener($abstracto);
    }
}
