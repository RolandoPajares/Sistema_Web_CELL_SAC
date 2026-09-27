<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\CampaniaServicio;
use App\Servicios\MensajeFlashServicio;
use Throwable;

final class AdministradorCampaniaController
{
    public function __construct(
        private Vista $vista,
        private CampaniaServicio $campanias,
        private MensajeFlashServicio $mensajesFlash,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        $idEdicion = filter_var($solicitud->consulta('edit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $this->vista->renderizar('administrador.campanias.indice', [
            'tituloPagina' => 'Publicidad',
            'baseDatosDisponible' => $this->campanias->conexionDisponible(),
            'campanias' => $this->campanias->todosParaAdministrador(),
            'edicion' => $idEdicion !== false ? $this->campanias->buscar((int) $idEdicion) : null,
            'exito' => $this->mensajesFlash->extraer('success'),
            'error' => $this->mensajesFlash->extraer('error'),
        ], 'administrador');
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $id = filter_var($solicitud->entrada('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $idGuardado = $this->campanias->guardar($solicitud->todos(), $id !== false ? (int) $id : null);
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
            $this->campanias->desactivar((int) $id);
            $this->mensajesFlash->exito('Campaña desactivada.');
        } catch (Throwable) {
            $this->mensajesFlash->error('No se pudo desactivar la campaña.');
        }

        return redirect('admin/campaigns');
    }
}
