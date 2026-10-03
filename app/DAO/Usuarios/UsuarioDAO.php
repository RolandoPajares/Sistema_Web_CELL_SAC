<?php

declare(strict_types=1);

namespace App\DAO\Usuarios;

use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioUsuarioInterfaz;
use App\DAO\Contratos\RepositorioCuentaAdministradorInterfaz;
use PDO;

final class UsuarioDAO implements RepositorioUsuarioInterfaz, RepositorioCuentaAdministradorInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Indica si el repositorio puede consultar la base de datos.
     */
    public function estaDisponible(): bool
    {
        return $this->conexion->pdo() !== null;
    }

    /**
     * Busca el registro usando el criterio «correo».
     * @return array<string, mixed>|null
     */
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

    /**
     * Devuelve los registros disponibles que cumplen los filtros actuales.
     */
    public function todos(): array
    {
        $sentencia = $this->pdo()->query('SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY id DESC');

        return $sentencia->fetchAll();
    }

    /**
     * Cuenta los elementos que cumplen las condiciones recibidas.
     */
    public function contar(): int
    {
        return (int) $this->pdo()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    /**
     * Busca el registro usando el criterio «identificador».
     */
    public function buscarPorId(int $idUsuario): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, nombre, correo, rol, creado_en FROM usuarios WHERE id = :id LIMIT 1'
        );
        $sentencia->execute([':id' => $idUsuario]);
        $usuario = $sentencia->fetch();

        return $usuario ?: null;
    }

    /**
     * Comprueba si el correo electrónico ya está registrado por otra cuenta.
     */
    public function correoEnUso(string $correo, int $idExcluido): bool
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id <> :id'
        );
        $sentencia->execute([':correo' => $correo, ':id' => $idExcluido]);

        return (int) $sentencia->fetchColumn() > 0;
    }

    /**
     * Actualiza la información relacionada con «perfil».
     */
    public function actualizarPerfil(int $idUsuario, string $nombre, string $correo): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo WHERE id = :id AND rol = :rol'
        );
        $sentencia->execute([':nombre' => $nombre, ':correo' => $correo, ':id' => $idUsuario, ':rol' => 'administrador']);
    }

    /**
     * Genera el hash seguro de la contraseña recibida.
     */
    public function hashContrasena(int $idUsuario): ?string
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT contrasena FROM usuarios WHERE id = :id AND rol = :rol LIMIT 1'
        );
        $sentencia->execute([':id' => $idUsuario, ':rol' => 'administrador']);
        $hash = $sentencia->fetchColumn();

        return is_string($hash) ? $hash : null;
    }

    /**
     * Actualiza la información relacionada con «contrasena».
     */
    public function actualizarContrasena(int $idUsuario, string $hash): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE usuarios SET contrasena = :hash WHERE id = :id AND rol = :rol'
        );
        $sentencia->execute([':hash' => $hash, ':id' => $idUsuario, ':rol' => 'administrador']);
    }

    /**
     * Obtiene la conexión PDO y detiene la operación si no está disponible.
     */
    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
