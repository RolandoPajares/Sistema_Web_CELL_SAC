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

        return $this->vista->renderizar('roles.internos.administrador.productos.indice', [
            'tituloPagina' => 'Productos',
            'baseDatosDisponible' => $baseDatosDisponible,
            'productos' => $baseDatosDisponible ? $this->productos->todosParaAdministrador() : [],
            'resumen' => $baseDatosDisponible ? $this->productos->resumenAdministrativo() : ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'stock_bajo' => 0],
            'categorias' => $baseDatosDisponible ? $this->categorias->activas() : [],
            'edicion' => $productoEdicion,
            'variantesEdicion' => $productoEdicion ? $this->cargarVariantes((int)$productoEdicion['id']) : [],
            'imagenReferencia' => $productoEdicion ? $this->productos->imagenReferencia((int)$productoEdicion['id']) : null,
            'caracteristicasEdicion' => $productoEdicion ? $this->productos->detalleConVariantes((int)$productoEdicion['id'])['caracteristicas'] ?? [] : [],
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    private function cargarVariantes(int $productoId): array
    {
        $variantes=$this->productos->variantes($productoId);
        foreach($variantes as &$v){ $v['imagenes']=$this->productos->imagenesVariante((int)$v['id']); }
        return $variantes;
    }

    public function crearVariante(Solicitud $solicitud): Respuesta
    {
        $pid=(int)$solicitud->parametroRuta('id');
        try { $nombre=trim((string)$solicitud->entrada('variant_name')); if($nombre==='') throw new \DomainException('Indica el nombre del color.');
            $this->productos->crearVariante($pid,$nombre,trim((string)$solicitud->entrada('variant_hex')),(int)$solicitud->entrada('variant_stock',0));
            $this->mensajes->exito('Color añadido. Ahora puedes cargar sus imágenes.');
        } catch(Throwable $e){$this->mensajes->error($e->getMessage());}
        return redirect('admin/products/'.$pid.'/edit#variantes-producto');
    }

    public function eliminarVariante(Solicitud $solicitud): Respuesta
    {
        $pid=(int)$solicitud->parametroRuta('id');
        $vid=(int)$solicitud->parametroRuta('variant');
        try {
            $rutas=$this->productos->eliminarVariante($pid,$vid);
            foreach($rutas as $ruta){ $this->borrarArchivoUpload((string)$ruta); }
            $dir=dirname(__DIR__,3).'/public/uploads/products/'.$pid.'/'.$vid;
            if(is_dir($dir)){ @rmdir($dir); }
            $this->mensajes->exito('Color eliminado correctamente.');
        } catch(Throwable $e){ $this->mensajes->error($e->getMessage()); }
        return redirect('admin/products/'.$pid.'/edit#variantes-producto');
    }

    public function subirImagenes(Solicitud $solicitud): Respuesta
    {
        $pid=(int)$solicitud->parametroRuta('id'); $vid=(int)$solicitud->parametroRuta('variant');
        try { $files=$solicitud->archivo('images'); if(!$files || !is_array($files['name']??null)) throw new \DomainException('Selecciona una o más imágenes.');
            $dir=dirname(__DIR__,3).'/public/uploads/products/'.$pid.'/'.$vid; if(!is_dir($dir) && !mkdir($dir,0775,true) && !is_dir($dir)) throw new \RuntimeException('No se pudo crear la carpeta de imágenes.');
            $permitidos=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp']; $finfo=new \finfo(FILEINFO_MIME_TYPE); $count=0;
            foreach($files['name'] as $i=>$name){$tmp=$files['tmp_name'][$i]??'';$err=(int)($files['error'][$i]??UPLOAD_ERR_NO_FILE);$size=(int)($files['size'][$i]??0);if($err!==UPLOAD_ERR_OK)continue;if($size>5*1024*1024)throw new \DomainException('Cada imagen debe pesar máximo 5 MB.');$mime=$finfo->file($tmp);if(!isset($permitidos[$mime]))throw new \DomainException('Solo se permiten JPG, PNG o WebP.');$file=bin2hex(random_bytes(8)).'.'.$permitidos[$mime];if(!move_uploaded_file($tmp,$dir.'/'.$file))throw new \RuntimeException('No se pudo guardar una imagen.');$ruta='uploads/products/'.$pid.'/'.$vid.'/'.$file;$this->productos->agregarImagenVariante($vid,$ruta);$count++;}
            if($count===0)throw new \DomainException('No se recibió ninguna imagen válida.');$this->mensajes->exito($count.' imagen(es) añadida(s) al color.');
        } catch(Throwable $e){$this->mensajes->error($e->getMessage());}
        return redirect('admin/products/'.$pid.'/edit#variantes-producto');
    }

    public function eliminarImagen(Solicitud $solicitud): Respuesta
    {
        $pid=(int)$solicitud->parametroRuta('id'); try{$ruta=$this->productos->eliminarImagenVariante((int)$solicitud->parametroRuta('image'));if($ruta){$base=dirname(__DIR__,3).'/public/';$real=realpath($base.$ruta);$uploads=realpath($base.'uploads');if($real && $uploads && str_starts_with($real,$uploads))@unlink($real);}$this->mensajes->exito('Imagen eliminada.');}catch(Throwable $e){$this->mensajes->error('No se pudo eliminar la imagen.');}
        return redirect('admin/products/'.$pid.'/edit#variantes-producto');
    }

    public function subirImagenReferencia(Solicitud $solicitud): Respuesta
    {
        $pid=(int)$solicitud->parametroRuta('id');
        try { $f=$solicitud->archivo('reference_image'); if(!$f || (int)($f['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new \DomainException('Selecciona una imagen de referencia.');
            if((int)($f['size']??0)>5*1024*1024) throw new \DomainException('La imagen debe pesar máximo 5 MB.');
            $mime=(new \finfo(FILEINFO_MIME_TYPE))->file((string)$f['tmp_name']); $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null; if(!$ext) throw new \DomainException('Solo se permiten JPG, PNG o WebP.');
            $dir=dirname(__DIR__,3).'/public/uploads/products/'.$pid.'/reference'; if(!is_dir($dir)) mkdir($dir,0775,true); $name=bin2hex(random_bytes(8)).'.'.$ext; if(!move_uploaded_file((string)$f['tmp_name'],$dir.'/'.$name)) throw new \RuntimeException('No se pudo guardar la imagen.');
            $old=$this->productos->guardarImagenReferencia($pid,'uploads/products/'.$pid.'/reference/'.$name); $this->borrarArchivoUpload($old); $this->mensajes->exito('Imagen de referencia actualizada.');
        } catch(Throwable $e){$this->mensajes->error($e->getMessage());} return redirect('admin/products/'.$pid.'/edit#editor-producto');
    }

    public function eliminarImagenReferencia(Solicitud $solicitud): Respuesta
    {
        $pid=(int)$solicitud->parametroRuta('id'); try{$old=$this->productos->eliminarImagenReferencia($pid);$this->borrarArchivoUpload($old);$this->mensajes->exito('Imagen de referencia eliminada.');}catch(Throwable $e){$this->mensajes->error('No se pudo eliminar la imagen.');} return redirect('admin/products/'.$pid.'/edit#editor-producto');
    }

    private function borrarArchivoUpload(?string $ruta): void
    { if(!$ruta)return; $base=dirname(__DIR__,3).'/public/'; $real=realpath($base.$ruta); $uploads=realpath($base.'uploads'); if($real&&$uploads&&str_starts_with($real,$uploads)) @unlink($real); }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudProducto::validar($solicitud);
            $idGuardado = $this->productos->guardar($datos);
            $this->productos->guardarCaracteristicas($idGuardado, [
                'Memoria RAM'=>(string)$solicitud->entrada('spec_ram',''), 'Memoria Interna'=>(string)$solicitud->entrada('spec_storage',''),
                'Batería'=>(string)$solicitud->entrada('spec_battery',''), 'Procesador y generación'=>(string)$solicitud->entrada('spec_processor','')
            ]);
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
            $this->productos->guardarCaracteristicas($idGuardado, [
                'Memoria RAM'=>(string)$solicitud->entrada('spec_ram',''), 'Memoria Interna'=>(string)$solicitud->entrada('spec_storage',''),
                'Batería'=>(string)$solicitud->entrada('spec_battery',''), 'Procesador y generación'=>(string)$solicitud->entrada('spec_processor','')
            ]);
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
