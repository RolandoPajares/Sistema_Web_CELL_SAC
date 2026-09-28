<?php

declare(strict_types=1);

namespace App\Servicios\Productos;

use App\DTO\Productos\FiltroProducto;
use App\DAO\Contratos\RepositorioProductoInterfaz;

final class ProductoServicio
{
    public function __construct(private RepositorioProductoInterfaz $productos)
    {
    }

    public function conexionDisponible(): bool
    {
        return $this->productos->estaDisponible();
    }

    /** @return array<int, array<string, mixed>> */
    public function destacados(int $limite = 4): array
    {
        return array_slice($this->todosActivos(), 0, $limite);
    }

    /** @return array<int, array<string, mixed>> */
    public function buscar(?string $consulta, ?string $marca, ?string $categoria): array
    {
        $consulta = trim((string) $consulta);
        $marca = trim((string) $marca);
        $categoria = trim((string) $categoria);

        return array_values(array_filter(
            $this->todosActivos(),
            static function (array $producto) use ($consulta, $marca, $categoria): bool {
                $coincideConsulta = $consulta === ''
                    || stripos((string) $producto['nombre'] . ' ' . (string) $producto['marca'], $consulta) !== false;
                $coincideCategoria = $categoria === '' || (string) $producto['categoria'] === $categoria;

                return $coincideConsulta
                    && ($marca === '' || $producto['marca'] === $marca)
                    && $coincideCategoria;
            }
        ));
    }

    /** @return array{productos:array<int,array<string,mixed>>,total:int,pagina:int,por_pagina:int,ultima_pagina:int} */
    public function paginar(FiltroProducto $filtro): array
    {
        return $this->productos->paginar($filtro);
    }

    /** @return array<string, mixed>|null */
    public function buscarActivo(int $idProducto): ?array
    {
        return $this->productos->buscarActivo($idProducto);
    }

    /** @return array<int, array<string, mixed>> */
    public function todosParaAdministrador(): array
    {
        return $this->productos->todosParaAdministrador();
    }

    /** @return array<string, mixed>|null */
    public function buscarParaAdministrador(int $idProducto): ?array
    {
        return $this->productos->buscarParaAdministrador($idProducto);
    }

    /** @param array<string, mixed> $datos */
    public function guardar(array $datos, ?int $idProducto = null): int
    {
        if (!$this->productos->estaDisponible()) {
            throw new \RuntimeException('La base de datos no está disponible.');
        }

        if ($idProducto !== null && $idProducto > 0) {
            if ($this->productos->buscarParaAdministrador($idProducto) === null) {
                throw new \DomainException('El producto no existe.');
            }
            if (
                $this->productos->existeConMarcaYNombre(
                    (string) $datos['marca'],
                    (string) $datos['nombre'],
                    $idProducto
                )
            ) {
                throw new \DomainException('Ya existe otro producto con la misma marca y nombre.');
            }
            $this->productos->actualizar($idProducto, $datos);

            return $idProducto;
        }

        if ($this->productos->existeConMarcaYNombre((string) $datos['marca'], (string) $datos['nombre'])) {
            throw new \DomainException('Ya existe un producto con la misma marca y nombre.');
        }

        return $this->productos->crear($datos);
    }

    public function eliminarODesactivar(int $idProducto): string
    {
        if (!$this->productos->estaDisponible()) {
            throw new \RuntimeException('La base de datos no está disponible.');
        }

        if ($idProducto <= 0 || $this->productos->buscarActivo($idProducto) === null) {
            throw new \DomainException('El producto no existe o ya fue desactivado.');
        }

        if ($this->productos->delete($idProducto)) {
            return 'deleted';
        }

        $this->productos->desactivar($idProducto);

        return 'deactivated';
    }

    public function contarActivos(): int
    {
        return $this->productos->contarActivos();
    }

    public function stockTotal(): int
    {
        return $this->productos->stockTotal();
    }

    /** @return array<int, array<string, mixed>> */
    public function todosActivos(): array
    {
        return $this->productos->todosActivos();
    }
}
