<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositorios\Contratos\RepositorioProductoInterfaz;
use App\Servicios\ProductoServicio;
use App\Dominio\Productos\FiltroProducto;
use PHPUnit\Framework\TestCase;

final class ProductoServicioTest extends TestCase
{
    public function testSearchFiltersProductsByBrandAndCategory(): void
    {
        $servicio = new ProductoServicio(new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            public function todosActivos(): array
            {
                return [
                ['id' => 1, 'marca' => 'Samsung', 'nombre' => 'Galaxy', 'categoria' => 'Celular', 'precio' => 100, 'existencias' => 2],
                ['id' => 2, 'marca' => 'JBL', 'nombre' => 'Tune', 'categoria' => 'Audifono', 'precio' => 50, 'existencias' => 3],
                ];
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
                return null;
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
        });

        $resultados = $servicio->buscar('Tune', 'JBL', 'Audifono');

        self::assertCount(1, $resultados);
        self::assertSame('Tune', $resultados[0]['nombre']);
    }
}
