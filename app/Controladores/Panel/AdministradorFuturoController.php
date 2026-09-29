<?php

declare(strict_types=1);

namespace App\Controladores\Panel;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Auditoria\AuditoriaServicio;
use App\Servicios\ComercioInteligente\InteligenciaNegocioServicio;
use App\Servicios\Usuarios\UsuarioServicio;

final class AdministradorFuturoController
{
    public function __construct(
        private Vista $vista,
        private InteligenciaNegocioServicio $inteligencia,
        private AuditoriaServicio $auditoria,
        private UsuarioServicio $usuarios,
    ) {
    }

    public function reportes(Solicitud $solicitud): Respuesta
    {
        $reporte = $this->inteligencia->reporte(
            is_scalar($solicitud->consulta('tipo')) ? (string) $solicitud->consulta('tipo') : 'ventas',
            is_scalar($solicitud->consulta('desde')) ? (string) $solicitud->consulta('desde') : '',
            is_scalar($solicitud->consulta('hasta')) ? (string) $solicitud->consulta('hasta') : ''
        );

        return $this->vista->renderizar('roles.internos.administrador.reportes.indice', [
            'tituloPagina' => 'Reportes',
            'reporte' => $reporte,
        ], 'administrador');
    }

    public function auditoria(Solicitud $solicitud): Respuesta
    {
        $usuarioId = filter_var($solicitud->consulta('usuario'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $consulta = $this->auditoria->consulta(
            is_scalar($solicitud->consulta('desde')) ? (string) $solicitud->consulta('desde') : '',
            is_scalar($solicitud->consulta('hasta')) ? (string) $solicitud->consulta('hasta') : '',
            is_scalar($solicitud->consulta('entidad')) ? (string) $solicitud->consulta('entidad') : '',
            $usuarioId === false ? 0 : (int) $usuarioId
        );

        return $this->vista->renderizar('roles.internos.administrador.auditoria.indice', [
            'tituloPagina' => 'Auditoría',
            'consultaAuditoria' => $consulta,
            'usuarios' => $this->usuarios->todos(),
            'entidadSeleccionada' => is_scalar($solicitud->consulta('entidad')) ? (string) $solicitud->consulta('entidad') : '',
            'usuarioSeleccionado' => $usuarioId === false ? 0 : (int) $usuarioId,
        ], 'administrador');
    }
}
