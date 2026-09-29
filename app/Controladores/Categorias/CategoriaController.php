<?php

declare(strict_types=1);

namespace App\Controladores\Categorias;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
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

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->renderizar();
    }

    public function editar(Solicitud $solicitud): Respuesta
    {
        $id = $this->id($solicitud);
        if ($id === null || ($categoria = $this->categorias->buscar($id)) === null) {
            $this->mensajes->error('La categoría solicitada no existe.');
            return redirect('admin/categories');
        }

        return $this->renderizar($categoria);
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudCategoria::validar($solicitud);
            $id = $this->categorias->guardar($datos);
            $this->auditoria->registrar('category.created', 'category', $id, null, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Categoría registrada correctamente.');
        } catch (ExcepcionValidacion | \DomainException $excepcion) {
            $this->mensajes->error($this->mensaje($excepcion));
        } catch (Throwable $excepcion) {
            $this->fallo('crear', $excepcion);
        }
        return redirect('admin/categories');
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        try {
            $id = $this->idObligatorio($solicitud);
            $anterior = $this->categorias->buscar($id);
            $datos = SolicitudCategoria::validar($solicitud);
            $this->categorias->guardar($datos, $id);
            $this->auditoria->registrar('category.updated', 'category', $id, $anterior, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Categoría actualizada correctamente.');
        } catch (ExcepcionValidacion | \DomainException $excepcion) {
            $this->mensajes->error($this->mensaje($excepcion));
        } catch (Throwable $excepcion) {
            $this->fallo('actualizar', $excepcion);
        }
        return redirect('admin/categories');
    }

    public function desactivar(Solicitud $solicitud): Respuesta
    {
        try {
            $id = $this->idObligatorio($solicitud);
            $anterior = $this->categorias->buscar($id);
            $this->categorias->desactivar($id);
            $this->auditoria->registrar('category.deactivated', 'category', $id, $anterior, ['activo' => 0], $solicitud->direccionIp());
            $this->mensajes->exito('Categoría desactivada correctamente.');
        } catch (Throwable $excepcion) {
            $this->fallo('desactivar', $excepcion, true);
        }
        return redirect('admin/categories');
    }

    private function renderizar(?array $edicion = null): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.crud.indice', [
            'tituloPagina' => 'Categorías', 'tituloModulo' => 'Categorías', 'tipoModulo' => 'categorias',
            'iconoModulo' => 'bi-tags', 'botonNuevo' => 'Nueva categoría',
            'descripcionModulo' => 'Organiza y clasifica los productos del catálogo.',
            'rutaBase' => 'admin/categories', 'registros' => $this->categorias->todas(), 'edicion' => $edicion,
            'resumen' => $this->categorias->resumen(),
            'columnas' => ['nombre' => 'Categoría', 'descripcion' => 'Descripción', 'productos_asociados' => 'Productos asociados', 'creado_en' => 'Fecha de creación', 'activo' => 'Estado'],
            'campos' => ['nombre' => ['label' => 'Nombre', 'required' => true, 'max' => 120],
                'descripcion' => ['label' => 'Descripción', 'type' => 'textarea', 'max' => 500]],
            'error' => $this->mensajes->extraer('error'), 'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    private function id(Solicitud $solicitud): ?int
    {
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : (int) $id;
    }

    private function idObligatorio(Solicitud $solicitud): int
    {
        return $this->id($solicitud) ?? throw new \DomainException('El identificador no es válido.');
    }

    private function mensaje(Throwable $excepcion): string
    {
        if (!$excepcion instanceof ExcepcionValidacion) {
            return $excepcion->getMessage();
        }
        $errores = $excepcion->errores();
        return reset($errores) ?: 'Revisa los datos ingresados.';
    }

    private function fallo(string $accion, Throwable $excepcion, bool $mostrar = false): void
    {
        $this->registro->error('No se pudo ' . $accion . ' la categoría.', ['message' => $excepcion->getMessage()]);
        $this->mensajes->error($mostrar && $excepcion instanceof \DomainException ? $excepcion->getMessage() : 'No se pudo ' . $accion . ' la categoría.');
    }
}
