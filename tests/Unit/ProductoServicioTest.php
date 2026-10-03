<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\Servicios\Productos\ProductoServicio;
use App\DTO\Productos\FiltroProducto;
use PHPUnit\Framework\TestCase;

final class ProductoServicioTest extends TestCase
{
    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testSearchFiltersProductsByBrandAndCategory`.
     */
    public function testLaBusquedaFiltraProductosPorMarcaYCategoria(): void
    {
        $servicio = new ProductoServicio(new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            /**
             * Devuelve todos los registros activos del repositorio.
             */
            public function todosActivos(): array
            {
                return [
                ['id' => 1, 'marca' => 'Samsung', 'nombre' => 'Galaxy', 'categoria' => 'Celular', 'precio' => 100, 'existencias' => 2],
                ['id' => 2, 'marca' => 'JBL', 'nombre' => 'Tune', 'categoria' => 'Audifono', 'precio' => 50, 'existencias' => 3],
                ];
            }
            /**
             * Devuelve productos activos con descuentos reales, ordenados según ventas y disponibilidad.
             */
            public function ofertasPopulares(): array
            {
                return [];
            }
            /**
             * Devuelve los productos de la página solicitada junto con los datos de paginación.
             */
            public function paginar(FiltroProducto $filtro): array
            {
                return ['productos' => [], 'total' => 0, 'pagina' => 1, 'por_pagina' => 12, 'ultima_pagina' => 1];
            }
            /**
             * Devuelve los registros disponibles para el panel de administración.
             */
            public function todosParaAdministrador(): array
            {
                return [];
            }
            /**
             * Busca el registro activo que coincide con el identificador o filtro indicado.
             */
            public function buscarActivo(int $idProducto): ?array
            {
                return $idProducto === 1
                    ? ['id' => 1, 'url_imagen' => 'assets/img/productos/celulares/samsung-galaxy-s24.jpeg']
                    : null;
            }
            /** Devuelve arreglos vacíos porque estos casos de prueba no modelan galerías ni fichas adicionales. */
            public function imagenesRelacionadas(int $idProducto): array
            {
                return ['assets/img/productos/celulares/samsung-galaxy-s24-frente.jpeg'];
            }
            /**
             * Recupera las características asociadas al producto solicitado.
             */
            public function caracteristicas(int $idProducto): array
            {
                return [['nombre' => 'Pantalla', 'valor' => '6.2 pulgadas']];
            }
            public function buscarParaAdministrador(int $idProducto): ?array
            {
                return null;
            }
            public function buscarActivoParaActualizar(int $idProducto): ?array
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
            public function actualizar(int $idProducto, array $datos): void
            {
            }
            /**
             * Elimina el registro indicado respetando sus relaciones.
             */
            public function eliminarFisicamente(int $idProducto): bool
            {
                return true;
            }
            /**
             * Marca como inactivo el registro seleccionado, sin borrar su historial.
             */
            public function desactivar(int $idProducto): void
            {
            }
            /**
             * Descuenta del inventario la cantidad indicada para el producto correspondiente.
             */
            public function reducirStock(int $idProducto, int $cantidad): void
            {
            }
            /**
             * Cuenta los elementos relacionados con «activos».
             */
            public function contarActivos(): int
            {
                return 0;
            }
            /**
             * Suma las existencias registradas para los productos activos.
             */
            public function stockTotal(): int
            {
                return 0;
            }
        });

        $resultados = $servicio->buscar('Tune', 'JBL', 'Audifono');

        self::assertCount(1, $resultados);
        self::assertSame('Tune', $resultados[0]['nombre']);

        // Confirma que el servicio entrega a la vista las relaciones solicitadas por el producto.
        $fichaProducto = $servicio->buscarActivoConRelaciones(1);
        self::assertSame([
            'assets/img/productos/celulares/samsung-galaxy-s24.jpeg',
            'assets/img/productos/celulares/samsung-galaxy-s24-frente.jpeg',
        ], $fichaProducto['imagenes']);
        self::assertSame([['nombre' => 'Pantalla', 'valor' => '6.2 pulgadas']], $fichaProducto['caracteristicas']);
    }
}
