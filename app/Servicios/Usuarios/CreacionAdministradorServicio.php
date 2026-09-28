<?php

declare(strict_types=1);

namespace App\Servicios\Usuarios;

use App\DAO\Contratos\RepositorioUsuarioInterfaz;

final class CreacionAdministradorServicio
{
    public function __construct(private RepositorioUsuarioInterfaz $usuarios)
    {
    }

    public function crear(string $nombre, string $correo, string $contrasena): int
    {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Correo electrónico inválido.');
        }
        if (mb_strlen($contrasena) < 10) {
            throw new \InvalidArgumentException('La clave debe tener al menos 10 caracteres.');
        }
        if ($this->usuarios->buscarPorCorreo($correo)) {
            throw new \InvalidArgumentException('El correo ya está registrado.');
        }

        return $this->usuarios->crear([
            'nombre' => trim($nombre),
            'correo' => mb_strtolower(trim($correo)),
            'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
            'rol' => 'administrador',
        ]);
    }
}
