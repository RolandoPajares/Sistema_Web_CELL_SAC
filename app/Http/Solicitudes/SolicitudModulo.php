<?php

declare(strict_types=1);

namespace App\Http\Solicitudes;

use App\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudModulo
{
    /** @param array<string, array<string, mixed>> $campos
     *  @return array<string, mixed>
     */
    public static function validar(Solicitud $solicitud, array $campos): array
    {
        $datos = [];
        $errores = [];
        foreach ($campos as $nombre => $campo) {
            $valor = trim((string) $solicitud->entrada($nombre, ''));
            $etiqueta = (string) ($campo['label'] ?? $nombre);
            $requerido = (bool) ($campo['required'] ?? false);
            if ($requerido && $valor === '') {
                $errores[$nombre] = "El campo {$etiqueta} es obligatorio.";
                continue;
            }
            if ($valor !== '' && ($campo['type'] ?? '') === 'email' && filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
                $errores[$nombre] = 'Ingresa un correo electrónico válido.';
                continue;
            }
            if ($valor !== '' && ($campo['type'] ?? '') === 'number') {
                if (!is_numeric($valor)) {
                    $errores[$nombre] = "El campo {$etiqueta} debe ser numérico.";
                    continue;
                }
                $minimo = (float) ($campo['min'] ?? 0);
                if ((float) $valor < $minimo) {
                    $errores[$nombre] = "El campo {$etiqueta} debe ser mayor o igual a {$minimo}.";
                    continue;
                }
            }
            if (isset($campo['options']) && $valor !== '' && !array_key_exists($valor, (array) $campo['options'])) {
                $errores[$nombre] = "El valor seleccionado para {$etiqueta} no es válido.";
                continue;
            }
            if (mb_strlen($valor) > (int) ($campo['max'] ?? 500)) {
                $errores[$nombre] = "El campo {$etiqueta} excede la longitud permitida.";
                continue;
            }
            $datos[$nombre] = $valor;
        }

        if ($errores !== []) {
            throw new ExcepcionValidacion($errores);
        }

        return $datos;
    }
}
