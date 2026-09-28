<?php

declare(strict_types=1);

namespace App\Validacion\Autenticacion;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudRegistro
{
    /** @return array{nombre:string,correo:string,contrasena:string} */
    public static function validar(Solicitud $solicitud): array
    {
        $entradaNombre = $solicitud->entrada('name', '');
        $entradaCorreo = $solicitud->entrada('email', '');
        $entradaContrasena = $solicitud->entrada('password', '');
        $nombre = is_scalar($entradaNombre) ? trim((string) $entradaNombre) : '';
        $correo = is_scalar($entradaCorreo) ? trim((string) $entradaCorreo) : '';
        $contrasena = is_scalar($entradaContrasena) ? (string) $entradaContrasena : '';
        $errores = [];

        if ($nombre === '' || mb_strlen($nombre) > 120) {
            $errores['nombre'] = 'Ingresa un nombre de hasta 120 caracteres.';
        }

        if (mb_strlen($correo) > 160 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Ingresa un correo válido.';
        }

        if (mb_strlen($contrasena) < 8 || mb_strlen($contrasena) > 4096) {
            $errores['contrasena'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($errores) {
            throw new ExcepcionValidacion($errores);
        }

        return ['nombre' => $nombre, 'correo' => $correo, 'contrasena' => $contrasena];
    }
}
