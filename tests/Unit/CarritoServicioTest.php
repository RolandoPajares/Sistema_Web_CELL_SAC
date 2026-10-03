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
    /**
     * Prepara el estado y los recursos necesarios para ejecutar la prueba.
     */
    protected function setUp(): void
    {
        $_SESSION['cart'] = [];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testAddCapsQuantityAtStock`.
     */
    public function testAgregarLimitaLaCantidadAlStock(): void
    {
        $carrito = new CarritoServicio(new ProductoServicio(new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            /**
             * Devuelve todos los registros activos del repositorio.
             */
            public function todosActivos(): array
            {
                return [];
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
                if ($idProducto <= 0) {
                    return null;
                }

                return ['id' => $idProducto, 'marca' => 'JBL', 'nombre' => 'Tune', 'categoria' => 'Audifono', 'precio' => 50, 'existencias' => 1];
            }
            /** Devuelve arreglos vacíos porque estos casos de prueba no modelan galerías ni fichas adicionales. */
            public function imagenesRelacionadas(int $idProducto): array { return []; }
            /**
             * Recupera las características asociadas al producto solicitado.
             */
            public function caracteristicas(int $idProducto): array { return []; }
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
        }), new GestorSesion());

        $carrito->agregar(10);
        $carrito->agregar(10);

        self::assertSame(1, $_SESSION['cart'][10]);
    }
}
