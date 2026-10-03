<?php

declare(strict_types=1);

namespace App\Controladores\Categorias;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorCrudAdministrativo;
use App\Servicios\Auditoria\AuditoriaServicio;
use App\Servicios\Categorias\CategoriaServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Validacion\Categorias\SolicitudCategoria;
use Throwable;

final class CategoriaController
{
    public function __construct(
        private Vista $vista,
        private CategoriaServicio $categorias,
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
        $idCategoria = $this->id($solicitud);
        if ($idCategoria === null || ($categoria = $this->categorias->buscar($idCategoria)) === null) {
            $this->mensajes->error('La categoría solicitada no existe.');
            return redirigir('admin/categories');
        }

        return $this->renderizar($categoria);
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudCategoria::validar($solicitud);
            $idCategoria = $this->categorias->guardar($datos);
            $this->auditoria->registrar('category.created', 'category', $idCategoria, null, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Categoría registrada correctamente.');
        } catch (ExcepcionValidacion | \DomainException $excepcion) {
            $this->mensajes->error($this->mensaje($excepcion));
        } catch (Throwable $excepcion) {
            $this->fallo('crear', $excepcion);
        }
        return redirigir('admin/categories');
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        try {
            $idCategoria = $this->idObligatorio($solicitud);
            $anterior = $this->categorias->buscar($idCategoria);
            $datos = SolicitudCategoria::validar($solicitud);
            $this->categorias->guardar($datos, $idCategoria);
            $this->auditoria->registrar('category.updated', 'category', $idCategoria, $anterior, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Categoría actualizada correctamente.');
        } catch (ExcepcionValidacion | \DomainException $excepcion) {
            $this->mensajes->error($this->mensaje($excepcion));
        } catch (Throwable $excepcion) {
            $this->fallo('actualizar', $excepcion);
        }
        return redirigir('admin/categories');
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(Solicitud $solicitud): Respuesta
    {
        try {
            $idCategoria = $this->idObligatorio($solicitud);
            $anterior = $this->categorias->buscar($idCategoria);
            $this->categorias->desactivar($idCategoria);
            $this->auditoria->registrar('category.deactivated', 'category', $idCategoria, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajes->exito('Categoría desactivada correctamente.');
        } catch (Throwable $excepcion) {
            $this->fallo('desactivar', $excepcion, true);
        }
        return redirigir('admin/categories');
    }

    /**
     * Renderiza la vista indicada con los datos preparados por el controlador.
     */
    private function renderizar(?array $edicion = null): Respuesta
    {
        $registros = $this->categorias->todas();
        $resumen = $this->categorias->resumen();
        $presentacion = PresentadorCrudAdministrativo::presentarCategorias($registros, $resumen, $edicion);
        $presentacion['camposFormularioVista'] = PresentadorCrudAdministrativo::presentarControlesFormulario(
            $presentacion['camposFormularioVista'],
            fn (string $vista, array $datos): string => $this->vista->renderizar($vista, $datos, '')->contenido(),
        );
        $datosVista = array_merge($presentacion, [
            'tituloPagina' => 'Categorías',
            'tituloModulo' => 'Categorías',
            'tipoModulo' => 'categorias',
            'iconoModulo' => 'bi-tags',
            'botonNuevo' => 'Nueva categoría',
            'descripcionModulo' => 'Organiza y clasifica los productos del catálogo.',
            'rutaBase' => 'admin/categories',
            'edicion' => $edicion,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ]);

        return $this->vista->renderizar(
            'roles.internos.administrador.crud.categorias',
            $datosVista,
            'administrador'
        );
    }
    /**
     * Obtiene el identificador correspondiente al registro solicitado.
     */
    private function id(Solicitud $solicitud): ?int
    {
        $idCategoria = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $idCategoria === false ? null : (int) $idCategoria;
    }

    /**
     * Valida y devuelve el identificador obligatorio recibido en la petición.
     */
    private function idObligatorio(Solicitud $solicitud): int
    {
        return $this->id($solicitud) ?? throw new \DomainException('El identificador no es válido.');
    }

    /**
     * Prepara el mensaje que se muestra al finalizar la operación.
     */
    private function mensaje(Throwable $excepcion): string
    {
        if (!$excepcion instanceof ExcepcionValidacion) {
            return $excepcion->getMessage();
        }
        $errores = $excepcion->errores();
        return reset($errores) ?: 'Revisa los datos ingresados.';
    }

    /**
     * Construye una respuesta de error con el mensaje y estado correspondientes.
     */
    private function fallo(string $accion, Throwable $excepcion, bool $mostrar = false): void
    {
        $this->registro->error('No se pudo ' . $accion . ' la categoría.', ['message' => $excepcion->getMessage()]);
        $this->mensajes->error($mostrar && $excepcion instanceof \DomainException ? $excepcion->getMessage() : 'No se pudo ' . $accion . ' la categoría.');
    }
}
