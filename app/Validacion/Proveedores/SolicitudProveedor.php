<?php

declare(strict_types=1);

namespace App\Validacion\Proveedores;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudProveedor
{
    public static function validar(Solicitud $solicitud): array
    {
        $datos = self::textos($solicitud, ['nombre', 'ruc', 'correo', 'telefono', 'ciudad']);
        $errores = [];
        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 160) {
            $errores['nombre'] = 'El nombre es obligatorio y admite hasta 160 caracteres.';
        }
        if (preg_match('/^(10|20)\d{9}$/', $datos['ruc']) !== 1) {
            $errores['ruc'] = 'El RUC debe contener 11 dígitos y comenzar con 10 o 20.';
        }
        if (filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) === false || mb_strlen($datos['correo']) > 160) {
            $errores['correo'] = 'Ingresa un correo electrónico válido.';
        }
        if ($datos['telefono'] !== '' && preg_match('/^\+?[0-9][0-9\s-]{6,19}$/', $datos['telefono']) !== 1) {
            $errores['telefono'] = 'Ingresa un teléfono válido de 7 a 20 caracteres.';
        }
        if (mb_strlen($datos['ciudad']) > 80) {
            $errores['ciudad'] = 'La ciudad admite hasta 80 caracteres.';
        }
        if ($errores !== []) {
            throw new ExcepcionValidacion($errores);
        }

        return $datos;
    }

    private static function textos(Solicitud $solicitud, array $campos): array
    {
        $datos = [];
        foreach ($campos as $campo) {
            $valor = $solicitud->entrada($campo, '');
            $datos[$campo] = is_scalar($valor) ? trim((string) $valor) : '';
        }

        return $datos;
    }
}
