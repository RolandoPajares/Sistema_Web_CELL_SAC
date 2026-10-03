<?php

declare(strict_types=1);

namespace App\Soporte\Mensajes;

use App\Soporte\Sesion\GestorSesion;

final class MensajeFlashServicio
{
    private const KEY = '_flash';

    public function __construct(private GestorSesion $sesion)
    {
    }

    /**
     * Construye una respuesta satisfactoria con el contenido indicado.
     */
    public function exito(string $mensaje): void
    {
        $this->guardar('success', $mensaje);
    }

    /**
     * Construye una respuesta de error con el mensaje y estado adecuados.
     */
    public function error(string $mensaje): void
    {
        $this->guardar('error', $mensaje);
    }

    /**
     * Extrae el valor solicitado de los datos recibidos.
     */
    public function extraer(string $tipo, string $predeterminado = ''): string
    {
        $mensajes = (array) $this->sesion->obtener(self::KEY, []);
        $mensaje = (string) ($mensajes[$tipo] ?? $predeterminado);
        unset($mensajes[$tipo]);
        $this->sesion->guardar(self::KEY, $mensajes);

        return $mensaje;
    }

    private function guardar(string $tipo, string $mensaje): void
    {
        $mensajes = (array) $this->sesion->obtener(self::KEY, []);
        $mensajes[$tipo] = $mensaje;
        $this->sesion->guardar(self::KEY, $mensajes);
    }
}
