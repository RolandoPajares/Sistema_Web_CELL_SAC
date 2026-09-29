<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ArquitecturaVistasTest extends TestCase
{
    public function testTodasLasVistasLiteralesRenderizadasExisten(): void
    {
        $raiz = dirname(__DIR__, 2);
        $iterador = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $raiz . '/app/Controladores',
                \FilesystemIterator::SKIP_DOTS
            )
        );
        $referencias = [];

        foreach ($iterador as $controlador) {
            if (!$controlador->isFile() || $controlador->getExtension() !== 'php') {
                continue;
            }

            $codigo = file_get_contents($controlador->getPathname());
            self::assertNotFalse($codigo);
            preg_match_all("/renderizar\\(\\s*['\"]([^'\"]+)['\"]/", $codigo, $coincidencias);
            $referencias = array_merge($referencias, $coincidencias[1]);
        }

        self::assertNotEmpty($referencias);
        foreach (array_unique($referencias) as $vista) {
            $archivo = $raiz . '/resources/views/' . str_replace('.', '/', $vista) . '.php';
            self::assertFileExists($archivo, "La vista referenciada {$vista} no existe.");
        }
    }

    public function testPlantillasYComponentesReutilizablesExisten(): void
    {
        $raizVistas = dirname(__DIR__, 2) . '/resources/views/';
        $archivos = [
            'plantillas/aplicacion.php',
            'plantillas/interno.php',
            'plantillas/administrador.php',
            'componentes/encabezados/publico.php',
            'componentes/navegacion/navbar-interno.php',
            'componentes/navegacion/sidebar-interno.php',
            'componentes/administracion/sidebar.php',
            'componentes/administracion/topbar.php',
            'componentes/administracion/asistente.php',
            'componentes/administracion/tarjetas-kpi.php',
            'componentes/pies/pie-pagina.php',
            'componentes/publicidad-dinamica.php',
            'componentes/tarjeta-producto.php',
            'modulos/cuenta/_parciales/lista-pedidos.php',
        ];

        foreach ($archivos as $archivo) {
            self::assertFileExists($raizVistas . $archivo);
        }
    }

    public function testNoQuedanDirectoriosVaciosEnVistas(): void
    {
        $raizVistas = dirname(__DIR__, 2) . '/resources/views';
        $iterador = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($raizVistas, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterador as $elemento) {
            if (!$elemento->isDir()) {
                continue;
            }

            $contenido = new \FilesystemIterator($elemento->getPathname(), \FilesystemIterator::SKIP_DOTS);
            self::assertTrue($contenido->valid(), 'Directorio vacío: ' . $elemento->getPathname());
        }
    }
}
