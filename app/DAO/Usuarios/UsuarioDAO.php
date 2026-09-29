<?php

declare(strict_types=1);

namespace App\DAO\Usuarios;

use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioUsuarioInterfaz;
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

    public function buscarPorId(int $id): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, nombre, correo, rol, creado_en FROM usuarios WHERE id = :id LIMIT 1'
        );
        $sentencia->execute([':id' => $id]);
        $usuario = $sentencia->fetch();

        return $usuario ?: null;
    }

    public function correoEnUso(string $correo, int $idExcluido): bool
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id <> :id'
        );
        $sentencia->execute([':correo' => $correo, ':id' => $idExcluido]);

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function actualizarPerfil(int $id, string $nombre, string $correo): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo WHERE id = :id AND rol = :rol'
        );
        $sentencia->execute([':nombre' => $nombre, ':correo' => $correo, ':id' => $id, ':rol' => 'administrador']);
    }

    public function hashContrasena(int $id): ?string
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT contrasena FROM usuarios WHERE id = :id AND rol = :rol LIMIT 1'
        );
        $sentencia->execute([':id' => $id, ':rol' => 'administrador']);
        $hash = $sentencia->fetchColumn();

        return is_string($hash) ? $hash : null;
    }

    public function actualizarContrasena(int $id, string $hash): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE usuarios SET contrasena = :hash WHERE id = :id AND rol = :rol'
        );
        $sentencia->execute([':hash' => $hash, ':id' => $id, ':rol' => 'administrador']);
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
