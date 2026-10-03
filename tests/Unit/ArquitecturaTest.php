<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Aplicacion;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

/**
 * Pruebas unitarias de arquitectura enfocadas en verificar las capas del sistema,
 * el autocargador PSR-4, la validez de las rutas, la seguridad y el correcto desacoplamiento.
 */
final class ArquitecturaTest extends TestCase
{
    /**
     * Comprueba que la aplicación utilice exclusivamente las capas de nivel superior esperadas.
     */
    public function testLaAplicacionUsaSoloLasCapasPrincipalesEsperadas(): void
    {
        $rutaAplicacion = dirname(__DIR__, 2) . '/app';
        
        // Filtra y obtiene únicamente los directorios de primer nivel dentro de la carpeta 'app'.
        $capas = array_values(array_filter(
            scandir($rutaAplicacion) ?: [],
            static fn (string $entrada): bool => $entrada !== '.' && $entrada !== '..'
                && is_dir($rutaAplicacion . '/' . $entrada)
        ));
        
        sort($capas, SORT_STRING);

        // Valida que la estructura de capas coincida exactamente con la arquitectura definida.
        self::assertSame(
            ['Controladores', 'DAO', 'DTO', 'Middleware', 'Modelos', 'Nucleo', 'Servicios', 'Soporte', 'Validacion'],
            $capas
        );
    }

    /**
     * Comprueba que el autocargador manual PSR-4 está registrado y funciona.
     */
    public function testElAutocargadorPsr4ManualEstaRegistradoYFunciona(): void
    {
        $clase = 'App\\Soporte\\Excepciones\\ExcepcionNoEncontrado';
        self::assertFalse(class_exists($clase, false));

        $cargadorManual = null;
        
        // Busca la función de autocarga PSR-4 entre las registradas.
        foreach (spl_autoload_functions() as $cargador) {
            if ($cargador instanceof \Closure) {
                $cargadorManual = $cargador;
                break;
            }
        }

        self::assertInstanceOf(\Closure::class, $cargadorManual);
        
        // Invoca el autocargador y comprueba que la clase se cargue.
        $cargadorManual($clase);
        self::assertTrue(class_exists($clase, false));
    }

    /**
     * Comprueba que los símbolos de la aplicación coincidan con las rutas PSR-4 y su autocarga.
     */
    public function testLosSimbolosCoincidenConLasRutasPsr4YLaAutocarga(): void
    {
        $rutaAplicacion = realpath(dirname(__DIR__, 2) . '/app');
        self::assertIsString($rutaAplicacion);

        // Recorre todos los archivos PHP de la aplicación verificando el cumplimiento estricto del estándar PSR-4.
        foreach ($this->archivosPhp($rutaAplicacion) as $archivo) {
            $codigoFuente = file_get_contents($archivo);
            self::assertIsString($codigoFuente);
            
            self::assertSame(1, preg_match('/namespace\s+([^;]+);/', $codigoFuente, $espacioNombres), $archivo);
            self::assertSame(
                1,
                preg_match('/(?:final\s+|abstract\s+)?(?:class|interface|trait|enum)\s+(\w+)/', $codigoFuente, $simbolo),
                $archivo
            );

            $nombreClaseCompleto = $espacioNombres[1] . '\\' . $simbolo[1];
            $rutaEsperada = $rutaAplicacion . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, substr($nombreClaseCompleto, strlen('App\\')))
                . '.php';

            self::assertSame(realpath($archivo), realpath($rutaEsperada), $nombreClaseCompleto);
            self::assertTrue(
                class_exists($nombreClaseCompleto)
                || interface_exists($nombreClaseCompleto)
                || trait_exists($nombreClaseCompleto)
                || enum_exists($nombreClaseCompleto),
                'Falló la carga automática de ' . $nombreClaseCompleto
            );
        }
    }

    /**
     * Comprueba que cada ruta registrada referencia un método de controlador existente.
     */
    public function testCadaRutaApuntaAUnMetodoExistente(): void
    {
        $enrutador = Aplicacion::obtener(Enrutador::class);
        $reflexion = new ReflectionClass($enrutador);
        
        // Obtiene la propiedad privada de rutas mediante reflexión para validar su integridad.
        $propiedadRutas = $reflexion->getProperty('rutas');
        $rutas = $propiedadRutas->getValue($enrutador);

        self::assertIsArray($rutas);
        self::assertNotEmpty($rutas);
        
        // Verifica que cada manejador apunte a una clase controladora y un método válidos.
        foreach ($rutas as $rutaRegistrada) {
            [$controlador, $metodo] = $rutaRegistrada['manejador'];
            self::assertTrue(class_exists($controlador), (string) $controlador);
            self::assertTrue(method_exists($controlador, $metodo), $controlador . '::' . $metodo);
        }
    }

    /**
     * Comprueba que los controladores y servicios no contengan ejecución directa de SQL.
     */
    public function testLosControladoresYServiciosNoEjecutanSqlDirectamente(): void
    {
        $rutaAplicacion = dirname(__DIR__, 2) . '/app';
        
        // Analiza las carpetas de controladores y servicios en búsqueda de consultas SQL directas prohibidas.
        foreach ([$rutaAplicacion . '/Controladores', $rutaAplicacion . '/Servicios'] as $directorio) {
            foreach ($this->archivosPhp($directorio) as $archivo) {
                $codigoFuente = file_get_contents($archivo);
                self::assertIsString($codigoFuente);
                
                $codigoFuente = str_replace('$solicitud->query', '$solicitud->input', $codigoFuente);
                
                self::assertDoesNotMatchRegularExpression(
                    '/->(?:prepare|query|exec)\s*\(/',
                    $codigoFuente,
                    'Se encontró ejecución directa de SQL en ' . $archivo
                );
            }
        }
    }

    /**
     * Comprueba que el servicio de proceso de compra no dependa directamente de la infraestructura PDO.
     */
    public function testProcesoCompraServicioNoDependeDeInfraestructuraPDO(): void
    {
        $codigoFuente = file_get_contents(
            dirname(__DIR__, 2) . '/app/Servicios/Compra/ProcesoCompraServicio.php'
        );
        
        self::assertIsString($codigoFuente);
        self::assertStringNotContainsString('Conexion', $codigoFuente);
        self::assertDoesNotMatchRegularExpression('/\\bPDO\\b/', $codigoFuente);
        self::assertStringContainsString('GestorTransaccionesInterfaz', $codigoFuente);
    }

    /**
     * Comprueba que la clase de conexión mantenga configuradas las opciones seguras de PDO.
     */
    public function testConexionMantieneOpcionesPDOSeguras(): void
    {
        $codigoFuente = file_get_contents(
            dirname(__DIR__, 2) . '/app/Nucleo/BaseDatos/Conexion.php'
        );
        
        self::assertIsString($codigoFuente);
        self::assertStringContainsString('PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION', $codigoFuente);
        self::assertStringContainsString('PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC', $codigoFuente);
        self::assertStringContainsString('PDO::ATTR_EMULATE_PREPARES => false', $codigoFuente);
    }

    /**
     * Lista recursivamente los archivos PHP que se deben revisar en la comprobación arquitectónica.
     *
     * @return array<int, string>
     */
    private function archivosPhp(string $directorio): array
    {
        $archivos = [];
        $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio));
        
        foreach ($iterador as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php') {
                $archivos[] = $archivo->getPathname();
            }
        }
        
        sort($archivos, SORT_STRING);

        return $archivos;
    }
}
