<?php

declare(strict_types=1);

namespace App\Controladores\Compra;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Compra\CarritoServicio;
use App\Servicios\Compra\ProcesoCompraServicio;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
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
        return $this->vista->renderizar('modulos.compra.checkout.indice', [
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
