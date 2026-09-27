<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Infraestructura\Registros\RegistradorArchivo;
use App\Servicios\AuditoriaServicio;
use App\Servicios\MensajeFlashServicio;
use App\Servicios\ProductoServicio;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Http\Solicitud;
use App\Http\Solicitudes\SolicitudProducto;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use Throwable;

final class AdministradorProductoController
{
    public function __construct(
        private Vista $vista,
        private ProductoServicio $productos,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro,
        private AuditoriaServicio $auditoria,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->renderizarIndice();
    }

    public function editar(Solicitud $solicitud): Respuesta
    {
        $idProducto = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idProducto === false) {
            $this->mensajes->error('El ID del producto no es válido.');

            return redirect('admin/products');
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

            return redirect('admin/products');
        }

        return $this->renderizarIndice($producto);
    }

    /** @param array<string, mixed>|null $productoEdicion */
    private function renderizarIndice(?array $productoEdicion = null): Respuesta
    {
        $baseDatosDisponible = $this->productos->conexionDisponible();

        return $this->vista->renderizar('administrador.productos.indice', [
            'tituloPagina' => 'Productos',
            'baseDatosDisponible' => $baseDatosDisponible,
            'productos' => $baseDatosDisponible ? $this->productos->todosParaAdministrador() : [],
            'edicion' => $productoEdicion,
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudProducto::validar($solicitud);
            $idGuardado = $this->productos->guardar($datos);
            $this->auditoria->registrar('product.created', 'product', $idGuardado, null, $datos, $solicitud->direccionIp());
            $this->mensajes->exito('Producto registrado correctamente.');
            $this->registro->info('Producto creado desde la administración.', [
                'producto_id' => $idGuardado,
                'usuario_id' => current_user()['id'] ?? null,
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

        return redirect('admin/products');
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        $idProducto = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        try {
            if ($idProducto === false) {
                throw new \DomainException('El ID del producto no es válido.');
            }
            $datos = SolicitudProducto::validar($solicitud);
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
                'usuario_id' => current_user()['id'] ?? null,
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

        return redirect('admin/products');
    }

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
                'usuario_id' => current_user()['id'] ?? null,
            ]);
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('Falló el retiro administrativo del producto.', [
                'message' => $excepcion->getMessage(),
            ]);
            $this->mensajes->error('No se pudo eliminar u ocultar el producto.');
        }

        return redirect('admin/products');
    }
}
