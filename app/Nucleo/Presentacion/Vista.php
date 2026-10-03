<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion;

use App\Nucleo\Http\Respuesta;
use App\Soporte\Configuracion\RepositorioConfiguracion;

final class Vista
{
    public function __construct(
        private RepositorioConfiguracion $configuracion,
        private CompositorVistas $compositor,
    ) {
    }

    /**
     * Renderiza la vista indicada con los datos preparados por el controlador.
     * @param array<string, mixed> $datos
     */
    public function renderizar(string $vista, array $datos = [], string $plantilla = 'aplicacion', int $estado = 200): Respuesta
    {
        $rutaVistas = (string) $this->configuracion->obtener('paths.views');
        $archivoVista = $rutaVistas . '/' . str_replace('.', '/', $vista) . '.php';
        $archivoPlantilla = $rutaVistas . '/plantillas/' . $plantilla . '.php';

        if (!is_file($archivoVista)) {
            throw new \RuntimeException("La vista {$vista} no existe.");
        }

        $datosVista = array_merge($this->compositor->datos($plantilla, $datos), $datos);
        // Las plantillas conservan variables locales para una lectura directa; cada vista documenta su contrato con PHPDoc.
        extract($datosVista, EXTR_SKIP);

        ob_start();
        require $archivoVista;
        $contenido = (string) ob_get_clean();

        if ($plantilla === '') {
            return new Respuesta($contenido, $estado);
        }

        if (!is_file($archivoPlantilla)) {
            throw new \RuntimeException("La plantilla {$plantilla} no existe.");
        }

        ob_start();
        require $archivoPlantilla;

        return new Respuesta((string) ob_get_clean(), $estado);
    }
}
