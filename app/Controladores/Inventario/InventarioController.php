<?php

declare(strict_types=1);

namespace App\Controladores\Inventario;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Auditoria\AuditoriaServicio;
use App\Servicios\Inventario\InventarioServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Validacion\Inventario\SolicitudMovimientoInventario;
use Throwable;

final class InventarioController
{
    public function __construct(
        private Vista $vista,
        private InventarioServicio $inventario,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro,
        private AuditoriaServicio $auditoria
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.inventario.indice', [
            'tituloPagina' => 'Inventario', 'existencias' => $this->inventario->existencias(),
            'movimientos' => $this->inventario->movimientos(),
            'resumen' => $this->inventario->resumen(),
            'error' => $this->mensajes->extraer('error'), 'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudMovimientoInventario::validar($solicitud);
            $id = $this->inventario->registrar($datos, (int) (current_user()['id'] ?? 0));
            $this->auditoria->registrar('inventory.movement.created', 'inventory_movement', $id, null, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Movimiento de inventario registrado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo registrar el movimiento de inventario.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo registrar el movimiento de inventario.');
        }
        return redirect('admin/inventory');
    }
}
