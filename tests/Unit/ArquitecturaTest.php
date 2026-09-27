<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Enrutamiento\Enrutador;
use App\Soporte\Aplicacion;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class ArquitecturaTest extends TestCase
{
    public function testManualPsr4AutoloaderIsRegisteredAndFunctional(): void
    {
        $clase = 'App\\Soporte\\Excepciones\\ExcepcionNoEncontrado';
        self::assertFalse(class_exists($clase, false));

        $cargadorManual = null;
        foreach (spl_autoload_functions() as $cargador) {
            if ($cargador instanceof \Closure) {
                $cargadorManual = $cargador;
                break;
            }
        }

        self::assertInstanceOf(\Closure::class, $cargadorManual);
        $cargadorManual($clase);
        self::assertTrue(class_exists($clase, false));
    }

    public function testApplicationSymbolsMatchPsr4PathsAndAutoload(): void
    {
        $rutaAplicacion = realpath(dirname(__DIR__, 2) . '/app');
        self::assertIsString($rutaAplicacion);

        foreach ($this->phpFiles($rutaAplicacion) as $archivo) {
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

    public function testEveryRouteReferencesAnExistingControllerMethod(): void
    {
        $enrutador = Aplicacion::obtener(Enrutador::class);
        $reflexion = new ReflectionClass($enrutador);
        $propiedadRutas = $reflexion->getProperty('rutas');
        $rutas = $propiedadRutas->getValue($enrutador);

        self::assertIsArray($rutas);
        self::assertNotEmpty($rutas);
        foreach ($rutas as $rutaRegistrada) {
            [$controlador, $metodo] = $rutaRegistrada['manejador'];
            self::assertTrue(class_exists($controlador), (string) $controlador);
            self::assertTrue(method_exists($controlador, $metodo), $controlador . '::' . $metodo);
        }
    }

    public function testControllersAndServicesContainNoDirectSqlExecution(): void
    {
        $rutaAplicacion = dirname(__DIR__, 2) . '/app';
        foreach ([$rutaAplicacion . '/Http', $rutaAplicacion . '/Servicios'] as $directorio) {
            foreach ($this->phpFiles($directorio) as $archivo) {
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

    public function testProcesoCompraServiceDoesNotDependOnPdoInfrastructure(): void
    {
        $codigoFuente = file_get_contents(dirname(__DIR__, 2) . '/app/Servicios/ProcesoCompraServicio.php');
        self::assertIsString($codigoFuente);
        self::assertStringNotContainsString('Conexion', $codigoFuente);
        self::assertDoesNotMatchRegularExpression('/\\bPDO\\b/', $codigoFuente);
        self::assertStringContainsString('GestorTransaccionesInterfaz', $codigoFuente);
    }

    /** @return array<int, string> */
    private function phpFiles(string $directorio): array
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
