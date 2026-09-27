<?php

declare(strict_types=1);

namespace App\Http\Respuestas;

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
        http_response_code($this->estado);

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

    public function conEncabezado(string $nombre, string $valor): self
    {
        $this->encabezados[$nombre] = $valor;

        return $this;
    }
}
