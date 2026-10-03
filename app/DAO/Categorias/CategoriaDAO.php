<?php

declare(strict_types=1);

namespace App\DAO\Categorias;

use App\DAO\Contratos\RepositorioCategoriaInterfaz;
use App\Nucleo\BaseDatos\Conexion;
use PDO;

final class CategoriaDAO implements RepositorioCategoriaInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Devuelve todos los registros de la entidad administrada por el repositorio.
     */
    public function todas(): array
    {
        return $this->pdo()->query(
            'SELECT c.id, c.nombre, c.descripcion, c.activo, c.creado_en,
                    COUNT(p.id) AS productos_asociados
             FROM categorias c
             LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1
             GROUP BY c.id, c.nombre, c.descripcion, c.activo, c.creado_en
             ORDER BY c.activo DESC, c.nombre'
        )->fetchAll();
    }

    /**
     * Devuelve únicamente los registros activos de la entidad.
     */
    public function activas(): array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, nombre, descripcion FROM categorias WHERE activo = :activo ORDER BY nombre'
        );
        $sentencia->execute([':activo' => 1]);

        return $sentencia->fetchAll();
    }

    /**
     * Busca una categoría por su identificador.
     */
    public function buscar(int $idCategoria): ?array
    {
        return $this->buscarConCondicion($idCategoria, false);
    }

    /**
     * Busca una categoría por su identificador y confirma que esté activa.
     */
    public function buscarActiva(int $idCategoria): ?array
    {
        return $this->buscarConCondicion($idCategoria, true);
    }

    /**
     * Busca el registro usando el criterio «nombre».
     */
    public function buscarPorNombre(string $nombre): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, nombre, descripcion, activo FROM categorias WHERE LOWER(nombre) = LOWER(:nombre) LIMIT 1'
        );
        $sentencia->execute([':nombre' => $nombre]);
        $categoria = $sentencia->fetch();

        return $categoria ?: null;
    }

    /**
     * Comprueba si otra categoría ya utiliza el nombre indicado.
     */
    public function existeNombre(string $nombre, ?int $idExcluido = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categorias WHERE LOWER(nombre) = LOWER(:nombre)';
        if ($idExcluido !== null) {
            $sql .= ' AND id <> :id';
        }
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->bindValue(':nombre', $nombre);
        if ($idExcluido !== null) {
            $sentencia->bindValue(':id', $idExcluido, PDO::PARAM_INT);
        }
        $sentencia->execute();

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO categorias(nombre, descripcion, activo) VALUES(:nombre, :descripcion, 1)'
        );
        $sentencia->execute([':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion']]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function actualizar(int $idCategoria, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id = :id'
        );
        $sentencia->execute([':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion'], ':id' => $idCategoria]);
        if ($sentencia->rowCount() === 0 && $this->buscar($idCategoria) === null) {
            throw new \DomainException('La categoría no existe.');
        }
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idCategoria): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE categorias SET activo = 0 WHERE id = :id AND activo = 1');
        $sentencia->execute([':id' => $idCategoria]);
        if ($sentencia->rowCount() !== 1) {
            throw new \DomainException('La categoría no existe o ya está inactiva.');
        }
    }

    /**
     * Busca una categoría por su identificador y, si se solicita, filtra las inactivas.
     */
    private function buscarConCondicion(int $idCategoria, bool $soloActiva): ?array
    {
        $sql = 'SELECT id, nombre, descripcion, activo, creado_en FROM categorias WHERE id = :id';
        if ($soloActiva) {
            $sql .= ' AND activo = 1';
        }
        $sql .= ' LIMIT 1';
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->execute([':id' => $idCategoria]);
        $categoria = $sentencia->fetch();

        return $categoria ?: null;
    }

    /**
     * Obtiene la conexión PDO y detiene la operación si no está disponible.
     */
    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
