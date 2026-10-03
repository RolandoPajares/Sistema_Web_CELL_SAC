<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Soporte\Presentacion\CatalogoInterfaces;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CatalogoInterfacesTest extends TestCase
{
    /**
     * Devuelve los perfiles disponibles para las recomendaciones de productos.
     *
     * @return array<string, array{string, string}>
     */
    public static function perfiles(): array
    {
        return [
            'administrador' => ['administrador', 'Dashboard ejecutivo'],
            'compras' => ['compras_logistica', 'Dashboard de operaciones'],
            'mayorista' => ['ventas_mayoristas', 'Dashboard comercial B2B'],
            'minorista' => ['ventas_minoristas', 'Punto de venta / Dashboard B2C'],
            'marketing' => ['marketing', 'Dashboard de marketing'],
            'cliente mayorista' => ['cliente_mayorista', 'Portal mayorista B2B'],
            'cliente minorista' => ['cliente_minorista', 'Mi cuenta'],
        ];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testCadaRolTieneUnTableroDiferenciado`.
     */
    #[DataProvider('perfiles')]
    public function testCadaRolTieneUnTableroDiferenciado(string $rol, string $titulo): void
    {
        $interfaz = CatalogoInterfaces::tablero($rol, []);

        self::assertSame($titulo, $interfaz['titulo']);
        self::assertGreaterThanOrEqual(4, count($interfaz['metricas']));
    }

    /**
     * Devuelve los módulos habilitados para la navegación o configuración.
     *
     * @return array<string, array{string, string}>
     */
    public static function modulos(): array
    {
        return [
            'inventario' => ['inventario', 'inventario'],
            'embudo comercial' => ['seguimiento-comercial', 'kanban'],
            'analítica' => ['analitica', 'analitica'],
            'contenido' => ['contenido', 'contenido'],
            'favoritos' => ['favoritos', 'tarjetas'],
            'perfil' => ['perfil', 'perfil'],
            'historial' => ['historial', 'historial'],
        ];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testLosModulosUsanComposicionesVisualesEspecificas`.
     */
    #[DataProvider('modulos')]
    public function testLosModulosUsanComposicionesVisualesEspecificas(string $modulo, string $tipo): void
    {
        $interfaz = CatalogoInterfaces::modulo($modulo, 'administrador');

        self::assertSame($tipo, $interfaz['tipo']);
        self::assertNotEmpty($interfaz['metricas']);
    }
}
