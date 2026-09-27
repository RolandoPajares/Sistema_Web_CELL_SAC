<?php

declare(strict_types=1);

namespace App\DAO;

use App\Infraestructura\BaseDatos\Conexion;
use App\Repositorios\Contratos\RepositorioUsuarioInterfaz;
use PDO;

final class UsuarioDAO implements RepositorioUsuarioInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function estaDisponible(): bool
    {
        return $this->conexion->pdo() !== null;
    }

    /** @return array<string, mixed>|null */
    public function buscarPorCorreo(string $correo): ?array
    {
        $sentencia = $this->pdo()->prepare('SELECT * FROM usuarios WHERE correo = ? LIMIT 1');
        $sentencia->execute([$correo]);
        $usuario = $sentencia->fetch();

        return $usuario ?: null;
    }

    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare('INSERT INTO usuarios(nombre, correo, contrasena, rol) VALUES(?, ?, ?, ?)');
        $sentencia->execute([$datos['nombre'], $datos['correo'], $datos['contrasena'], $datos['rol']]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function todos(): array
    {
        $sentencia = $this->pdo()->query('SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY id DESC');

        return $sentencia->fetchAll();
    }

    public function contar(): int
    {
        return (int) $this->pdo()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
