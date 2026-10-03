<?php

declare(strict_types=1);

namespace App\Controladores\Pedidos;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorPedidosAdministrador;
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

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $pedidos = $this->pedidos->todosConUsuarios();
        $conteos = $this->pedidos->contarPorEstado();
        $presentacion = PresentadorPedidosAdministrador::presentarLista($pedidos, $conteos);

        return $this->vista->renderizar('roles.internos.administrador.pedidos.indice', [
            'tituloPagina' => 'Pedidos',
            'pedidos' => $presentacion['pedidos'],
            'atributoPedidosVaciosOculto' => $presentacion['pedidos'] === [] ? '' : 'hidden',
            'atributoPedidoDetalleOculto' => 'hidden',
            'atributoPedidoDetalleVacioOculto' => '',
            'conteos' => $conteos,
            'tarjetasKpi' => $presentacion['tarjetasKpi'],
            'estadosPedido' => SolicitudEstadoPedido::ESTADOS,
            'detalle' => PresentadorPedidosAdministrador::presentarDetalle([]),
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    /**
     * Obtiene los datos de detalle del registro solicitado.
     */
    public function detalle(Solicitud $solicitud): Respuesta
    {
        $idPedido = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idPedido === false || ($detalle = $this->pedidos->buscarConDetalle((int) $idPedido)) === null) {
            $this->mensajes->error('El pedido solicitado no existe.');
            return redirigir('admin/orders');
        }

        $pedidos = $this->pedidos->todosConUsuarios();
        $conteos = $this->pedidos->contarPorEstado();
        $presentacion = PresentadorPedidosAdministrador::presentarLista($pedidos, $conteos, (int) $idPedido);
        $detalle = PresentadorPedidosAdministrador::presentarDetalle($detalle);

        return $this->vista->renderizar('roles.internos.administrador.pedidos.indice', [
            'tituloPagina' => 'Pedidos', 'pedidos' => $presentacion['pedidos'],
            'atributoPedidosVaciosOculto' => $presentacion['pedidos'] === [] ? '' : 'hidden',
            'atributoPedidoDetalleOculto' => '',
            'atributoPedidoDetalleVacioOculto' => 'hidden',
            'conteos' => $conteos, 'tarjetasKpi' => $presentacion['tarjetasKpi'], 'detalle' => $detalle,
            'estadosPedido' => SolicitudEstadoPedido::ESTADOS,
            'error' => $this->mensajes->extraer('error'), 'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    /**
     * Actualiza la información relacionada con «estado».
     */
    public function actualizarEstado(Solicitud $solicitud): Respuesta
    {
        $idPedido = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        try {
            if ($idPedido === false) {
                throw new \DomainException('El identificador del pedido no es válido.');
            }
            $estado = SolicitudEstadoPedido::validar($solicitud);
            $anterior = $this->pedidos->buscarConDetalle((int) $idPedido);
            $this->pedidos->actualizarEstado((int) $idPedido, $estado);
            $this->auditoria->registrar(
                'order.status.updated',
                'order',
                (int) $idPedido,
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

        return redirigir('admin/orders/' . ($idPedido === false ? '' : (int) $idPedido));
    }

}
