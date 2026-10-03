<?php

declare(strict_types=1);

namespace App\Servicios\Proveedores;

use App\DAO\Contratos\RepositorioProveedorInterfaz;

final class ProveedorServicio
{
    public function __construct(private RepositorioProveedorInterfaz $proveedores)
    {
    }

    /**
     * Devuelve los registros disponibles que cumplen los filtros actuales.
     */
    public function todos(): array
    {
        return $this->proveedores->todos();
    }

    public function buscar(int $idProveedor): ?array
    {
        return $this->proveedores->buscar($idProveedor);
    }

    /**
     * Calcula un resumen consolidado de la información solicitada.
     *
     * @return array{total:int,activos:int,inactivos:int,ciudades:int}
     */
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

    public function guardar(array $datos, ?int $idProveedor = null): int
    {
        if ($this->proveedores->existeRuc($datos['ruc'], $idProveedor)) {
            throw new \DomainException('Ya existe un proveedor con ese RUC.');
        }
        if ($idProveedor === null) {
            return $this->proveedores->crear($datos);
        }
        if ($this->proveedores->buscar($idProveedor) === null) {
            throw new \DomainException('El proveedor no existe.');
        }
        $this->proveedores->actualizar($idProveedor, $datos);

        return $idProveedor;
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idProveedor): void
    {
        $this->proveedores->desactivar($idProveedor);
    }
}
