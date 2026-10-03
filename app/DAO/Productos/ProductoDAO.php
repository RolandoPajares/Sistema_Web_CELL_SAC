<?php

declare(strict_types=1);

namespace App\DAO\Productos; //namespace que define la ubicación de la clase dentro del proyecto

use App\DTO\Productos\FiltroProducto;
use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use PDO;

// Clase final que implementa la interfaz RepositorioProductoInterfaz para gestionar 
// operaciones de productos en la base de datos
final class ProductoDAO implements RepositorioProductoInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Indica si el repositorio puede consultar la base de datos.
     */
    public function estaDisponible(): bool
    {
        return $this->conexion->pdo() !== null;
    }

    // Devuelve todos los productos activos ordenados por su identificador en orden descendente.
    public function todosActivos(): array // Devuelve todos los productos activos ordenados por su identificador en orden descendente.
    {
        $sentencia = $this->pdo()->query('SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC');

        return $sentencia->fetchAll(); // Devuelve un arreglo con todos los productos activos obtenidos de la base de datos
    }

    /**
     * Devuelve productos activos con descuentos reales, ordenados según ventas y disponibilidad.
     *
     * @return array<int, array<string, mixed>>
     */
    // metodo público que obtiene los productos activos con descuentos reales, 
    // ordenados según ventas y disponibilidad,
    public function ofertasPopulares(): array
    {
        // Cuenta las ventas no canceladas y excluye productos sin descuento real o sin existencias.
        // query SQL que selecciona productos activos con descuentos reales, ordenados por popularidad y disponibilidad
        $sentencia = $this->pdo()->query(  
            "SELECT p.id, p.marca, p.nombre, p.categoria, p.precio, p.precio_original,
                    p.descuento, p.precio_oferta, p.url_imagen, p.existencias,
                    p.etiqueta, p.almacenamiento, p.descripcion,
                    COALESCE(ventas.unidades_vendidas, 0) AS unidades_vendidas /* coalesce para manejar  nulos */
             FROM productos p
             LEFT JOIN (
                 SELECT dp.producto_id, SUM(dp.cantidad) AS unidades_vendidas
                 FROM detalle_pedidos dp
                 INNER JOIN pedidos pe ON pe.id = dp.pedido_id
                 WHERE UPPER(pe.estado) <> 'CANCELADO'    -- upper para evitar problemas de mayúsculas/minúsculas
                 GROUP BY dp.producto_id
             ) ventas ON ventas.producto_id = p.id
             WHERE p.activo = 1
               AND p.existencias > 0
               AND p.precio_original IS NOT NULL
               AND p.precio_oferta IS NOT NULL
               AND p.precio_oferta < p.precio_original
             ORDER BY unidades_vendidas DESC,
                      (p.url_imagen IS NOT NULL AND TRIM(p.url_imagen) <> '') DESC,  -- Prioriza productos con imágenes disponibles
                      ((p.precio_original - p.precio_oferta) / p.precio_original) DESC,
                      p.id DESC"
        );

        return $sentencia->fetchAll();  // Devuelve un arreglo con los productos activos que cumplen los criterios de descuento y popularidad
    }

    // metodo público que obtiene los productos activos según los filtros aplicados,
    public function paginar(FiltroProducto $filtro): array
    {
        $condiciones = ['activo = 1'];
        $parametros = [];

        // Aplica los filtros de búsqueda, marca, categoría y rango de precios según los valores proporcionados en el objeto FiltroProducto
        if ($filtro->busqueda !== '') {
            $condiciones[] = '(nombre LIKE :buscar_nombre OR marca LIKE :buscar_marca)';
            $parametros['buscar_nombre'] = '%' . $filtro->busqueda . '%';
            $parametros['buscar_marca'] = '%' . $filtro->busqueda . '%';
        }

        // Si se especifica una marca, se agrega una condición para filtrar por esa marca exacta
        if ($filtro->marca !== '') {
            $condiciones[] = 'marca = :marca';
            $parametros['marca'] = $filtro->marca;
        }

        //  Si se especifica una categoría, se agregan condiciones para filtrar por todas las categorías compatibles (singular, plural y variantes)
        if ($filtro->categoria !== '') {
            $placeholdersCategoria = [];

            // Itera sobre las categorías compatibles y crea placeholders para la consulta SQL
            foreach (self::categoriasCompatibles($filtro->categoria) as $indice => $categoriaCompatible) {
                $nombreParametro = 'categoria_' . $indice;
                $placeholdersCategoria[] = ':' . $nombreParametro;
                $parametros[$nombreParametro] = $categoriaCompatible;
            }
            $condiciones[] = 'LOWER(TRIM(categoria)) IN (' . implode(', ', $placeholdersCategoria) . ')';
        }


        // Si se especifica un precio mínimo, se agrega una condición para filtrar por productos con precio mayor o igual al mínimo 
        if ($filtro->precioMinimo !== null) {
            $condiciones[] = 'COALESCE(precio_oferta, precio) >= CAST(:precio_minimo AS DECIMAL(10,2))';
            $parametros['precio_minimo'] = $filtro->precioMinimo;
        }
        
        // Si se especifica un precio máximo, se agrega una condición para filtrar por productos con precio menor o igual al máximo
        if ($filtro->precioMaximo !== null) {
            $condiciones[] = 'COALESCE(precio_oferta, precio) <= CAST(:precio_maximo AS DECIMAL(10,2))';
            $parametros['precio_maximo'] = $filtro->precioMaximo;
        }

        // Combina todas las condiciones en una cláusula WHERE y prepara la consulta para contar el total de registros que cumplen los filtros aplicados
        $clausulaWhere = implode(' AND ', $condiciones);
        $conteo = $this->pdo()->prepare('SELECT COUNT(*) FROM productos WHERE ' . $clausulaWhere);
        $conteo->execute($parametros);
        $total = (int) $conteo->fetchColumn();
        $pagina = max(1, $filtro->pagina);
        $porPagina = min(48, max(1, $filtro->porPagina));
        $desplazamiento = ($pagina - 1) * $porPagina;
       
        // Determina el orden de los resultados según el valor del filtro 'orden' y prepara la consulta para obtener los productos filtrados y paginados    
        $orden = match ($filtro->orden) {
            'price_asc' => 'COALESCE(precio_oferta, precio) ASC, id DESC',
            'price_desc' => 'COALESCE(precio_oferta, precio) DESC, id DESC',
            'name' => 'nombre ASC, id DESC',
            default => 'id DESC',
        };

        // Prepara la consulta SQL para obtener los productos filtrados y paginados según los criterios aplicados

        $sentencia = $this->pdo()->prepare(
            'SELECT id, marca, nombre, categoria, precio, precio_original, descuento, precio_oferta,
                    url_imagen, existencias, etiqueta, almacenamiento, color, descripcion
             FROM productos WHERE ' . $clausulaWhere . ' ORDER BY ' . $orden . ' LIMIT :limit OFFSET :offset'
        );
        
          // Vincula los valores de los parámetros a la consulta preparada y ejecuta la sentencia para obtener los productos filtrados y paginados
        foreach ($parametros as $nombre => $valor) {
            $sentencia->bindValue(':' . $nombre, $valor);
        }
        $sentencia->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $sentencia->bindValue(':offset', $desplazamiento, PDO::PARAM_INT);
        $sentencia->execute();

        // Devuelve un arreglo con los productos filtrados y paginados, junto con información de paginación como el total de registros, la página actual, la cantidad por página y la última página disponible
        return [
            'productos' => $sentencia->fetchAll(),
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'ultima_pagina' => max(1, (int) ceil($total / $porPagina)),
        ];
    }

    /**
     * Devuelve los registros disponibles para el panel de administración.
     */
    public function todosParaAdministrador(): array
    {
        $sentencia = $this->pdo()->query('SELECT * FROM productos ORDER BY activo DESC, id DESC');

        return $sentencia->fetchAll();
    }

    /**
     * Busca el registro activo que coincide con el identificador o filtro indicado.
     * @return array<string, mixed>|null
     */
    public function buscarActivo(int $idProducto): ?array
    {
        $sentencia = $this->pdo()->prepare('SELECT * FROM productos WHERE id = :id AND activo = 1 LIMIT 1');
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
        $producto = $sentencia->fetch();

        return $producto ?: null;
    }

    /**
     * Devuelve las imágenes vinculadas al producto en el orden definido para la galería.
     *
     * @return array<int, string>
     */
    public function imagenesRelacionadas(int $idProducto): array
    {
        // Reúne referencias del producto e imágenes de sus variantes activas desde las tablas relacionadas.
        $imagenes = [];
        $referencia = $this->pdo()->prepare(
            'SELECT ruta_imagen
             FROM producto_imagen_referencia
             WHERE producto_id = :producto_id
             ORDER BY orden ASC, id ASC'
        );
        $referencia->bindValue(':producto_id', $idProducto, PDO::PARAM_INT);
        $referencia->execute();
        $imagenes = array_merge($imagenes, $referencia->fetchAll(PDO::FETCH_COLUMN));

        $variantes = $this->pdo()->prepare(
            'SELECT vi.ruta_imagen
             FROM variante_imagenes vi
             INNER JOIN producto_variantes pv ON pv.id = vi.variante_id
             WHERE pv.producto_id = :producto_id
               AND (pv.estado IS NULL OR pv.estado = :estado_activo)
             ORDER BY vi.imagen_principal DESC, vi.orden ASC, vi.id ASC'
        );
        $variantes->bindValue(':producto_id', $idProducto, PDO::PARAM_INT);
        $variantes->bindValue(':estado_activo', 'activo', PDO::PARAM_STR);
        $variantes->execute();
        $imagenes = array_merge($imagenes, $variantes->fetchAll(PDO::FETCH_COLUMN));

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $ruta): string => is_scalar($ruta) ? trim((string) $ruta) : '',
            $imagenes
        ))));
    }

    /**
     * Recupera las características asociadas al producto solicitado.
     *
     * @return array<int, array{nombre:string,valor:string}>
     */
    public function caracteristicas(int $idProducto): array
    {
        // Las características adicionales se consultan por producto y se ordenan para mostrar una ficha estable.
        $sentencia = $this->pdo()->prepare(
            'SELECT nombre, valor FROM producto_caracteristicas WHERE producto_id = :producto_id ORDER BY id ASC'
        );
        $sentencia->bindValue(':producto_id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    /**
     * Busca un producto por su identificador para mostrarlo en la administración.
     * @return array<string, mixed>|null
     */
    public function buscarParaAdministrador(int $idProducto): ?array
    {
        $sentencia = $this->pdo()->prepare('SELECT * FROM productos WHERE id = :id LIMIT 1');
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
        $producto = $sentencia->fetch();

        return $producto ?: null;
    }

    /**
     * Busca un producto activo para modificarlo.
     * @return array<string, mixed>|null
     */
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

    /**
     * Comprueba si ya existe un producto con esa marca y ese nombre, excepto el identificador indicado.
     */

    //metodo público que verifica si ya existe un producto con la misma marca y nombre,
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

    //metodo público que crea un nuevo producto en la base de datos y devuelve su identificador recién creado
    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO productos(marca, nombre, categoria_id, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, url_imagen, activo)
             VALUES(:marca, :nombre, :categoria_id, :categoria, :precio, :existencias, :almacenamiento, :color, :etiqueta, :descripcion, :url_imagen, 1)'
        );
        $this->vincularProducto($sentencia, $datos);
        $sentencia->bindValue(':existencias', (int) ($datos['existencias'] ?? 0), PDO::PARAM_INT);
        $sentencia->execute();

        return (int) $this->pdo()->lastInsertId(); // devuelve el ID del producto recién creado para su posterior uso
    }

    //metodo privado que vincula los valores de un producto a una sentencia preparada para su ejecución en la base de datos
    public function actualizar(int $idProducto, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE productos
             SET marca = :marca, nombre = :nombre, categoria_id = :categoria_id, categoria = :categoria, precio = :precio,
                 almacenamiento = :almacenamiento, color = :color, etiqueta = :etiqueta, descripcion = :descripcion,
                 url_imagen = :url_imagen
             WHERE id = :id'
        );
        $this->vincularProducto($sentencia, $datos);
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $sentencia->execute();
    }

    /**
     * metodo público que elimina físicamente un producto de la base de datos, verificando que no existan
     *  pedidos ni movimientos de inventario asociados al producto antes de proceder con la eliminación
     */

    //metodo público que elimina físicamente un producto de la base de datos, verificando que no existan
    // pedidos ni movimientos de inventario asociados al producto antes de proceder con la eliminación
    public function eliminarFisicamente(int $idProducto): bool
    {
        // Inicia una transacción para garantizar la consistencia de la operación de eliminación
        return $this->conexion->transaccion(function () use ($idProducto): bool {
            $pdo = $this->pdo();
            $producto = $pdo->prepare('SELECT id FROM productos WHERE id = :id AND activo = 1 FOR UPDATE');
            $producto->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $producto->execute();
            if ($producto->fetchColumn() === false) {
                return false;
            }

            // Verifica si existen pedidos asociados al producto antes de eliminarlo
            $detallePedido = $pdo->prepare(
                'SELECT producto_id FROM detalle_pedidos WHERE producto_id = :id LIMIT 1 FOR UPDATE'
            );

            // Vincula el valor del identificador del producto a la consulta preparada y ejecuta la sentencia para verificar si existen pedidos asociados
            $detallePedido->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $detallePedido->execute();
            if ($detallePedido->fetchColumn() !== false) {
                return false;
            }

            // Verifica si existen movimientos de inventario asociados al producto antes de eliminarlo
            $movimiento = $pdo->prepare(
                'SELECT producto_id FROM movimientos_inventario WHERE producto_id = :id LIMIT 1 FOR UPDATE'
            );

            // Vincula el valor del identificador del producto a la consulta preparada y ejecuta la sentencia para verificar si existen movimientos de inventario asociados
            $movimiento->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $movimiento->execute();
            if ($movimiento->fetchColumn() !== false) {
                return false;
            }
             
            // Si no hay pedidos ni movimientos asociados, procede a eliminar físicamente el producto de la base de datos
            $eliminar = $pdo->prepare('DELETE FROM productos WHERE id = :id AND activo = 1');
            $eliminar->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $eliminar->execute();

            return $eliminar->rowCount() === 1;
        });
    }

    /**
     * Metodo público que desactiva un producto activo en la base de datos, estableciendo su estado 
     * como inactivo.
     */
    public function desactivar(int $idProducto): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE productos SET activo = 0 WHERE id = :id AND activo = 1');
        $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT); // pdo::PARAM_INT para indicar que el valor es un entero
        $sentencia->execute();

        // Si no se afectó ninguna fila, significa que el producto no existe o ya estaba desactivado
        if ($sentencia->rowCount() !== 1) {
            throw new \DomainException('El producto no existe o ya fue desactivado.');
        }
    }

    /**
     * Descuenta del inventario la cantidad indicada para el producto correspondiente.
     */
    public function reducirStock(int $idProducto, int $cantidad): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE productos SET existencias = existencias - ? WHERE id = ? AND existencias >= ?');
        $sentencia->execute([$cantidad, $idProducto, $cantidad]);

        if ($sentencia->rowCount() !== 1) {
            throw new \RuntimeException('No fue posible actualizar el stock.');
        }
    }

    //metodo público que devuelve la cantidad de productos activos en la base de datos,
    // devolviendo un valor entero que representa el stock total de productos activos
    public function contarActivos(): int
    {
        return (int) $this->pdo()->query('SELECT COUNT(*) FROM productos WHERE activo = 1')->fetchColumn();
    }

    //metodo público que devuelve la suma de existencias de todos los productos activos 
    //en la base de datos,
    public function stockTotal(): int
    {
        return (int) $this->pdo()->query('SELECT COALESCE(SUM(existencias), 0) FROM productos  
                                            WHERE activo = 1')->fetchColumn();
    }

    /**
     * Obtiene la conexión PDO y detiene la operación si no está disponible.
     */
    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio(); // Devuelve la conexión PDO obligatoria, deteniendo la operación si no está disponible
    }

    /**
     * Devuelve las categorías equivalentes aceptadas por los filtros del catálogo.
     *
     * @return array<int, string>
     */
    //metodo privado que devuelve un arreglo con las categorías equivalentes aceptadas por los filtros del catálogo,
    // incluyendo la categoría original, su forma singular y su forma plural
    private static function categoriasCompatibles(string $categoria): array
    {
        $categoria = mb_strtolower(trim($categoria));
        $singular = preg_replace('/(?:es|s)$/u', '', $categoria) ?? $categoria; // Elimina sufijos "es" o "s" para obtener la forma singular de la categoría
        $plural = preg_match('/[aeiouáéíóú]$/u', $singular) === 1
            ? $singular . 's'
            : $singular . 'es';

        return array_values(array_unique(array_filter([$categoria, $singular, $plural])));
    }

    /**
     * Asocia el producto con la entidad indicada y conserva la relación correspondiente.
     *
     * @param array<string, mixed> $datos
     */
    private function vincularProducto(\PDOStatement $sentencia, array $datos): void
    {
        foreach (['marca', 'nombre', 'categoria_id', 'categoria', 'precio', 'almacenamiento', 'color', 'etiqueta', 'descripcion', 'url_imagen'] as $campo) {
            $sentencia->bindValue(
                ':' . $campo,
                $datos[$campo],
                $campo === 'categoria_id' ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}
