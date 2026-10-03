<?php

declare(strict_types=1);

namespace App\Controladores\Productos;

use App\Soporte\Registros\RegistradorArchivo;
use App\Servicios\Auditoria\AuditoriaServicio;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Servicios\Productos\ProductoServicio;
use App\Servicios\Categorias\CategoriaServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Nucleo\Http\Solicitud;
use App\Validacion\Productos\SolicitudProducto;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Administracion\PresentadorCrudAdministrativo;
use Throwable;

final class AdministradorProductoController
{
    public function __construct(
        private Vista $vista,
        private ProductoServicio $productos,
        private CategoriaServicio $categorias,
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
        return $this->renderizarIndice();
    }

    /**
     * Busca el registro solicitado y prepara su formulario de edición.
     */
    public function editar(Solicitud $solicitud): Respuesta
    {
        $idProducto = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idProducto === false) {
            $this->mensajes->error('El ID del producto no es válido.');

            return redirigir('admin/products');
        }

        try {
            $producto = $this->productos->buscarParaAdministrador((int) $idProducto);
            if ($producto === null) {
                throw new \DomainException('El producto no existe.');
            }
        } catch (Throwable $excepcion) {
            $this->registro->advertencia('Falló la consulta administrativa del producto.', [
                'message' => $excepcion->getMessage(),
            ]);
            $this->mensajes->error($excepcion instanceof \DomainException
                ? $excepcion->getMessage()
                : 'No se pudo consultar el producto.');

            return redirigir('admin/products');
        }

        return $this->renderizarIndice($producto);
    }

    /**
     * Prepara y muestra «indice» en la vista correspondiente.
     * @param array<string, mixed>|null $productoEdicion
     */
    private function renderizarIndice(?array $productoEdicion = null): Respuesta
    {
        $baseDatosDisponible = $this->productos->conexionDisponible();
        $productos = $baseDatosDisponible ? $this->productos->todosParaAdministrador() : [];
        $categorias = $baseDatosDisponible ? $this->categorias->activas() : [];
        $resumen = $baseDatosDisponible
            ? $this->productos->resumenAdministrativo()
            : ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'stock_bajo' => 0];
        $presentacion = PresentadorCrudAdministrativo::presentarProductos(
            $productos,
            $categorias,
            $resumen,
            $productoEdicion,
            SolicitudProducto::MARCAS_PERMITIDAS
        );

        return $this->vista->renderizar('roles.internos.administrador.productos.indice', [
            'tituloPagina' => 'Productos',
            'baseDatosDisponible' => $baseDatosDisponible,
            'atributoBaseDatosNoDisponibleOculto' => $baseDatosDisponible ? 'hidden' : '',
            'atributoContenidoProductosOculto' => $baseDatosDisponible ? '' : 'hidden',
            'atributoProductosVaciosOculto' => $presentacion['productos'] === [] ? '' : 'hidden',
            'productos' => $presentacion['productos'],
            'resumen' => $resumen,
            'categorias' => $categorias,
            'categoriasFiltro' => $presentacion['categoriasFiltro'],
            'tarjetasKpi' => $presentacion['tarjetasKpi'],
            'marcasPermitidas' => SolicitudProducto::MARCAS_PERMITIDAS,
            'marcasPermitidasVista' => $presentacion['marcasPermitidasVista'],
            'edicion' => $productoEdicion,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    // validación de datos y persistencia de cambios en la base de datos
    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudProducto::validar($solicitud);
            $idGuardado = $this->productos->guardar($datos);

            // Registrar la acción de creación en el servicio de auditoría
            $this->auditoria->registrar('product.created', 'product', $idGuardado, null, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Producto registrado correctamente.');
            $this->registro->info('Producto creado desde la administración.', [
                'producto_id' => $idGuardado,
                'usuario_id' => usuario_actual()['id'] ?? null,
            ]);
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Datos inválidos.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('Falló la creación administrativa del producto.', [
                'message' => $excepcion->getMessage(),
            ]);
            $this->mensajes->error('No se pudo registrar el producto.');
        }

        return redirigir('admin/products'); // Redirige a la página de listado de productos después de guardar
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        $idProducto = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        try {
            if ($idProducto === false) {
                throw new \DomainException('El ID del producto no es válido.');
            }
            $datos = SolicitudProducto::validar($solicitud, false);
            $valoresAnteriores = $this->productos->buscarParaAdministrador((int) $idProducto);
            $idGuardado = $this->productos->guardar($datos, (int) $idProducto);
            $this->auditoria->registrar(
                'product.updated',
                'product',
                $idGuardado,
                $valoresAnteriores,
                $datos,
                $solicitud->direccionIp()
            );
            $this->mensajes->exito('Producto actualizado correctamente.');
            $this->registro->info('Producto actualizado desde la administración.', [
                'producto_id' => $idGuardado,
                'usuario_id' => usuario_actual()['id'] ?? null,
            ]);
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Datos inválidos.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('Falló la actualización administrativa del producto.', [
                'message' => $excepcion->getMessage(),
            ]);
            $this->mensajes->error('No se pudo actualizar el producto.');
        }

        return redirigir('admin/products');
    }

    /**
     * Elimina el producto si no tiene pedidos asociados; si los tiene, lo desactiva.
     */
    public function eliminar(Solicitud $solicitud): Respuesta
    {
        try {
            $idProducto = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idProducto === false) {
                throw new \DomainException('El ID del producto no es válido.');
            }
            $valoresAnteriores = $this->productos->buscarParaAdministrador($idProducto);
            $resultado = $this->productos->eliminarODesactivar($idProducto);
            $eliminado = $resultado === 'deleted';
            $this->auditoria->registrar(
                $eliminado ? 'product.deleted' : 'product.deactivated',
                'product',
                $idProducto,
                $valoresAnteriores,
                $eliminado ? null : ['activo' => 0],
                $solicitud->direccionIp()
            );
            $this->mensajes->exito($eliminado
                ? 'Producto eliminado correctamente.'
                : 'Producto ocultado porque tiene pedidos relacionados.');
            $this->registro->info('Retiro administrativo del producto completado.', [
                'producto_id' => $idProducto,
                'result' => $resultado,
                'usuario_id' => usuario_actual()['id'] ?? null,
            ]);
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('Falló el retiro administrativo del producto.', [
                'message' => $excepcion->getMessage(),
            ]);
            $this->mensajes->error('No se pudo eliminar u ocultar el producto.');
        }

        return redirigir('admin/products');
    }
}
