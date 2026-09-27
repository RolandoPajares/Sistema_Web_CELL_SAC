<?php

declare(strict_types=1);

namespace App\Soporte;

final class Aplicacion
{
    private static ?Contenedor $contenedor = null;

    public static function establecerContenedor(Contenedor $contenedor): void
    {
        self::$contenedor = $contenedor;
    }

    public static function contenedor(): Contenedor
    {
        if (self::$contenedor === null) {
            throw new \RuntimeException('El contenedor de la aplicación no está disponible.');
        }

        return self::$contenedor;
    }

    public static function obtener(string $abstracto): mixed
    {
        return self::contenedor()->obtener($abstracto);
    }
}
