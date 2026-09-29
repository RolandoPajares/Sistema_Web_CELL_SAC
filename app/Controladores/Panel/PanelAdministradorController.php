<?php

declare(strict_types=1);

namespace App\Controladores\Panel;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Panel\PanelAdministradorServicio;
use App\Servicios\ComercioInteligente\InteligenciaNegocioServicio;
use App\Servicios\Panel\AsistenteAdministradorServicio;

final class PanelAdministradorController
{
    public function __construct(
        private Vista $vista,
        private PanelAdministradorServicio $panel,
        private InteligenciaNegocioServicio $inteligencia,
        private AsistenteAdministradorServicio $asistente,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.tablero', [
            'tituloPagina' => 'Panel administrativo',
            'estadisticas' => $this->panel->estadisticas(),
            'inteligencia' => $this->inteligencia->resumenActual(),
            'ventasMensuales' => $this->inteligencia->ventasMensuales(),
            'pedidosRecientes' => $this->inteligencia->pedidosRecientes(),
        ], 'administrador');
    }

    public function respuestaAsistente(Solicitud $solicitud): Respuesta
    {
        $consulta = is_scalar($solicitud->entrada('consulta')) ? trim((string) $solicitud->entrada('consulta')) : '';
        if ($consulta === '' || mb_strlen($consulta) > 300) {
            return new Respuesta(
                json_encode(['ok' => false, 'mensaje' => 'Escribe una consulta de hasta 300 caracteres.'], JSON_UNESCAPED_UNICODE),
                422,
                ['Content-Type' => 'application/json; charset=utf-8']
            );
        }

        return new Respuesta(
            json_encode(['ok' => true, 'respuesta' => $this->asistente->responder($consulta)], JSON_UNESCAPED_UNICODE),
            200,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }
}
