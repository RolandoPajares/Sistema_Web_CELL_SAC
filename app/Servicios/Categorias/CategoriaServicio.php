<?php

declare(strict_types=1);

namespace App\Servicios\Categorias;

use App\DAO\Contratos\RepositorioCategoriaInterfaz;

final class CategoriaServicio
{
    public function __construct(private RepositorioCategoriaInterfaz $categorias)
    {
    }

    /**
     * Devuelve todos los registros de la entidad administrada por el repositorio.
     */
    public function todas(): array
    {
        return $this->categorias->todas();
    }

    /**
     * Devuelve únicamente los registros activos de la entidad.
     */
    public function activas(): array
    {
        return $this->categorias->activas();
    }

    /**
     * Calcula un resumen consolidado de la información solicitada.
     *
     * @return array{total:int,activas:int,con_productos:int,sin_productos:int}
     */
    public function resumen(): array
    {
        $categorias = $this->todas();

        return [
            'total' => count($categorias),
            'activas' => count(array_filter($categorias, static fn (array $categoria): bool => (int) $categoria['activo'] === 1)),
            'con_productos' => count(array_filter($categorias, static fn (array $categoria): bool => (int) $categoria['productos_asociados'] > 0)),
            'sin_productos' => count(array_filter($categorias, static fn (array $categoria): bool => (int) $categoria['productos_asociados'] === 0)),
        ];
    }

    public function buscar(int $idCategoria): ?array
    {
        return $this->categorias->buscar($idCategoria);
    }

    public function guardar(array $datos, ?int $idCategoria = null): int
    {
        if ($this->categorias->existeNombre($datos['nombre'], $idCategoria)) {
            throw new \DomainException('Ya existe una categoría con ese nombre.');
        }
        if ($idCategoria === null) {
            return $this->categorias->crear($datos);
        }
        if ($this->categorias->buscar($idCategoria) === null) {
            throw new \DomainException('La categoría no existe.');
        }
        $this->categorias->actualizar($idCategoria, $datos);

        return $idCategoria;
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idCategoria): void
    {
        $this->categorias->desactivar($idCategoria);
    }
}
