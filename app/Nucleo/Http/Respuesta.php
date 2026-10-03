<?php

declare(strict_types=1);

namespace App\Nucleo\Http;

class Respuesta
{
    /**
     * @param array<string, string> $encabezados
     */
    public function __construct(
        protected string $contenido = '',
        protected int $estado = 200,
        protected array $encabezados = [],
    ) {
    }

    /**
     * Envía el contenido y los encabezados de la respuesta HTTP al cliente.
     */
    public function enviar(): void
    {
        if ($this->estado === 419) {
            $protocolo = (string) ($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
            header($protocolo . ' 419 Authentication Timeout');
        } else {
            http_response_code($this->estado);
        }

        foreach ($this->encabezados as $nombre => $valor) {
            header($nombre . ': ' . $valor);
        }

        echo $this->contenido;
    }

    /**
     * Devuelve el estado actual del recurso o proceso consultado.
     */
    public function estado(): int
    {
        return $this->estado;
    }

    /**
     * Devuelve el contenido configurado para la respuesta HTTP.
     */
    public function contenido(): string
    {
        return $this->contenido;
    }

    /**
     * Devuelve los encabezados configurados para la respuesta HTTP.
     *
     * @return array<string, string>
     */
    public function encabezados(): array
    {
        return $this->encabezados;
    }

    /**
     * Crea una respuesta con el encabezado HTTP adicional indicado.
     */
    public function conEncabezado(string $nombre, string $valor): self
    {
        $this->encabezados[$nombre] = $valor;

        return $this;
    }
}
