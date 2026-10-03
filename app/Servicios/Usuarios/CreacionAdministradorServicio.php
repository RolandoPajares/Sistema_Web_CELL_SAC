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
        // La creacion inicial valida formato y longitud antes de guardar credenciales.
        $nombre = trim($nombre);
        $correo = mb_strtolower(trim($correo));
        if ($nombre === '' || mb_strlen($nombre) > 120) {
            throw new \InvalidArgumentException('Ingresa un nombre de hasta 120 caracteres.');
        }
        if (mb_strlen($correo) > 160 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Correo electrónico inválido.');
        }
        if (mb_strlen($contrasena) < 10 || mb_strlen($contrasena) > 255) {
            throw new \InvalidArgumentException('La clave debe tener entre 10 y 255 caracteres.');
        }
        if ($this->usuarios->buscarPorCorreo($correo)) {
            throw new \InvalidArgumentException('El correo ya está registrado.');
        }

        return $this->usuarios->crear([
            'nombre' => $nombre,
            'correo' => $correo,
            'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
            'rol' => 'administrador',
        ]);
    }
}
