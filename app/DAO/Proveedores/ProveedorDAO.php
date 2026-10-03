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

    /**
     * Devuelve los registros disponibles que cumplen los filtros actuales.
     */
    public function todos(): array
    {
        return $this->pdo()->query(
            'SELECT id, nombre, ruc, correo, telefono, ciudad, activo, creado_en
             FROM proveedores ORDER BY activo DESC, id DESC'
        )->fetchAll();
    }

    public function buscar(int $idProveedor): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, nombre, ruc, correo, telefono, ciudad, activo FROM proveedores WHERE id = :id LIMIT 1'
        );
        $sentencia->execute([':id' => $idProveedor]);
        $proveedor = $sentencia->fetch();

        return $proveedor ?: null;
    }

    /**
     * Comprueba si otro proveedor ya utiliza el RUC indicado.
     */
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

    public function actualizar(int $idProveedor, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE proveedores SET nombre=:nombre, ruc=:ruc, correo=:correo, telefono=:telefono, ciudad=:ciudad
             WHERE id=:id'
        );
        $parametros = $this->parametros($datos);
        $parametros[':id'] = $idProveedor;
        $sentencia->execute($parametros);
        if ($sentencia->rowCount() === 0 && $this->buscar($idProveedor) === null) {
            throw new \DomainException('El proveedor no existe.');
        }
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idProveedor): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE proveedores SET activo = 0 WHERE id = :id AND activo = 1');
        $sentencia->execute([':id' => $idProveedor]);
        if ($sentencia->rowCount() !== 1) {
            throw new \DomainException('El proveedor no existe o ya está inactivo.');
        }
    }

    /**
     * Prepara los parámetros asociados a la consulta o escritura solicitada.
     *
     * @param array<string, string> $datos
     */
    private function parametros(array $datos): array
    {
        return [':nombre' => $datos['nombre'], ':ruc' => $datos['ruc'], ':correo' => $datos['correo'],
            ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']];
    }

    /**
     * Obtiene la conexión PDO y detiene la operación si no está disponible.
     */
    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
