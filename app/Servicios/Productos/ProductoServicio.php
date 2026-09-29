<?php

declare(strict_types=1);

namespace App\Servicios\Productos;

use App\DTO\Productos\FiltroProducto;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\DAO\Contratos\RepositorioCategoriaInterfaz;

final class ProductoServicio
{
    public function __construct(
        private RepositorioProductoInterfaz $productos,
        private ?RepositorioCategoriaInterfaz $categorias = null,
    ) {
    }

    public function conexionDisponible(): bool
    {
        return $this->productos->estaDisponible();
    }

    /** @return array<int, array<string, mixed>> */
    public function destacados(int $limite = 4): array
    {
        $productos = $this->todosActivos();
        usort($productos, static function (array $productoA, array $productoB): int {
            $esCelularA = stripos((string) ($productoA['categoria'] ?? ''), 'celular') !== false;
            $esCelularB = stripos((string) ($productoB['categoria'] ?? ''), 'celular') !== false;

            return ($esCelularB <=> $esCelularA)
                ?: ((int) ($productoB['id'] ?? 0) <=> (int) ($productoA['id'] ?? 0));
        });

        return array_slice($productos, 0, max(0, $limite));
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

    /** @return array{total:int,activos:int,inactivos:int,stock_bajo:int} */
    public function resumenAdministrativo(): array
    {
        $productos = $this->todosParaAdministrador();

        return [
            'total' => count($productos),
            'activos' => count(array_filter($productos, static fn (array $producto): bool => (int) $producto['activo'] === 1)),
            'inactivos' => count(array_filter($productos, static fn (array $producto): bool => (int) $producto['activo'] === 0)),
            'stock_bajo' => count(array_filter($productos, static fn (array $producto): bool => (int) $producto['activo'] === 1 && (int) $producto['existencias'] <= 8)),
        ];
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

        $datos = $this->resolverCategoria($datos);

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

    /** @param array<string, mixed> $datos
     *  @return array<string, mixed>
     */
    private function resolverCategoria(array $datos): array
    {
        if ($this->categorias === null) {
            return $datos;
        }

        $categoria = (int) ($datos['categoria_id'] ?? 0) > 0
            ? $this->categorias->buscarActiva((int) $datos['categoria_id'])
            : $this->categorias->buscarPorNombre((string) ($datos['categoria'] ?? ''));

        if ($categoria === null || (int) ($categoria['activo'] ?? 1) !== 1) {
            throw new \DomainException('La categoría seleccionada no existe o está inactiva.');
        }

        $datos['categoria_id'] = (int) $categoria['id'];
        $datos['categoria'] = (string) $categoria['nombre'];

        return $datos;
    }
}
