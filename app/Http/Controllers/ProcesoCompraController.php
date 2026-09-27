<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\CarritoServicio;
use App\Servicios\ProcesoCompraServicio;
use App\Servicios\MensajeFlashServicio;
use App\Infraestructura\Registros\RegistradorArchivo;
use App\Soporte\Excepciones\ExcepcionStockInsuficiente;
use Throwable;

final class ProcesoCompraController
{
    public function __construct(
        private Vista $vista,
        private CarritoServicio $carrito,
        private ProcesoCompraServicio $pago,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('compra.indice', [
            'tituloPagina' => 'Finalizar pedido',
            'resumen' => $this->carrito->resumen(),
            'mensaje' => $this->mensajes->extraer('success'),
            'error' => $this->mensajes->extraer('error'),
        ]);
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $idPedido = $this->pago->procesarCompra((int) current_user()['id']);
            $this->mensajes->exito(
                'Pedido registrado correctamente. Código #' . $idPedido . '. El negocio podrá confirmarlo.'
            );
        } catch (ExcepcionStockInsuficiente $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('Falló el procesamiento del pedido.', [
                'exception' => $excepcion::class,
                'message' => $excepcion->getMessage(),
                'usuario_id' => current_user()['id'] ?? null,
            ]);
            $this->mensajes->error('No se pudo completar el pedido. Inténtalo nuevamente.');
        }

        return redirect('checkout');
    }
}
