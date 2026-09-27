-- ============================================================
-- MD Technology Digital Cell
-- Base de datos con tablas, campos, indices y restricciones en espanol
-- Version adaptada desde el esquema actual del proyecto
-- ============================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS md_tecnologia_digital_cell
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE md_tecnologia_digital_cell;

-- ------------------------------------------------------------
-- 1. CONTROL DE MIGRACIONES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS migraciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  migracion VARCHAR(255) NOT NULL UNIQUE,
  lote INT UNSIGNED NOT NULL,
  ejecutada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. USUARIOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(160) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  rol ENUM(
    'cliente_minorista',
    'cliente_mayorista',
    'administrador',
    'compras_logistica',
    'ventas_mayoristas',
    'ventas_minoristas',
    'marketing'
  ) NOT NULL DEFAULT 'cliente_minorista',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. PRODUCTOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  marca VARCHAR(80) NOT NULL,
  nombre VARCHAR(160) NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0,
  existencias INT NOT NULL DEFAULT 0,
  almacenamiento VARCHAR(80) NULL,
  color VARCHAR(80) NULL,
  etiqueta VARCHAR(80) NULL,
  descripcion TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_productos_catalogo (activo, categoria, marca, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. PEDIDOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  estado VARCHAR(40) NOT NULL DEFAULT 'Pendiente',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_pedidos_usuario_fecha (usuario_id, creado_en),
  INDEX indice_pedidos_estado_fecha (estado, creado_en),
  CONSTRAINT fk_pedidos_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. DETALLE DE PEDIDOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS detalle_pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  INDEX indice_detalle_pedidos_producto (producto_id),
  CONSTRAINT fk_detalle_pedidos_pedido
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
  CONSTRAINT fk_detalle_pedidos_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. REGISTROS DE AUDITORIA
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS registros_auditoria (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  accion VARCHAR(80) NOT NULL,
  entidad VARCHAR(80) NOT NULL,
  entidad_id INT NULL,
  valores_anteriores JSON NULL,
  valores_nuevos JSON NULL,
  direccion_ip VARCHAR(45) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_auditoria_usuario_fecha (usuario_id, creado_en),
  INDEX indice_auditoria_entidad (entidad, entidad_id),
  CONSTRAINT fk_auditoria_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. MENSAJES DE CONTACTO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mensajes_contacto (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  contacto VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'Nuevo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_contacto_estado_fecha (estado, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. CAMPANAS PUBLICITARIAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS campanas_publicitarias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  ubicacion ENUM('emergente', 'lateral', 'barra_superior') NOT NULL DEFAULT 'emergente',
  titulo VARCHAR(160) NOT NULL,
  descripcion VARCHAR(500) NULL,
  texto_boton VARCHAR(80) NOT NULL DEFAULT 'Ver oferta',
  url_boton VARCHAR(255) NOT NULL DEFAULT 'catalog',
  url_imagen VARCHAR(255) NULL,
  precio_anterior DECIMAL(10,2) NULL,
  precio_oferta DECIMAL(10,2) NULL,
  inicia_en DATETIME NOT NULL,
  finaliza_en DATETIME NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  vistas INT UNSIGNED NOT NULL DEFAULT 0,
  clics INT UNSIGNED NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX indice_campanas_activas_fechas (activo, inicia_en, finaliza_en),
  INDEX indice_campanas_ubicacion (ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. PERFILES INTELIGENTES DE PRODUCTOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfiles_inteligentes_productos (
  producto_id INT PRIMARY KEY,
  puntuacion_rendimiento TINYINT UNSIGNED NOT NULL DEFAULT 70,
  puntuacion_camara TINYINT UNSIGNED NOT NULL DEFAULT 70,
  puntuacion_bateria TINYINT UNSIGNED NOT NULL DEFAULT 70,
  puntuacion_valor TINYINT UNSIGNED NOT NULL DEFAULT 70,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_perfiles_inteligentes_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. CATEGORIAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  descripcion VARCHAR(500) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. PROVEEDORES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(160) NOT NULL,
  ruc VARCHAR(20) NOT NULL UNIQUE,
  correo VARCHAR(160) NOT NULL,
  telefono VARCHAR(30) NULL,
  ciudad VARCHAR(80) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_proveedores_activos_nombre (activo, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. CLIENTES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  tipo ENUM('minorista', 'mayorista') NOT NULL DEFAULT 'minorista',
  documento VARCHAR(20) NOT NULL UNIQUE,
  razon_social VARCHAR(160) NULL,
  nombre_contacto VARCHAR(160) NOT NULL,
  correo VARCHAR(160) NOT NULL,
  telefono VARCHAR(30) NULL,
  ciudad VARCHAR(80) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_clientes_tipo_activo (tipo, activo),
  CONSTRAINT fk_clientes_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. COTIZACIONES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cotizaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  creado_por INT NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado ENUM('Borrador', 'Enviada', 'Aprobada', 'Rechazada') NOT NULL DEFAULT 'Borrador',
  notas VARCHAR(500) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_cotizaciones_estado_fecha (estado, creado_en),
  CONSTRAINT fk_cotizaciones_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_cotizaciones_usuario
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 14. COMPRAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS compras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proveedor_id INT NOT NULL,
  creado_por INT NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado ENUM('Pendiente', 'Aprobada', 'Recibida', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
  fecha_compra DATE NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_compras_estado_fecha (estado, fecha_compra),
  CONSTRAINT fk_compras_proveedor
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
  CONSTRAINT fk_compras_usuario
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 15. MOVIMIENTOS DE INVENTARIO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS movimientos_inventario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  usuario_id INT NULL,
  tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
  cantidad INT NOT NULL,
  notas VARCHAR(500) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX indice_movimientos_producto_fecha (producto_id, creado_en),
  CONSTRAINT fk_movimientos_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT fk_movimientos_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

INSERT IGNORE INTO categorias (nombre, descripcion) VALUES
  ('Celulares', 'Smartphones y equipos moviles'),
  ('Audio', 'Audifonos, parlantes y accesorios de audio'),
  ('Accesorios', 'Cargadores, cables, fundas y complementos');

INSERT INTO proveedores (nombre, ruc, correo, telefono, ciudad)
SELECT 'Distribuidora Andina SAC', '20123456789', 'ventas@andina.demo', '987333221', 'Lima'
WHERE NOT EXISTS (
  SELECT 1 FROM proveedores WHERE ruc = '20123456789'
);

INSERT INTO clientes (tipo, documento, razon_social, nombre_contacto, correo, telefono, ciudad)
SELECT 'mayorista', '20456789123', 'Tecno Norte EIRL', 'Maria Lopez', 'compras@tecnonorte.demo', '986222119', 'Trujillo'
WHERE NOT EXISTS (
  SELECT 1 FROM clientes WHERE documento = '20456789123'
);

-- Usuarios de demostracion. Las contrasenas se almacenan como hashes.
INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Administrador Demo', 'admin@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'administrador'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'admin@md.demo');

INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Compras Demo', 'compras@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'compras_logistica'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'compras@md.demo');

INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Ventas B2B Demo', 'b2b@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'ventas_mayoristas'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'b2b@md.demo');

INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Ventas B2C Demo', 'b2c@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'ventas_minoristas'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'b2c@md.demo');

INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Marketing Demo', 'marketing@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'marketing'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'marketing@md.demo');

INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Cliente Minorista Demo', 'minorista@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'cliente_minorista'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'minorista@md.demo');

INSERT INTO usuarios (nombre, correo, contrasena, rol)
SELECT 'Cliente Mayorista Demo', 'mayorista@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'cliente_mayorista'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'mayorista@md.demo');

-- Campanas iniciales.
INSERT INTO campanas_publicitarias
  (nombre, ubicacion, titulo, descripcion, texto_boton, url_boton, url_imagen,
   precio_anterior, precio_oferta, inicia_en, finaliza_en, activo)
SELECT
  'Smart Week', 'emergente', 'SMART WEEK: ofertas que vuelan',
  'Encuentra celulares seleccionados con precios especiales por tiempo limitado.',
  'Ver ofertas', 'catalog', 'assets/img/showcase1.jpg',
  1599.00, 1449.00, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1
WHERE NOT EXISTS (
  SELECT 1 FROM campanas_publicitarias WHERE nombre = 'Smart Week'
);

INSERT INTO campanas_publicitarias
  (nombre, ubicacion, titulo, descripcion, texto_boton, url_boton, url_imagen,
   precio_anterior, precio_oferta, inicia_en, finaliza_en, activo)
SELECT
  'Accesorios inteligentes', 'lateral', 'Completa tu compra',
  'Descubre accesorios y productos destacados para acompanar tu nuevo equipo.',
  'Explorar catalogo', 'catalog', 'assets/img/showcase3.jpg',
  NULL, NULL, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1
WHERE NOT EXISTS (
  SELECT 1 FROM campanas_publicitarias WHERE nombre = 'Accesorios inteligentes'
);

INSERT INTO campanas_publicitarias
  (nombre, ubicacion, titulo, descripcion, texto_boton, url_boton, url_imagen,
   precio_anterior, precio_oferta, inicia_en, finaliza_en, activo)
SELECT
  'Envio y promociones', 'barra_superior', 'SMART WEEK | Ofertas especiales en tecnologia',
  'Promociones activas por tiempo limitado.',
  'Ver ahora', 'catalog', NULL,
  NULL, NULL, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1
WHERE NOT EXISTS (
  SELECT 1 FROM campanas_publicitarias WHERE nombre = 'Envio y promociones'
);

-- Productos de demostracion usados por SmartCommerce.
INSERT INTO productos
  (marca, nombre, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo)
SELECT
  'Samsung', 'Cargador 25W USB-C', 'Accesorio', 89.00, 30,
  'USB-C', 'Negro', 'Compatible',
  'Cargador de carga rapida recomendado para equipos Samsung compatibles.', 1
WHERE NOT EXISTS (
  SELECT 1 FROM productos WHERE nombre = 'Cargador 25W USB-C'
);

INSERT INTO productos
  (marca, nombre, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo)
SELECT
  'Samsung', 'Funda Galaxy A Series', 'Accesorio', 39.00, 25,
  'Galaxy A', 'Transparente', 'Combo',
  'Funda protectora para modelos seleccionados de la familia Galaxy A.', 1
WHERE NOT EXISTS (
  SELECT 1 FROM productos WHERE nombre = 'Funda Galaxy A Series'
);

INSERT INTO productos
  (marca, nombre, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo)
SELECT
  'Xiaomi', 'Cargador Turbo USB-C', 'Accesorio', 79.00, 28,
  'USB-C', 'Blanco', 'Compatible',
  'Cargador rapido para smartphones Xiaomi y Redmi compatibles.', 1
WHERE NOT EXISTS (
  SELECT 1 FROM productos WHERE nombre = 'Cargador Turbo USB-C'
);

INSERT INTO productos
  (marca, nombre, categoria, precio, existencias, almacenamiento, color, etiqueta, descripcion, activo)
SELECT
  'Apple', 'Cable USB-C trenzado', 'Accesorio', 99.00, 22,
  'USB-C', 'Blanco', 'Original',
  'Cable USB-C para carga y sincronizacion de dispositivos compatibles.', 1
WHERE NOT EXISTS (
  SELECT 1 FROM productos WHERE nombre = 'Cable USB-C trenzado'
);

-- Registro informativo de la migracion al esquema en espanol.
INSERT IGNORE INTO migraciones (migracion, lote) VALUES
  ('000001_crear_base_datos_espanol.sql', 1);

