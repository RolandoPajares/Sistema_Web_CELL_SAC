<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\Servicios\Compra\CarritoServicio;
use App\Servicios\Productos\ProductoServicio;
use App\DTO\Productos\FiltroProducto;
use App\Soporte\Sesion\GestorSesion;
use PHPUnit\Framework\TestCase;

final class CarritoServicioTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION['cart'] = [];
    }

    public function testAddCapsQuantityAtStock(): void
    {
        $carrito = new CarritoServicio(new ProductoServicio(new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            public function todosActivos(): array
            {
                return [];
            }
            public function paginar(FiltroProducto $filtro): array
            {
                return ['productos' => [], 'total' => 0, 'pagina' => 1, 'por_pagina' => 12, 'ultima_pagina' => 1];
            }
            public function todosParaAdministrador(): array
            {
                return [];
            }
            public function buscarActivo(int $id): ?array
            {
                if ($id <= 0) {
                    return null;
                }

                return ['id' => $id, 'marca' => 'JBL', 'nombre' => 'Tune', 'categoria' => 'Audifono', 'precio' => 50, 'existencias' => 1];
            }
            public function buscarParaAdministrador(int $id): ?array
            {
                return null;
            }
            public function buscarActivoParaActualizar(int $id): ?array
            {
                return null;
            }
            public function existeConMarcaYNombre(string $marca, string $nombre, ?int $idExcluido = null): bool
            {
                return false;
            }
            public function crear(array $datos): int
            {
                return 1;
            }
            public function actualizar(int $id, array $datos): void
            {
            }
            public function delete(int $id): bool
            {
                return true;
            }
            public function desactivar(int $id): void
            {
            }
            public function reducirStock(int $id, int $cantidad): void
            {
            }
            public function contarActivos(): int
            {
                return 0;
            }
            public function stockTotal(): int
            {
                return 0;
            }
        }), new GestorSesion());

        $carrito->agregar(10);
        $carrito->agregar(10);

        self::assertSame(1, $_SESSION['cart'][10]);
    }
}
