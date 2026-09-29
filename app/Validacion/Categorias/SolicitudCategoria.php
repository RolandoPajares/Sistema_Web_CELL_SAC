<?php

declare(strict_types=1);

namespace App\Validacion\Categorias;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudCategoria
{
    public static function validar(Solicitud $solicitud): array
    {
        $nombre = trim((string) $solicitud->entrada('nombre', ''));
        $descripcion = trim((string) $solicitud->entrada('descripcion', ''));
        $errores = [];
        if ($nombre === '' || mb_strlen($nombre) > 120) {
            $errores['nombre'] = 'El nombre es obligatorio y admite hasta 120 caracteres.';
        }
        if (mb_strlen($descripcion) > 500) {
            $errores['descripcion'] = 'La descripción admite hasta 500 caracteres.';
        }
        if ($errores !== []) {
            throw new ExcepcionValidacion($errores);
        }

        return ['nombre' => $nombre, 'descripcion' => $descripcion];
    }
}
