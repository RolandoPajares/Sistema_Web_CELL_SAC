<?php

declare(strict_types=1);

namespace App\Servicios\Usuarios;

use App\DAO\Contratos\RepositorioUsuarioInterfaz;

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

    /** @return array{total:int,administradores:int,otros_roles:int,por_rol:array<string,int>} */
    public function resumen(): array
    {
        $porRol = [];
        foreach ($this->usuarios->todos() as $usuario) {
            $rol = (string) $usuario['rol'];
            $porRol[$rol] = ($porRol[$rol] ?? 0) + 1;
        }
        $total = array_sum($porRol);
        $administradores = (int) ($porRol['administrador'] ?? 0);

        return [
            'total' => $total,
            'administradores' => $administradores,
            'otros_roles' => $total - $administradores,
            'por_rol' => $porRol,
        ];
    }
}
