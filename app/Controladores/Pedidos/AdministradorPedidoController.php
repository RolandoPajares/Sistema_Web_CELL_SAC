<?php

declare(strict_types=1);

namespace App\Controladores\Pedidos;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Pedidos\PedidoServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Validacion\Pedidos\SolicitudEstadoPedido;
use App\Servicios\Auditoria\AuditoriaServicio;

final class AdministradorPedidoController
{
    public function __construct(
        private Vista $vista,
        private PedidoServicio $pedidos,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro,
        private AuditoriaServicio $auditoria,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.pedidos.indice', [
            'tituloPagina' => 'Pedidos',
            'pedidos' => $this->pedidos->todosConUsuarios(),
            'conteos' => $this->pedidos->contarPorEstado(),
            'detalle' => null,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    public function detalle(Solicitud $solicitud): Respuesta
    {
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || ($detalle = $this->pedidos->buscarConDetalle((int) $id)) === null) {
            $this->mensajes->error('El pedido solicitado no existe.');
            return redirect('admin/orders');
        }

        return $this->vista->renderizar('roles.internos.administrador.pedidos.indice', [
            'tituloPagina' => 'Pedidos', 'pedidos' => $this->pedidos->todosConUsuarios(),
            'conteos' => $this->pedidos->contarPorEstado(), 'detalle' => $detalle,
            'error' => $this->mensajes->extraer('error'), 'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    public function actualizarEstado(Solicitud $solicitud): Respuesta
    {
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        try {
            if ($id === false) {
                throw new \DomainException('El identificador del pedido no es válido.');
            }
            $estado = SolicitudEstadoPedido::validar($solicitud);
            $anterior = $this->pedidos->buscarConDetalle((int) $id);
            $this->pedidos->actualizarEstado((int) $id, $estado);
            $this->auditoria->registrar(
                'order.status.updated',
                'order',
                (int) $id,
                ['estado' => $anterior['estado'] ?? null],
                ['estado' => $estado],
                $solicitud->direccionIp()
            );
            $this->mensajes->exito('Estado del pedido actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'El estado no es válido.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (\Throwable $excepcion) {
            $this->registro->error('No se pudo actualizar el estado del pedido.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo actualizar el estado del pedido.');
        }

        return redirect('admin/orders/' . ($id === false ? '' : (int) $id));
    }
}
