<?php

declare(strict_types=1);

namespace App\Servicios\Proveedores;

use App\DAO\Contratos\RepositorioProveedorInterfaz;

final class ProveedorServicio
{
    public function __construct(private RepositorioProveedorInterfaz $proveedores)
    {
    }

    public function todos(): array
    {
        return $this->proveedores->todos();
    }

    public function buscar(int $id): ?array
    {
        return $this->proveedores->buscar($id);
    }

    /** @return array{total:int,activos:int,inactivos:int,ciudades:int} */
    public function resumen(): array
    {
        $proveedores = $this->todos();
        $ciudades = array_filter(array_unique(array_map(
            static fn (array $proveedor): string => trim((string) ($proveedor['ciudad'] ?? '')),
            $proveedores
        )));

        return [
            'total' => count($proveedores),
            'activos' => count(array_filter($proveedores, static fn (array $proveedor): bool => (int) $proveedor['activo'] === 1)),
            'inactivos' => count(array_filter($proveedores, static fn (array $proveedor): bool => (int) $proveedor['activo'] === 0)),
            'ciudades' => count($ciudades),
        ];
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        if ($this->proveedores->existeRuc($datos['ruc'], $id)) {
            throw new \DomainException('Ya existe un proveedor con ese RUC.');
        }
        if ($id === null) {
            return $this->proveedores->crear($datos);
        }
        if ($this->proveedores->buscar($id) === null) {
            throw new \DomainException('El proveedor no existe.');
        }
        $this->proveedores->actualizar($id, $datos);

        return $id;
    }

    public function desactivar(int $id): void
    {
        $this->proveedores->desactivar($id);
    }
}
