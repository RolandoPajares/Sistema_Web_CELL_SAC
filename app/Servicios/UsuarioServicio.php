<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Repositorios\Contratos\RepositorioUsuarioInterfaz;

final class UsuarioServicio
{
    public function __construct(private RepositorioUsuarioInterfaz $usuarios)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function todos(): array
    {
        return $this->usuarios->todos();
    }

    public function contar(): int
    {
        return $this->usuarios->contar();
    }
}
