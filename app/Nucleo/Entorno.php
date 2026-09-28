<?php

declare(strict_types=1);

namespace App\Nucleo;

final class Entorno
{
    /** @var array<string, string> */
    private static array $valores = [];

    public static function load(string $ruta): void
    {
        if (!is_file($ruta)) {
            return;
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lineas === false) {
            return;
        }

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
                continue;
            }

            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);

            if (getenv($clave) !== false) {
                continue;
            }

            if (
                (str_starts_with($valor, '"') && str_ends_with($valor, '"'))
                || (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
            ) {
                $valor = substr($valor, 1, -1);
            }

            self::$valores[$clave] = $valor;
            $_ENV[$clave] = $valor;
            putenv($clave . '=' . $valor);
        }
    }

    public static function obtener(string $clave, ?string $predeterminado = null): ?string
    {
        $valor = self::$valores[$clave] ?? $_ENV[$clave] ?? getenv($clave);

        return $valor === false ? $predeterminado : $valor;
    }

    public static function bool(string $clave, bool $predeterminado = false): bool
    {
        $valor = self::obtener($clave);

        if ($valor === null) {
            return $predeterminado;
        }

        return filter_var($valor, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $predeterminado;
    }
}
