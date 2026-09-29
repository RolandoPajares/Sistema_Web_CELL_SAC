<?php

declare(strict_types=1);

namespace App\Controladores\Proveedores;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
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

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->renderizar();
    }

    public function editar(Solicitud $solicitud): Respuesta
    {
        $id = $this->id($solicitud);
        if ($id === null || ($registro = $this->proveedores->buscar($id)) === null) {
            $this->mensajes->error('El proveedor solicitado no existe.');
            return redirect('admin/suppliers');
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

    public function desactivar(Solicitud $solicitud): Respuesta
    {
        try {
            $id = $this->id($solicitud) ?? throw new \DomainException('El identificador no es válido.');
            $anterior = $this->proveedores->buscar($id);
            $this->proveedores->desactivar($id);
            $this->auditoria->registrar('supplier.deactivated', 'supplier', $id, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajes->exito('Proveedor desactivado correctamente.');
        } catch (Throwable $excepcion) {
            $this->registro->advertencia('No se pudo desactivar el proveedor.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \DomainException ? $excepcion->getMessage() : 'No se pudo desactivar el proveedor.');
        }
        return redirect('admin/suppliers');
    }

    private function persistir(Solicitud $solicitud, ?int $id): Respuesta
    {
        try {
            if ($solicitud->parametroRuta('id') !== null && $id === null) {
                throw new \DomainException('El identificador no es válido.');
            }
            $anterior = $id === null ? null : $this->proveedores->buscar($id);
            $datos = SolicitudProveedor::validar($solicitud);
            $guardado = $this->proveedores->guardar($datos, $id);
            $this->auditoria->registrar($id === null ? 'supplier.created' : 'supplier.updated', 'supplier', $guardado, $anterior, $datos, $solicitud->direccionIp());
            $this->mensajes->exito($id === null ? 'Proveedor registrado correctamente.' : 'Proveedor actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo guardar el proveedor.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo guardar el proveedor.');
        }
        return redirect('admin/suppliers');
    }

    private function renderizar(?array $edicion = null): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.crud.indice', [
            'tituloPagina' => 'Proveedores', 'tituloModulo' => 'Proveedores', 'tipoModulo' => 'proveedores',
            'iconoModulo' => 'bi-truck', 'botonNuevo' => 'Nuevo proveedor',
            'descripcionModulo' => 'Gestiona empresas y contactos de abastecimiento.', 'rutaBase' => 'admin/suppliers',
            'registros' => $this->proveedores->todos(), 'edicion' => $edicion,
            'resumen' => $this->proveedores->resumen(),
            'columnas' => ['nombre' => 'Proveedor', 'ruc' => 'RUC', 'telefono' => 'Teléfono', 'correo' => 'Correo', 'ciudad' => 'Ciudad', 'creado_en' => 'Registro', 'activo' => 'Estado'],
            'campos' => ['nombre' => ['label' => 'Nombre', 'required' => true, 'max' => 160],
                'ruc' => ['label' => 'RUC', 'required' => true, 'max' => 11],
                'correo' => ['label' => 'Correo', 'type' => 'email', 'required' => true, 'max' => 160],
                'telefono' => ['label' => 'Teléfono', 'max' => 20], 'ciudad' => ['label' => 'Ciudad', 'max' => 80]],
            'error' => $this->mensajes->extraer('error'), 'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    private function id(Solicitud $solicitud): ?int
    {
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : (int) $id;
    }
}
