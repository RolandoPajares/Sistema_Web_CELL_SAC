<?php

declare(strict_types=1);

namespace App\Nucleo\Enrutamiento;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\ContextoSolicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Contenedor;

final class Enrutador
{
    /** @var array<int, array{metodo:string,ruta:string,manejador:array{0:string,1:string},intermediarios:array<int,string>}> */
    private array $rutas = [];

    private ContextoSolicitud $contextoSolicitud;

    public function __construct(private Contenedor $contenedor)
    {
        $this->contextoSolicitud = $contenedor->obtener(ContextoSolicitud::class);
    }

    /**
     * Registra una ruta para el verbo HTTP GET.
     * @param array{0:string,1:string} $manejador
     * @param array<int, class-string> $intermediarios
     */
    public function obtener(string $ruta, array $manejador, array $intermediarios = []): void
    {
        $this->agregar('GET', $ruta, $manejador, $intermediarios);
    }

    /**
     * Registra una ruta para el verbo HTTP POST.
     * @param array{0:string,1:string} $manejador
     * @param array<int, class-string> $intermediarios
     */
    public function post(string $ruta, array $manejador, array $intermediarios = []): void
    {
        $this->agregar('POST', $ruta, $manejador, $intermediarios);
    }

    /**
     * Registra una ruta para el verbo HTTP DELETE.
     * @param array{0:string,1:string} $manejador
     * @param array<int, class-string> $intermediarios
     */
    public function delete(string $ruta, array $manejador, array $intermediarios = []): void
    {
        $this->agregar('DELETE', $ruta, $manejador, $intermediarios);
    }

    /**
     * Registra una ruta con su manejador y sus intermediarios.
     * @param array{0:string,1:string} $manejador
     * @param array<int, class-string> $intermediarios
     */
    public function agregar(string $metodo, string $ruta, array $manejador, array $intermediarios = []): void
    {
        $this->rutas[] = [
            'metodo' => strtoupper($metodo),
            'ruta' => '/' . trim($ruta, '/'),
            'manejador' => $manejador,
            'intermediarios' => $intermediarios,
        ];
    }

    /**
     * Busca la ruta que coincide con la solicitud y ejecuta su controlador.
     */
    public function despachar(Solicitud $solicitud): Respuesta
    {
        $this->contextoSolicitud->establecer($solicitud);

        foreach ($this->rutas as $rutaRegistrada) {
            $parametros = $this->coincidir($rutaRegistrada['metodo'], $rutaRegistrada['ruta'], $solicitud);

            if ($parametros === null) {
                continue;
            }

            $solicitud = $solicitud->conParametrosRuta($parametros);
            $this->contextoSolicitud->establecer($solicitud);
            $manejador = fn (Solicitud $solicitud): Respuesta => $this->invocar(
                $rutaRegistrada['manejador'],
                $solicitud
            );

            foreach (array_reverse($rutaRegistrada['intermediarios']) as $claseIntermediario) {
                $siguiente = $manejador;
                $manejador = function (Solicitud $solicitud) use ($claseIntermediario, $siguiente): Respuesta {
                    $intermediario = $this->contenedor->obtener($claseIntermediario);

                    return $intermediario->manejar($solicitud, $siguiente);
                };
            }

            return $manejador($solicitud);
        }

        return new Respuesta('Página no encontrada.', 404);
    }

    /**
     * Comprueba si la ruta coincide con el método y la dirección recibidos.
     *
     * @return array<string, string>|null
     */
    private function coincidir(string $metodo, string $rutaRegistrada, Solicitud $solicitud): ?array
    {
        if ($metodo !== $solicitud->metodo()) {
            return null;
        }

        $patron = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)}#', '(?P<$1>[^/]+)', $rutaRegistrada);
        $patron = '#^' . $patron . '$#';

        if (!preg_match($patron, $solicitud->ruta(), $coincidencias)) {
            return null;
        }

        return array_filter(
            $coincidencias,
            static fn (mixed $clave): bool => is_string($clave),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Ejecuta el controlador asociado a la ruta encontrada.
     *
     * @param array{0:string,1:string} $manejador
     */
    private function invocar(array $manejador, Solicitud $solicitud): Respuesta
    {
        [$clase, $metodo] = $manejador;
        $controlador = $this->contenedor->obtener($clase);
        $respuesta = $controlador->{$metodo}($solicitud);

        if ($respuesta instanceof Respuesta) {
            return $respuesta;
        }

        return new Respuesta((string) $respuesta);
    }
}
