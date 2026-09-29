<?php

declare(strict_types=1);

namespace App\Controladores\Clientes;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Auditoria\AuditoriaServicio;
use App\Servicios\Clientes\ClienteServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Validacion\Clientes\SolicitudCliente;
use Throwable;

final class ClienteController
{
    public function __construct(
        private Vista $vista,
        private ClienteServicio $clientes,
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
        if ($id === null || ($registro = $this->clientes->buscar($id)) === null) {
            $this->mensajes->error('El cliente solicitado no existe.');
            return redirect('admin/customers');
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
            $anterior = $this->clientes->buscar($id);
            $this->clientes->desactivar($id);
            $this->auditoria->registrar('customer.deactivated', 'customer', $id, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajes->exito('Cliente desactivado correctamente.');
        } catch (Throwable $excepcion) {
            $this->registro->advertencia('No se pudo desactivar el cliente.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \DomainException ? $excepcion->getMessage() : 'No se pudo desactivar el cliente.');
        }
        return redirect('admin/customers');
    }

    private function persistir(Solicitud $solicitud, ?int $id): Respuesta
    {
        try {
            if ($solicitud->parametroRuta('id') !== null && $id === null) {
                throw new \DomainException('El identificador no es válido.');
            }
            $anterior = $id === null ? null : $this->clientes->buscar($id);
            $datos = SolicitudCliente::validar($solicitud);
            $guardado = $this->clientes->guardar($datos, $id);
            $this->auditoria->registrar($id === null ? 'customer.created' : 'customer.updated', 'customer', $guardado, $anterior, $datos, $solicitud->direccionIp());
            $this->mensajes->exito($id === null ? 'Cliente registrado correctamente.' : 'Cliente actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo guardar el cliente.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo guardar el cliente.');
        }
        return redirect('admin/customers');
    }

    private function renderizar(?array $edicion = null): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.crud.indice', [
            'tituloPagina' => 'Clientes', 'tituloModulo' => 'Clientes', 'tipoModulo' => 'clientes',
            'iconoModulo' => 'bi-people', 'botonNuevo' => 'Nuevo cliente',
            'descripcionModulo' => 'Gestiona clientes minoristas y mayoristas.', 'rutaBase' => 'admin/customers',
            'registros' => $this->clientes->todos(), 'edicion' => $edicion,
            'resumen' => $this->clientes->resumen(),
            'columnas' => ['contacto' => 'Cliente', 'documento' => 'Documento', 'tipo' => 'Tipo', 'telefono' => 'Teléfono', 'correo' => 'Correo', 'pedidos' => 'Pedidos', 'activo' => 'Estado'],
            'campos' => ['tipo' => ['label' => 'Tipo', 'type' => 'select', 'required' => true, 'options' => ['minorista' => 'Minorista', 'mayorista' => 'Mayorista']],
                'documento' => ['label' => 'DNI o RUC', 'required' => true, 'max' => 11], 'empresa' => ['label' => 'Empresa', 'max' => 160],
                'contacto' => ['label' => 'Contacto', 'required' => true, 'max' => 160],
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
