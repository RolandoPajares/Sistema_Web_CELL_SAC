<?php

declare(strict_types=1);

namespace App\Controladores\Inventario;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorInventario;
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

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $existencias = $this->inventario->existencias();
        $movimientos = $this->inventario->movimientos();
        $resumen = $this->inventario->resumen();
        $presentacion = PresentadorInventario::presentar($existencias, $movimientos, $resumen);

        return $this->vista->renderizar('roles.internos.administrador.inventario.indice', [
            'tituloPagina' => 'Inventario',
            'existencias' => $existencias,
            'existenciasInventario' => $presentacion['existenciasInventario'],
            'fechaHoyVista' => $presentacion['fechaHoyVista'],
            'categoriasInventario' => $presentacion['categoriasInventario'],
            'tarjetasKpi' => $presentacion['tarjetasKpi'],
            'movimientos' => $presentacion['movimientos'],
            'resumen' => $resumen,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudMovimientoInventario::validar($solicitud);
            $idMovimiento = $this->inventario->registrar($datos, (int) (usuario_actual()['id'] ?? 0));
            $this->auditoria->registrar('inventory.movement.created', 'inventory_movement', $idMovimiento, null, $datos, $solicitud->direccionIp());
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
        return redirigir('admin/inventory');
    }
}
