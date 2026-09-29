<?php

declare(strict_types=1);

namespace App\DAO\Productos;

use App\DTO\Productos\FiltroProducto;
use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use PDO;

final class ProductoDAO implements RepositorioProductoInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function estaDisponible(): bool
    {
        return $this->conexion->pdo() !== null;
    }

    public function todosActivos(): array
    {
        $sentencia = $this->pdo()->query('SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC');

        return $sentencia->fetchAll();
    }

    public function paginar(FiltroProducto $filtro): array
    {
        $condiciones = ['activo = 1'];
        $parametros = [];

        if ($filtro->busqueda !== '') {
            $condiciones[] = '(nombre LIKE :buscar_nombre OR marca LIKE :buscar_marca)';
            $parametros['buscar_nombre'] = '%' . $filtro->busqueda . '%';
            $parametros['buscar_marca'] = '%' . $filtro->busqueda . '%';
        }
        if ($filtro->marca !== '') {
            $condiciones[] = 'marca = :marca';
            $parametros['marca'] = $filtro->marca;
        }
        if ($filtro->categoria !== '') {
            $placeholdersCategoria = [];
            foreach (self::categoriasCompatibles($filtro->categoria) as $indice => $categoriaCompatible) {
                $nombreParametro = 'categoria_' . $indice;
                $placeholdersCategoria[] = ':' . $nombreParametro;
                $parametros[$nombreParametro] = $categoriaCompatible;
            }
            $condiciones[] = 'LOWER(TRIM(categoria)) IN (' . implode(', ', $placeholdersCategoria) . ')';
        }
        if ($filtro->precioMinimo !== null) {
            $condiciones[] = 'precio >= :precio_minimo';
            $parametros['precio_minimo'] = $filtro->precioMinimo;
        }
        if ($filtro->precioMaximo !== null) {
            $condiciones[] = 'precio <= :precio_maximo';
            $parametros['precio_maximo'] = $filtro->precioMaximo;
        }

        $clausulaWhere = implode(' AND ', $condiciones);
        $conteo = $this->pdo()->prepare('SELECT COUNT(*) FROM productos WHERE ' . $clausulaWhere);
        $conteo->execute($parametros);
        $total = (int) $conteo->fetchColumn();
        $pagina = max(1, $filtro->pagina);
        $porPagina = min(48, max(1, $filtro->porPagina));
        $desplazamiento = ($pagina - 1) * $porPagina;
        $orden = match ($filtro->orden) {
            'price_asc' => 'precio ASC, id DESC',
            'price_desc' => 'precio DESC, id DESC',
            'name' => 'nombre ASC, id DESC',
            default => 'id DESC',
        };
        $sentencia = $this->pdo()->prepare(
            'SELECT id, marca, nombre, categoria, precio, precio_original, descuento, precio_oferta,
                    url_imagen, existencias, etiqueta, almacenamiento, color, descripcion
             FROM productos WHERE ' . $clausulaWhere . ' ORDER BY ' . $orden . ' LIMIT :limit OFFSET :offset'
        );
        foreach ($parametros as $nombre => $valor) {
            $sentencia->bindValue(':' . $nombre, $valor);
        }
        $sentencia->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $sentencia->bindValue(':offset', $desplazamiento, PDO::PARAM_INT);
        $sentencia->execute();

        return [
            'productos' => $sentencia->fetchAll(),
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'ultima_pagina' => max(1, (int) ceil($total / $porPagina)),
        ];
    }

    public function todosParaAdministrador(): array
    {
        $sentencia = $this->pdo()->query('SELECT * FROM productos ORDER BY activo DESC, id DESC');

        return $sentencia->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscarActivo(int $idProducto): ?array
    {
        $sentencia = $this->pdo()->prepare('SELECT * FROM productos WHERE id = :id AND activo = 1 LIMIT 1');
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
        $producto = $sentencia->fetch();

        return $producto ?: null;
    }

    /** @return array<string, mixed>|null */
    public function buscarParaAdministrador(int $idProducto): ?array
    {
        $sentencia = $this->pdo()->prepare('SELECT * FROM productos WHERE id = :id LIMIT 1');
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
        $producto = $sentencia->fetch();

        return $producto ?: null;
    }

    /** @return array<string, mixed>|null */
    public function buscarActivoParaActualizar(int $idProducto): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT * FROM productos WHERE id = :id AND activo = 1 LIMIT 1 FOR UPDATE'
        );
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
        $producto = $sentencia->fetch();

        return $producto ?: null;
    }

    public function existeConMarcaYNombre(string $marca, string $nombre, ?int $idExcluido = null): bool
    {
        $consultaSql = 'SELECT COUNT(*) FROM productos WHERE marca = :marca AND nombre = :nombre';
        if ($idExcluido !== null) {
            $consultaSql .= ' AND id <> :id_excluido';
        }

        $sentencia = $this->pdo()->prepare($consultaSql);
        $sentencia->bindValue(':marca', $marca, PDO::PARAM_STR);
        $sentencia->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        if ($idExcluido !== null) {
            $sentencia->bindValue(':id_excluido', $idExcluido, PDO::PARAM_INT);
        }
        $sentencia->execute();

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO productos(marca, nombre, categoria_id, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo)
             VALUES(:marca, :nombre, :categoria_id, :categoria, :precio, 0, :almacenamiento, :color, :etiqueta, :descripcion, 1)'
        );
        $this->vincularProducto($sentencia, $datos);
        $sentencia->execute();

        return (int) $this->pdo()->lastInsertId();
    }

    public function actualizar(int $idProducto, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE productos
             SET marca = :marca, nombre = :nombre, categoria_id = :categoria_id, categoria = :categoria, precio = :precio,
                 almacenamiento = :almacenamiento, color = :color, etiqueta = :etiqueta, descripcion = :descripcion
             WHERE id = :id'
        );
        $this->vincularProducto($sentencia, $datos);
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
    }

    /**
     * Elimina físicamente solo productos sin pedidos asociados.
     * Si existe una relación histórica, el servicio conserva el registro mediante baja lógica.
     */
    public function delete(int $idProducto): bool
    {
        $sentencia = $this->pdo()->prepare(
            'DELETE FROM productos
             WHERE id = :id
               AND NOT EXISTS (
                   SELECT 1 FROM detalle_pedidos WHERE producto_id = :id_producto_relacionado
               )'
        );
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->bindValue(':id_producto_relacionado', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->rowCount() === 1;
    }

    public function desactivar(int $idProducto): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE productos SET activo = 0 WHERE id = :id AND activo = 1');
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();

        if ($sentencia->rowCount() !== 1) {
            throw new \DomainException('El producto no existe o ya fue desactivado.');
        }
    }

    public function reducirStock(int $idProducto, int $cantidad): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE productos SET existencias = existencias - ? WHERE id = ? AND existencias >= ?');
        $sentencia->execute([$cantidad, $idProducto, $cantidad]);

        if ($sentencia->rowCount() !== 1) {
            throw new \RuntimeException('No fue posible actualizar el stock.');
        }
    }

    public function contarActivos(): int
    {
        return (int) $this->pdo()->query('SELECT COUNT(*) FROM productos WHERE activo = 1')->fetchColumn();
    }

    public function stockTotal(): int
    {
        return (int) $this->pdo()->query('SELECT COALESCE(SUM(existencias), 0) FROM productos WHERE activo = 1')->fetchColumn();
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }

    /** @return array<int, string> */
    private static function categoriasCompatibles(string $categoria): array
    {
        $categoria = mb_strtolower(trim($categoria));
        $singular = preg_replace('/(?:es|s)$/u', '', $categoria) ?? $categoria;
        $plural = preg_match('/[aeiouáéíóú]$/u', $singular) === 1
            ? $singular . 's'
            : $singular . 'es';

        return array_values(array_unique(array_filter([$categoria, $singular, $plural])));
    }

    /** @param array<string, mixed> $datos */
    private function vincularProducto(\PDOStatement $sentencia, array $datos): void
    {
        foreach (['marca', 'nombre', 'categoria_id', 'categoria', 'precio', 'almacenamiento', 'color', 'etiqueta', 'descripcion'] as $campo) {
            $sentencia->bindValue(
                ':' . $campo,
                $datos[$campo],
                $campo === 'categoria_id' ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}
