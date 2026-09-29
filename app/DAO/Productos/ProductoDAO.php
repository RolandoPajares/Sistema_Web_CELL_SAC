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
        $this->asegurarTablaImagenReferencia();
        $this->asegurarImagenesProductosExistentes();
        $sentencia = $this->pdo()->query('SELECT p.*, (SELECT r.ruta_imagen FROM producto_imagen_referencia r WHERE r.producto_id=p.id LIMIT 1) AS imagen_referencia FROM productos p WHERE p.activo = 1 ORDER BY p.id DESC');

        return $sentencia->fetchAll();
    }

    public function paginar(FiltroProducto $filtro): array
    {
        $this->asegurarTablaImagenReferencia();
        $this->asegurarImagenesProductosExistentes();
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
            $condiciones[] = 'categoria = :categoria';
            $parametros['categoria'] = $filtro->categoria;
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
            'SELECT p.id, p.marca, p.nombre, p.categoria, p.precio, p.existencias, p.almacenamiento, p.color, p.etiqueta, p.descripcion, (SELECT r.ruta_imagen FROM producto_imagen_referencia r WHERE r.producto_id=p.id LIMIT 1) AS imagen_referencia
             FROM productos p WHERE ' . $clausulaWhere . ' ORDER BY ' . $orden . ' LIMIT :limit OFFSET :offset'
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
            'INSERT INTO productos(marca, nombre, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo)
             VALUES(:marca, :nombre, :categoria, :precio, :existencias, :almacenamiento, :color, :etiqueta, :descripcion, 1)'
        );
        $this->vincularProducto($sentencia, $datos);
        $sentencia->execute();

        return (int) $this->pdo()->lastInsertId();
    }

    public function actualizar(int $idProducto, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE productos
             SET marca = :marca, nombre = :nombre, categoria = :categoria, precio = :precio, existencias = :existencias,
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


    /** @return array<string,mixed>|null */
    public function detalleConVariantes(int $idProducto): ?array
    {
        $this->asegurarTablaImagenReferencia();
        $this->asegurarImagenesProductosExistentes();
        $producto = $this->buscarActivo($idProducto);
        if (!$producto) { return null; }

        $stmt = $this->pdo()->prepare('SELECT id, nombre_color, codigo_color, stock FROM producto_variantes WHERE producto_id = :id AND estado = "activo"');
        $stmt->execute([':id'=>$idProducto]);
        $producto['variantes'] = $stmt->fetchAll();

        foreach ($producto['variantes'] as &$variante) {
            $img = $this->pdo()->prepare('SELECT ruta_imagen, imagen_principal, orden FROM variante_imagenes WHERE variante_id = :id ORDER BY orden ASC');
            $img->execute([':id'=>$variante['id']]);
            $variante['imagenes'] = $img->fetchAll();
        }

        $car = $this->pdo()->prepare('SELECT nombre, valor FROM producto_caracteristicas WHERE producto_id = :id');
        $car->execute([':id'=>$idProducto]);
        $producto['caracteristicas'] = $car->fetchAll();
        $producto['imagen_referencia'] = $this->imagenReferencia($idProducto);

        return $producto;
    }
public function variantes(int $productoId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM producto_variantes WHERE producto_id = :id ORDER BY id');
        $stmt->execute(['id'=>$productoId]);
        return $stmt->fetchAll();
    }

    public function imagenesVariante(int $varianteId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM variante_imagenes WHERE variante_id = :id ORDER BY orden, id');
        $stmt->execute(['id'=>$varianteId]);
        return $stmt->fetchAll();
    }

    public function caracteristicas(int $productoId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM producto_caracteristicas WHERE producto_id = :id ORDER BY id');
        $stmt->execute(['id'=>$productoId]);
        return $stmt->fetchAll();
    }

    public function crearVariante(int $productoId, string $nombre, string $codigo, int $stock): int
    {
        $stmt=$this->pdo()->prepare('INSERT INTO producto_variantes(producto_id,nombre_color,codigo_color,stock,estado) VALUES(:p,:n,:c,:s,"activo")');
        $stmt->execute(['p'=>$productoId,'n'=>$nombre,'c'=>$codigo !== '' ? $codigo : null,'s'=>$stock]);
        return (int)$this->pdo()->lastInsertId();
    }

    /** @return array<int,string> Rutas de imagenes que pertenecian a la variante eliminada. */
    public function eliminarVariante(int $productoId, int $varianteId): array
    {
        $q=$this->pdo()->prepare('SELECT id FROM producto_variantes WHERE id=:v AND producto_id=:p');
        $q->execute(['v'=>$varianteId,'p'=>$productoId]);
        if (!$q->fetchColumn()) throw new \DomainException('El color seleccionado no pertenece a este producto.');
        $imgs=$this->pdo()->prepare('SELECT ruta_imagen FROM variante_imagenes WHERE variante_id=:v');
        $imgs->execute(['v'=>$varianteId]);
        $rutas=array_values(array_filter(array_map('strval',$imgs->fetchAll(PDO::FETCH_COLUMN))));
        $d=$this->pdo()->prepare('DELETE FROM producto_variantes WHERE id=:v AND producto_id=:p');
        $d->execute(['v'=>$varianteId,'p'=>$productoId]);
        return $rutas;
    }

    public function agregarImagenVariante(int $varianteId, string $ruta): int
    {
        $q=$this->pdo()->prepare('SELECT COUNT(*) FROM variante_imagenes WHERE variante_id=:v'); $q->execute(['v'=>$varianteId]); $orden=(int)$q->fetchColumn();
        $stmt=$this->pdo()->prepare('INSERT INTO variante_imagenes(variante_id,ruta_imagen,imagen_principal,orden) VALUES(:v,:r,:p,:o)');
        $stmt->execute(['v'=>$varianteId,'r'=>$ruta,'p'=>$orden===0?1:0,'o'=>$orden]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function eliminarImagenVariante(int $imagenId): ?string
    {
        $q=$this->pdo()->prepare('SELECT ruta_imagen FROM variante_imagenes WHERE id=:id'); $q->execute(['id'=>$imagenId]); $ruta=$q->fetchColumn();
        if (!$ruta) return null;
        $d=$this->pdo()->prepare('DELETE FROM variante_imagenes WHERE id=:id'); $d->execute(['id'=>$imagenId]);
        return (string)$ruta;
    }


    /** Asigna fotos referenciales SOLO a productos que ya existen en la BD. No crea productos. */
    private function asegurarImagenesProductosExistentes(): void
    {
        static $hecho = false;
        if ($hecho || $this->pdo() === null) { return; }
        $hecho = true;
        $imagenes = [
            'Cable USB-C trenzado' => 'https://content.abt.com/image.php/d222e22774b8436dac643392c29accf4?canvas=&ck=2&height=600&image=%2Fimages%2Fproducts%2FBDP_Images%2Fapple-usb-c-cable-MW493AMA-top-angled.jpg&width=650',
            'Cargador Turbo USB-C' => 'https://images.tcdn.com.br/img/img_prod/643493/carregador_xiaomi_turbo_power_tipo_c_67w_original_completo_2295_3_faf56a4457e60b563715e05640822aab.jpg',
            'Funda Galaxy A Series' => 'https://www.spigen.com/cdn/shop/files/detail_web_sp65n_ultrahybrid_cc_08.jpg?v=1747157159&width=1946',
            'Cargador 25W USB-C' => 'https://images.samsung.com/is/image/samsung/p6pim/us/ep-t2510nwegus/gallery/us-25w-power-adapter-ep-t2510-ep-t2510nwegus-550504227?$product-details-jpg$=',
        ];
        $buscar = $this->pdo()->prepare('SELECT id FROM productos WHERE nombre=:n AND activo=1 LIMIT 1');
        $guardar = $this->pdo()->prepare('INSERT INTO producto_imagen_referencia(producto_id,ruta_imagen) VALUES(:p,:r) ON DUPLICATE KEY UPDATE ruta_imagen=IF(ruta_imagen="" OR ruta_imagen IS NULL,VALUES(ruta_imagen),ruta_imagen)');
        foreach ($imagenes as $nombre=>$ruta) {
            $buscar->execute(['n'=>$nombre]);
            $id=(int)($buscar->fetchColumn() ?: 0);
            if ($id>0) { $guardar->execute(['p'=>$id,'r'=>$ruta]); }
        }
    }

    private function asegurarDatosDemoTelefonos(): void
    {
        static $hecho = false;
        if ($hecho || $this->pdo() === null) { return; }
        $hecho = true;

        // Catálogo demostrativo con ficha técnica verificable. No reemplaza productos que el administrador ya haya creado.
        $productos = [
            ['Apple','iPhone 15 128GB','Celular',2999.00,8,'128 GB','Negro','Más vendido','iPhone 15 con chip A16 Bionic y almacenamiento de 128 GB.',
             'https://www.apple.com/newsroom/images/2023/09/apple-debuts-iphone-15-and-iphone-15-plus/article/Apple-iPhone-15-lineup-hero-geo-230912_inline.jpg.large.jpg',
             ['Memoria RAM'=>'No publicada por Apple','Memoria Interna'=>'128 GB','Batería'=>'No publicada en mAh por Apple','Procesador y generación'=>'Apple A16 Bionic']],
            ['Samsung','Galaxy S24 256GB','Celular',2399.00,12,'256 GB','Negro','Oferta','Galaxy S24 con 8 GB de RAM, 256 GB de almacenamiento y batería de 4000 mAh.',
             'https://image-stgus.samsung.com/SamsungUS/support/solutions/mobile/phones/galaxy-s/s24/Differences-between-Galaxy-Eureka-models.jpg',
             ['Memoria RAM'=>'8 GB','Memoria Interna'=>'256 GB','Batería'=>'4000 mAh','Procesador y generación'=>'Exynos 2400 / Snapdragon 8 Gen 3 (según mercado)']],
            ['Xiaomi','Redmi Note 13 Pro','Celular',899.00,15,'256 GB','Negro','Nuevo','Redmi Note 13 Pro con pantalla AMOLED, cámara de 200 MP y carga rápida de 67 W.',
             'https://i02.appmifile.com/mi-com-product/fly-birds/redmi-note-13-pro/pc/a69a4089984da42166ce89df3dc83468.jpg',
             ['Memoria RAM'=>'8 GB','Memoria Interna'=>'256 GB','Batería'=>'5000 mAh','Procesador y generación'=>'MediaTek Helio G99-Ultra (6 nm)']],
            ['Motorola','Moto G47 128GB','Celular',639.00,6,'128 GB','Azul','Oferta','Moto G47 5G con almacenamiento de 128 GB y procesador MediaTek Dimensity 6300.',
             'https://p4-ofp.static.pub//fes/cms/2026/04/28/gqnzghhzn0tqhhuio7syyqiwnjckv8413909.png',
             ['Memoria RAM'=>'8 GB','Memoria Interna'=>'128 GB','Batería'=>'5200 mAh','Procesador y generación'=>'MediaTek Dimensity 6300 (6 nm)']],
            ['Samsung','Galaxy A55 128GB','Celular',1599.00,8,'128 GB','Azul','Popular','Galaxy A55 5G con 8 GB de RAM, batería de 5000 mAh y procesador Exynos 1480.',
             'https://commons.wikimedia.org/wiki/Special:Redirect/file/Samsung_Galaxy_A55_5G_2024.jpg',
             ['Memoria RAM'=>'8 GB','Memoria Interna'=>'128 GB','Batería'=>'5000 mAh','Procesador y generación'=>'Samsung Exynos 1480 (4 nm)']],
        ];

        $buscar=$this->pdo()->prepare('SELECT id FROM productos WHERE nombre=:n LIMIT 1');
        $insertar=$this->pdo()->prepare('INSERT INTO productos(marca,nombre,categoria,precio,existencias,almacenamiento,color,etiqueta,descripcion,activo) VALUES(:m,:n,:c,:p,:e,:a,:co,:et,:d,1)');
        foreach($productos as $p){
            [$marca,$nombre,$categoria,$precio,$stock,$alm,$color,$etiqueta,$desc,$imagen,$specs]=$p;
            $buscar->execute(['n'=>$nombre]); $id=(int)($buscar->fetchColumn() ?: 0);
            if($id===0){ $insertar->execute(['m'=>$marca,'n'=>$nombre,'c'=>$categoria,'p'=>$precio,'e'=>$stock,'a'=>$alm,'co'=>$color,'et'=>$etiqueta,'d'=>$desc]); $id=(int)$this->pdo()->lastInsertId(); }
            $this->asegurarTablaImagenReferencia();
            $q=$this->pdo()->prepare('INSERT INTO producto_imagen_referencia(producto_id,ruta_imagen) VALUES(:p,:r) ON DUPLICATE KEY UPDATE ruta_imagen=IF(ruta_imagen="" OR ruta_imagen IS NULL,VALUES(ruta_imagen),ruta_imagen)');
            $q->execute(['p'=>$id,'r'=>$imagen]);
            $this->pdo()->prepare('INSERT INTO producto_variantes(producto_id,nombre_color,codigo_color,stock) SELECT :p,:n,:h,:s WHERE NOT EXISTS(SELECT 1 FROM producto_variantes WHERE producto_id=:p2)')->execute(['p'=>$id,'n'=>$color,'h'=>'#1f2937','s'=>$stock,'p2'=>$id]);
            foreach($specs as $n=>$v){
                $q=$this->pdo()->prepare('SELECT id FROM producto_caracteristicas WHERE producto_id=:p AND nombre=:n LIMIT 1'); $q->execute(['p'=>$id,'n'=>$n]);
                if(!$q->fetchColumn()) $this->pdo()->prepare('INSERT INTO producto_caracteristicas(producto_id,nombre,valor) VALUES(:p,:n,:v)')->execute(['p'=>$id,'n'=>$n,'v'=>$v]);
            }
        }
    }

    private function asegurarTablaImagenReferencia(): void
    {
        $this->pdo()->exec('CREATE TABLE IF NOT EXISTS producto_imagen_referencia (producto_id INT NOT NULL PRIMARY KEY, ruta_imagen VARCHAR(255) NOT NULL, actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, CONSTRAINT fk_ref_producto FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    public function imagenReferencia(int $productoId): ?string
    {
        $this->asegurarTablaImagenReferencia();
        $q=$this->pdo()->prepare('SELECT ruta_imagen FROM producto_imagen_referencia WHERE producto_id=:p'); $q->execute(['p'=>$productoId]);
        $r=$q->fetchColumn(); return $r ? (string)$r : null;
    }

    public function guardarImagenReferencia(int $productoId, string $ruta): ?string
    {
        $this->asegurarTablaImagenReferencia();
        $anterior=$this->imagenReferencia($productoId);
        $q=$this->pdo()->prepare('INSERT INTO producto_imagen_referencia(producto_id,ruta_imagen) VALUES(:p,:r) ON DUPLICATE KEY UPDATE ruta_imagen=VALUES(ruta_imagen)');
        $q->execute(['p'=>$productoId,'r'=>$ruta]); return $anterior;
    }

    public function eliminarImagenReferencia(int $productoId): ?string
    {
        $this->asegurarTablaImagenReferencia(); $anterior=$this->imagenReferencia($productoId);
        $q=$this->pdo()->prepare('DELETE FROM producto_imagen_referencia WHERE producto_id=:p'); $q->execute(['p'=>$productoId]); return $anterior;
    }

    public function guardarCaracteristicas(int $productoId, array $datos): void
    {
        foreach($datos as $nombre=>$valor){ $valor=trim((string)$valor);
            $d=$this->pdo()->prepare('DELETE FROM producto_caracteristicas WHERE producto_id=:p AND nombre=:n'); $d->execute(['p'=>$productoId,'n'=>$nombre]);
            if($valor==='') continue;
            $i=$this->pdo()->prepare('INSERT INTO producto_caracteristicas(producto_id,nombre,valor) VALUES(:p,:n,:v)'); $i->execute(['p'=>$productoId,'n'=>$nombre,'v'=>$valor]);
        }
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }

    /** @param array<string, mixed> $datos */
    private function vincularProducto(\PDOStatement $sentencia, array $datos): void
    {
        foreach (['marca', 'nombre', 'categoria', 'precio', 'existencias', 'almacenamiento', 'color', 'etiqueta', 'descripcion'] as $campo) {
            $sentencia->bindValue(
                ':' . $campo,
                $datos[$campo],
                $campo === 'existencias' ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}
