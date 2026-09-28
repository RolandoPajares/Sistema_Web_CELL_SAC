<?php

declare(strict_types=1);

namespace App\Soporte\Configuracion;

final class RepositorioConfiguracion
{
    /** @param array<string, mixed> $elementos */
    public function __construct(private array $elementos)
    {
    }

    public function obtener(string $clave, mixed $predeterminado = null): mixed
    {
        $segmentos = explode('.', $clave);
        $valor = $this->elementos;

        foreach ($segmentos as $segmento) {
            if (!is_array($valor) || !array_key_exists($segmento, $valor)) {
                return $predeterminado;
            }

            $valor = $valor[$segmento];
        }

        return $valor;
    }

    /** @return array<string, mixed> */
    public function todos(): array
    {
        return $this->elementos;
    }
}
