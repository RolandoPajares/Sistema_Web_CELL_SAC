<?php

declare(strict_types=1);

namespace App\Controladores\Campanias;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorCampanias;
use App\Servicios\Campanias\CampaniaServicio;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Servicios\Auditoria\AuditoriaServicio;
use Throwable;

final class AdministradorCampaniaController
{
    public function __construct(
        private Vista $vista,
        private CampaniaServicio $campanias,
        private MensajeFlashServicio $mensajesFlash,
        private AuditoriaServicio $auditoria,
    ) {
    }

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $idEdicion = filter_var($solicitud->consulta('edit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $campanias = $this->campanias->todosParaAdministrador();
        $resumen = $this->campanias->resumen();
        $edicion = $idEdicion !== false ? $this->campanias->buscar((int) $idEdicion) : null;
        $edicion = is_array($edicion) ? $edicion : null;
        $presentacion = PresentadorCampanias::presentar($campanias, $resumen, $edicion);
        $baseDatosDisponible = $this->campanias->conexionDisponible();
        $datosVista = array_merge($presentacion, [
            'tituloPagina' => 'Publicidad',
            'baseDatosDisponible' => $baseDatosDisponible,
            'atributoBaseDatosDisponibleOculto' => $baseDatosDisponible ? 'hidden' : '',
            'atributoContenidoCampaniasOculto' => $baseDatosDisponible ? '' : 'hidden',
            'resumen' => $resumen,
            'exito' => $this->mensajesFlash->extraer('success'),
            'error' => $this->mensajesFlash->extraer('error'),
        ]);

        return $this->vista->renderizar(
            'roles.internos.administrador.campanias.indice',
            $datosVista,
            'administrador'
        );
    }
    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $idCampania = filter_var($solicitud->entrada('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $anterior = $idCampania !== false ? $this->campanias->buscar((int) $idCampania) : null;
            $idGuardado = $this->campanias->guardar($solicitud->todos(), $idCampania !== false ? (int) $idCampania : null);
            $this->auditoria->registrar(
                $idCampania === false ? 'campaign.created' : 'campaign.updated',
                'campaign',
                $idGuardado,
                $anterior,
                $this->campanias->buscar($idGuardado),
                $solicitud->direccionIp()
            );
            $this->mensajesFlash->exito('Campaña #' . $idGuardado . ' guardada correctamente.');
        } catch (\InvalidArgumentException $excepcion) {
            $this->mensajesFlash->error($excepcion->getMessage() ?: 'No se pudo guardar la campaña.');
        } catch (Throwable) {
            $this->mensajesFlash->error('No se pudo guardar la campaña.');
        }

        return redirigir('admin/campaigns');
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(Solicitud $solicitud): Respuesta
    {
        try {
            $idCampania = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idCampania === false) {
                throw new \InvalidArgumentException('El ID de la campaña no es válido.');
            }
            $anterior = $this->campanias->buscar((int) $idCampania);
            $this->campanias->desactivar((int) $idCampania);
            $this->auditoria->registrar('campaign.deactivated', 'campaign', (int) $idCampania, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajesFlash->exito('Campaña desactivada.');
        } catch (Throwable) {
            $this->mensajesFlash->error('No se pudo desactivar la campaña.');
        }

        return redirigir('admin/campaigns');
    }
}
