<?php

declare(strict_types=1);

namespace App\Validacion\Autenticacion;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudInicioSesion
{
    /** @return array{correo:string,contrasena:string} */
    public static function validar(Solicitud $solicitud): array
    {
        $entradaCorreo = $solicitud->entrada('email', '');
        $entradaContrasena = $solicitud->entrada('password', '');
        $correo = is_scalar($entradaCorreo) ? trim((string) $entradaCorreo) : '';
        $contrasena = is_scalar($entradaContrasena) ? (string) $entradaContrasena : '';
        $errores = [];

        if (mb_strlen($correo) > 160 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Ingresa un correo válido.';
        }

        if ($contrasena === '' || mb_strlen($contrasena) > 4096) {
            $errores['contrasena'] = 'Ingresa tu contraseña.';
        }

        if ($errores) {
            throw new ExcepcionValidacion($errores);
        }

        return ['correo' => $correo, 'contrasena' => $contrasena];
    }
}
