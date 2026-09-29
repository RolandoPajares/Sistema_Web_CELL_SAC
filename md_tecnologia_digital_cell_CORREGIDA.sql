-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-09-2026 a las 17:51:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

-- BASE DE DATOS CORREGIDA PARA XAMPP / MariaDB
-- Crea y selecciona automaticamente la base de datos esperada por el sistema.
CREATE DATABASE IF NOT EXISTS `md_tecnologia_digital_cell`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `md_tecnologia_digital_cell`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `md_tecnologia_digital_cell`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanas_publicitarias`
--

CREATE TABLE `campanas_publicitarias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `ubicacion` enum('emergente','lateral','barra_superior') NOT NULL DEFAULT 'emergente',
  `titulo` varchar(160) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `texto_boton` varchar(80) NOT NULL DEFAULT 'Ver oferta',
  `url_boton` varchar(255) NOT NULL DEFAULT 'catalog',
  `url_imagen` varchar(255) DEFAULT NULL,
  `precio_anterior` decimal(10,2) DEFAULT NULL,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `inicia_en` datetime NOT NULL,
  `finaliza_en` datetime NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `vistas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `clics` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `campanas_publicitarias`
--

INSERT INTO `campanas_publicitarias` (`id`, `nombre`, `ubicacion`, `titulo`, `descripcion`, `texto_boton`, `url_boton`, `url_imagen`, `precio_anterior`, `precio_oferta`, `inicia_en`, `finaliza_en`, `activo`, `vistas`, `clics`, `creado_en`, `actualizado_en`) VALUES
(1, 'Smart Week', 'emergente', 'SMART WEEK: ofertas que vuelan', 'Encuentra celulares seleccionados con precios especiales por tiempo limitado.', 'Ver ofertas', 'catalog', 'assets/img/showcase1.jpg', 1599.00, 1449.00, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1, 2, 0, '2026-09-27 17:46:03', '2026-09-28 15:11:21'),
(2, 'Accesorios inteligentes', 'lateral', 'Completa tu compra', 'Descubre accesorios y productos destacados para acompanar tu nuevo equipo.', 'Explorar catalogo', 'catalog', 'assets/img/showcase3.jpg', NULL, NULL, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1, 0, 0, '2026-09-27 17:46:03', '2026-09-27 17:46:03'),
(3, 'Envio y promociones', 'barra_superior', 'SMART WEEK | Ofertas especiales en tecnologia', 'Promociones activas por tiempo limitado.', 'Ver ahora', 'catalog', NULL, NULL, NULL, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1, 0, 0, '2026-09-27 17:46:03', '2026-09-27 17:46:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `activo`, `creado_en`) VALUES
(1, 'Celulares', 'Smartphones y equipos moviles', 1, '2026-09-27 17:46:02'),
(2, 'Audio', 'Audifonos, parlantes y accesorios de audio', 1, '2026-09-27 17:46:02'),
(3, 'Accesorios', 'Cargadores, cables, fundas y complementos', 1, '2026-09-27 17:46:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` enum('minorista','mayorista') NOT NULL DEFAULT 'minorista',
  `documento` varchar(20) NOT NULL,
  `razon_social` varchar(160) DEFAULT NULL,
  `nombre_contacto` varchar(160) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `usuario_id`, `tipo`, `documento`, `razon_social`, `nombre_contacto`, `correo`, `telefono`, `ciudad`, `activo`, `creado_en`) VALUES
(1, NULL, 'mayorista', '20456789123', 'Tecno Norte EIRL', 'Maria Lopez', 'compras@tecnonorte.demo', '986222119', 'Trujillo', 1, '2026-09-27 17:46:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Pendiente','Aprobada','Recibida','Cancelada') NOT NULL DEFAULT 'Pendiente',
  `fecha_compra` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Borrador','Enviada','Aprobada','Rechazada') NOT NULL DEFAULT 'Borrador',
  `notas` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `contacto` varchar(160) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'Nuevo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migraciones`
--

CREATE TABLE `migraciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `migracion` varchar(255) NOT NULL,
  `lote` int(10) UNSIGNED NOT NULL,
  `ejecutada_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migraciones`
--

INSERT INTO `migraciones` (`id`, `migracion`, `lote`, `ejecutada_en`) VALUES
(1, '000001_crear_base_datos_espanol.sql', 1, '2026-09-27 17:46:03'),
(3, '2026_09_27_000001_esquema_espanol.sql', 2, '2026-09-27 18:18:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `notas` varchar(500) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(40) NOT NULL DEFAULT 'Pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles_inteligentes_productos`
--

CREATE TABLE `perfiles_inteligentes_productos` (
  `producto_id` int(11) NOT NULL,
  `puntuacion_rendimiento` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `puntuacion_camara` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `puntuacion_bateria` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `puntuacion_valor` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `marca` varchar(80) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `categoria` varchar(80) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `existencias` int(11) NOT NULL DEFAULT 0,
  `almacenamiento` varchar(80) DEFAULT NULL,
  `color` varchar(80) DEFAULT NULL,
  `etiqueta` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `marca`, `nombre`, `categoria`, `precio`, `existencias`, `almacenamiento`, `color`, `etiqueta`, `descripcion`, `activo`, `creado_en`) VALUES
(1, 'Samsung', 'Cargador 25W USB-C', 'Accesorio', 89.00, 30, 'USB-C', 'Negro', 'Compatible', 'Cargador de carga rapida recomendado para equipos Samsung compatibles.', 1, '2026-09-27 17:46:03'),
(2, 'Samsung', 'Funda Galaxy A Series', 'Accesorio', 39.00, 25, 'Galaxy A', 'Transparente', 'Combo', 'Funda protectora para modelos seleccionados de la familia Galaxy A.', 1, '2026-09-27 17:46:03'),
(3, 'Xiaomi', 'Cargador Turbo USB-C', 'Accesorio', 79.00, 28, 'USB-C', 'Blanco', 'Compatible', 'Cargador rapido para smartphones Xiaomi y Redmi compatibles.', 1, '2026-09-27 17:46:03'),
(4, 'Apple', 'Cable USB-C trenzado', 'Accesorio', 99.00, 22, 'USB-C', 'Blanco', 'Original', 'Cable USB-C para carga y sincronizacion de dispositivos compatibles.', 1, '2026-09-27 17:46:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `ruc`, `correo`, `telefono`, `ciudad`, `activo`, `creado_en`) VALUES
(1, 'Distribuidora Andina SAC', '20123456789', 'ventas@andina.demo', '987333221', 'Lima', 1, '2026-09-27 17:46:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_auditoria`
--

CREATE TABLE `registros_auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `entidad` varchar(80) NOT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_nuevos`)),
  `direccion_ip` varchar(45) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('cliente_minorista','cliente_mayorista','administrador','compras_logistica','ventas_mayoristas','ventas_minoristas','marketing') NOT NULL DEFAULT 'cliente_minorista',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `rol`, `creado_en`) VALUES
(1, 'Administrador Demo', 'admin@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'administrador', '2026-09-27 17:46:02'),
(2, 'Compras Demo', 'compras@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'compras_logistica', '2026-09-27 17:46:02'),
(3, 'Ventas B2B Demo', 'b2b@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'ventas_mayoristas', '2026-09-27 17:46:02'),
(4, 'Ventas B2C Demo', 'b2c@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'ventas_minoristas', '2026-09-27 17:46:02'),
(5, 'Marketing Demo', 'marketing@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'marketing', '2026-09-27 17:46:02'),
(6, 'Cliente Minorista Demo', 'minorista@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'cliente_minorista', '2026-09-27 17:46:02'),
(7, 'Cliente Mayorista Demo', 'mayorista@md.demo', '$2y$10$FpENJhPlwsFNKpDia8634eObGaYl/WmtDgoDSMFeiYsPFnSmPs07e', 'cliente_mayorista', '2026-09-27 17:46:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `campanas_publicitarias`
--
ALTER TABLE `campanas_publicitarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_campanas_activas_fechas` (`activo`,`inicia_en`,`finaliza_en`),
  ADD KEY `indice_campanas_ubicacion` (`ubicacion`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documento` (`documento`),
  ADD KEY `indice_clientes_tipo_activo` (`tipo`,`activo`),
  ADD KEY `fk_clientes_usuario` (`usuario_id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_compras_estado_fecha` (`estado`,`fecha_compra`),
  ADD KEY `fk_compras_proveedor` (`proveedor_id`),
  ADD KEY `fk_compras_usuario` (`creado_por`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_cotizaciones_estado_fecha` (`estado`,`creado_en`),
  ADD KEY `fk_cotizaciones_cliente` (`cliente_id`),
  ADD KEY `fk_cotizaciones_usuario` (`creado_por`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_detalle_pedidos_producto` (`producto_id`),
  ADD KEY `fk_detalle_pedidos_pedido` (`pedido_id`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_contacto_estado_fecha` (`estado`,`creado_en`);

--
-- Indices de la tabla `migraciones`
--
ALTER TABLE `migraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migracion` (`migracion`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_movimientos_producto_fecha` (`producto_id`,`creado_en`),
  ADD KEY `fk_movimientos_usuario` (`usuario_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_pedidos_usuario_fecha` (`usuario_id`,`creado_en`),
  ADD KEY `indice_pedidos_estado_fecha` (`estado`,`creado_en`);

--
-- Indices de la tabla `perfiles_inteligentes_productos`
--
ALTER TABLE `perfiles_inteligentes_productos`
  ADD PRIMARY KEY (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_productos_catalogo` (`activo`,`categoria`,`marca`,`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD KEY `indice_proveedores_activos_nombre` (`activo`,`nombre`);

--
-- Indices de la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indice_auditoria_usuario_fecha` (`usuario_id`,`creado_en`),
  ADD KEY `indice_auditoria_entidad` (`entidad`,`entidad_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `campanas_publicitarias`
--
ALTER TABLE `campanas_publicitarias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migraciones`
--
ALTER TABLE `migraciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `fk_cotizaciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `fk_cotizaciones_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `fk_detalle_pedidos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `fk_detalle_pedidos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `perfiles_inteligentes_productos`
--
ALTER TABLE `perfiles_inteligentes_productos`
  ADD CONSTRAINT `fk_perfiles_inteligentes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- =====================================================
-- ACTUALIZACION FINAL SISTEMA CELL SAC
-- VARIANTES DE PRODUCTO
-- =====================================================

CREATE TABLE IF NOT EXISTS producto_variantes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 producto_id INT NOT NULL,
 nombre_color VARCHAR(100) NOT NULL DEFAULT 'Único',
 codigo_color VARCHAR(20) DEFAULT NULL,
 stock INT NOT NULL DEFAULT 0,
 estado ENUM('activo','inactivo') DEFAULT 'activo',
 fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_producto_variantes_producto
 FOREIGN KEY(producto_id) REFERENCES productos(id)
 ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS variante_imagenes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 variante_id INT NOT NULL,
 ruta_imagen VARCHAR(255) NOT NULL,
 imagen_principal BOOLEAN DEFAULT FALSE,
 orden INT DEFAULT 0,
 fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_variante_imagenes_variante
 FOREIGN KEY(variante_id) REFERENCES producto_variantes(id)
 ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS producto_caracteristicas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 producto_id INT NOT NULL,
 nombre VARCHAR(100) NOT NULL,
 valor VARCHAR(255) NOT NULL,
 CONSTRAINT fk_producto_caracteristicas_producto
 FOREIGN KEY(producto_id) REFERENCES productos(id)
 ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO producto_variantes(producto_id,nombre_color,codigo_color,stock)
SELECT p.id, COALESCE(NULLIF(p.color,''),'Único'), NULL, p.existencias
FROM productos p
WHERE NOT EXISTS (
 SELECT 1 FROM producto_variantes pv WHERE pv.producto_id=p.id
);

CREATE INDEX idx_producto_variantes_producto ON producto_variantes(producto_id);
CREATE INDEX idx_variante_imagenes_variante ON variante_imagenes(variante_id);
CREATE INDEX idx_producto_caracteristicas_producto ON producto_caracteristicas(producto_id);


CREATE TABLE IF NOT EXISTS producto_imagen_referencia (
 producto_id INT NOT NULL PRIMARY KEY, ruta_imagen VARCHAR(255) NOT NULL, actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_ref_producto FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
