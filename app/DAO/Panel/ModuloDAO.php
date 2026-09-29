<?php

declare(strict_types=1);

namespace App\DAO\Panel;

use App\Nucleo\BaseDatos\Conexion;
use PDO;

final class ModuloDAO
{
    public function __construct(private Conexion $conexion)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listar(string $modulo): array
    {
        if (in_array($modulo, ['alertas-stock','logistica-mayorista'], true)) { $this->asegurarTablasOperativas(); }
        $sql = match ($modulo) {
            'categorias' => 'SELECT id, nombre, descripcion, activo, creado_en FROM categorias ORDER BY id DESC',
            'proveedores' => 'SELECT id, nombre, ruc, correo, telefono, ciudad, activo FROM proveedores ORDER BY id DESC',
            'clientes', 'clientes-mayoristas' => "SELECT id, tipo, documento, razon_social AS empresa, nombre_contacto AS contacto, correo, telefono, ciudad, activo FROM clientes WHERE (:tipo = '' OR tipo = :tipo2) ORDER BY id DESC",
            'cotizaciones' => 'SELECT co.id, co.cliente_id, COALESCE(cl.razon_social, cl.nombre_contacto) AS cliente, co.total, co.estado, co.notas, co.activo, co.creado_en FROM cotizaciones co JOIN clientes cl ON cl.id = co.cliente_id ORDER BY co.id DESC',
            'compras' => 'SELECT c.id, c.proveedor_id, p.nombre AS proveedor, c.total, c.estado, c.fecha_compra AS fecha, c.activo FROM compras c JOIN proveedores p ON p.id = c.proveedor_id ORDER BY c.id DESC',
            'inventario' => 'SELECT p.id, p.marca, p.nombre AS producto, p.categoria, p.existencias, CASE WHEN p.existencias <= 3 THEN \'Crítico\' WHEN p.existencias <= 8 THEN \'Bajo\' ELSE \'Disponible\' END AS estado FROM productos p WHERE p.activo = 1 ORDER BY p.existencias ASC, p.nombre',
            'productos' => 'SELECT p.id, CONCAT(\'PRO-\', YEAR(p.creado_en), \'-\', LPAD(p.id,4,\'0\')) AS sku, CONCAT(p.marca, \' \', p.nombre) AS producto, p.categoria, p.precio, p.existencias AS stock, CASE WHEN p.activo=1 AND p.existencias<=8 THEN \'Stock bajo\' WHEN p.activo=1 THEN \'Publicado\' ELSE \'Borrador\' END AS estado FROM productos p ORDER BY p.activo DESC,p.id DESC',
            'movimientos-stock' => "SELECT * FROM (SELECT m.id AS id, CONCAT('MOV-',YEAR(m.creado_en),'-',LPAD(m.id,4,'0')) codigo, CONCAT(p.marca,' ',p.nombre) producto, m.tipo_movimiento movimiento, m.cantidad, COALESCE(u.nombre,'Sistema') responsable, m.notas motivo, m.creado_en fecha FROM movimientos_inventario m JOIN productos p ON p.id=m.producto_id LEFT JOIN usuarios u ON u.id=m.usuario_id UNION ALL SELECT -dp.id id, CONCAT('PED-',YEAR(pe.creado_en),'-',LPAD(pe.id,4,'0')) codigo, CONCAT(pr.marca,' ',pr.nombre) producto, 'salida' movimiento, dp.cantidad, u.nombre responsable, CONCAT('Venta en línea · Pedido #',pe.id) motivo, pe.creado_en fecha FROM detalle_pedidos dp JOIN pedidos pe ON pe.id=dp.pedido_id JOIN usuarios u ON u.id=pe.usuario_id JOIN productos pr ON pr.id=dp.producto_id WHERE NOT EXISTS (SELECT 1 FROM movimientos_inventario mi WHERE mi.producto_id=dp.producto_id AND mi.notas=CONCAT('Venta en línea · Pedido #',pe.id))) x ORDER BY fecha DESC",
            'preparacion-pedidos' => 'SELECT pe.id, CONCAT(\'PED-\', YEAR(pe.creado_en), \'-\', LPAD(pe.id,4,\'0\')) AS pedido, u.nombre AS cliente, CASE WHEN u.rol=\'cliente_mayorista\' THEN \'Mayorista\' ELSE \'Minorista\' END AS tipo_cliente, (SELECT COALESCE(SUM(dp.cantidad),0) FROM detalle_pedidos dp WHERE dp.pedido_id=pe.id) AS items, pe.total, pe.estado, pe.creado_en AS fecha FROM pedidos pe JOIN usuarios u ON u.id=pe.usuario_id ORDER BY pe.id DESC',
            'alertas-stock' => 'SELECT p.id, CONCAT(\'PRO-\', YEAR(p.creado_en), \'-\', LPAD(p.id,4,\'0\')) AS sku, CONCAT(p.marca, \' \', p.nombre) AS producto, p.categoria, p.existencias AS stock_actual, 3 AS stock_minimo, COALESCE(pr.nombre,\'Sin proveedor asignado\') AS proveedor, COALESCE(pr.correo,\'—\') AS correo_proveedor, COALESCE(pr.telefono,\'—\') AS telefono_proveedor FROM productos p LEFT JOIN producto_proveedor pp ON pp.producto_id=p.id LEFT JOIN proveedores pr ON pr.id=pp.proveedor_id AND pr.activo=1 WHERE p.activo=1 AND p.existencias < 3 ORDER BY p.existencias ASC,p.nombre',
            'logistica-mayorista' => $this->listarLogisticaMayorista(),
            'pedidos', 'pedidos-mayoristas' => 'SELECT p.id, u.nombre AS cliente, p.total, p.estado, p.creado_en FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.id DESC',
            'usuarios' => 'SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY id DESC',
            'publicidad', 'campanias' => 'SELECT id, nombre, ubicacion, titulo, activo, vistas, clics FROM campanas_publicitarias ORDER BY id DESC',
            default => '',
        };

        if ($sql === '') {
            return [];
        }

        $sentencia = $this->pdo()->prepare($sql);
        if (in_array($modulo, ['clientes', 'clientes-mayoristas'], true)) {
            $tipo = $modulo === 'clientes-mayoristas' ? 'mayorista' : '';
            $sentencia->bindValue(':tipo', $tipo);
            $sentencia->bindValue(':tipo2', $tipo);
        }
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    private function listarLogisticaMayorista(): array
    {
        $this->asegurarTablasOperativas();
        $sql = "SELECT pe.id, CONCAT('LOG-',YEAR(pe.creado_en),'-',LPAD(pe.id,4,'0')) codigo,
            COALESCE(l.exportador,'MD Technology Cell') exportador, u.nombre consignatario,
            COALESCE(l.factura_numero,'No registrado') factura, COALESCE(l.orden_compra,'No registrado') orden_compra,
            COALESCE(l.pais_origen,'No registrado') pais_origen, COALESCE(l.lugar_carga,'No registrado') lugar_carga,
            COALESCE(l.puerto_descarga,'No registrado') puerto_descarga, COALESCE(l.direccion_entrega,'No registrado') entrega,
            COALESCE(l.descripcion_mercancia,'No registrado') mercancia, COALESCE(l.bultos,'No registrado') bultos,
            COALESCE(l.peso_neto,'0.00') peso_neto_kg, COALESCE(l.peso_bruto,'0.00') peso_bruto_kg, pe.estado,
            l.direccion_exportador,l.contacto_exportador,l.direccion_consignatario,l.contacto_consignatario,l.factura_numero,l.factura_fecha,l.orden_compra,l.carta_credito,l.fecha_emision,l.pais_origen,l.lugar_carga,l.puerto_descarga,l.direccion_entrega,l.descripcion_mercancia,l.codigo_hs,l.cantidad_unidades,l.bultos,l.tipo_embalaje,l.dimensiones,l.volumen_m3,l.peso_neto,l.peso_bruto
            FROM pedidos pe JOIN usuarios u ON u.id=pe.usuario_id AND u.rol='cliente_mayorista'
            LEFT JOIN logistica_mayorista l ON l.pedido_id=pe.id ORDER BY pe.id DESC";
        return $this->pdo()->query($sql)->fetchAll();
    }

    private function asegurarTablasOperativas(): void
    {
        $pdo=$this->pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS producto_proveedor (producto_id INT NOT NULL PRIMARY KEY, proveedor_id INT NOT NULL, CONSTRAINT fk_pp_producto FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE, CONSTRAINT fk_pp_proveedor FOREIGN KEY(proveedor_id) REFERENCES proveedores(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS logistica_mayorista (pedido_id INT NOT NULL PRIMARY KEY, exportador VARCHAR(160) NULL, direccion_exportador VARCHAR(255) NULL, contacto_exportador VARCHAR(160) NULL, direccion_consignatario VARCHAR(255) NULL, contacto_consignatario VARCHAR(160) NULL, factura_numero VARCHAR(80) NULL, factura_fecha DATE NULL, orden_compra VARCHAR(80) NULL, carta_credito VARCHAR(100) NULL, fecha_emision DATE NULL, pais_origen VARCHAR(100) NULL, lugar_carga VARCHAR(160) NULL, puerto_descarga VARCHAR(160) NULL, direccion_entrega VARCHAR(255) NULL, descripcion_mercancia TEXT NULL, codigo_hs VARCHAR(40) NULL, cantidad_unidades INT NULL, bultos VARCHAR(160) NULL, tipo_embalaje VARCHAR(100) NULL, dimensiones VARCHAR(120) NULL, volumen_m3 DECIMAL(10,3) NULL, peso_neto DECIMAL(10,2) NULL, peso_bruto DECIMAL(10,2) NULL, CONSTRAINT fk_log_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /** @param array<string, mixed> $datos */
    public function crear(string $modulo, array $datos, int $usuarioId): int
    {
        return match ($modulo) {
            'categorias' => $this->insertar(
                'INSERT INTO categorias(nombre, descripcion) VALUES(:nombre, :descripcion)',
                [':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion']]
            ),
            'proveedores' => $this->insertar(
                'INSERT INTO proveedores(nombre, ruc, correo, telefono, ciudad) VALUES(:nombre, :ruc, :correo, :telefono, :ciudad)',
                [':nombre' => $datos['nombre'], ':ruc' => $datos['ruc'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']]
            ),
            'clientes', 'clientes-mayoristas' => $this->insertar(
                'INSERT INTO clientes(tipo, documento, razon_social, nombre_contacto, correo, telefono, ciudad) VALUES(:tipo, :documento, :empresa, :contacto, :correo, :telefono, :ciudad)',
                [
                    ':tipo' => $modulo === 'clientes-mayoristas' ? 'mayorista' : $datos['tipo'],
                    ':documento' => $datos['documento'], ':empresa' => $datos['empresa'], ':contacto' => $datos['contacto'],
                    ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad'],
                ]
            ),
            'cotizaciones' => $this->insertar(
                'INSERT INTO cotizaciones(cliente_id, creado_por, total, estado, notas) VALUES(:cliente, :usuario, :total, :estado, :notas)',
                [':cliente' => (int) $datos['cliente_id'], ':usuario' => $usuarioId, ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':notas' => $datos['notas']]
            ),
            'compras' => $this->insertar(
                'INSERT INTO compras(proveedor_id, creado_por, total, estado, fecha_compra) VALUES(:proveedor, :usuario, :total, :estado, :fecha)',
                [':proveedor' => (int) $datos['proveedor_id'], ':usuario' => $usuarioId, ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':fecha' => $datos['fecha']]
            ),
            'inventario' => $this->registrarMovimiento($datos, $usuarioId),
            default => throw new \InvalidArgumentException('El módulo no admite creación de registros.'),
        };
    }

    /** @param array<string, mixed> $datos */
    public function actualizar(string $modulo, int $id, array $datos): void
    {
        [$sql, $parametros] = match ($modulo) {
            'categorias' => ['UPDATE categorias SET nombre=:nombre, descripcion=:descripcion WHERE id=:id', [':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion']]],
            'proveedores' => ['UPDATE proveedores SET nombre=:nombre, ruc=:ruc, correo=:correo, telefono=:telefono, ciudad=:ciudad WHERE id=:id', [':nombre' => $datos['nombre'], ':ruc' => $datos['ruc'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']]],
            'clientes', 'clientes-mayoristas' => ['UPDATE clientes SET tipo=:tipo, documento=:documento, razon_social=:empresa, nombre_contacto=:contacto, correo=:correo, telefono=:telefono, ciudad=:ciudad WHERE id=:id', [':tipo' => $modulo === 'clientes-mayoristas' ? 'mayorista' : $datos['tipo'], ':documento' => $datos['documento'], ':empresa' => $datos['empresa'], ':contacto' => $datos['contacto'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']]],
            'cotizaciones' => ['UPDATE cotizaciones SET cliente_id=:cliente, total=:total, estado=:estado, notas=:notas WHERE id=:id', [':cliente' => (int) $datos['cliente_id'], ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':notas' => $datos['notas']]],
            'compras' => ['UPDATE compras SET proveedor_id=:proveedor, total=:total, estado=:estado, fecha_compra=:fecha WHERE id=:id', [':proveedor' => (int) $datos['proveedor_id'], ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':fecha' => $datos['fecha']]],
            'pedidos', 'pedidos-mayoristas', 'preparacion-pedidos' => ['UPDATE pedidos SET estado=:estado WHERE id=:id', [':estado' => $datos['estado']]],
            'logistica-mayorista' => $this->parametrosActualizarLogistica($id, $datos),
            default => throw new \InvalidArgumentException('El módulo no admite actualización.'),
        };
        $parametros[':id'] = $id;
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->execute($parametros);
        if ($sentencia->rowCount() === 0 && !$this->existe($this->tabla($modulo), $id)) {
            throw new \RuntimeException('El registro solicitado no existe.');
        }
    }

    /** @param array<string,mixed> $datos @return array{0:string,1:array<string,mixed>} */
    private function parametrosActualizarLogistica(int $id, array $datos): array
    {
        $this->asegurarTablasOperativas();
        $campos=['exportador','direccion_exportador','contacto_exportador','direccion_consignatario','contacto_consignatario','factura_numero','factura_fecha','orden_compra','carta_credito','fecha_emision','pais_origen','lugar_carga','puerto_descarga','direccion_entrega','descripcion_mercancia','codigo_hs','cantidad_unidades','bultos','tipo_embalaje','dimensiones','volumen_m3','peso_neto','peso_bruto'];
        $cols=[];$vals=[':pedido_id'=>$id];$updates=[];
        foreach($campos as $c){$cols[]=$c;$vals[':'.$c]=$datos[$c]??null;$updates[]="$c=VALUES($c)";}
        $sql='INSERT INTO logistica_mayorista(pedido_id,'.implode(',',$cols).') VALUES(:pedido_id,'.implode(',',array_map(fn($c)=>':'.$c,$campos)).') ON DUPLICATE KEY UPDATE '.implode(',',$updates);
        $stmt=$this->pdo()->prepare($sql);$stmt->execute($vals);
        if(isset($datos['estado'])){$st=$this->pdo()->prepare('UPDATE pedidos SET estado=:estado WHERE id=:id');$st->execute([':estado'=>$datos['estado'],':id'=>$id]);}
        return ['UPDATE pedidos SET id=id WHERE id=:id', []];
    }

    public function desactivar(string $modulo, int $id): void
    {
        $tabla = $this->tabla($modulo);
        if (!in_array($tabla, ['categorias', 'proveedores', 'clientes', 'cotizaciones', 'compras'], true)) {
            throw new \InvalidArgumentException('El módulo no admite desactivación.');
        }
        $sentencia = $this->pdo()->prepare("UPDATE {$tabla} SET activo = 0 WHERE id = :id");
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();
        if ($sentencia->rowCount() === 0) {
            throw new \RuntimeException('El registro solicitado no existe o ya está inactivo.');
        }
    }

    /** @return array<string, int|float> */
    public function resumen(): array
    {
        $pdo = $this->pdo();

        return [
            'productos' => (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE activo=1')->fetchColumn(),
            'existencias' => (int) $pdo->query('SELECT COALESCE(SUM(existencias),0) FROM productos WHERE activo=1')->fetchColumn(),
            'stock_bajo' => (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE activo=1 AND existencias<=8')->fetchColumn(),
            'pedidos' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
            'clientes' => (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol IN ('cliente_minorista','cliente_mayorista')")->fetchColumn(),
            'ventas' => (float) $pdo->query('SELECT COALESCE(SUM(total),0) FROM pedidos')->fetchColumn(),
        ];
    }

    /** @return array<string, mixed> */
    public function dashboardCompras(int $meses = 6): array
    {
        $meses = max(3, min(12, $meses));
        $pdo = $this->pdo();
        $tendencia = [];
        try {
            $q = $pdo->prepare("SELECT DATE_FORMAT(fecha_compra, '%Y-%m') periodo, COALESCE(SUM(total),0) total, COUNT(*) operaciones FROM compras WHERE activo=1 AND fecha_compra >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH) GROUP BY DATE_FORMAT(fecha_compra, '%Y-%m') ORDER BY periodo");
            $q->bindValue(':meses', $meses, PDO::PARAM_INT); $q->execute(); $tendencia = $q->fetchAll();
        } catch (\Throwable) { $tendencia = []; }
        $proveedores = [];
        try {
            $q = $pdo->query("SELECT p.nombre, COUNT(c.id) operaciones, COALESCE(SUM(c.total),0) total FROM proveedores p LEFT JOIN compras c ON c.proveedor_id=p.id AND c.activo=1 WHERE p.activo=1 GROUP BY p.id,p.nombre HAVING operaciones>0 ORDER BY total DESC LIMIT 4");
            $proveedores = $q->fetchAll();
        } catch (\Throwable) { $proveedores = []; }
        return ['tendencia'=>$tendencia, 'proveedores'=>$proveedores];
    }

    /** @return array<int, array<string,mixed>> */
    public function actividadRecienteCompras(int $limite = 5): array
    {
        $limite = max(1, min(10, $limite));
        $sql = "SELECT * FROM (
            SELECT pe.creado_en fecha, CONCAT('#PED-',LPAD(pe.id,4,'0')) codigo,
                   COALESCE(u.nombre,'Cliente') responsable, 'Pedido registrado' actividad,
                   pe.total total, pe.estado estado
            FROM pedidos pe LEFT JOIN usuarios u ON u.id=pe.usuario_id
            UNION ALL
            SELECT c.fecha_compra fecha, CONCAT('#COM-',LPAD(c.id,4,'0')) codigo,
                   COALESCE(p.nombre,'Proveedor') responsable, 'Compra a proveedor' actividad,
                   c.total total, c.estado estado
            FROM compras c LEFT JOIN proveedores p ON p.id=c.proveedor_id WHERE c.activo=1
            UNION ALL
            SELECT m.creado_en fecha, CONCAT('#MOV-',LPAD(m.id,4,'0')) codigo,
                   COALESCE(u.nombre,'Sistema') responsable,
                   CONCAT('Movimiento de stock: ',m.tipo_movimiento) actividad,
                   0 total, m.tipo_movimiento estado
            FROM movimientos_inventario m LEFT JOIN usuarios u ON u.id=m.usuario_id
        ) actividad ORDER BY fecha DESC LIMIT {$limite}";
        try { return $this->pdo()->query($sql)->fetchAll(); } catch (\Throwable) { return []; }
    }

    /** @return array<int, array<string,mixed>> */
    public function prioridadesCompras(): array
    {
        $pdo = $this->pdo(); $salida = [];
        try {
            $q=$pdo->query("SELECT COUNT(*) cantidad FROM productos WHERE activo=1 AND existencias<3");
            $n=(int)$q->fetchColumn(); if($n>0) $salida[]=['titulo'=>'Stock crítico','detalle'=>$n.' producto'.($n===1?'':'s').' con menos de 3 unidades','tono'=>'rojo','modulo'=>'alertas-stock'];
        } catch (\Throwable) {}
        try {
            $q=$pdo->query("SELECT COUNT(*) FROM compras WHERE activo=1 AND LOWER(estado) IN ('pendiente','en proceso')");
            $n=(int)$q->fetchColumn(); if($n>0) $salida[]=['titulo'=>'Compras pendientes','detalle'=>$n.' compra'.($n===1?'':'s').' por gestionar','tono'=>'ambar','modulo'=>'compras'];
        } catch (\Throwable) {}
        try {
            $q=$pdo->query("SELECT COUNT(*) FROM pedidos WHERE LOWER(estado)='empacado'");
            $n=(int)$q->fetchColumn(); if($n>0) $salida[]=['titulo'=>'Pedidos listos para entrega','detalle'=>$n.' pedido'.($n===1?'':'s').' empacado'.($n===1?'':'s'),'tono'=>'verde','modulo'=>'preparacion-pedidos'];
        } catch (\Throwable) {}
        try {
            $q=$pdo->query("SELECT COUNT(*) FROM pedidos WHERE LOWER(estado)='pendiente'");
            $n=(int)$q->fetchColumn(); if($n>0) $salida[]=['titulo'=>'Pedidos por preparar','detalle'=>$n.' pedido'.($n===1?'':'s').' pendiente'.($n===1?'':'s'),'tono'=>'azul','modulo'=>'preparacion-pedidos'];
        } catch (\Throwable) {}
        return array_slice($salida,0,4);
    }

    /** @return array<int, array{id:int,etiqueta:string}> */
    public function opciones(string $tipo): array
    {
        $sql = match ($tipo) {
            'productos' => "SELECT id, CONCAT(marca, ' ', nombre, ' · stock ', existencias) AS etiqueta FROM productos WHERE activo=1 ORDER BY nombre",
            'proveedores' => "SELECT id, nombre AS etiqueta FROM proveedores WHERE activo=1 ORDER BY nombre",
            'clientes' => "SELECT id, COALESCE(NULLIF(razon_social,''), nombre_contacto) AS etiqueta FROM clientes WHERE activo=1 ORDER BY etiqueta",
            default => throw new \InvalidArgumentException('Tipo de opción no permitido.'),
        };

        return $this->pdo()->query($sql)->fetchAll();
    }

    /** @param array<string, mixed> $parametros */
    private function insertar(string $sql, array $parametros): int
    {
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->execute($parametros);

        return (int) $this->pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $datos */
    private function registrarMovimiento(array $datos, int $usuarioId): int
    {
        return (int) $this->conexion->transaccion(function () use ($datos, $usuarioId): int {
            $pdo = $this->pdo();
            $productoId = (int) $datos['producto_id'];
            $cantidad = (int) $datos['cantidad'];
            $tipo = (string) $datos['tipo_movimiento'];
            $consulta = $pdo->prepare('SELECT existencias FROM productos WHERE id=:id AND activo=1 FOR UPDATE');
            $consulta->execute([':id' => $productoId]);
            $existenciasActuales = $consulta->fetchColumn();
            if ($existenciasActuales === false) {
                throw new \RuntimeException('El producto seleccionado no existe.');
            }
            $nuevoStock = match ($tipo) {
                'entrada' => (int) $existenciasActuales + $cantidad,
                'salida' => (int) $existenciasActuales - $cantidad,
                'ajuste' => $cantidad,
                default => throw new \InvalidArgumentException('Tipo de movimiento inválido.'),
            };
            if ($nuevoStock < 0) {
                throw new \RuntimeException('El movimiento dejaría el stock en un valor negativo.');
            }
            $movimiento = $pdo->prepare('INSERT INTO movimientos_inventario(producto_id,usuario_id,tipo_movimiento,cantidad,notas) VALUES(:producto,:usuario,:tipo,:cantidad,:notas)');
            $movimiento->execute([':producto' => $productoId, ':usuario' => $usuarioId, ':tipo' => $tipo, ':cantidad' => $cantidad, ':notas' => $datos['notas']]);
            $actualizacion = $pdo->prepare('UPDATE productos SET existencias=:existencias WHERE id=:id');
            $actualizacion->execute([':existencias' => $nuevoStock, ':id' => $productoId]);

            return (int) $pdo->lastInsertId();
        });
    }

    private function existe(string $tabla, int $id): bool
    {
        $permitidas = ['categorias', 'proveedores', 'clientes', 'cotizaciones', 'compras', 'pedidos'];
        if (!in_array($tabla, $permitidas, true)) {
            return false;
        }
        $sentencia = $this->pdo()->prepare("SELECT COUNT(*) FROM {$tabla} WHERE id=:id");
        $sentencia->execute([':id' => $id]);

        return (int) $sentencia->fetchColumn() > 0;
    }

    private function tabla(string $modulo): string
    {
        return match ($modulo) {
            'categorias' => 'categorias',
            'proveedores' => 'proveedores',
            'clientes', 'clientes-mayoristas' => 'clientes',
            'cotizaciones' => 'cotizaciones',
            'compras' => 'compras',
            'pedidos', 'pedidos-mayoristas', 'preparacion-pedidos', 'logistica-mayorista' => 'pedidos',
            default => '',
        };
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
