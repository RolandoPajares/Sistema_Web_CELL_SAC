<?php

declare(strict_types=1);

namespace App\Servicios\Categorias;

use App\DAO\Contratos\RepositorioCategoriaInterfaz;

final class CategoriaServicio
{
    public function __construct(private RepositorioCategoriaInterfaz $categorias)
    {
    }

    public function todas(): array
    {
        return $this->categorias->todas();
    }

    public function activas(): array
    {
        return $this->categorias->activas();
    }

    /** @return array{total:int,activas:int,con_productos:int,sin_productos:int} */
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

    public function buscar(int $id): ?array
    {
        return $this->categorias->buscar($id);
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        if ($this->categorias->existeNombre($datos['nombre'], $id)) {
            throw new \DomainException('Ya existe una categoría con ese nombre.');
        }
        if ($id === null) {
            return $this->categorias->crear($datos);
        }
        if ($this->categorias->buscar($id) === null) {
            throw new \DomainException('La categoría no existe.');
        }
        $this->categorias->actualizar($id, $datos);

        return $id;
    }

    public function desactivar(int $id): void
    {
        $this->categorias->desactivar($id);
    }
}
