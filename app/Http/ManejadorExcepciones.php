<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Respuestas\Respuesta;
use App\Infraestructura\Registros\RegistradorArchivo;
use App\Soporte\RepositorioConfiguracion;
use App\Soporte\Excepciones\ExcepcionAutenticacion;
use App\Soporte\Excepciones\ExcepcionAutorizacion;
use App\Soporte\Excepciones\ExcepcionNoEncontrado;
use App\Soporte\Excepciones\ExcepcionValidacion;
use DomainException;
use Throwable;

final class ManejadorExcepciones
{
    public function __construct(private RepositorioConfiguracion $configuracion, private RegistradorArchivo $registro)
    {
    }

    public function renderizar(Throwable $excepcion): Respuesta
    {
        $estado = match (true) {
            $excepcion instanceof ExcepcionValidacion => 422,
            $excepcion instanceof ExcepcionAutenticacion => 401,
            $excepcion instanceof ExcepcionAutorizacion => 403,
            $excepcion instanceof ExcepcionNoEncontrado => 404,
            $excepcion instanceof DomainException => 409,
            default => 500,
        };

        $this->registro->error('Excepción no controlada durante la solicitud.', [
            'exception' => $excepcion::class,
            'message' => $excepcion->getMessage(),
            'file' => $excepcion->getFile(),
            'line' => $excepcion->getLine(),
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        ]);

        $mensaje = match ($estado) {
            401 => 'Debes iniciar sesión para continuar.',
            403 => 'No tienes permisos para realizar esta operación.',
            404 => 'Página no encontrada.',
            409, 422 => 'No se pudo completar la operación. Revisa los datos ingresados.',
            default => 'No se pudo completar la operación.',
        };

        if ((bool) $this->configuracion->obtener('app.debug', false)) {
            $mensaje .= ' ' . $excepcion::class . ': ' . $excepcion->getMessage();
        }

        return new Respuesta(e($mensaje), $estado);
    }
}
