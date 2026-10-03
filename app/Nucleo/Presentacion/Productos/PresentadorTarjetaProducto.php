<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Productos;

use App\Modelos\Productos\Producto;

/**
 * Presentador para transformar datos crudos de productos en arreglos estructurados
 * listos para ser renderizados en las tarjetas de la interfaz de usuario.
 */
final class PresentadorTarjetaProducto
{
    /**
     * Prepara los datos visuales necesarios para mostrar una tarjeta de producto.
     *
     * @param array<string, mixed> $producto Datos originales del producto.
     * @param int $anchoResumen Longitud máxima para el resumen de la descripción.
     * @return array<string, mixed> Arreglo con los atributos listos para la vista.
     */
    public function presentar(array $producto, int $anchoResumen = 30): array
    {
        // 1. Limpieza y normalización de datos básicos de texto
        $marca = trim((string) ($producto['marca'] ?? ''));
        $nombre = trim((string) ($producto['nombre'] ?? ''));
        $categoria = trim((string) ($producto['categoria'] ?? ''));
        $almacenamiento = trim((string) ($producto['almacenamiento'] ?? ''));
        
        // Limpia etiquetas HTML y espacios múltiples de la descripción
        $descripcion = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($producto['descripcion'] ?? ''))) ?? '');
        $descripcionResumen = mb_strimwidth($descripcion, 0, max(0, $anchoResumen), '…', 'UTF-8');

        // 2. Procesamiento de precios, stock e imágenes
        $precioOferta = Producto::desdeRegistro($producto)->precioEfectivo();
        $precioOriginal = (float) ($producto['precio_original'] ?? 0);
        $descuento = trim((string) ($producto['descuento'] ?? ''));
        $existencias = (int) ($producto['existencias'] ?? 0);
        
        $imagenes = $this->imagenes($producto);
        $imagenPrincipal = $imagenes[0] ?? '';

        // 3. Indicadores de estado y categorización lógica
        $esAudio = stripos($categoria, 'audio') !== false;
        $esCelular = stripos($categoria, 'celular') !== false;
        $tieneImagen = $imagenPrincipal !== '';
        $mostrarDescuento = $precioOriginal > $precioOferta && $descuento !== '';
        $disponible = $existencias > 0;
        $idProducto = (int) ($producto['id'] ?? 0);

        // 4. Construcción del arreglo de respuesta estructurado para la vista
        return [
            'id' => $idProducto,
            'marca' => $marca,
            'nombre' => $nombre,
            'categoria' => $categoria,
            'almacenamiento' => $almacenamiento,
            'descripcion_resumen' => $descripcionResumen,
            'especificaciones' => $almacenamiento
                . ($almacenamiento !== '' && $descripcionResumen !== '' ? ' · ' : '')
                . $descripcionResumen,
            
            // Atributos de imagen y control visual condicional
            'imagen_principal' => $imagenPrincipal,
            'atributoImagenOculta' => $tieneImagen ? '' : 'hidden',
            'atributoPlaceholderOculto' => $tieneImagen ? 'hidden' : '',
            'atributoAudioOculto' => !$tieneImagen && $esAudio ? '' : 'hidden',
            'atributoCelularOculto' => !$tieneImagen && $esCelular ? '' : 'hidden',
            'atributoAccesorioOculto' => !$tieneImagen && !$esAudio && !$esCelular ? '' : 'hidden',
            'texto_alternativo' => trim($marca . ' ' . $nombre),
            'tipo_imagen' => $esAudio ? 'audio' : ($esCelular ? 'celular' : 'accesorio'),
            'icono_imagen' => $esAudio ? 'bi-headphones' : ($esCelular ? 'bi-phone' : 'bi-box-seam'),
            'visual_marca' => representacion_visual_marca($marca),
            
            // Manejo de etiquetas promocionales
            'etiqueta' => trim((string) ($producto['etiqueta'] ?? '')),
            'etiqueta_inicio' => trim((string) ($producto['etiqueta'] ?? '')) !== ''
                ? trim((string) $producto['etiqueta'])
                : 'Oferta',
            'atributoEtiquetaOculta' => trim((string) ($producto['etiqueta'] ?? '')) !== '' ? '' : 'hidden',
            
            // Datos comerciales (precios y descuentos)
            'precio_oferta' => $precioOferta,
            'precio_original' => $precioOriginal,
            'descuento' => $descuento,
            'mostrar_descuento' => $mostrarDescuento,
            'atributoDescuentoOculto' => $mostrarDescuento ? '' : 'hidden',
            
            // Control de stock e interactividad de compra
            'disponible' => $disponible,
            'atributoAgotadoOculto' => $disponible ? 'hidden' : '',
            'atributoCompraDesactivada' => $disponible ? '' : 'disabled',
            'textoBotonCompra' => $disponible ? 'Comprar' : 'Agotado',
            'texto_stock' => $existencias > 0
                ? number_format($existencias, 0, ',', '.') . ' unidades disponibles'
                : 'Agotado',
            
            // Enlaces de navegación internos
            'enlace_detalle' => url_interna('catalog?' . http_build_query([
                'producto' => $idProducto,
                'cat' => $categoria,
            ])),
        ];
    }

    /**
     * Prepara una colección de tarjetas respetando el orden recibido.
     *
     * @param array<int, mixed> $productos Lista de productos crudos.
     * @param int $anchoResumen Longitud máxima para el resumen de descripciones.
     * @return array<int, array<string, mixed>> Colección de tarjetas presentadas.
     */
    public function presentarColeccion(array $productos, int $anchoResumen = 30): array
    {
        $tarjetas = [];

        foreach ($productos as $producto) {
            if (is_array($producto)) {
                $tarjetas[] = $this->presentar($producto, $anchoResumen);
            }
        }

        return $tarjetas;
    }

    /**
     * Normaliza las rutas y resuelve una URL pública válida para cada imagen del producto.
     *
     * @param array<string, mixed> $producto Datos del producto.
     * @return array<int, string> Lista de URLs de imágenes válidas.
     */
    private function imagenes(array $producto): array
    {
        $imagenes = $producto['imagenes'] ?? [];
        if (!is_array($imagenes)) {
            $imagenes = [];
        }

        // Soporte retrocompatible para claves alternativas de imagen única
        $imagenUnica = trim((string) ($producto['imagen_referencia'] ?? $producto['url_imagen'] ?? $producto['imagen'] ?? ''));
        if ($imagenUnica !== '' && $imagenes === []) {
            $imagenes[] = $imagenUnica;
        }

        // Mapea y filtra cada elemento para asegurar que sea una ruta escalar válida
        $imagenes = array_values(array_filter(array_map(
            static function (mixed $imagen): string {
                if (is_array($imagen)) {
                    $imagen = $imagen['ruta_imagen'] ?? $imagen['url'] ?? '';
                }

                return is_scalar($imagen) ? trim((string) $imagen) : '';
            },
            $imagenes
        ), static fn (string $imagen): bool => $imagen !== ''));

        // Aplica la función auxiliar global para resolver las URLs públicas finales
        return array_values(array_filter(array_map('url_imagen_producto', $imagenes)));
    }
}