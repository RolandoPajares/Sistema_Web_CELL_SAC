<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Nucleo\Http\Solicitud;
use App\Validacion\Productos\SolicitudProducto;
use PHPUnit\Framework\TestCase;

final class SolicitudProductoTest extends TestCase
{
    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testCategoryIdIsAcceptedAndStockIsNotPartOfProductEditing`.
     */
    public function testAceptaIdCategoriaYSinExistenciasEnEdicion(): void
    {
        $campos = [
            'brand' => 'Samsung',
            'name' => 'Cable',
            'category_id' => '3',
            'price' => '19.90',
            'stock' => '3',
        ];

        $datosValidos = SolicitudProducto::validar(new Solicitud('POST', '/admin/products', [], $campos, []));
        self::assertSame(3, $datosValidos['categoria_id']);
        self::assertArrayNotHasKey('existencias', $datosValidos);
    }
}
