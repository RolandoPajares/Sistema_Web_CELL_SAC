<?php

declare(strict_types=1);

namespace App\Controladores\Productos;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Productos\ProductoServicio;
use App\Servicios\Productos\RecomendacionServicio;

final class ProductoController
{
    public function __construct(private Vista $vista, private ProductoServicio $productos, private RecomendacionServicio $recomendaciones)
    {
    }

    public function detalle(Solicitud $solicitud): Respuesta
    {
        $idProducto = (int) ($solicitud->parametroRuta('id') ?? $solicitud->consulta('id', 0));
        $producto = $this->productos->detalleConVariantes($idProducto);

        if (!$producto) {
            return $this->vista->renderizar('errores.404', ['tituloPagina' => 'Producto no encontrado'], 'aplicacion', 404);
        }

        return $this->vista->renderizar('publico.catalogo.detalle', [
            'tituloPagina' => (string) $producto['nombre'],
            'producto' => $producto,
            'complementos' => $this->conImagenes($this->recomendaciones->complementos($idProducto)),
            'similares' => $this->conImagenes($this->recomendaciones->similares($idProducto)),
        ]);
    }

    private function conImagenes(array $items): array
    {
        foreach ($items as &$item) {
            $detalle = $this->productos->detalleConVariantes((int)$item['id']);
            $item['imagen_referencia'] = $detalle['imagen_referencia'] ?? '';
            $item['imagen'] = '';
            $item['caracteristicas'] = $detalle['caracteristicas'] ?? [];
            foreach (($detalle['variantes'] ?? []) as $v) {
                if (!empty($v['imagenes'][0]['ruta_imagen'])) { $item['imagen'] = $v['imagenes'][0]['ruta_imagen']; break; }
            }
        }
        return $items;
    }
}
