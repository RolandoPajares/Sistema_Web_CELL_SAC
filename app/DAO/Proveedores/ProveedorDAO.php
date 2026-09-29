<?php

declare(strict_types=1);

namespace App\DAO\Proveedores;

use App\DAO\Contratos\RepositorioProveedorInterfaz;
use App\Nucleo\BaseDatos\Conexion;
use PDO;

final class ProveedorDAO implements RepositorioProveedorInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function todos(): array
    {
        return $this->pdo()->query(
            'SELECT id, nombre, ruc, correo, telefono, ciudad, activo, creado_en
             FROM proveedores ORDER BY activo DESC, id DESC'
        )->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, nombre, ruc, correo, telefono, ciudad, activo FROM proveedores WHERE id = :id LIMIT 1'
        );
        $sentencia->execute([':id' => $id]);
        $proveedor = $sentencia->fetch();

        return $proveedor ?: null;
    }

    public function existeRuc(string $ruc, ?int $idExcluido = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM proveedores WHERE ruc = :ruc';
        if ($idExcluido !== null) {
            $sql .= ' AND id <> :id';
        }
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->bindValue(':ruc', $ruc);
        if ($idExcluido !== null) {
            $sentencia->bindValue(':id', $idExcluido, PDO::PARAM_INT);
        }
        $sentencia->execute();

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO proveedores(nombre, ruc, correo, telefono, ciudad, activo)
             VALUES(:nombre, :ruc, :correo, :telefono, :ciudad, 1)'
        );
        $sentencia->execute($this->parametros($datos));

        return (int) $this->pdo()->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE proveedores SET nombre=:nombre, ruc=:ruc, correo=:correo, telefono=:telefono, ciudad=:ciudad
             WHERE id=:id'
        );
        $parametros = $this->parametros($datos);
        $parametros[':id'] = $id;
        $sentencia->execute($parametros);
        if ($sentencia->rowCount() === 0 && $this->buscar($id) === null) {
            throw new \DomainException('El proveedor no existe.');
        }
    }

    public function desactivar(int $id): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE proveedores SET activo = 0 WHERE id = :id AND activo = 1');
        $sentencia->execute([':id' => $id]);
        if ($sentencia->rowCount() !== 1) {
            throw new \DomainException('El proveedor no existe o ya está inactivo.');
        }
    }

    /** @param array<string, string> $datos */
    private function parametros(array $datos): array
    {
        return [':nombre' => $datos['nombre'], ':ruc' => $datos['ruc'], ':correo' => $datos['correo'],
            ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']];
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
