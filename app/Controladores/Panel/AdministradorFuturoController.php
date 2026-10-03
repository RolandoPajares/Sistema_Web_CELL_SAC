<?php

declare(strict_types=1);

namespace App\Controladores\Panel;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorInformesAdministrador;
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
        $presentacion = PresentadorInformesAdministrador::presentarReporte($reporte);

        return $this->vista->renderizar(
            'roles.internos.administrador.reportes.indice',
            array_merge($presentacion, ['tituloPagina' => 'Reportes']),
            'administrador'
        );
    }

    public function auditoria(Solicitud $solicitud): Respuesta
    {
        $idUsuario = filter_var($solicitud->consulta('usuario'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $consulta = $this->auditoria->consulta(
            is_scalar($solicitud->consulta('desde')) ? (string) $solicitud->consulta('desde') : '',
            is_scalar($solicitud->consulta('hasta')) ? (string) $solicitud->consulta('hasta') : '',
            is_scalar($solicitud->consulta('entidad')) ? (string) $solicitud->consulta('entidad') : '',
            $idUsuario === false ? 0 : (int) $idUsuario
        );
        $presentacion = PresentadorInformesAdministrador::presentarAuditoria($consulta);
        $datosVista = array_merge($presentacion, [
            'tituloPagina' => 'Auditoría',
            'usuarios' => $this->usuarios->todos(),
            'entidadSeleccionada' => is_scalar($solicitud->consulta('entidad')) ? (string) $solicitud->consulta('entidad') : '',
            'usuarioSeleccionado' => $idUsuario === false ? 0 : (int) $idUsuario,
        ]);

        return $this->vista->renderizar(
            'roles.internos.administrador.auditoria.indice',
            $datosVista,
            'administrador'
        );
    }
}
