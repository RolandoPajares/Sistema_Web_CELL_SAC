<?php

declare(strict_types=1);

namespace App\Nucleo\Http;

class Respuesta
{
    /** @param array<string, string> $encabezados */
    public function __construct(
        protected string $contenido = '',
        protected int $estado = 200,
        protected array $encabezados = [],
    ) {
    }

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

    public function estado(): int
    {
        return $this->estado;
    }

    public function contenido(): string
    {
        return $this->contenido;
    }

    /** @return array<string, string> */
    public function encabezados(): array
    {
        return $this->encabezados;
    }

    public function conEncabezado(string $nombre, string $valor): self
    {
        $this->encabezados[$nombre] = $valor;

        return $this;
    }
}
