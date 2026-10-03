<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DAO\Contratos\RepositorioInventarioInterfaz;
use App\Servicios\Inventario\InventarioServicio;
use PHPUnit\Framework\TestCase;

final class InventarioServicioTest extends TestCase
{
    public function testValidaYEnviaLosTresTiposDeMovimientoAlRepositorio(): void
    {
        $repositorio = $this->createMock(RepositorioInventarioInterfaz::class);
        $repositorio->expects(self::exactly(3))
            ->method('registrarMovimiento')
            ->willReturnCallback(static function (array $datos, int $idUsuario): int {
                self::assertSame(8, $idUsuario);
                self::assertContains($datos['tipo_movimiento'], ['entrada', 'salida', 'ajuste']);
                self::assertSame(3, $datos['cantidad']);

                return 9;
            });
        $servicio = new InventarioServicio($repositorio);

        foreach (['entrada', 'salida', 'ajuste'] as $tipo) {
            self::assertSame(9, $servicio->registrar([
                'producto_id' => '12',
                'tipo_movimiento' => $tipo,
                'cantidad' => '3',
                'notas' => 'Movimiento de prueba',
            ], 8));
        }
    }

    public function testRechazaMovimientoInvalidoAntesDePersistir(): void
    {
        $repositorio = $this->createMock(RepositorioInventarioInterfaz::class);
        $repositorio->expects(self::never())->method('registrarMovimiento');
        $servicio = new InventarioServicio($repositorio);

        foreach ([
            ['producto_id' => 0, 'tipo_movimiento' => 'entrada', 'cantidad' => 1, 'notas' => 'Motivo'],
            ['producto_id' => 2, 'tipo_movimiento' => 'salida', 'cantidad' => 0, 'notas' => 'Motivo'],
            ['producto_id' => 2, 'tipo_movimiento' => 'otro', 'cantidad' => 1, 'notas' => 'Motivo'],
            ['producto_id' => 2, 'tipo_movimiento' => 'ajuste', 'cantidad' => 1, 'notas' => ' '],
        ] as $datos) {
            try {
                $servicio->registrar($datos, 8);
                self::fail('El movimiento inválido debía rechazarse.');
            } catch (\DomainException) {
                self::assertTrue(true);
            }
        }
    }
}
