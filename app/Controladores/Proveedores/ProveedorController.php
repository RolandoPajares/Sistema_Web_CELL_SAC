<?php

declare(strict_types=1);

namespace App\Controladores\Proveedores;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorCrudAdministrativo;
use App\Servicios\Auditoria\AuditoriaServicio;
use App\Servicios\Proveedores\ProveedorServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Validacion\Proveedores\SolicitudProveedor;
use Throwable;

final class ProveedorController
{
    public function __construct(
        private Vista $vista,
        private ProveedorServicio $proveedores,
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
        return $this->renderizar();
    }

    /**
     * Busca el registro solicitado y prepara su formulario de edición.
     */
    public function editar(Solicitud $solicitud): Respuesta
    {
        $idProveedor = $this->id($solicitud);
        if ($idProveedor === null || ($registro = $this->proveedores->buscar($idProveedor)) === null) {
            $this->mensajes->error('El proveedor solicitado no existe.');
            return redirigir('admin/suppliers');
        }
        return $this->renderizar($registro);
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        return $this->persistir($solicitud, null);
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        return $this->persistir($solicitud, $this->id($solicitud));
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(Solicitud $solicitud): Respuesta
    {
        try {
            $idProveedor = $this->id($solicitud) ?? throw new \DomainException('El identificador no es válido.');
            $anterior = $this->proveedores->buscar($idProveedor);
            $this->proveedores->desactivar($idProveedor);
            $this->auditoria->registrar('supplier.deactivated', 'supplier', $idProveedor, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajes->exito('Proveedor desactivado correctamente.');
        } catch (Throwable $excepcion) {
            $this->registro->advertencia('No se pudo desactivar el proveedor.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \DomainException ? $excepcion->getMessage() : 'No se pudo desactivar el proveedor.');
        }
        return redirigir('admin/suppliers');
    }

    private function persistir(Solicitud $solicitud, ?int $idProveedor): Respuesta
    {
        try {
            if ($solicitud->parametroRuta('id') !== null && $idProveedor === null) {
                throw new \DomainException('El identificador no es válido.');
            }
            $anterior = $idProveedor === null ? null : $this->proveedores->buscar($idProveedor);
            $datos = SolicitudProveedor::validar($solicitud);
            $guardado = $this->proveedores->guardar($datos, $idProveedor);
            $this->auditoria->registrar($idProveedor === null ? 'supplier.created' : 'supplier.updated', 'supplier', $guardado, $anterior, $datos, $solicitud->direccionIp());
            $this->mensajes->exito($idProveedor === null ? 'Proveedor registrado correctamente.' : 'Proveedor actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo guardar el proveedor.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo guardar el proveedor.');
        }
        return redirigir('admin/suppliers');
    }

    /**
     * Renderiza la vista indicada con los datos preparados por el controlador.
     */
    private function renderizar(?array $edicion = null): Respuesta
    {
        $registros = $this->proveedores->todos();
        $resumen = $this->proveedores->resumen();
        $presentacion = PresentadorCrudAdministrativo::presentarProveedores($registros, $resumen, $edicion);
        $presentacion['camposFormularioVista'] = PresentadorCrudAdministrativo::presentarControlesFormulario(
            $presentacion['camposFormularioVista'],
            fn (string $vista, array $datos): string => $this->vista->renderizar($vista, $datos, '')->contenido(),
        );
        $datosVista = array_merge($presentacion, [
            'tituloPagina' => 'Proveedores',
            'tituloModulo' => 'Proveedores',
            'tipoModulo' => 'proveedores',
            'iconoModulo' => 'bi-truck',
            'botonNuevo' => 'Nuevo proveedor',
            'descripcionModulo' => 'Gestiona empresas y contactos de abastecimiento.',
            'rutaBase' => 'admin/suppliers',
            'edicion' => $edicion,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ]);

        return $this->vista->renderizar(
            'roles.internos.administrador.crud.proveedores',
            $datosVista,
            'administrador'
        );
    }
    /**
     * Obtiene el identificador correspondiente al registro solicitado.
     */
    private function id(Solicitud $solicitud): ?int
    {
        $idProveedor = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $idProveedor === false ? null : (int) $idProveedor;
    }
}
