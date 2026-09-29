<?php

declare(strict_types=1);

namespace App\Validacion\Clientes;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudCliente
{
    public static function validar(Solicitud $solicitud): array
    {
        $datos = [];
        foreach (['tipo', 'documento', 'empresa', 'contacto', 'correo', 'telefono', 'ciudad'] as $campo) {
            $valor = $solicitud->entrada($campo, '');
            $datos[$campo] = is_scalar($valor) ? trim((string) $valor) : '';
        }
        $errores = [];
        if (!in_array($datos['tipo'], ['minorista', 'mayorista'], true)) {
            $errores['tipo'] = 'El tipo de cliente no es válido.';
        }
        if (preg_match('/^(\d{8}|\d{11})$/', $datos['documento']) !== 1) {
            $errores['documento'] = 'El documento debe ser un DNI de 8 dígitos o un RUC de 11 dígitos.';
        }
        if ($datos['contacto'] === '' || mb_strlen($datos['contacto']) > 160) {
            $errores['contacto'] = 'El nombre de contacto es obligatorio y admite hasta 160 caracteres.';
        }
        if (mb_strlen($datos['empresa']) > 160) {
            $errores['empresa'] = 'La razón social admite hasta 160 caracteres.';
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
}
