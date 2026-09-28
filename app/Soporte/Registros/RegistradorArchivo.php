<?php

declare(strict_types=1);

namespace App\Soporte\Registros;

final class RegistradorArchivo
{
    public function __construct(private string $archivo)
    {
    }

    /** @param array<string, mixed> $contexto */
    public function info(string $mensaje, array $contexto = []): void
    {
        $this->escribir('INFO', $mensaje, $contexto);
    }

    /** @param array<string, mixed> $contexto */
    public function advertencia(string $mensaje, array $contexto = []): void
    {
        $this->escribir('WARNING', $mensaje, $contexto);
    }

    /** @param array<string, mixed> $contexto */
    public function error(string $mensaje, array $contexto = []): void
    {
        $this->escribir('ERROR', $mensaje, $contexto);
    }

    /** @param array<string, mixed> $contexto */
    private function escribir(string $nivel, string $mensaje, array $contexto): void
    {
        unset($contexto['contrasena'], $contexto['token'], $contexto['csrf']);
        $directorio = dirname($this->archivo);

        if (!is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }

        $linea = sprintf(
            "[%s] %s %s %s\n",
            date('Y-m-d H:i:s'),
            $nivel,
            $mensaje,
            $contexto === [] ? '' : (string) json_encode($contexto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        error_log($linea, 3, $this->archivo);
    }
}
