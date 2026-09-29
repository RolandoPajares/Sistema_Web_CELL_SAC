<?php

declare(strict_types=1);

namespace App\Controladores\ComercioInteligente;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Productos\ProductoServicio;
use App\Servicios\ComercioInteligente\ComercioInteligenteServicio;
use App\Servicios\Campanias\CampaniaServicio;

final class ComercioInteligenteController
{
    public function __construct(
        private Vista $vista,
        private ComercioInteligenteServicio $comercioInteligente,
        private ProductoServicio $productos,
        private CampaniaServicio $campanias,
    ) {
    }

    public function recomendador(Solicitud $solicitud): Respuesta
    {
        $presupuesto = $this->numeroPositivo($solicitud->consulta('budget'));
        $uso = $this->valorPermitido($solicitud->consulta('use'), ['study', 'gaming', 'camera', 'work', 'social'], 'study');
        $prioridad = $this->valorPermitido(
            $solicitud->consulta('priority'),
            ['valor', 'rendimiento', 'bateria', 'camara'],
            'valor'
        );

        return $this->vista->renderizar('comercio-inteligente.recomendador.indice', [
            'tituloPagina' => 'Recomendador inteligente',
            'resultados' => $presupuesto > 0
                ? $this->comercioInteligente->recomendar($presupuesto, $uso, $prioridad)
                : [],
            'presupuesto' => $presupuesto,
            'uso' => $uso,
            'prioridad' => $prioridad,
        ]);
    }

    public function comparar(Solicitud $solicitud): Respuesta
    {
        $idsCrudos = $solicitud->consulta('ids', '');
        $idsProductos = is_scalar($idsCrudos)
            ? array_values(array_filter(
                array_map('intval', explode(',', (string) $idsCrudos)),
                static fn (int $id): bool => $id > 0
            ))
            : [];

        return $this->vista->renderizar('comercio-inteligente.comparador.indice', [
            'tituloPagina' => 'Comparador inteligente',
            'productos' => $this->comercioInteligente->comparar($idsProductos),
            'todosLosProductos' => array_values(array_filter(
                $this->productos->todosActivos(),
                static fn (array $producto): bool => stripos((string) $producto['categoria'], 'cel') !== false
            )),
            'idsProductos' => $idsProductos,
        ]);
    }

    public function optimizador(Solicitud $solicitud): Respuesta
    {
        $presupuesto = $this->numeroPositivo($solicitud->consulta('budget'));
        $objetivo = $this->valorPermitido(
            $solicitud->consulta('goal'),
            ['variety', 'units', 'margin', 'premium'],
            'variety'
        );

        return $this->vista->renderizar('comercio-inteligente.optimizador.indice', [
            'tituloPagina' => 'Optimizador de compra',
            'propuesta' => $presupuesto > 0
                ? $this->comercioInteligente->optimizar($presupuesto, $objetivo)
                : null,
            'presupuesto' => $presupuesto,
            'objetivo' => $objetivo,
        ]);
    }

    public function asistente(Solicitud $solicitud): Respuesta
    {
        if (user_role() === 'ventas_mayoristas') {
            return $this->vista->renderizar(
                'comercio-inteligente.asistente-ia.interno',
                ['tituloPagina' => 'MD Assistant B2B'],
                'interno'
            );
        }
        return $this->vista->renderizar('comercio-inteligente.asistente-ia.indice', ['tituloPagina' => 'MD Assistant']);
    }

    public function mayorista(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.externos.cliente-mayorista.portal-mayorista.indice', [
            'tituloPagina' => 'Portal mayorista',
            'productos' => array_slice($this->productos->todosActivos(), 0, 6),
        ]);
    }

    public function publicidadInteligente(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('comercio-inteligente.publicidad.indice', [
            'tituloPagina' => 'MD Ads inteligente',
            'resumen' => $this->campanias->resumen(),
            'campanias' => $this->campanias->todosParaAdministrador(),
        ]);
    }

    public function respuestaAsistente(Solicitud $solicitud): Respuesta
    {
        $entradaMensaje = $solicitud->consulta('message', '');
        $mensaje = is_scalar($entradaMensaje) ? trim((string) $entradaMensaje) : '';
        if ($mensaje === '') {
            return new Respuesta(json_encode(['ok' => false, 'mensaje' => 'Escribe una consulta para poder ayudarte.'], JSON_UNESCAPED_UNICODE), 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }
        if (mb_strlen($mensaje) > 500) {
            return new Respuesta(json_encode(['ok' => false, 'mensaje' => 'La consulta admite hasta 500 caracteres.'], JSON_UNESCAPED_UNICODE), 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $respuesta = $this->comercioInteligente->responder($mensaje);

        return new Respuesta(json_encode(['ok' => true, 'respuesta' => $respuesta], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /** @param array<int, string> $permitidos */
    private function valorPermitido(mixed $valor, array $permitidos, string $predeterminado): string
    {
        $valor = is_scalar($valor) ? (string) $valor : '';

        return in_array($valor, $permitidos, true) ? $valor : $predeterminado;
    }

    private function numeroPositivo(mixed $valor): float
    {
        if (!is_scalar($valor) || !is_numeric($valor)) {
            return 0.0;
        }

        $numero = (float) $valor;

        return is_finite($numero) && $numero > 0 ? min($numero, 99999999.99) : 0.0;
    }
}
