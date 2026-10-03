<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Productos;

use App\Modelos\Productos\Producto;

final class PresentadorDetalleProducto
{
    /**
     * Prepara los datos visuales de la ficha de un producto.
     *
     * @param array<string, mixed> $producto
     * @return array<string, mixed>
     */
    public function presentar(array $producto): array
    {
        $marca = trim((string) ($producto['marca'] ?? ''));
        $nombre = trim((string) ($producto['nombre'] ?? ''));
        $categoria = trim((string) ($producto['categoria'] ?? ''));
        $precioOferta = Producto::desdeRegistro($producto)->precioEfectivo();
        $precioOriginal = is_numeric($producto['precio_original'] ?? null)
            ? (float) $producto['precio_original']
            : null;
        $tieneDescuento = $precioOriginal !== null && $precioOriginal > $precioOferta;
        $descuento = trim((string) ($producto['descuento'] ?? ''));
        if ($tieneDescuento && $descuento === '' && $precioOriginal > 0) {
            $descuento = '-' . (string) round((1 - ($precioOferta / $precioOriginal)) * 100) . '%';
        }

        $stock = max(0, (int) ($producto['existencias'] ?? 0));
        $imagenes = $this->imagenes($producto['imagenes'] ?? []);
        $imagenesVista = $this->presentarImagenes($imagenes);
        $atributos = $this->atributos($producto);
        $caracteristicas = $this->caracteristicas($producto, $nombre, $categoria);
        $categoriaNormalizada = mb_strtolower($categoria, 'UTF-8');
        $iconoImagen = str_contains($categoriaNormalizada, 'audio')
            ? 'bi-headphones'
            : (str_contains($categoriaNormalizada, 'celular') ? 'bi-phone' : 'bi-box-seam');

        return [
            'id' => (int) ($producto['id'] ?? 0),
            'marca' => $marca,
            'nombre' => $nombre,
            'categoria' => $categoria,
            'imagenes' => $imagenes,
            'imagenes_vista' => $imagenesVista,
            'imagen_principal' => $imagenes[0] ?? '',
            'atributoMiniaturasOculto' => $imagenes !== [] ? '' : 'hidden',
            'atributoImagenOculta' => $imagenes !== [] ? '' : 'hidden',
            'atributoPlaceholderOculto' => $imagenes === [] ? '' : 'hidden',
            'texto_alternativo' => trim($marca . ' ' . $nombre),
            'visual_marca' => representacion_visual_marca($marca),
            'icono_imagen' => $iconoImagen,
            'tipo_imagen' => str_contains($categoriaNormalizada, 'audio')
                ? 'audio'
                : (str_contains($categoriaNormalizada, 'celular') ? 'celular' : 'accesorio'),
            'precio_oferta' => $precioOferta,
            'precio_original' => $precioOriginal,
            'tiene_descuento' => $tieneDescuento,
            'descuento' => $descuento,
            'etiqueta' => trim((string) ($producto['etiqueta'] ?? '')),
            'atributoCategoriaOculto' => $categoria !== '' ? '' : 'hidden',
            'atributoMarcaOculto' => $marca !== '' ? '' : 'hidden',
            'atributoEtiquetaOculto' => trim((string) ($producto['etiqueta'] ?? '')) !== '' ? '' : 'hidden',
            'atributoPrecioAnteriorOculto' => $tieneDescuento ? '' : 'hidden',
            'atributoDescuentoOculto' => $tieneDescuento && $descuento !== '' ? '' : 'hidden',
            'atributoTextoMarcaVisualOculto' => $iconoImagen === 'bi-phone' ? '' : 'hidden',
            'atributoIconoVisualOculto' => $iconoImagen !== 'bi-phone' ? '' : 'hidden',
            'descripcion' => trim((string) ($producto['descripcion'] ?? '')),
            'atributoDescripcionOculto' => trim((string) ($producto['descripcion'] ?? '')) !== '' ? '' : 'hidden',
            'stock' => $stock,
            'disponible' => $stock > 0,
            'clase_stock' => $stock > 0 ? 'is-available' : 'is-unavailable',
            'icono_stock' => $stock > 0 ? 'bi-check-circle-fill' : 'bi-x-circle-fill',
            'texto_stock' => $stock > 0
                ? number_format($stock, 0, ',', '.') . ' unidades disponibles'
                : 'Agotado',
            'caracteristicas' => $caracteristicas,
            'enlace_categoria' => url_interna('catalog?' . http_build_query([
                'cat' => $categoria,
            ])),
            'atributos' => $atributos,
            'atributoAtributosOculto' => $atributos !== [] ? '' : 'hidden',
            'atributoCaracteristicasOculto' => $caracteristicas !== [] ? '' : 'hidden',
        ];
    }

    /**
     * Prepara los atributos mostrados en el resumen de la ficha.
     *
     * @param array<string, mixed> $producto
     * @return array<int, array{0:string, 1:string}>
     */
    private function atributos(array $producto): array
    {
        $atributos = [];
        foreach ([
            'almacenamiento' => 'Almacenamiento',
            'color' => 'Color',
        ] as $campo => $etiqueta) {
            $valor = trim((string) ($producto[$campo] ?? ''));
            if ($valor !== '') {
                $atributos[] = [$etiqueta, $valor];
            }
        }

        return $atributos;
    }

    /**
     * Devuelve únicamente las imágenes con una URL pública válida.
     *
     * @param mixed $imagenes
     * @return array<int, string>
     */
    private function imagenes(mixed $imagenes): array
    {
        if (!is_array($imagenes)) {
            return [];
        }

        $rutas = [];
        foreach ($imagenes as $imagen) {
            if (is_array($imagen)) {
                $imagen = $imagen['ruta_imagen'] ?? $imagen['url'] ?? '';
            }

            if (!is_scalar($imagen)) {
                continue;
            }

            $urlImagen = url_imagen_producto((string) $imagen);
            if ($urlImagen !== '') {
                $rutas[] = $urlImagen;
            }
        }

        return array_values(array_unique($rutas));
    }

    /**
     * Prepara el orden, las clases y las etiquetas de las galerías del producto.
     *
     * @param array<int, string> $imagenes
     * @return array<int, array{url:string,clase_detalle:string,clase_modal:string,etiqueta_detalle:string,etiqueta_modal:string,atributo_actual:string}>
     */
    private function presentarImagenes(array $imagenes): array
    {
        $imagenesVista = [];
        foreach ($imagenes as $indice => $url) {
            $esPrincipal = $indice === 0;
            $numero = $indice + 1;
            $imagenesVista[] = [
                'url' => $url,
                'clase_detalle' => $esPrincipal ? 'active' : '',
                'clase_modal' => $esPrincipal ? 'is-active' : '',
                'etiqueta_detalle' => (string) $numero,
                'etiqueta_modal' => $esPrincipal ? 'principal' : (string) $numero,
                'atributo_actual' => $esPrincipal ? 'aria-current="true"' : '',
            ];
        }

        return $imagenesVista;
    }

    /**
     * Une las características principales con las adicionales sin repetir etiquetas.
     *
     * @param array<string, mixed> $producto
     * @return array<int, array{etiqueta:string, valor:string}>
     */
    private function caracteristicas(array $producto, string $nombre, string $categoria): array
    {
        $caracteristicas = [
            ['etiqueta' => 'Modelo', 'valor' => $nombre],
            ['etiqueta' => 'Categoría', 'valor' => $categoria],
            ['etiqueta' => 'Color', 'valor' => trim((string) ($producto['color'] ?? ''))],
            ['etiqueta' => 'Almacenamiento', 'valor' => trim((string) ($producto['almacenamiento'] ?? ''))],
            ['etiqueta' => 'Etiqueta', 'valor' => trim((string) ($producto['etiqueta'] ?? ''))],
        ];
        $etiquetasIncluidas = [];
        foreach ($caracteristicas as $caracteristica) {
            $etiquetasIncluidas[mb_strtolower($caracteristica['etiqueta'], 'UTF-8')] = true;
        }

        $caracteristicasAdicionales = $producto['caracteristicas'] ?? [];
        if (!is_array($caracteristicasAdicionales)) {
            return $caracteristicas;
        }

        foreach ($caracteristicasAdicionales as $caracteristica) {
            if (!is_array($caracteristica)) {
                continue;
            }

            $etiqueta = trim((string) ($caracteristica['nombre'] ?? ''));
            $valor = trim((string) ($caracteristica['valor'] ?? ''));
            $etiquetaNormalizada = mb_strtolower($etiqueta, 'UTF-8');
            if ($etiqueta !== '' && $valor !== '' && !isset($etiquetasIncluidas[$etiquetaNormalizada])) {
                $caracteristicas[] = ['etiqueta' => $etiqueta, 'valor' => $valor];
                $etiquetasIncluidas[$etiquetaNormalizada] = true;
            }
        }

        return array_values(array_filter(
            $caracteristicas,
            static fn (array $caracteristica): bool => trim($caracteristica['valor']) !== ''
        ));
    }
}
