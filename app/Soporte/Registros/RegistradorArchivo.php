<?php

declare(strict_types=1);

namespace App\Soporte\Registros;

final class RegistradorArchivo
{
    public function __construct(private string $archivo)
    {
    }

    /**
     * Construye una respuesta informativa con el mensaje indicado.
     *
     * @param array<string, mixed> $contexto
     */
    public function info(string $mensaje, array $contexto = []): void
    {
        $this->escribir('INFO', $mensaje, $contexto);
    }

    /**
     * Construye una respuesta de advertencia con el mensaje indicado.
     *
     * @param array<string, mixed> $contexto
     */
    public function advertencia(string $mensaje, array $contexto = []): void
    {
        $this->escribir('WARNING', $mensaje, $contexto);
    }

    /**
     * Construye una respuesta de error con el mensaje y estado adecuados.
     *
     * @param array<string, mixed> $contexto
     */
    public function error(string $mensaje, array $contexto = []): void
    {
        $this->escribir('ERROR', $mensaje, $contexto);
    }

    /**
     * Guarda el mensaje y el contexto recibidos en el registro correspondiente.
     *
     * @param array<string, mixed> $contexto
     */
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
