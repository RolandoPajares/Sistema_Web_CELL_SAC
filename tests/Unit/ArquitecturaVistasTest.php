<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Nucleo\Presentacion\Panel\PresentadorPanelRol;

/**
 * Pruebas unitarias de arquitectura para verificar la existencia de vistas renderizadas,
 * plantillas reutilizables, componentes y la limpieza de directorios vacíos.
 */
final class ArquitecturaVistasTest extends TestCase
{
    /**
     * Comprueba que todas las vistas literales referenciadas en los controladores existan físicamente.
     */
    public function testTodasLasVistasLiteralesRenderizadasExisten(): void
    {
        $raiz = dirname(__DIR__, 2);
        
        // Recorre de forma recursiva la carpeta de controladores para extraer las vistas utilizadas.
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
            
            // Busca coincidencias de llamadas al método renderizar con vistas literales.
            preg_match_all("/renderizar\\(\\s*(['\"])([^'\"]+)\\1\\s*(?=[,)])/", $codigo, $coincidencias);
            $referencias = array_merge($referencias, $coincidencias[2]);
        }

        self::assertNotEmpty($referencias);
        
        // Valida que cada archivo de vista único referenciado exista en la ruta esperada.
        foreach (array_unique($referencias) as $vista) {
            $archivo = $raiz . '/resources/views/' . str_replace('.', '/', $vista) . '.php';
            self::assertFileExists($archivo, "La vista referenciada {$vista} no existe.");
        }
    }

    /**
     * Comprueba las vistas permitidas que el panel selecciona dinámicamente.
     */
    public function testDestinosDinamicosPermitidosDelPanelExisten(): void
    {
        $raizVistas = dirname(__DIR__, 2) . '/resources/views/';
        $destinos = [
            'modulos.panel._parciales.controles.entrada',
            'modulos.panel._parciales.controles.select',
            'modulos.panel._parciales.controles.select-data',
            'modulos.panel._parciales.controles.textarea',
            'modulos.panel.indice',
            'modulos.panel._parciales.david.indice',
        ];

        foreach (['dashboard', 'pedidos', 'pedidos-mayoristas', 'historial', 'perfil', 'direcciones', 'favoritos', 'desconocido'] as $modulo) {
            $destinos[] = PresentadorPanelRol::vistaContenidoCuenta($modulo);
        }

        foreach (array_unique($destinos) as $vista) {
            $archivo = $raizVistas . str_replace('.', '/', $vista) . '.php';
            self::assertFileExists($archivo, "El destino dinámico del panel {$vista} no existe.");
        }
    }

    /**
     * Comprueba que las plantillas maestras y componentes reutilizables esenciales existan.
     */
    public function testPlantillasYComponentesReutilizablesExisten(): void
    {
        $raizVistas = dirname(__DIR__, 2) . '/resources/views/';
        
        // Lista de plantillas y componentes críticos que deben estar presentes en el proyecto.
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
            'publico/inicio/_tarjeta-producto.php',
            'publico/catalogo/_tarjeta-producto.php',
            'modulos/cuenta/_parciales/lista-pedidos.php',
        ];

        foreach ($archivos as $archivo) {
            self::assertFileExists($raizVistas . $archivo);
        }
    }

    /**
     * Comprueba que no queden directorios vacíos dentro de la estructura de vistas.
     */
    public function testNoQuedanDirectoriosVaciosEnVistas(): void
    {
        $raizVistas = dirname(__DIR__, 2) . '/resources/views';
        
        // Recorre todos los elementos dentro de la carpeta de vistas para verificar su contenido.
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
