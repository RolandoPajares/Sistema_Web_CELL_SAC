<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Soporte\Presentacion\CatalogoEstilos;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CatalogoEstilosTest extends TestCase
{
    /**
     * Devuelve los contextos de estilo registrados para el catálogo.
     *
     * @return array<string, array{string,string,string,string}>
     */
    public static function contextos(): array
    {
        return [
            'inicio' => ['/', 'aplicacion', '', 'inicio'],
            'catalogo' => ['/catalog', 'aplicacion', 'cliente_minorista', 'catalog'],
            'perfil cliente' => ['/panel/perfil', 'aplicacion', 'cliente_mayorista', 'perfil'],
            'productos administrador' => ['/admin/products', 'interno', 'administrador', 'admin-products'],
            'inventario compras' => ['/panel/inventario', 'interno', 'compras_logistica', 'inventario'],
            'asistente ventas' => ['/smart/assistant', 'interno', 'ventas_mayoristas', 'asistente-ia'],
            'publicidad marketing' => ['/smart/ads', 'aplicacion', 'marketing', 'publicidad-inteligente'],
        ];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testLosEstilosContextualesExisten`.
     */
    #[DataProvider('contextos')]
    public function testLosEstilosContextualesExisten(
        string $ruta,
        string $plantilla,
        string $rol,
        string $moduloEsperado
    ): void {
        $raiz = dirname(__DIR__, 2) . '/public/';
        $estilos = CatalogoEstilos::para($ruta, $plantilla, $rol);

        self::assertNotEmpty($estilos);
        self::assertStringContainsString('modulo-' . $moduloEsperado, CatalogoEstilos::clasesCuerpo($ruta, $rol));
        foreach ($estilos as $estilo) {
            self::assertFileExists($raiz . $estilo, "No existe el CSS contextual {$estilo}");
        }
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testNoCargaCssAdministrativoEnElCatalogoPublico`.
     */
    public function testNoCargaCssAdministrativoEnElCatalogoPublico(): void
    {
        $estilos = CatalogoEstilos::para('/catalog', 'aplicacion');

        self::assertNotContains('assets/css/modulos/comunes/panel.css', $estilos);
        self::assertNotContains('assets/css/modulos/marketing/campanias.css', $estilos);
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testElVisitanteNoIntentaCargarUnaHojaDeRolInexistente`.
     */
    public function testElVisitanteNoIntentaCargarUnaHojaDeRolInexistente(): void
    {
        $estilos = CatalogoEstilos::para('/login', 'aplicacion', 'visitante');

        self::assertNotContains('assets/css/roles/internos/visitante.css', $estilos);
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testNormalizaLaRutaCuandoLaAplicacionViveEnUnaSubcarpeta`.
     */
    public function testNormalizaLaRutaCuandoLaAplicacionViveEnUnaSubcarpeta(): void
    {
        $scriptAnterior = $_SERVER['SCRIPT_NAME'] ?? null;
        $_SERVER['SCRIPT_NAME'] = '/dwa2/CRMLAND/Sistema_Web_CELL_SAC/public/index.php';

        try {
            $estilos = CatalogoEstilos::para(
                '/dwa2/CRMLAND/Sistema_Web_CELL_SAC/public/catalog',
                'aplicacion'
            );
        } finally {
            if ($scriptAnterior === null) {
                unset($_SERVER['SCRIPT_NAME']);
            } else {
                $_SERVER['SCRIPT_NAME'] = $scriptAnterior;
            }
        }

        self::assertContains('assets/css/publico/catalogo.css', $estilos);
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testElPerfilCargaRolYModuloSinDuplicados`.
     */
    public function testElPerfilCargaRolYModuloSinDuplicados(): void
    {
        $estilos = CatalogoEstilos::para('/panel/perfil', 'aplicacion', 'cliente_mayorista', 'perfil');

        self::assertContains('assets/css/roles/externos/cliente-mayorista.css', $estilos);
        self::assertContains('assets/css/modulos/cuenta/cuenta.css', $estilos);
        self::assertSame($estilos, array_values(array_unique($estilos)));
    }
}
