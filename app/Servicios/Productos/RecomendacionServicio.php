<?php
declare(strict_types=1);
namespace App\Servicios\Productos;

use App\DAO\Productos\ProductoDAO;

final class RecomendacionServicio
{
    public function __construct(private ProductoDAO $productos) {}

    public function complementos(int $idProducto): array
    {
        $actual = $this->productos->buscarActivo($idProducto);
        if (!$actual) return [];

        $todos = $this->productos->todosActivos();
        $resultado = [];
        foreach ($todos as $producto) {
            if ((int)$producto['id'] === $idProducto) continue;
            if (($producto['categoria'] ?? '') === ($actual['categoria'] ?? '')) {
                $resultado[] = $producto;
            }
        }
        return array_slice($resultado,0,4);
    }

    public function similares(int $idProducto): array
    {
        return $this->complementos($idProducto);
    }
}
