<?php

declare(strict_types=1);

namespace App\Controladores\Campanias;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
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

    public function indice(Solicitud $solicitud): Respuesta
    {
        $idEdicion = filter_var($solicitud->consulta('edit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $this->vista->renderizar('roles.internos.administrador.campanias.indice', [
            'tituloPagina' => 'Publicidad',
            'baseDatosDisponible' => $this->campanias->conexionDisponible(),
            'campanias' => $this->campanias->todosParaAdministrador(),
            'resumen' => $this->campanias->resumen(),
            'edicion' => $idEdicion !== false ? $this->campanias->buscar((int) $idEdicion) : null,
            'exito' => $this->mensajesFlash->extraer('success'),
            'error' => $this->mensajesFlash->extraer('error'),
        ], 'administrador');
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $id = filter_var($solicitud->entrada('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $anterior = $id !== false ? $this->campanias->buscar((int) $id) : null;
            $idGuardado = $this->campanias->guardar($solicitud->todos(), $id !== false ? (int) $id : null);
            $this->auditoria->registrar(
                $id === false ? 'campaign.created' : 'campaign.updated',
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

        return redirect('admin/campaigns');
    }

    public function desactivar(Solicitud $solicitud): Respuesta
    {
        try {
            $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                throw new \InvalidArgumentException('El ID de la campaña no es válido.');
            }
            $anterior = $this->campanias->buscar((int) $id);
            $this->campanias->desactivar((int) $id);
            $this->auditoria->registrar('campaign.deactivated', 'campaign', (int) $id, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajesFlash->exito('Campaña desactivada.');
        } catch (Throwable) {
            $this->mensajesFlash->error('No se pudo desactivar la campaña.');
        }

        return redirect('admin/campaigns');
    }
}
