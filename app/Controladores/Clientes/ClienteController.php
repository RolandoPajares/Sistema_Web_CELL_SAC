<?php

declare(strict_types=1);

namespace App\Controladores\Clientes;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorCrudAdministrativo;
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
        $idCliente = $this->id($solicitud);
        if ($idCliente === null || ($registro = $this->clientes->buscar($idCliente)) === null) {
            $this->mensajes->error('El cliente solicitado no existe.');
            return redirigir('admin/customers');
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
            $idCliente = $this->id($solicitud) ?? throw new \DomainException('El identificador no es válido.');
            $anterior = $this->clientes->buscar($idCliente);
            $this->clientes->desactivar($idCliente);
            $this->auditoria->registrar('customer.deactivated', 'customer', $idCliente, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajes->exito('Cliente desactivado correctamente.');
        } catch (Throwable $excepcion) {
            $this->registro->advertencia('No se pudo desactivar el cliente.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \DomainException ? $excepcion->getMessage() : 'No se pudo desactivar el cliente.');
        }
        return redirigir('admin/customers');
    }

    private function persistir(Solicitud $solicitud, ?int $idCliente): Respuesta
    {
        try {
            if ($solicitud->parametroRuta('id') !== null && $idCliente === null) {
                throw new \DomainException('El identificador no es válido.');
            }
            $anterior = $idCliente === null ? null : $this->clientes->buscar($idCliente);
            $datos = SolicitudCliente::validar($solicitud);
            $guardado = $this->clientes->guardar($datos, $idCliente);
            $this->auditoria->registrar($idCliente === null ? 'customer.created' : 'customer.updated', 'customer', $guardado, $anterior, $datos, $solicitud->direccionIp());
            $this->mensajes->exito($idCliente === null ? 'Cliente registrado correctamente.' : 'Cliente actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo guardar el cliente.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo guardar el cliente.');
        }
        return redirigir('admin/customers');
    }

    /**
     * Renderiza la vista indicada con los datos preparados por el controlador.
     */
    private function renderizar(?array $edicion = null): Respuesta
    {
        $registros = $this->clientes->todos();
        $resumen = $this->clientes->resumen();
        $presentacion = PresentadorCrudAdministrativo::presentarClientes($registros, $resumen, $edicion);
        $presentacion['camposFormularioVista'] = PresentadorCrudAdministrativo::presentarControlesFormulario(
            $presentacion['camposFormularioVista'],
            fn (string $vista, array $datos): string => $this->vista->renderizar($vista, $datos, '')->contenido(),
        );
        $datosVista = array_merge($presentacion, [
            'tituloPagina' => 'Clientes',
            'tituloModulo' => 'Clientes',
            'tipoModulo' => 'clientes',
            'iconoModulo' => 'bi-people',
            'botonNuevo' => 'Nuevo cliente',
            'descripcionModulo' => 'Gestiona clientes minoristas y mayoristas.',
            'rutaBase' => 'admin/customers',
            'edicion' => $edicion,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ]);

        return $this->vista->renderizar(
            'roles.internos.administrador.crud.clientes',
            $datosVista,
            'administrador'
        );
    }
    /**
     * Obtiene el identificador correspondiente al registro solicitado.
     */
    private function id(Solicitud $solicitud): ?int
    {
        $idCliente = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $idCliente === false ? null : (int) $idCliente;
    }
}
