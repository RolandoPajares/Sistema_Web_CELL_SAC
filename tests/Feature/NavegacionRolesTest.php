<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Aplicacion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Autorizacion\AccesoRol;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de integración para validar la navegación, los permisos y las restricciones de acceso según el rol del usuario.
 */
final class NavegacionRolesTest extends TestCase
{
    private Enrutador $enrutador;

    /**
     * Prepara el estado y los recursos necesarios para ejecutar la prueba.
     */
    protected function setUp(): void
    {
        $_SESSION = [];
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
    }

    /**
     * Devuelve los perfiles disponibles para las recomendaciones de productos.
     *
     * @return array<string, array{0:string, 1:array<int,string>, 2:string}>
     */
    public static function perfiles(): array
    {
        return [
            'cliente minorista' => [
                'cliente_minorista', 
                ['perfil', 'direcciones', 'pedidos', 'historial'], 
                'usuarios'
            ],
            'cliente mayorista' => [
                'cliente_mayorista', 
                ['cotizaciones', 'pedidos-mayoristas', 'historial'], 
                'usuarios'
            ],
            'compras y logística' => [
                'compras_logistica', 
                ['inventario', 'productos', 'proveedores', 'compras', 'movimientos-stock', 'preparacion-pedidos', 'logistica-mayorista', 'alertas-stock'], 
                'usuarios'
            ],
            'ventas mayoristas' => [
                'ventas_mayoristas', 
                ['productos', 'clientes-mayoristas', 'cotizaciones', 'pedidos-mayoristas', 'historial-cliente', 'seguimiento-comercial'], 
                'usuarios'
            ],
            'ventas minoristas' => [
                'ventas_minoristas', 
                ['productos', 'inventario', 'clientes', 'ventas', 'pedidos', 'garantias', 'devoluciones', 'reclamaciones'], 
                'usuarios'
            ],
            'marketing' => [
                'marketing', 
                ['publicidad', 'campanias', 'catalogo-digital', 'destacados', 'promociones', 'contenido', 'analitica', 'consultas-digitales', 'segmentacion', 'smartcommerce-analytics'], 
                'usuarios'
            ],
        ];
    }

    /**
     * Comprueba qué módulos puede abrir cada perfil y qué rutas deben bloquearse.
     * 
     * @param array<int, string> $permitidos
     */
    #[DataProvider('perfiles')]
    public function testTableroModulosYBloqueosPorPerfil(string $rol, array $permitidos, string $prohibido): void
    {
        $_SESSION['user'] = [
            'id' => 1, 
            'nombre' => 'Prueba', 
            'correo' => $rol . '@test.local', 
            'rol' => $rol
        ];

        self::assertSame(200, $this->solicitarGet('/panel')->estado(), $rol . ' debe entrar a su dashboard');
        
        foreach ($permitidos as $modulo) {
            self::assertSame(200, $this->solicitarGet('/panel/' . $modulo)->estado(), $rol . ' debe acceder a ' . $modulo);
        }
        
        if ($prohibido !== '') {
            self::assertSame(403, $this->solicitarGet('/panel/' . $prohibido)->estado(), $rol . ' no debe acceder a ' . $prohibido);
        }
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba testVisitanteSoloAccedeAlRecorridoPublico.
     */
    public function testVisitanteSoloAccedeAlRecorridoPublico(): void
    {
        $rutasPublicas = [
            '/', 
            '/catalog', 
            '/products/1', 
            '/smart/recommend', 
            '/smart/compare', 
            '/smart/assistant', 
            '/about', 
            '/contact'
        ];
        
        foreach ($rutasPublicas as $ruta) {
            self::assertSame(200, $this->solicitarGet($ruta)->estado(), 'Visitante debe acceder a ' . $ruta);
        }
        
        $rutasProtegidas = [
            '/panel', 
            '/cart', 
            '/checkout', 
            '/mayorista', 
            '/smart/optimizer', 
            '/smart/ads'
        ];
        
        foreach ($rutasProtegidas as $ruta) {
            self::assertSame(302, $this->solicitarGet($ruta)->estado(), 'Visitante debe ser enviado al login desde ' . $ruta);
        }
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba testRutasEspecialesRespetanElPerfil.
     */
    public function testRutasEspecialesRespetanElPerfil(): void
    {
        $casos = [
            ['cliente_minorista', '/cart', 200],
            ['cliente_minorista', '/checkout', 200],
            ['cliente_minorista', '/mayorista', 403],
            ['cliente_mayorista', '/mayorista', 200],
            ['cliente_mayorista', '/smart/optimizer', 200],
            ['cliente_mayorista', '/cart', 403],
            ['ventas_mayoristas', '/smart/optimizer', 200],
            ['ventas_minoristas', '/smart/optimizer', 403],
            ['marketing', '/smart/ads', 200],
            ['compras_logistica', '/smart/ads', 403],
        ];

        foreach ($casos as [$rol, $ruta, $estado]) {
            $_SESSION['user'] = [
                'id' => 1, 
                'nombre' => 'Prueba', 
                'rol' => $rol
            ];
            
            self::assertSame($estado, $this->solicitarGet($ruta)->estado(), $rol . ' en ' . $ruta);
        }
    }

    /**
     * Prepara los indicadores necesarios para los paneles disponibles.
     *
     * @return array<string, array{0:string, 1:string}>
     */
    public static function tableros(): array
    {
        return [
            'cliente minorista' => ['cliente_minorista', 'Mi cuenta'],
            'cliente mayorista' => ['cliente_mayorista', 'Portal mayorista B2B'],
            'administrador' => ['administrador', 'Dashboard ejecutivo'],
            'compras y logística' => ['compras_logistica', 'Dashboard de operaciones'],
            'ventas mayoristas' => ['ventas_mayoristas', 'Dashboard comercial B2B'],
            'ventas minoristas' => ['ventas_minoristas', 'Punto de venta / Dashboard B2C'],
            'marketing' => ['marketing', 'Dashboard de marketing'],
        ];
    }

    /**
     * Comprueba que cada rol llegue al tablero que le corresponde.
     */
    #[DataProvider('tableros')]
    public function testCadaRolLlegaAlTableroQueLeCorresponde(string $rol, string $titulo): void
    {
        $_SESSION['user'] = [
            'id' => 1, 
            'nombre' => 'Prueba', 
            'correo' => $rol . '@test.local', 
            'rol' => $rol
        ];

        $respuesta = $this->solicitarGet('/panel');

        if ($rol === 'administrador') {
            self::assertSame(302, $respuesta->estado());
            return;
        }
        
        self::assertSame(200, $respuesta->estado());
        self::assertStringContainsString($titulo, $respuesta->contenido());
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba testDestinosDeNavegacionCriticos.
     */
    public function testDestinosDeNavegacionCriticos(): void
    {
        self::assertSame('catalog', AccesoRol::destino('cliente_minorista', 'catalogo'));
        self::assertSame('mayorista', AccesoRol::destino('cliente_mayorista', 'catalogo-b2b'));
        self::assertSame('smart/optimizer', AccesoRol::destino('cliente_mayorista', 'optimizador'));
        self::assertSame('admin/campaigns', AccesoRol::destino('administrador', 'publicidad'));
        self::assertSame('admin/categories', AccesoRol::destino('administrador', 'categorias'));
        self::assertSame('panel/movimientos-stock', AccesoRol::destino('compras_logistica', 'movimientos-stock'));
    }

    // Despacha una solicitud GET a la ruta indicada.
    private function solicitarGet(string $ruta): Respuesta
    {
        return $this->enrutador->despachar(new Solicitud('GET', $ruta, [], [], []));
    }
}
