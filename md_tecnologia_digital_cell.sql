CREATE DATABASE IF NOT EXISTS `md_tecnologia_digital_cell`
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `md_tecnologia_digital_cell`;

SET FOREIGN_KEY_CHECKS=0;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2026 a las 08:20:26
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
(1, 'Smart Week', 'emergente', 'SMART WEEK: ofertas que vuelan', 'Encuentra celulares seleccionados con precios especiales por tiempo limitado.', 'Ver ofertas', 'catalog', 'assets/img/showcase1.jpg', 1599.00, 1449.00, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1, 5, 1, '2026-09-27 17:46:03', '2026-09-29 20:27:24'),
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
-- Estructura de tabla para la tabla `logistica_mayorista`
--

CREATE TABLE `logistica_mayorista` (
  `pedido_id` int(11) NOT NULL,
  `exportador` varchar(160) DEFAULT NULL,
  `direccion_exportador` varchar(255) DEFAULT NULL,
  `contacto_exportador` varchar(160) DEFAULT NULL,
  `direccion_consignatario` varchar(255) DEFAULT NULL,
  `contacto_consignatario` varchar(160) DEFAULT NULL,
  `factura_numero` varchar(80) DEFAULT NULL,
  `factura_fecha` date DEFAULT NULL,
  `orden_compra` varchar(80) DEFAULT NULL,
  `carta_credito` varchar(100) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `pais_origen` varchar(100) DEFAULT NULL,
  `lugar_carga` varchar(160) DEFAULT NULL,
  `puerto_descarga` varchar(160) DEFAULT NULL,
  `direccion_entrega` varchar(255) DEFAULT NULL,
  `descripcion_mercancia` text DEFAULT NULL,
  `codigo_hs` varchar(40) DEFAULT NULL,
  `cantidad_unidades` int(11) DEFAULT NULL,
  `bultos` varchar(160) DEFAULT NULL,
  `tipo_embalaje` varchar(100) DEFAULT NULL,
  `dimensiones` varchar(120) DEFAULT NULL,
  `volumen_m3` decimal(10,3) DEFAULT NULL,
  `peso_neto` decimal(10,2) DEFAULT NULL,
  `peso_bruto` decimal(10,2) DEFAULT NULL
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

--
-- Volcado de datos para la tabla `mensajes_contacto`
--

INSERT INTO `mensajes_contacto` (`id`, `nombre`, `contacto`, `mensaje`, `estado`, `creado_en`) VALUES
(1, 'Duver Pajares', 'pajaresduver@gmail.com', 'tiene disponible el hipone 17 pro max', 'Nuevo', '2026-09-29 20:47:01');

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
(3, '2026_09_27_000001_esquema_espanol.sql', 2, '2026-09-27 18:18:43'),
(16, '2026_09_28_000002_integrar_categorias_productos.sql', 3, '2026-09-29 21:56:05'),
(17, '2026_09_29_000001_campos_catalogo_publico.sql', 3, '2026-09-29 21:56:05'),
(18, '2026_09_29_000002_imagenes_productos.sql', 3, '2026-09-29 21:56:05'),
(19, '2026_09_29_000003_identificar_modelos_catalogo.sql', 3, '2026-09-29 21:56:05'),
(20, '2026_09_29_000004_relaciones_productos.sql', 3, '2026-09-29 21:56:05'),
(21, '2026_09_29_000005_catalogo_publico.sql', 3, '2026-09-29 21:56:05'),
(22, '2026_09_29_000006_rutas_imagenes_productos.sql', 3, '2026-09-29 21:56:05'),
(23, '2026_09_29_000007_enlazar_lente_macro.sql', 4, '2026-09-29 21:57:59'),
(24, '2026_09_29_000008_limitar_marcas_catalogo.sql', 5, '2026-09-29 22:35:36'),
(25, '2026_09_29_000009_catalogo_galerias_y_rutas.sql', 6, '2026-09-30 02:06:16'),
(26, '2026_09_30_000010_completar_catalogo_imagenes.sql', 7, '2026-09-30 05:46:07'),
(27, '2026_09_30_000011_precios_y_ofertas_catalogo.sql', 8, '2026-09-30 06:06:07'),
(28, '2026_09_30_000012_stock_catalogo_visible.sql', 9, '2026-09-30 06:10:29'),
(29, '2026_09_30_000013_variar_descuentos_catalogo.sql', 10, '2026-09-30 06:12:36');

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
  `categoria_id` int(11) NOT NULL,
  `categoria` varchar(80) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_original` decimal(10,2) DEFAULT NULL,
  `descuento` varchar(20) DEFAULT NULL,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `url_imagen` varchar(255) DEFAULT NULL,
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

INSERT INTO `productos` (`id`, `marca`, `nombre`, `categoria_id`, `categoria`, `precio`, `precio_original`, `descuento`, `precio_oferta`, `url_imagen`, `existencias`, `almacenamiento`, `color`, `etiqueta`, `descripcion`, `activo`, `creado_en`) VALUES
(1, 'Samsung', 'Cargador 25W USB-C', 3, 'Accesorios', 89.00, NULL, NULL, NULL, 'assets/img/productos/Accesorios/Apple/samsung-cargador-25w-usb-c-negro-frontal.jpg', 30, 'USB-C', 'Negro', 'Compatible', 'Cargador de carga rapida recomendado para equipos Samsung compatibles.', 1, '2026-09-27 17:46:03'),
(2, 'Samsung', 'Funda Galaxy A Series', 3, 'Accesorios', 39.00, NULL, NULL, NULL, NULL, 25, 'Galaxy A', 'Transparente', 'Combo', 'Funda protectora para modelos seleccionados de la familia Galaxy A.', 0, '2026-09-27 17:46:03'),
(3, 'Xiaomi', 'Cargador Turbo USB-C', 3, 'Accesorios', 79.00, NULL, NULL, NULL, NULL, 28, 'USB-C', 'Blanco', 'Compatible', 'Cargador rapido para smartphones Xiaomi y Redmi compatibles.', 0, '2026-09-27 17:46:03'),
(4, 'Apple', 'Cable USB-C trenzado', 3, 'Accesorios', 99.00, NULL, NULL, NULL, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-60w-1m-frontal.jpg', 22, 'USB-C', 'Blanco', 'Original', 'Cable USB-C para carga y sincronizacion de dispositivos compatibles.', 1, '2026-09-27 17:46:03'),
(9, 'Apple', 'iPhone 17 Pro', 1, 'Celulares', 4719.20, 5899.00, '-20%', 4719.20, 'assets/img/productos/Celulares/Apple/013-apple-iphone-17-pro-principal.jpg', 15, NULL, NULL, 'Oferta', 'Apple iPhone 17 Pro con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(17, 'Samsung', 'Galaxy A57 5G', 1, 'Celulares', 1279.20, 1599.00, '-20%', 1279.20, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a57-5g-frontal.jpg', 15, NULL, NULL, 'Oferta', 'Samsung Galaxy A57 5G con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(18, 'Samsung', 'Galaxy A37', 1, 'Celulares', 959.20, 1199.00, '-20%', 959.20, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a37-5g-frontal.jpg', 15, NULL, NULL, 'Oferta', 'Samsung Galaxy A37 con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(26, 'Honor', 'Honor 200 Pro', 1, 'Celulares', 2159.20, 2699.00, '-20%', 2159.20, 'assets/img/productos/Celulares/Honor/024-honor-200-pro-principal.jpg', 15, NULL, NULL, 'Oferta', 'Honor 200 Pro con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(31, 'Samsung', 'Galaxy Buds3 Pro', 2, 'Audio', 719.20, 899.00, '-20%', 719.20, 'assets/img/productos/Audio/Samsung/084-samsung-galaxy-buds3-pro-principal.jpg', 15, NULL, NULL, 'Oferta', 'Galaxy Buds3 Pro con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(33, 'Xiaomi', 'Redmi Buds 5 Pro', 2, 'Audio', 239.20, 299.00, '-20%', 239.20, 'assets/img/productos/Audio/Xiaomi/115-xiaomi-buds-5-pro-principal.jpg', 15, NULL, NULL, 'Oferta', 'Redmi Buds 5 Pro con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(38, 'JBL', 'Tune 520BT Wireless', 2, 'Audio', 159.20, 199.00, '-20%', 159.20, 'assets/img/productos/Audio/JBL/jbl-tune-520bt-blanco-frontal.jpg', 15, NULL, NULL, 'Oferta', 'Tune 520BT Wireless con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(49, 'Apple', 'Cargador de Pared 20W USB-C', 3, 'Accesorios', 95.20, 119.00, '-20%', 95.20, 'assets/img/productos/Accesorios/Apple/apple-cargador-20w-usb-c-frontal.jpg', 15, NULL, NULL, 'Oferta', 'Cargador de Pared 20W USB-C con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(50, 'Apple', 'Cable Lightning a USB-C 1m', 3, 'Accesorios', 79.20, 99.00, '-20%', 79.20, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-a-lightning-1m-frontal.jpg', 15, NULL, NULL, 'Oferta', 'Cable Lightning a USB-C 1m con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(51, 'Samsung', 'Cargador Super Fast Charging 45W', 3, 'Accesorios', 119.20, 149.00, '-20%', 119.20, 'assets/img/productos/Accesorios/Apple/samsung-cargador-45w-usb-c-negro-frontal.jpg', 15, NULL, NULL, 'Oferta', 'Cargador Super Fast Charging 45W con descuento promocional -20%', 1, '2026-09-29 17:04:50'),
(69, 'Apple', 'AirPods 5', 2, 'Audio', 679.15, 799.00, '-15%', 679.15, 'assets/img/productos/Audio/Apple/009-apple-airpods-5-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(70, 'Apple', 'AirPods Max 2', 2, 'Audio', 2349.06, 2499.00, '-6%', 2349.06, 'assets/img/productos/Audio/Apple/011-apple-airpods-max-2-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(71, 'Apple', 'AirPods Pro 3', 2, 'Audio', 1103.08, 1199.00, '-8%', 1103.08, 'assets/img/productos/Audio/Apple/010-apple-airpods-pro-3-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(72, 'Apple', 'Airtag', 3, 'Accesorios', 134.10, 149.00, '-10%', 134.10, 'assets/img/productos/Accesorios/Apple/apple-airtag-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(73, 'Apple', 'Bateria MagSafe iPhone Air', 3, 'Accesorios', 439.12, 499.00, '-12%', 439.12, 'assets/img/productos/Accesorios/Apple/apple-bateria-magsafe-iphone-air-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(74, 'Apple', 'Cargador 35W Doble USB-C', 3, 'Accesorios', 194.65, 229.00, '-15%', 194.65, 'assets/img/productos/Accesorios/Apple/apple-cargador-35w-doble-usb-c-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(75, 'Apple', 'Cargador 40W Dynamic USB-C', 3, 'Accesorios', 234.06, 249.00, '-6%', 234.06, 'assets/img/productos/Accesorios/Apple/002-apple-40w-dynamic-power-adapter-with-60w-max-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(76, 'Apple', 'Cargador MagSafe 1M', 3, 'Accesorios', 183.08, 199.00, '-8%', 183.08, 'assets/img/productos/Accesorios/Apple/apple-cargador-magsafe-1m-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(77, 'Apple', 'Funda iPhone 16 MagSafe', 3, 'Accesorios', 179.10, 199.00, '-10%', 179.10, 'assets/img/productos/Audio/Apple/apple-funda-iphone-16-clear-magsafe-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(78, 'Apple', 'Funda iPhone 17 Silicona MagSafe', 3, 'Accesorios', 219.12, 249.00, '-12%', 219.12, 'assets/img/productos/Accesorios/Apple/apple-funda-iphone-17-silicona-magsafe-guava-trasera-2.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(79, 'Apple', 'iPhone 17', 1, 'Celulares', 3654.15, 4299.00, '-15%', 3654.15, 'assets/img/productos/Celulares/Apple/012-apple-iphone-17-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(80, 'Apple', 'iPhone 17 Clear Case With MagSafe', 3, 'Accesorios', 215.26, 229.00, '-6%', 215.26, 'assets/img/productos/Audio/Apple/003-apple-iphone-17-clear-case-with-magsafe-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(81, 'Apple', 'iPhone 17 Pro Max', 1, 'Celulares', 5979.08, 6499.00, '-8%', 5979.08, 'assets/img/productos/Celulares/Apple/014-apple-iphone-17-pro-max-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(82, 'Apple', 'iPhone 17 Pro Max Techwoven Case With MagSafe', 3, 'Accesorios', 269.10, 299.00, '-10%', 269.10, 'assets/img/productos/Accesorios/Apple/004-apple-iphone-17-pro-max-techwoven-case-with-magsafe-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(83, 'Apple', 'iPhone 17 Pro Techwoven Case With MagSafe', 3, 'Accesorios', 263.12, 299.00, '-12%', 263.12, 'assets/img/productos/Accesorios/Apple/005-apple-iphone-17-pro-techwoven-case-with-magsafe-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(84, 'Apple', 'iPhone 17 Silicone Case With MagSafe', 3, 'Accesorios', 211.65, 249.00, '-15%', 211.65, 'assets/img/productos/Accesorios/Apple/006-apple-iphone-17-silicone-case-with-magsafe-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(85, 'Apple', 'iPhone 17E', 1, 'Celulares', 2819.06, 2999.00, '-6%', 2819.06, 'assets/img/productos/Celulares/Apple/015-apple-iphone-17e-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(86, 'Apple', 'iPhone 18 Pro', 1, 'Celulares', 6439.08, 6999.00, '-8%', 6439.08, 'assets/img/productos/Celulares/Apple/016-apple-iphone-18-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(87, 'Apple', 'iPhone 18 Pro Max', 1, 'Celulares', 7199.10, 7999.00, '-10%', 7199.10, 'assets/img/productos/Celulares/Apple/017-apple-iphone-18-pro-max-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(88, 'Apple', 'iPhone Air', 1, 'Celulares', 4399.12, 4999.00, '-12%', 4399.12, 'assets/img/productos/Celulares/Apple/018-apple-iphone-air-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(89, 'Apple', 'iPhone Air Case With MagSafe', 3, 'Accesorios', 211.65, 249.00, '-15%', 211.65, 'assets/img/productos/Accesorios/Apple/007-apple-iphone-air-case-with-magsafe-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(90, 'Apple', 'iPhone Air MagSafe Battery', 3, 'Accesorios', 469.06, 499.00, '-6%', 469.06, 'assets/img/productos/Accesorios/Apple/008-apple-iphone-air-magsafe-battery-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(91, 'Apple', 'MagSafe Charger Qi2 25W', 3, 'Accesorios', 229.08, 249.00, '-8%', 229.08, 'assets/img/productos/Accesorios/Apple/001-apple-magsafe-charger-qi2-25w-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(92, 'Beats', 'Cable USB-C 3M', 3, 'Accesorios', 179.10, 199.00, '-10%', 179.10, 'assets/img/productos/Audio/JBL/beats-cable-usb-c-3m-azul-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(93, 'Beats', 'Solo 4', 2, 'Audio', 615.12, 699.00, '-12%', 615.12, 'assets/img/productos/Audio/JBL/beats-solo-4-rosa-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(94, 'Beats', 'Studio Pro', 2, 'Audio', 1104.15, 1299.00, '-15%', 1104.15, 'assets/img/productos/Audio/JBL/beats-studio-pro-arena-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(95, 'Honor', 'Honor 400', 1, 'Celulares', 1503.06, 1599.00, '-6%', 1503.06, 'assets/img/productos/Celulares/Honor/025-honor-400-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(96, 'Honor', 'Honor 400 Lite', 1, 'Celulares', 919.08, 999.00, '-8%', 919.08, 'assets/img/productos/Celulares/Honor/026-honor-400-lite-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(97, 'Honor', 'Honor 400 Pro', 1, 'Celulares', 2159.10, 2399.00, '-10%', 2159.10, 'assets/img/productos/Celulares/Honor/027-honor-400-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(98, 'Honor', 'Honor 600', 1, 'Celulares', 1671.12, 1899.00, '-12%', 1671.12, 'assets/img/productos/Celulares/Honor/028-honor-600-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(99, 'Honor', 'Honor 600 Lite', 1, 'Celulares', 1019.15, 1199.00, '-15%', 1019.15, 'assets/img/productos/Celulares/Honor/029-honor-600-lite-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(100, 'Honor', 'Honor 600 Pro', 1, 'Celulares', 2631.06, 2799.00, '-6%', 2631.06, 'assets/img/productos/Celulares/Honor/030-honor-600-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(101, 'Honor', 'Honor 600 Smart 5G', 1, 'Celulares', 919.08, 999.00, '-8%', 919.08, 'assets/img/productos/Celulares/Honor/031-honor-600-smart-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(102, 'Honor', 'Honor Choice Earbuds X', 2, 'Audio', 125.10, 139.00, '-10%', 125.10, 'assets/img/productos/Audio/Honor/honor-choice-earbuds-x-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(103, 'Honor', 'Honor Earbuds 4', 2, 'Audio', 175.12, 199.00, '-12%', 175.12, 'assets/img/productos/Audio/Honor/019-honor-earbuds-4-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(104, 'Honor', 'Honor Earbuds 5E', 2, 'Audio', 152.15, 179.00, '-15%', 152.15, 'assets/img/productos/Audio/Honor/020-honor-earbuds-5e-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(105, 'Honor', 'Honor Earbuds A Pro', 2, 'Audio', 140.06, 149.00, '-6%', 140.06, 'assets/img/productos/Audio/Honor/021-honor-earbuds-a-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(106, 'Honor', 'Honor Earbuds Open', 2, 'Audio', 551.08, 599.00, '-8%', 551.08, 'assets/img/productos/Audio/Honor/022-honor-earbuds-open-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(107, 'Honor', 'Honor Earbuds X10 Lite', 2, 'Audio', 116.10, 129.00, '-10%', 116.10, 'assets/img/productos/Audio/Honor/023-honor-earbuds-x10-lite-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(108, 'Honor', 'Honor Magic V5', 1, 'Celulares', 5719.12, 6499.00, '-12%', 5719.12, 'assets/img/productos/Celulares/Honor/032-honor-magic-v5-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(109, 'Honor', 'Honor Magic V6', 1, 'Celulares', 5949.15, 6999.00, '-15%', 5949.15, 'assets/img/productos/Celulares/Honor/033-honor-magic-v6-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(110, 'Honor', 'Honor Magic7 Pro', 1, 'Celulares', 3759.06, 3999.00, '-6%', 3759.06, 'assets/img/productos/Celulares/Honor/034-honor-magic7-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(111, 'Honor', 'Honor Magic8 Pro', 1, 'Celulares', 4139.08, 4499.00, '-8%', 4139.08, 'assets/img/productos/Celulares/Honor/036-honor-magic8-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(112, 'Honor', 'Honor Magic9 Pro Max', 1, 'Celulares', 4949.10, 5499.00, '-10%', 4949.10, 'assets/img/productos/Celulares/Honor/037-honor-magic9-pro-max-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(113, 'Honor', 'Honor Porsche Design Honor Magic7 Rsr', 1, 'Celulares', 7919.12, 8999.00, '-12%', 7919.12, 'assets/img/productos/Celulares/Honor/035-honor-porsche-design-honor-magic7-rsr-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(114, 'Honor', 'Honor Robot Phone', 1, 'Celulares', 4249.15, 4999.00, '-15%', 4249.15, 'assets/img/productos/Celulares/Honor/038-honor-robot-phone-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(115, 'Honor', 'Supercharge 66W', 3, 'Accesorios', 140.06, 149.00, '-6%', 140.06, 'assets/img/productos/Celulares/Honor/honor-supercharge-66w-perspectiva.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(116, 'Honor', 'Supercharge Wireless 100W', 3, 'Accesorios', 275.08, 299.00, '-8%', 275.08, 'assets/img/productos/Celulares/Honor/honor-supercharge-wireless-100w-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(117, 'JBL', 'Charge 6', 2, 'Audio', 449.10, 499.00, '-10%', 449.10, 'assets/img/productos/Audio/JBL/044-jbl-charge-6-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(118, 'JBL', 'Clip 5', 2, 'Audio', 201.52, 229.00, '-12%', 201.52, 'assets/img/productos/Audio/JBL/042-jbl-clip-5-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(119, 'JBL', 'Flip 7', 2, 'Audio', 339.15, 399.00, '-15%', 339.15, 'assets/img/productos/Audio/JBL/045-jbl-flip-7-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(120, 'JBL', 'Go 4', 2, 'Audio', 140.06, 149.00, '-6%', 140.06, 'assets/img/productos/Audio/Xiaomi/jbl-go-4-militar-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(121, 'JBL', 'Go 5', 2, 'Audio', 155.48, 169.00, '-8%', 155.48, 'assets/img/productos/Audio/JBL/043-jbl-go-5-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(122, 'JBL', 'Grip', 2, 'Audio', 224.10, 249.00, '-10%', 224.10, 'assets/img/productos/Audio/JBL/046-jbl-grip-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(123, 'JBL', 'Live 680Nc', 2, 'Audio', 615.12, 699.00, '-12%', 615.12, 'assets/img/productos/Audio/JBL/039-jbl-live-680nc-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(124, 'JBL', 'Live 780Nc', 2, 'Audio', 764.15, 899.00, '-15%', 764.15, 'assets/img/productos/Audio/JBL/040-jbl-live-780nc-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(125, 'JBL', 'Tour One M3', 2, 'Audio', 1315.06, 1399.00, '-6%', 1315.06, 'assets/img/productos/Audio/JBL/041-jbl-tour-one-m3-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(126, 'Oppo', '22 5W Magnetic Ring Power Bank', 3, 'Accesorios', 275.08, 299.00, '-8%', 275.08, 'assets/img/productos/Accesorios/Oppo/058-oppo-22-5w-magnetic-ring-power-bank-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(127, 'Oppo', 'AirVOOC 50W Magnetic Charger 2', 3, 'Accesorios', 269.10, 299.00, '-10%', 269.10, 'assets/img/productos/Accesorios/Oppo/047-oppo-airvooc-50w-magnetic-charger-2-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(128, 'Oppo', 'Cable SuperVOOC USB-C 8A 1M', 3, 'Accesorios', 87.12, 99.00, '-12%', 87.12, 'assets/img/productos/Accesorios/Oppo/oppo-cable-supervooc-usb-c-8a-1m-detalle-conectores.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(129, 'Oppo', 'Cable USB-C A USB-C SuperVOOC', 3, 'Accesorios', 67.15, 79.00, '-15%', 67.15, 'assets/img/productos/Accesorios/Apple/oppo-cable-usb-c-a-usb-c-supervooc-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(130, 'Oppo', 'Cargador Magnetico AirVOOC 50W', 3, 'Accesorios', 262.26, 279.00, '-6%', 262.26, 'assets/img/productos/Accesorios/Apple/oppo-cargador-magnetico-airvooc-50w-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(131, 'Oppo', 'Cargador SuperVOOC 100W Doble Puerto', 3, 'Accesorios', 321.08, 349.00, '-8%', 321.08, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-100w-doble-puerto-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(132, 'Oppo', 'Cargador SuperVOOC 45W', 3, 'Accesorios', 152.10, 169.00, '-10%', 152.10, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-45w-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(133, 'Oppo', 'Cargador SuperVOOC 80W', 3, 'Accesorios', 219.12, 249.00, '-12%', 219.12, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-80w-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(134, 'Oppo', 'Enco Buds2 Pro', 2, 'Audio', 169.15, 199.00, '-15%', 169.15, 'assets/img/productos/Audio/Oppo/oppo-enco-buds2-pro-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(135, 'Oppo', 'Enco Clip 2', 2, 'Audio', 516.06, 549.00, '-6%', 516.06, 'assets/img/productos/Celulares/Oppo/060-oppo-enco-clip2-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(136, 'Oppo', 'Enco X 3S', 2, 'Audio', 551.08, 599.00, '-8%', 551.08, 'assets/img/productos/Celulares/Oppo/061-oppo-enco-x3s-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(137, 'Oppo', 'Find N5', 1, 'Celulares', 6299.10, 6999.00, '-10%', 6299.10, 'assets/img/productos/Celulares/Oppo/063-oppo-find-n5-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(138, 'Oppo', 'Find X9', 1, 'Celulares', 3079.12, 3499.00, '-12%', 3079.12, 'assets/img/productos/Celulares/Oppo/064-oppo-find-x9-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(139, 'Oppo', 'Find X9 Light Luxury Magnetic Case', 3, 'Accesorios', 169.15, 199.00, '-15%', 169.15, 'assets/img/productos/Accesorios/Oppo/053-oppo-find-x9-light-luxury-magnetic-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(140, 'Oppo', 'Find X9 Pro', 1, 'Celulares', 4229.06, 4499.00, '-6%', 4229.06, 'assets/img/productos/Celulares/Oppo/065-oppo-find-x9-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(141, 'Oppo', 'Funda Find X9 Magnetica', 3, 'Accesorios', 183.08, 199.00, '-8%', 183.08, 'assets/img/productos/Accesorios/Apple/oppo-funda-find-x9-magnetica-gris-interior.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(142, 'Oppo', 'Funda Reno16 Magnetica', 3, 'Accesorios', 161.10, 179.00, '-10%', 161.10, 'assets/img/productos/Accesorios/Apple/oppo-funda-reno16-magnetica-con-telefono.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(143, 'Oppo', 'Magnetic Power Bank Air 5000MAH', 3, 'Accesorios', 263.12, 299.00, '-12%', 263.12, 'assets/img/productos/Accesorios/Oppo/059-oppo-magnetic-power-bank-air-5000mah-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(144, 'Oppo', 'Palo Selfie Magnetico 3 En 1', 3, 'Accesorios', 169.15, 199.00, '-15%', 169.15, 'assets/img/productos/Celulares/Apple/oppo-palo-selfie-magnetico-3-en-1-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(145, 'Oppo', 'Portable Wireless Speaker', 2, 'Audio', 281.06, 299.00, '-6%', 281.06, 'assets/img/productos/Audio/Oppo/062-oppo-portable-wireless-speaker-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(146, 'Oppo', 'Reno13 5G', 1, 'Celulares', 1747.08, 1899.00, '-8%', 1747.08, 'assets/img/productos/Celulares/Oppo/066-oppo-reno13-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(147, 'Oppo', 'Reno13 F', 1, 'Celulares', 989.10, 1099.00, '-10%', 989.10, 'assets/img/productos/Celulares/Oppo/067-oppo-reno13-f-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(148, 'Oppo', 'Reno13 F 5G', 1, 'Celulares', 1231.12, 1399.00, '-12%', 1231.12, 'assets/img/productos/Celulares/Oppo/068-oppo-reno13-f-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(149, 'Oppo', 'Reno13 Pro 5G', 1, 'Celulares', 2124.15, 2499.00, '-15%', 2124.15, 'assets/img/productos/Celulares/Oppo/069-oppo-reno13-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(150, 'Oppo', 'Reno14 5G', 1, 'Celulares', 2067.06, 2199.00, '-6%', 2067.06, 'assets/img/productos/Celulares/Oppo/070-oppo-reno14-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(151, 'Oppo', 'Reno14 F 5G', 1, 'Celulares', 1195.08, 1299.00, '-8%', 1195.08, 'assets/img/productos/Celulares/Oppo/071-oppo-reno14-f-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(152, 'Oppo', 'Reno14 F Gradient Blue Magnetic Case', 3, 'Accesorios', 161.10, 179.00, '-10%', 161.10, 'assets/img/productos/Accesorios/Oppo/054-oppo-reno14-f-gradient-blue-magnetic-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(153, 'Oppo', 'Reno14 Gradient Blue Magnetic Case', 3, 'Accesorios', 157.52, 179.00, '-12%', 157.52, 'assets/img/productos/Accesorios/Oppo/055-oppo-reno14-gradient-blue-magnetic-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(154, 'Oppo', 'Reno14 Pro 5G', 1, 'Celulares', 2379.15, 2799.00, '-15%', 2379.15, 'assets/img/productos/Celulares/Oppo/072-oppo-reno14-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(155, 'Oppo', 'Reno15 5G', 1, 'Celulares', 2255.06, 2399.00, '-6%', 2255.06, 'assets/img/productos/Celulares/Oppo/073-oppo-reno15-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(156, 'Oppo', 'Reno15 F 5G', 1, 'Celulares', 1379.08, 1499.00, '-8%', 1379.08, 'assets/img/productos/Celulares/Oppo/074-oppo-reno15-f-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(157, 'Oppo', 'Reno15 Pro 5G', 1, 'Celulares', 2699.10, 2999.00, '-10%', 2699.10, 'assets/img/productos/Celulares/Oppo/075-oppo-reno15-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(158, 'Oppo', 'Reno15 Pro Max 5G', 1, 'Celulares', 3255.12, 3699.00, '-12%', 3255.12, 'assets/img/productos/Celulares/Oppo/076-oppo-reno15-pro-max-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(159, 'Oppo', 'Reno16', 1, 'Celulares', 2124.15, 2499.00, '-15%', 2124.15, 'assets/img/productos/Celulares/Oppo/077-oppo-reno16-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(160, 'Oppo', 'Reno16 16 Pro Magnetic Protective Case', 3, 'Accesorios', 187.06, 199.00, '-6%', 187.06, 'assets/img/productos/Accesorios/Oppo/057-oppo-reno16-16-pro-magnetic-protective-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(161, 'Oppo', 'Reno16 F', 1, 'Celulares', 1471.08, 1599.00, '-8%', 1471.08, 'assets/img/productos/Celulares/Oppo/078-oppo-reno16-f-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(162, 'Oppo', 'Reno16 F Fs Magnetic Protective Case', 3, 'Accesorios', 161.10, 179.00, '-10%', 161.10, 'assets/img/productos/Accesorios/Oppo/056-oppo-reno16-f-fs-magnetic-protective-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(163, 'Oppo', 'Reno16 Pro', 1, 'Celulares', 2903.12, 3299.00, '-12%', 2903.12, 'assets/img/productos/Celulares/Oppo/079-oppo-reno16-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(164, 'Oppo', 'SuperVOOC 100W Dual', 3, 'Accesorios', 279.65, 329.00, '-15%', 279.65, 'assets/img/productos/Celulares/Oppo/oppo-supervooc-100w-dual-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(165, 'Oppo', 'SuperVOOC 100W Power Adapter Kit', 3, 'Accesorios', 356.26, 379.00, '-6%', 356.26, 'assets/img/productos/Accesorios/Oppo/048-oppo-supervooc-100w-power-adapter-kit-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(166, 'Oppo', 'SuperVOOC 45W Charger', 3, 'Accesorios', 155.48, 169.00, '-8%', 155.48, 'assets/img/productos/Accesorios/Oppo/049-oppo-supervooc-45w-charger-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(167, 'Oppo', 'SuperVOOC 45W Dual Ports GaN Power Adapter', 3, 'Accesorios', 224.10, 249.00, '-10%', 224.10, 'assets/img/productos/Accesorios/Oppo/050-oppo-supervooc-45w-dual-ports-gan-power-adapter-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(168, 'Oppo', 'SuperVOOC 80W Charger', 3, 'Accesorios', 219.12, 249.00, '-12%', 219.12, 'assets/img/productos/Accesorios/Oppo/051-oppo-supervooc-80w-charger-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(169, 'Oppo', 'SuperVOOC 80W Dual Ports GaN Power Adapter', 3, 'Accesorios', 279.65, 329.00, '-15%', 279.65, 'assets/img/productos/Accesorios/Oppo/052-oppo-supervooc-80w-dual-ports-gan-power-adapter-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(170, 'Oppo', 'Tarjeta Enfriamiento Magnetica', 3, 'Accesorios', 234.06, 249.00, '-6%', 234.06, 'assets/img/productos/Celulares/Apple/oppo-tarjeta-enfriamiento-magnetica-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(171, 'Samsung', 'Cable USB-C 3A 1 8M', 3, 'Accesorios', 54.28, 59.00, '-8%', 54.28, 'assets/img/productos/Accesorios/Samsung/samsung-cable-usb-c-3a-1-8m-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(172, 'Samsung', 'Cargador USB-C 25W Con Cable', 3, 'Accesorios', 89.10, 99.00, '-10%', 89.10, 'assets/img/productos/Accesorios/Samsung/samsung-cargador-usb-c-25w-con-cable.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(173, 'Samsung', 'Funda Galaxy A27 Con Tarjetero', 3, 'Accesorios', 87.12, 99.00, '-12%', 87.12, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-a27-con-tarjetero-negra-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(174, 'Samsung', 'Funda Galaxy A55 Silicone', 3, 'Accesorios', 109.65, 129.00, '-15%', 109.65, 'assets/img/productos/Accesorios/Samsung/samsung-funda-galaxy-a55-silicone-negra-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(175, 'Samsung', 'Funda Galaxy S26 Fe', 3, 'Accesorios', 140.06, 149.00, '-6%', 140.06, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-transparente-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(176, 'Samsung', 'Funda Galaxy S26 Fe Silicona Magnetica', 3, 'Accesorios', 183.08, 199.00, '-8%', 183.08, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(177, 'Samsung', 'Galaxy A27 5G', 1, 'Celulares', 989.10, 1099.00, '-10%', 989.10, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a27-5g-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(178, 'Samsung', 'Galaxy Buds3 Fe', 2, 'Audio', 351.12, 399.00, '-12%', 351.12, 'assets/img/productos/Audio/Samsung/083-samsung-galaxy-buds3-fe-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(179, 'Samsung', 'Galaxy Buds4', 2, 'Audio', 594.15, 699.00, '-15%', 594.15, 'assets/img/productos/Audio/Samsung/085-samsung-galaxy-buds4-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(180, 'Samsung', 'Galaxy Buds4 Pro', 2, 'Audio', 939.06, 999.00, '-6%', 939.06, 'assets/img/productos/Audio/Samsung/086-samsung-galaxy-buds4-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(181, 'Samsung', 'Galaxy S25', 1, 'Celulares', 3035.08, 3299.00, '-8%', 3035.08, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(182, 'Samsung', 'Galaxy S25 Fe', 1, 'Celulares', 2339.10, 2599.00, '-10%', 2339.10, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-fe-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(183, 'Samsung', 'Galaxy S25 Ultra', 1, 'Celulares', 4839.12, 5499.00, '-12%', 4839.12, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-ultra-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(184, 'Samsung', 'Galaxy S26', 1, 'Celulares', 3399.15, 3999.00, '-15%', 3399.15, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(185, 'Samsung', 'Galaxy S26 Fe', 1, 'Celulares', 2631.06, 2799.00, '-6%', 2631.06, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-fe-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(186, 'Samsung', 'Galaxy S26 Plus', 1, 'Celulares', 4599.08, 4999.00, '-8%', 4599.08, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-plus-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(187, 'Samsung', 'Galaxy S26 Plus Slim Magnet Case', 3, 'Accesorios', 161.10, 179.00, '-10%', 161.10, 'assets/img/productos/Accesorios/Samsung/082-samsung-galaxy-s26-plus-slim-magnet-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(188, 'Samsung', 'Galaxy S26 Slim Magnet Case', 3, 'Accesorios', 157.52, 179.00, '-12%', 157.52, 'assets/img/productos/Accesorios/Samsung/080-samsung-galaxy-s26-slim-magnet-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(189, 'Samsung', 'Galaxy S26 Ultra', 1, 'Celulares', 5524.15, 6499.00, '-15%', 5524.15, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-ultra-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(190, 'Samsung', 'Galaxy S26 Ultra Slim Magnet Case', 3, 'Accesorios', 187.06, 199.00, '-6%', 187.06, 'assets/img/productos/Accesorios/Samsung/081-samsung-galaxy-s26-ultra-slim-magnet-case-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(191, 'Samsung', 'Galaxy Smarttag2', 3, 'Accesorios', 137.08, 149.00, '-8%', 137.08, 'assets/img/productos/Celulares/Apple/samsung-galaxy-smarttag2-negro-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(192, 'Samsung', 'Galaxy Z Flip8', 1, 'Celulares', 5309.10, 5899.00, '-10%', 5309.10, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-flip8-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(193, 'Samsung', 'Galaxy Z Fold8', 1, 'Celulares', 7479.12, 8499.00, '-12%', 7479.12, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(194, 'Samsung', 'Galaxy Z Fold8 Ultra', 1, 'Celulares', 8499.15, 9999.00, '-15%', 8499.15, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-ultra-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(195, 'Xiaomi', '120W HyperCharge Combo Type A', 3, 'Accesorios', 309.26, 329.00, '-6%', 309.26, 'assets/img/productos/Celulares/Xiaomi/108-xiaomi-120w-hypercharge-combo-type-a-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(196, 'Xiaomi', '67W GaN Charger 2C1A', 3, 'Accesorios', 183.08, 199.00, '-8%', 183.08, 'assets/img/productos/Accesorios/Xiaomi/109-xiaomi-67w-gan-charger-2c1a-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(197, 'Xiaomi', 'Cable USB A USB-C 6A', 3, 'Accesorios', 53.10, 59.00, '-10%', 53.10, 'assets/img/productos/Accesorios/Xiaomi/xiaomi-cable-usb-a-usb-c-6a-detalle-conectores.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(198, 'Xiaomi', 'HyperCharge 67W', 3, 'Accesorios', 148.72, 169.00, '-12%', 148.72, 'assets/img/productos/Celulares/Xiaomi/xiaomi-hypercharge-67w-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(199, 'Xiaomi', 'POCO C71', 1, 'Celulares', 424.15, 499.00, '-15%', 424.15, 'assets/img/productos/Celulares/Xiaomi/124-xiaomi-poco-c71-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(200, 'Xiaomi', 'POCO C81 Pro', 1, 'Celulares', 657.06, 699.00, '-6%', 657.06, 'assets/img/productos/Celulares/Xiaomi/125-xiaomi-poco-c81-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(201, 'Xiaomi', 'POCO C85', 1, 'Celulares', 735.08, 799.00, '-8%', 735.08, 'assets/img/productos/Celulares/Xiaomi/126-xiaomi-poco-c85-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(202, 'Xiaomi', 'POCO F7', 1, 'Celulares', 1709.10, 1899.00, '-10%', 1709.10, 'assets/img/productos/Celulares/Xiaomi/119-xiaomi-poco-f7-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(203, 'Xiaomi', 'POCO F7 Pro', 1, 'Celulares', 2199.12, 2499.00, '-12%', 2199.12, 'assets/img/productos/Celulares/Xiaomi/120-xiaomi-poco-f7-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(204, 'Xiaomi', 'POCO F7 Ultra', 1, 'Celulares', 2974.15, 3499.00, '-15%', 2974.15, 'assets/img/productos/Celulares/Xiaomi/121-xiaomi-poco-f7-ultra-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(205, 'Xiaomi', 'POCO F8 Pro', 1, 'Celulares', 2631.06, 2799.00, '-6%', 2631.06, 'assets/img/productos/Celulares/Xiaomi/127-xiaomi-poco-f8-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(206, 'Xiaomi', 'POCO F8 Ultra', 1, 'Celulares', 3679.08, 3999.00, '-8%', 3679.08, 'assets/img/productos/Celulares/Xiaomi/128-xiaomi-poco-f8-ultra-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(207, 'Xiaomi', 'POCO F9 Pro', 1, 'Celulares', 2699.10, 2999.00, '-10%', 2699.10, 'assets/img/productos/Celulares/Xiaomi/129-xiaomi-poco-f9-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(208, 'Xiaomi', 'POCO F9 Ultra', 1, 'Celulares', 3783.12, 4299.00, '-12%', 3783.12, 'assets/img/productos/Celulares/Xiaomi/130-xiaomi-poco-f9-ultra-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(209, 'Xiaomi', 'POCO M7 Pro 5G', 1, 'Celulares', 849.15, 999.00, '-15%', 849.15, 'assets/img/productos/Celulares/Xiaomi/131-xiaomi-poco-m7-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(210, 'Xiaomi', 'POCO M8 5G', 1, 'Celulares', 1127.06, 1199.00, '-6%', 1127.06, 'assets/img/productos/Celulares/Xiaomi/132-xiaomi-poco-m8-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(211, 'Xiaomi', 'POCO M8 Pro 5G', 1, 'Celulares', 1471.08, 1599.00, '-8%', 1471.08, 'assets/img/productos/Celulares/Xiaomi/133-xiaomi-poco-m8-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(212, 'Xiaomi', 'POCO M8S 5G', 1, 'Celulares', 1259.10, 1399.00, '-10%', 1259.10, 'assets/img/productos/Celulares/Xiaomi/134-xiaomi-poco-m8s-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(213, 'Xiaomi', 'POCO X7', 1, 'Celulares', 1143.12, 1299.00, '-12%', 1143.12, 'assets/img/productos/Celulares/Xiaomi/122-xiaomi-poco-x7-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(214, 'Xiaomi', 'POCO X7 Pro', 1, 'Celulares', 1529.15, 1799.00, '-15%', 1529.15, 'assets/img/productos/Celulares/Xiaomi/123-xiaomi-poco-x7-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(215, 'Xiaomi', 'POCO X8 Pro', 1, 'Celulares', 2067.06, 2199.00, '-6%', 2067.06, 'assets/img/productos/Celulares/Xiaomi/135-xiaomi-poco-x8-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(216, 'Xiaomi', 'POCO X8 Pro Max', 1, 'Celulares', 2575.08, 2799.00, '-8%', 2575.08, 'assets/img/productos/Celulares/Xiaomi/136-xiaomi-poco-x8-pro-max-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(217, 'Xiaomi', 'Power Bank 33W 20000MAH', 3, 'Accesorios', 224.10, 249.00, '-10%', 224.10, 'assets/img/productos/Accesorios/Xiaomi/xiaomi-power-bank-33w-20000mah-frontal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(218, 'Xiaomi', 'Redmi 15C', 1, 'Celulares', 527.12, 599.00, '-12%', 527.12, 'assets/img/productos/Celulares/Xiaomi/140-xiaomi-redmi-15c-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(219, 'Xiaomi', 'Redmi 17', 1, 'Celulares', 849.15, 999.00, '-15%', 849.15, 'assets/img/productos/Celulares/Xiaomi/137-xiaomi-redmi-17-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(220, 'Xiaomi', 'Redmi A5', 1, 'Celulares', 422.06, 449.00, '-6%', 422.06, 'assets/img/productos/Celulares/Xiaomi/138-xiaomi-redmi-a5-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(221, 'Xiaomi', 'Redmi A7 Pro', 1, 'Celulares', 551.08, 599.00, '-8%', 551.08, 'assets/img/productos/Celulares/Xiaomi/139-xiaomi-redmi-a7-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(222, 'Xiaomi', 'Redmi Buds6', 2, 'Audio', 143.10, 159.00, '-10%', 143.10, 'assets/img/productos/Audio/Xiaomi/116-xiaomi-buds-6-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(223, 'Xiaomi', 'Redmi Buds8', 2, 'Audio', 157.52, 179.00, '-12%', 157.52, 'assets/img/productos/Audio/Xiaomi/111-xiaomi-redmi-buds-8-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(224, 'Xiaomi', 'Redmi Buds8 Active', 2, 'Audio', 101.15, 119.00, '-15%', 101.15, 'assets/img/productos/Audio/Xiaomi/112-xiaomi-redmi-buds-8-active-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(225, 'Xiaomi', 'Redmi Buds8 Lite', 2, 'Audio', 140.06, 149.00, '-6%', 140.06, 'assets/img/productos/Audio/Xiaomi/113-xiaomi-redmi-buds-8-lite-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(226, 'Xiaomi', 'Redmi Buds8 Pro', 2, 'Audio', 256.68, 279.00, '-8%', 256.68, 'assets/img/productos/Audio/Xiaomi/114-xiaomi-redmi-buds-8-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(227, 'Xiaomi', 'Redmi Headphones Neo', 2, 'Audio', 269.10, 299.00, '-10%', 269.10, 'assets/img/productos/Audio/Xiaomi/118-xiaomi-redmi-headphones-neo-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(228, 'Xiaomi', 'Redmi Note 14', 1, 'Celulares', 791.12, 899.00, '-12%', 791.12, 'assets/img/productos/Celulares/Xiaomi/141-xiaomi-redmi-note-14-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(229, 'Xiaomi', 'Redmi Note 14 5G', 1, 'Celulares', 934.15, 1099.00, '-15%', 934.15, 'assets/img/productos/Celulares/Xiaomi/142-xiaomi-redmi-note-14-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(230, 'Xiaomi', 'Redmi Note 14 Pro', 1, 'Celulares', 1315.06, 1399.00, '-6%', 1315.06, 'assets/img/productos/Celulares/Xiaomi/143-xiaomi-redmi-note-14-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(231, 'Xiaomi', 'Redmi Note 14 Pro 5G', 1, 'Celulares', 1471.08, 1599.00, '-8%', 1471.08, 'assets/img/productos/Celulares/Xiaomi/144-xiaomi-redmi-note-14-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(232, 'Xiaomi', 'Redmi Note 14 Pro Plus 5G', 1, 'Celulares', 1709.10, 1899.00, '-10%', 1709.10, 'assets/img/productos/Celulares/Xiaomi/145-xiaomi-redmi-note-14-pro-plus-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(233, 'Xiaomi', 'Redmi Note 15', 1, 'Celulares', 879.12, 999.00, '-12%', 879.12, 'assets/img/productos/Celulares/Xiaomi/146-xiaomi-redmi-note-15-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(234, 'Xiaomi', 'Redmi Note 15 5G', 1, 'Celulares', 1104.15, 1299.00, '-15%', 1104.15, 'assets/img/productos/Celulares/Xiaomi/147-xiaomi-redmi-note-15-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(235, 'Xiaomi', 'Redmi Note 15 Pro', 1, 'Celulares', 1503.06, 1599.00, '-6%', 1503.06, 'assets/img/productos/Celulares/Xiaomi/148-xiaomi-redmi-note-15-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(236, 'Xiaomi', 'Redmi Note 15 Pro 5G', 1, 'Celulares', 1655.08, 1799.00, '-8%', 1655.08, 'assets/img/productos/Celulares/Xiaomi/149-xiaomi-redmi-note-15-pro-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(237, 'Xiaomi', 'Redmi Note 15 Pro Plus 5G', 1, 'Celulares', 1979.10, 2199.00, '-10%', 1979.10, 'assets/img/productos/Celulares/Xiaomi/150-xiaomi-redmi-note-15-pro-plus-5g-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(238, 'Xiaomi', 'Redmi Openwear Stereo Pro', 2, 'Audio', 527.12, 599.00, '-12%', 527.12, 'assets/img/productos/Audio/Xiaomi/117-xiaomi-openwear-stereo-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(239, 'Xiaomi', 'Ultrathin Magnetic Power Bank 5000 15W', 3, 'Accesorios', 254.15, 299.00, '-15%', 254.15, 'assets/img/productos/Accesorios/Xiaomi/110-xiaomi-ultrathin-magnetic-power-bank-5000-15w-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(240, 'Xiaomi', 'Xiaomi 14 Ultra', 1, 'Celulares', 4699.06, 4999.00, '-6%', 4699.06, 'assets/img/productos/Celulares/Xiaomi/151-xiaomi-14-ultra-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(241, 'Xiaomi', 'Xiaomi 15', 1, 'Celulares', 3219.08, 3499.00, '-8%', 3219.08, 'assets/img/productos/Celulares/Xiaomi/152-xiaomi-15-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(242, 'Xiaomi', 'Xiaomi 15 Ultra', 1, 'Celulares', 5399.10, 5999.00, '-10%', 5399.10, 'assets/img/productos/Celulares/Xiaomi/153-xiaomi-15-ultra-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(243, 'Xiaomi', 'Xiaomi 15T', 1, 'Celulares', 1671.12, 1899.00, '-12%', 1671.12, 'assets/img/productos/Celulares/Xiaomi/154-xiaomi-15t-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(244, 'Xiaomi', 'Xiaomi 15T Pro', 1, 'Celulares', 2124.15, 2499.00, '-15%', 2124.15, 'assets/img/productos/Celulares/Xiaomi/155-xiaomi-15t-pro-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(245, 'Xiaomi', 'Xiaomi 17', 1, 'Celulares', 3759.06, 3999.00, '-6%', 3759.06, 'assets/img/productos/Celulares/Xiaomi/156-xiaomi-17-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07'),
(246, 'Xiaomi', 'Xiaomi 17 Ultra', 1, 'Celulares', 5979.08, 6499.00, '-8%', 5979.08, 'assets/img/productos/Celulares/Xiaomi/157-xiaomi-17-ultra-principal.jpg', 20, NULL, NULL, 'Oferta', NULL, 1, '2026-09-30 05:46:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_caracteristicas`
--

CREATE TABLE `producto_caracteristicas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagen_referencia`
--

CREATE TABLE `producto_imagen_referencia` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_imagen_referencia`
--

INSERT INTO `producto_imagen_referencia` (`id`, `producto_id`, `ruta_imagen`, `orden`, `actualizado_en`) VALUES
(1, 1, 'assets/img/productos/Accesorios/Samsung/samsung-cargador-usb-c-25w-detalle-conector.jpg', 1, '2026-09-30 02:06:16'),
(2, 1, 'assets/img/productos/Accesorios/Samsung/samsung-cargador-usb-c-25w-lateral.jpg', 2, '2026-09-30 02:06:16'),
(3, 1, 'assets/img/productos/Accesorios/Samsung/samsung-cargador-usb-c-25w-perspectiva.jpg', 3, '2026-09-30 02:06:16'),
(4, 4, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-60w-1m-detalle-conectores.jpg', 1, '2026-09-30 02:06:16'),
(5, 4, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-60w-1m-detalle-conectores_fc3115cc.jpg', 2, '2026-09-30 02:06:16'),
(6, 4, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-60w-1m-frontal_5659e941.jpg', 3, '2026-09-30 02:06:16'),
(7, 17, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a57-5g-trasera.jpg', 1, '2026-09-30 02:06:16'),
(8, 17, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a57-5g-lateral.jpg', 2, '2026-09-30 02:06:16'),
(9, 17, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a57-5g-detalle-camara.jpg', 3, '2026-09-30 02:06:16'),
(10, 18, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a37-5g-trasera.jpg', 1, '2026-09-30 02:06:16'),
(11, 18, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a37-5g-lateral.jpg', 2, '2026-09-30 02:06:16'),
(12, 18, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a37-5g-detalle-camara.jpg', 3, '2026-09-30 02:06:16'),
(13, 26, 'assets/img/productos/Celulares/Honor/024-honor-200-pro-posterior.jpg', 1, '2026-09-30 02:06:16'),
(14, 31, 'assets/img/productos/Audio/Samsung/Buds3Pro-SAMSUNG-2024-0.jpg', 1, '2026-09-30 02:06:16'),
(15, 31, 'assets/img/productos/Audio/Samsung/Buds3Pro-SAMSUNG-2024-1.jpg', 2, '2026-09-30 02:06:16'),
(16, 31, 'assets/img/productos/Audio/Samsung/Buds3Pro-SAMSUNG-2024-2.jpg', 3, '2026-09-30 02:06:16'),
(17, 33, 'assets/img/productos/Audio/Xiaomi/Buds5Pro-REDMI-2025-0.jpg', 1, '2026-09-30 02:06:16'),
(18, 33, 'assets/img/productos/Audio/Xiaomi/Buds5Pro-REDMI-2025-1.jpg', 2, '2026-09-30 02:06:16'),
(19, 38, 'assets/img/productos/Audio/JBL/jbl-tune-520bt-blanco-lateral.jpg', 1, '2026-09-30 02:06:16'),
(20, 38, 'assets/img/productos/Audio/JBL/jbl-tune-520bt-blanco-perspectiva.jpg', 2, '2026-09-30 02:06:16'),
(21, 38, 'assets/img/productos/Audio/JBL/jbl-tune-520bt-blanco-plegado.jpg', 3, '2026-09-30 02:06:16'),
(22, 38, 'assets/img/productos/Audio/JBL/jbl-tune-520bt-blanco-detalle-almohadillas.jpg', 4, '2026-09-30 02:06:16'),
(23, 49, 'assets/img/productos/Accesorios/Apple/apple-cargador-20w-usb-c-detalle-puerto.jpg', 1, '2026-09-30 02:06:16'),
(24, 49, 'assets/img/productos/Accesorios/Apple/apple-cargador-usb-c-20w-comparacion-tamano.jpg', 2, '2026-09-30 02:06:16'),
(25, 49, 'assets/img/productos/Accesorios/Apple/apple-cargador-usb-c-20w-detalle-conector.jpg', 3, '2026-09-30 02:06:16'),
(26, 49, 'assets/img/productos/Accesorios/Apple/apple-cargador-usb-c-20w-frontal.jpg', 4, '2026-09-30 02:06:16'),
(27, 49, 'assets/img/productos/Accesorios/Apple/apple-cargador-usb-c-20w-perspectiva.jpg', 5, '2026-09-30 02:06:16'),
(28, 50, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-a-lightning-1m-detalle-lightning.jpg', 1, '2026-09-30 02:06:16'),
(29, 50, 'assets/img/productos/Accesorios/Apple/apple-cable-usb-c-a-lightning-1m-detalle-usb-c.jpg', 2, '2026-09-30 02:06:16'),
(30, 51, 'assets/img/productos/Accesorios/Apple/samsung-cargador-45w-usb-c-negro-detalle-puerto.jpg', 1, '2026-09-30 02:06:16'),
(31, 51, 'assets/img/productos/Accesorios/Apple/samsung-cargador-45w-usb-c-negro-lateral.jpg', 2, '2026-09-30 02:06:16'),
(32, 73, 'assets/img/productos/Accesorios/Apple/apple-bateria-magsafe-iphone-air-en-uso.jpg', 1, '2026-09-30 05:46:07'),
(33, 73, 'assets/img/productos/Accesorios/Apple/apple-bateria-magsafe-iphone-air-lateral.jpg', 2, '2026-09-30 05:46:07'),
(34, 49, 'assets/img/productos/Accesorios/Apple/apple-cargador-20w-usb-c-lateral.jpg', 4, '2026-09-30 05:46:07'),
(35, 74, 'assets/img/productos/Accesorios/Apple/apple-cargador-35w-doble-usb-c-lateral.jpg', 1, '2026-09-30 05:46:07'),
(36, 74, 'assets/img/productos/Accesorios/Apple/apple-cargador-35w-doble-usb-c-trasera.jpg', 2, '2026-09-30 05:46:07'),
(37, 75, 'assets/img/productos/Accesorios/Apple/apple-cargador-40w-dynamic-usb-c-frontal.jpg', 1, '2026-09-30 05:46:07'),
(38, 75, 'assets/img/productos/Accesorios/Apple/apple-cargador-40w-dynamic-usb-c-lateral.jpg', 2, '2026-09-30 05:46:07'),
(39, 75, 'assets/img/productos/Accesorios/Apple/apple-cargador-40w-dynamic-usb-c-trasera.jpg', 3, '2026-09-30 05:46:07'),
(40, 76, 'assets/img/productos/Accesorios/Apple/apple-cargador-magsafe-1m-detalle-conector.jpg', 4, '2026-09-30 05:46:07'),
(41, 76, 'assets/img/productos/Accesorios/Apple/apple-cargador-magsafe-1m-en-uso.jpg', 5, '2026-09-30 05:46:07'),
(42, 76, 'assets/img/productos/Accesorios/Apple/apple-cargador-magsafe-1m-lateral.jpg', 6, '2026-09-30 05:46:07'),
(43, 76, 'assets/img/productos/Accesorios/Apple/apple-cargador-magsafe-1m-perspectiva.jpg', 2, '2026-09-30 05:46:07'),
(44, 77, 'assets/img/productos/Accesorios/Apple/apple-funda-iphone-16-transparente-magsafe-trasera-negro.jpg', 1, '2026-09-30 05:46:07'),
(45, 77, 'assets/img/productos/Accesorios/Apple/apple-funda-iphone-16-transparente-magsafe-trasera.jpg', 2, '2026-09-30 05:46:07'),
(46, 78, 'assets/img/productos/Accesorios/Apple/apple-funda-iphone-17-silicona-magsafe-guava-trasera.jpg', 1, '2026-09-30 05:46:07'),
(47, 76, 'assets/img/productos/Accesorios/Apple/apple-magsafe-1m-detalle-conector.jpg', 7, '2026-09-30 05:46:07'),
(48, 76, 'assets/img/productos/Accesorios/Apple/apple-magsafe-1m-frontal.jpg', 1, '2026-09-30 05:46:07'),
(49, 76, 'assets/img/productos/Accesorios/Apple/apple-magsafe-1m-perspectiva.jpg', 3, '2026-09-30 05:46:07'),
(50, 76, 'assets/img/productos/Accesorios/Apple/apple-magsafe-1m-trasera.jpg', 8, '2026-09-30 05:46:07'),
(51, 130, 'assets/img/productos/Accesorios/Apple/oppo-cargador-magnetico-airvooc-50w-lateral.jpg', 2, '2026-09-30 05:46:07'),
(52, 130, 'assets/img/productos/Accesorios/Apple/oppo-cargador-magnetico-airvooc-50w-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(53, 130, 'assets/img/productos/Accesorios/Apple/oppo-cargador-magnetico-airvooc-50w-trasera.jpg', 3, '2026-09-30 05:46:07'),
(54, 131, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-100w-doble-puerto-detalle-puerto.jpg', 2, '2026-09-30 05:46:07'),
(55, 131, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-100w-doble-puerto-lateral.jpg', 3, '2026-09-30 05:46:07'),
(56, 131, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-100w-doble-puerto-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(57, 132, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-45w-detalle-puerto.jpg', 2, '2026-09-30 05:46:07'),
(58, 132, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-45w-lateral.jpg', 3, '2026-09-30 05:46:07'),
(59, 132, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-45w-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(60, 133, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-80w-detalle-puerto.jpg', 2, '2026-09-30 05:46:07'),
(61, 133, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-80w-lateral.jpg', 3, '2026-09-30 05:46:07'),
(62, 133, 'assets/img/productos/Accesorios/Apple/oppo-cargador-supervooc-80w-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(63, 141, 'assets/img/productos/Accesorios/Apple/oppo-funda-find-x9-magnetica-gris-lateral.jpg', 1, '2026-09-30 05:46:07'),
(64, 141, 'assets/img/productos/Accesorios/Apple/oppo-funda-find-x9-magnetica-gris-trasera.jpg', 2, '2026-09-30 05:46:07'),
(65, 141, 'assets/img/productos/Accesorios/Apple/oppo-funda-find-x9-magnetica-negra-interior.jpg', 3, '2026-09-30 05:46:07'),
(66, 141, 'assets/img/productos/Accesorios/Apple/oppo-funda-find-x9-magnetica-negra-lateral.jpg', 4, '2026-09-30 05:46:07'),
(67, 141, 'assets/img/productos/Accesorios/Apple/oppo-funda-find-x9-magnetica-negra-trasera.jpg', 5, '2026-09-30 05:46:07'),
(68, 142, 'assets/img/productos/Accesorios/Apple/oppo-funda-reno16-magnetica-lateral.jpg', 1, '2026-09-30 05:46:07'),
(69, 142, 'assets/img/productos/Accesorios/Apple/oppo-funda-reno16-magnetica-trasera.jpg', 2, '2026-09-30 05:46:07'),
(70, 1, 'assets/img/productos/Accesorios/Apple/samsung-cargador-25w-usb-c-negro-detalle-puerto.jpg', 3, '2026-09-30 05:46:07'),
(71, 1, 'assets/img/productos/Accesorios/Apple/samsung-cargador-25w-usb-c-negro-lateral.jpg', 4, '2026-09-30 05:46:07'),
(72, 1, 'assets/img/productos/Accesorios/Apple/samsung-cargador-25w-usb-c-negro-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(73, 173, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-a27-con-tarjetero-negra-detalle-tarjetero.jpg', 1, '2026-09-30 05:46:07'),
(74, 173, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-a27-con-tarjetero-negra-lateral.jpg', 2, '2026-09-30 05:46:07'),
(75, 173, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-a27-con-tarjetero-negra-trasera.jpg', 3, '2026-09-30 05:46:07'),
(76, 176, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-detalle-magnetico.jpg', 1, '2026-09-30 05:46:07'),
(77, 176, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-lateral.jpg', 2, '2026-09-30 05:46:07'),
(78, 176, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-trasera.jpg', 3, '2026-09-30 05:46:07'),
(79, 175, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-transparente-lateral.jpg', 1, '2026-09-30 05:46:07'),
(80, 175, 'assets/img/productos/Accesorios/Apple/samsung-funda-galaxy-s26-fe-transparente-trasera.jpg', 2, '2026-09-30 05:46:07'),
(81, 128, 'assets/img/productos/Accesorios/Oppo/oppo-cable-supervooc-usb-c-8a-1m-en-uso.jpg', 1, '2026-09-30 05:46:07'),
(82, 171, 'assets/img/productos/Accesorios/Samsung/samsung-cable-usb-c-3a-1-8m-detalle-conectores.jpg', 1, '2026-09-30 05:46:07'),
(83, 171, 'assets/img/productos/Accesorios/Samsung/samsung-cable-usb-c-3a-1-8m-detalle-contactos.jpg', 2, '2026-09-30 05:46:07'),
(84, 171, 'assets/img/productos/Accesorios/Samsung/samsung-cable-usb-c-3a-1-8m-empaque.jpg', 3, '2026-09-30 05:46:07'),
(85, 174, 'assets/img/productos/Accesorios/Samsung/samsung-funda-galaxy-a55-silicone-negra-interior.jpg', 2, '2026-09-30 05:46:07'),
(86, 174, 'assets/img/productos/Accesorios/Samsung/samsung-funda-galaxy-a55-silicone-negra-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(87, 174, 'assets/img/productos/Accesorios/Samsung/samsung-funda-galaxy-a55-silicone-negra-trasera.jpg', 3, '2026-09-30 05:46:07'),
(88, 197, 'assets/img/productos/Accesorios/Xiaomi/xiaomi-cable-usb-a-usb-c-6a-en-uso.jpg', 1, '2026-09-30 05:46:07'),
(89, 217, 'assets/img/productos/Accesorios/Xiaomi/xiaomi-power-bank-33w-20000mah-colores.jpg', 1, '2026-09-30 05:46:07'),
(90, 217, 'assets/img/productos/Accesorios/Xiaomi/xiaomi-power-bank-33w-20000mah-detalle-puertos.jpg', 2, '2026-09-30 05:46:07'),
(91, 217, 'assets/img/productos/Accesorios/Xiaomi/xiaomi-power-bank-33w-20000mah-en-uso.jpg', 3, '2026-09-30 05:46:07'),
(92, 69, 'assets/img/productos/Audio/Apple/AirPods5-2026-Apple-0.jpg', 1, '2026-09-30 05:46:07'),
(93, 69, 'assets/img/productos/Audio/Apple/AirPods5-2026-Apple-1.jpg', 2, '2026-09-30 05:46:07'),
(94, 69, 'assets/img/productos/Audio/Apple/AirPods5-2026-Apple-2.jpg', 3, '2026-09-30 05:46:07'),
(95, 69, 'assets/img/productos/Audio/Apple/AirPods5-2026-Apple-3.jpg', 4, '2026-09-30 05:46:07'),
(96, 70, 'assets/img/productos/Audio/Apple/AirPodsMax2-2026-Apple-0.jpg', 1, '2026-09-30 05:46:07'),
(97, 70, 'assets/img/productos/Audio/Apple/AirPodsMax2-2026-Apple-1.jpg', 2, '2026-09-30 05:46:07'),
(98, 70, 'assets/img/productos/Audio/Apple/AirPodsMax2-2026-Apple-2.jpg', 3, '2026-09-30 05:46:07'),
(99, 70, 'assets/img/productos/Audio/Apple/AirPodsMax2-2026-Apple-3.jpg', 4, '2026-09-30 05:46:07'),
(100, 70, 'assets/img/productos/Audio/Apple/AirPodsMax2-2026-Apple-4.jpg', 5, '2026-09-30 05:46:07'),
(101, 71, 'assets/img/productos/Audio/Apple/AirPodsPro3-2025-Apple-0.jpg', 1, '2026-09-30 05:46:07'),
(102, 71, 'assets/img/productos/Audio/Apple/AirPodsPro3-2025-Apple-1.jpg', 2, '2026-09-30 05:46:07'),
(103, 71, 'assets/img/productos/Audio/Apple/AirPodsPro3-2025-Apple-2.jpg', 3, '2026-09-30 05:46:07'),
(104, 77, 'assets/img/productos/Audio/Apple/apple-funda-iphone-16-clear-magsafe-trasera.jpg', 3, '2026-09-30 05:46:07'),
(105, 102, 'assets/img/productos/Audio/Honor/honor-choice-earbuds-x-detalle-auricular.jpg', 1, '2026-09-30 05:46:07'),
(106, 102, 'assets/img/productos/Audio/Honor/honor-choice-earbuds-x-estuche.jpg', 2, '2026-09-30 05:46:07'),
(107, 103, 'assets/img/productos/Audio/Honor/HonorEarbuds4-Honor-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(108, 103, 'assets/img/productos/Audio/Honor/HonorEarbuds4-Honor-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(109, 103, 'assets/img/productos/Audio/Honor/HonorEarbuds4-Honor-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(110, 103, 'assets/img/productos/Audio/Honor/HonorEarbuds4-Honor-2026-3.jpg', 4, '2026-09-30 05:46:07'),
(111, 103, 'assets/img/productos/Audio/Honor/HonorEarbuds4-Honor-2026-4.jpg', 5, '2026-09-30 05:46:07'),
(112, 104, 'assets/img/productos/Audio/Honor/honorEarBuds5e-Honor-2026-0.webp', 1, '2026-09-30 05:46:07'),
(113, 104, 'assets/img/productos/Audio/Honor/honorEarBuds5e-Honor-2026-1.webp', 2, '2026-09-30 05:46:07'),
(114, 104, 'assets/img/productos/Audio/Honor/honorEarBuds5e-Honor-2026-2.webp', 3, '2026-09-30 05:46:07'),
(115, 104, 'assets/img/productos/Audio/Honor/honorEarBuds5e-Honor-2026-3.webp', 4, '2026-09-30 05:46:07'),
(116, 105, 'assets/img/productos/Audio/Honor/HonorEarbudsAPro-Honor-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(117, 105, 'assets/img/productos/Audio/Honor/HonorEarbudsAPro-Honor-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(118, 105, 'assets/img/productos/Audio/Honor/HonorEarbudsAPro-Honor-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(119, 105, 'assets/img/productos/Audio/Honor/HonorEarbudsAPro-Honor-2026-3.jpg', 4, '2026-09-30 05:46:07'),
(120, 106, 'assets/img/productos/Audio/Honor/HonorEarbudsOpen-Honor-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(121, 106, 'assets/img/productos/Audio/Honor/HonorEarbudsOpen-Honor-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(122, 106, 'assets/img/productos/Audio/Honor/HonorEarbudsOpen-Honor-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(123, 107, 'assets/img/productos/Audio/Honor/HonorEarbudsX10Lite-Honor-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(124, 107, 'assets/img/productos/Audio/Honor/HonorEarbudsX10Lite-Honor-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(125, 107, 'assets/img/productos/Audio/Honor/HonorEarbudsX10Lite-Honor-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(126, 107, 'assets/img/productos/Audio/Honor/HonorEarbudsX10Lite-Honor-2026-3.jpg', 4, '2026-09-30 05:46:07'),
(127, 123, 'assets/img/productos/Audio/JBL/039-jbl-live-680nc-plegado.jpg', 1, '2026-09-30 05:46:07'),
(128, 124, 'assets/img/productos/Audio/JBL/040-jbl-live-780nc-plegado.jpg', 1, '2026-09-30 05:46:07'),
(129, 92, 'assets/img/productos/Audio/JBL/beats-cable-usb-c-3m-azul-detalle-conectores.jpg', 1, '2026-09-30 05:46:07'),
(130, 92, 'assets/img/productos/Audio/JBL/beats-cable-usb-c-3m-azul-en-uso.jpg', 2, '2026-09-30 05:46:07'),
(131, 93, 'assets/img/productos/Audio/JBL/beats-solo-4-rosa-lateral.jpg', 1, '2026-09-30 05:46:07'),
(132, 93, 'assets/img/productos/Audio/JBL/beats-solo-4-rosa-plegado.jpg', 2, '2026-09-30 05:46:07'),
(133, 93, 'assets/img/productos/Audio/JBL/beats-solo-4-rosa-sobre-mesa.jpg', 3, '2026-09-30 05:46:07'),
(134, 94, 'assets/img/productos/Audio/JBL/beats-studio-pro-arena-con-estuche.jpg', 2, '2026-09-30 05:46:07'),
(135, 94, 'assets/img/productos/Audio/JBL/beats-studio-pro-arena-detalle-controles.jpg', 3, '2026-09-30 05:46:07'),
(136, 94, 'assets/img/productos/Audio/JBL/beats-studio-pro-arena-perspectiva-trasera.jpg', 1, '2026-09-30 05:46:07'),
(137, 94, 'assets/img/productos/Audio/JBL/beats-studio-pro-arena-plegado.jpg', 4, '2026-09-30 05:46:07'),
(138, 117, 'assets/img/productos/Audio/JBL/Charge6-JBL-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(139, 117, 'assets/img/productos/Audio/JBL/Charge6-JBL-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(140, 117, 'assets/img/productos/Audio/JBL/Charge6-JBL-2025-2.jpg', 3, '2026-09-30 05:46:07'),
(141, 117, 'assets/img/productos/Audio/JBL/Charge6-JBL-2025-3.jpg', 4, '2026-09-30 05:46:07'),
(142, 117, 'assets/img/productos/Audio/JBL/Charge6-JBL-2025-4.jpg', 5, '2026-09-30 05:46:07'),
(143, 117, 'assets/img/productos/Audio/JBL/Charge6-JBL-2025-5.jpg', 6, '2026-09-30 05:46:07'),
(144, 118, 'assets/img/productos/Audio/JBL/Clip5-JBL-2024-0.jpg', 1, '2026-09-30 05:46:07'),
(145, 118, 'assets/img/productos/Audio/JBL/Clip5-JBL-2024-1.jpg', 2, '2026-09-30 05:46:07'),
(146, 118, 'assets/img/productos/Audio/JBL/Clip5-JBL-2024-2.jpg', 3, '2026-09-30 05:46:07'),
(147, 118, 'assets/img/productos/Audio/JBL/Clip5-JBL-2024-3.jpg', 4, '2026-09-30 05:46:07'),
(148, 119, 'assets/img/productos/Audio/JBL/Flip7-JBL-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(149, 119, 'assets/img/productos/Audio/JBL/Flip7-JBL-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(150, 119, 'assets/img/productos/Audio/JBL/Flip7-JBL-2025-2.jpg', 3, '2026-09-30 05:46:07'),
(151, 119, 'assets/img/productos/Audio/JBL/Flip7-JBL-2025-3.jpg', 4, '2026-09-30 05:46:07'),
(152, 119, 'assets/img/productos/Audio/JBL/Flip7-JBL-2025-4.jpg', 5, '2026-09-30 05:46:07'),
(153, 121, 'assets/img/productos/Audio/JBL/Go5-JBL-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(154, 121, 'assets/img/productos/Audio/JBL/Go5-JBL-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(155, 121, 'assets/img/productos/Audio/JBL/Go5-JBL-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(156, 121, 'assets/img/productos/Audio/JBL/Go5-JBL-2026-3.jpg', 4, '2026-09-30 05:46:07'),
(157, 122, 'assets/img/productos/Audio/JBL/Grip-JBL-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(158, 122, 'assets/img/productos/Audio/JBL/Grip-JBL-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(159, 122, 'assets/img/productos/Audio/JBL/Grip-JBL-2025-2.jpg', 3, '2026-09-30 05:46:07'),
(160, 122, 'assets/img/productos/Audio/JBL/Grip-JBL-2025-3.jpg', 4, '2026-09-30 05:46:07'),
(161, 123, 'assets/img/productos/Audio/JBL/Live680NC-JBL-2026-0.jpg', 2, '2026-09-30 05:46:07'),
(162, 123, 'assets/img/productos/Audio/JBL/Live680NC-JBL-2026-1.jpg', 3, '2026-09-30 05:46:07'),
(163, 123, 'assets/img/productos/Audio/JBL/Live680NC-JBL-2026-2.jpg', 4, '2026-09-30 05:46:07'),
(164, 123, 'assets/img/productos/Audio/JBL/Live680NC-JBL-2026-3.jpg', 5, '2026-09-30 05:46:07'),
(165, 123, 'assets/img/productos/Audio/JBL/Live680NC-JBL-2026-4.jpg', 6, '2026-09-30 05:46:07'),
(166, 124, 'assets/img/productos/Audio/JBL/Live780NC-JBL-2026-0.jpg', 2, '2026-09-30 05:46:07'),
(167, 124, 'assets/img/productos/Audio/JBL/Live780NC-JBL-2026-1.jpg', 3, '2026-09-30 05:46:07'),
(168, 124, 'assets/img/productos/Audio/JBL/Live780NC-JBL-2026-2.jpg', 4, '2026-09-30 05:46:07'),
(169, 125, 'assets/img/productos/Audio/JBL/TourONEM3-JBL-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(170, 125, 'assets/img/productos/Audio/JBL/TourONEM3-JBL-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(171, 125, 'assets/img/productos/Audio/JBL/TourONEM3-JBL-2025-2.jpg', 3, '2026-09-30 05:46:07'),
(172, 134, 'assets/img/productos/Audio/Oppo/oppo-enco-buds2-pro-colores.jpg', 1, '2026-09-30 05:46:07'),
(173, 134, 'assets/img/productos/Audio/Oppo/oppo-enco-buds2-pro-detalle-auricular.jpg', 2, '2026-09-30 05:46:07'),
(174, 134, 'assets/img/productos/Audio/Oppo/oppo-enco-buds2-pro-estuche.jpg', 3, '2026-09-30 05:46:07'),
(175, 145, 'assets/img/productos/Audio/Oppo/Speaker-OPPO-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(176, 145, 'assets/img/productos/Audio/Oppo/Speaker-OPPO-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(177, 179, 'assets/img/productos/Audio/Samsung/085-samsung-galaxy-buds4-posterior.jpg', 1, '2026-09-30 05:46:07'),
(178, 180, 'assets/img/productos/Audio/Samsung/086-samsung-galaxy-buds4-pro-posterior.jpg', 1, '2026-09-30 05:46:07'),
(179, 178, 'assets/img/productos/Audio/Samsung/Buds3FE-SAMSUNG-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(180, 178, 'assets/img/productos/Audio/Samsung/Buds3FE-SAMSUNG-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(181, 178, 'assets/img/productos/Audio/Samsung/Buds3FE-SAMSUNG-2025-2.jpg', 3, '2026-09-30 05:46:07'),
(182, 179, 'assets/img/productos/Audio/Samsung/Buds4-SAMSUNG-2026-0.jpg', 2, '2026-09-30 05:46:07'),
(183, 179, 'assets/img/productos/Audio/Samsung/Buds4-SAMSUNG-2026-1.jpg', 3, '2026-09-30 05:46:07'),
(184, 179, 'assets/img/productos/Audio/Samsung/Buds4-SAMSUNG-2026-2.jpg', 4, '2026-09-30 05:46:07'),
(185, 180, 'assets/img/productos/Audio/Samsung/Buds4Pro-SAMSUNG-2026-0.jpg', 2, '2026-09-30 05:46:07'),
(186, 180, 'assets/img/productos/Audio/Samsung/Buds4Pro-SAMSUNG-2026-1.jpg', 3, '2026-09-30 05:46:07'),
(187, 222, 'assets/img/productos/Audio/Xiaomi/Buds6-REDMI-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(188, 222, 'assets/img/productos/Audio/Xiaomi/Buds6-REDMI-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(189, 222, 'assets/img/productos/Audio/Xiaomi/Buds6-REDMI-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(190, 223, 'assets/img/productos/Audio/Xiaomi/Buds8-REDMI-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(191, 223, 'assets/img/productos/Audio/Xiaomi/Buds8-REDMI-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(192, 223, 'assets/img/productos/Audio/Xiaomi/Buds8-REDMI-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(193, 224, 'assets/img/productos/Audio/Xiaomi/Buds8Active-REDMI-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(194, 224, 'assets/img/productos/Audio/Xiaomi/Buds8Active-REDMI-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(195, 224, 'assets/img/productos/Audio/Xiaomi/Buds8Active-REDMI-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(196, 224, 'assets/img/productos/Audio/Xiaomi/Buds8Active-REDMI-2026-3.jpg', 4, '2026-09-30 05:46:07'),
(197, 225, 'assets/img/productos/Audio/Xiaomi/Buds8Lite-REDMI-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(198, 225, 'assets/img/productos/Audio/Xiaomi/Buds8Lite-REDMI-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(199, 225, 'assets/img/productos/Audio/Xiaomi/Buds8Lite-REDMI-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(200, 226, 'assets/img/productos/Audio/Xiaomi/Buds8Pro-REDMI-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(201, 226, 'assets/img/productos/Audio/Xiaomi/Buds8Pro-REDMI-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(202, 227, 'assets/img/productos/Audio/Xiaomi/HeadphonesNeo-REDMI-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(203, 227, 'assets/img/productos/Audio/Xiaomi/HeadphonesNeo-REDMI-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(204, 227, 'assets/img/productos/Audio/Xiaomi/HeadphonesNeo-REDMI-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(205, 120, 'assets/img/productos/Audio/Xiaomi/jbl-go-4-militar-detalle-conector.jpg', 2, '2026-09-30 05:46:07'),
(206, 120, 'assets/img/productos/Audio/Xiaomi/jbl-go-4-militar-detalle-controles.jpg', 3, '2026-09-30 05:46:07'),
(207, 120, 'assets/img/productos/Audio/Xiaomi/jbl-go-4-militar-empaque.jpg', 4, '2026-09-30 05:46:07'),
(208, 120, 'assets/img/productos/Audio/Xiaomi/jbl-go-4-militar-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(209, 120, 'assets/img/productos/Audio/Xiaomi/jbl-go-4-militar-trasera.jpg', 5, '2026-09-30 05:46:07'),
(210, 238, 'assets/img/productos/Audio/Xiaomi/OpenWearStereoPro-REDMI-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(211, 238, 'assets/img/productos/Audio/Xiaomi/OpenWearStereoPro-REDMI-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(212, 144, 'assets/img/productos/Celulares/Apple/oppo-palo-selfie-magnetico-3-en-1-extendido.jpg', 2, '2026-09-30 05:46:07'),
(213, 144, 'assets/img/productos/Celulares/Apple/oppo-palo-selfie-magnetico-3-en-1-modo-tripode.jpg', 3, '2026-09-30 05:46:07'),
(214, 144, 'assets/img/productos/Celulares/Apple/oppo-palo-selfie-magnetico-3-en-1-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(215, 170, 'assets/img/productos/Celulares/Apple/oppo-tarjeta-enfriamiento-magnetica-lateral.jpg', 2, '2026-09-30 05:46:07'),
(216, 170, 'assets/img/productos/Celulares/Apple/oppo-tarjeta-enfriamiento-magnetica-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(217, 170, 'assets/img/productos/Celulares/Apple/oppo-tarjeta-enfriamiento-magnetica-trasera.jpg', 3, '2026-09-30 05:46:07'),
(218, 191, 'assets/img/productos/Celulares/Apple/samsung-galaxy-smarttag2-negro-lateral.jpg', 2, '2026-09-30 05:46:07'),
(219, 191, 'assets/img/productos/Celulares/Apple/samsung-galaxy-smarttag2-negro-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(220, 191, 'assets/img/productos/Celulares/Apple/samsung-galaxy-smarttag2-negro-trasera.jpg', 3, '2026-09-30 05:46:07'),
(221, 98, 'assets/img/productos/Celulares/Honor/028-honor-600-posterior.jpg', 1, '2026-09-30 05:46:07'),
(222, 99, 'assets/img/productos/Celulares/Honor/029-honor-600-lite-posterior.jpg', 1, '2026-09-30 05:46:07'),
(223, 100, 'assets/img/productos/Celulares/Honor/030-honor-600-pro-posterior.jpg', 1, '2026-09-30 05:46:07'),
(224, 101, 'assets/img/productos/Celulares/Honor/031-honor-600-smart-5g-posterior.jpg', 1, '2026-09-30 05:46:07'),
(225, 110, 'assets/img/productos/Celulares/Honor/034-honor-magic7-pro-posterior.jpg', 1, '2026-09-30 05:46:07'),
(226, 113, 'assets/img/productos/Celulares/Honor/035-honor-porsche-design-honor-magic7-rsr-posterior.jpg', 1, '2026-09-30 05:46:07'),
(227, 112, 'assets/img/productos/Celulares/Honor/037-honor-magic9-pro-max-posterior.jpg', 1, '2026-09-30 05:46:07'),
(228, 135, 'assets/img/productos/Celulares/Oppo/EncoClip2-OPPO-2026-0.jpg', 1, '2026-09-30 05:46:07'),
(229, 135, 'assets/img/productos/Celulares/Oppo/EncoClip2-OPPO-2026-1.jpg', 2, '2026-09-30 05:46:07'),
(230, 135, 'assets/img/productos/Celulares/Oppo/EncoClip2-OPPO-2026-2.jpg', 3, '2026-09-30 05:46:07'),
(231, 135, 'assets/img/productos/Celulares/Oppo/EncoClip2-OPPO-2026-3.jpg', 4, '2026-09-30 05:46:07'),
(232, 136, 'assets/img/productos/Celulares/Oppo/EncoX3s-OPPO-2025-0.jpg', 1, '2026-09-30 05:46:07'),
(233, 136, 'assets/img/productos/Celulares/Oppo/EncoX3s-OPPO-2025-1.jpg', 2, '2026-09-30 05:46:07'),
(234, 136, 'assets/img/productos/Celulares/Oppo/EncoX3s-OPPO-2025-2.jpg', 3, '2026-09-30 05:46:07'),
(235, 136, 'assets/img/productos/Celulares/Oppo/EncoX3s-OPPO-2025-3.jpg', 4, '2026-09-30 05:46:07'),
(236, 164, 'assets/img/productos/Celulares/Oppo/oppo-supervooc-100w-dual-detalle-puertos.jpg', 2, '2026-09-30 05:46:07'),
(237, 164, 'assets/img/productos/Celulares/Oppo/oppo-supervooc-100w-dual-lateral.jpg', 3, '2026-09-30 05:46:07'),
(238, 164, 'assets/img/productos/Celulares/Oppo/oppo-supervooc-100w-dual-perspectiva.jpg', 1, '2026-09-30 05:46:07'),
(239, 177, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a27-5g-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(240, 177, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a27-5g-lateral.jpg', 2, '2026-09-30 05:46:07'),
(241, 177, 'assets/img/productos/Celulares/samsung/samsung-galaxy-a27-5g-trasera.jpg', 3, '2026-09-30 05:46:07'),
(242, 181, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(243, 182, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-fe-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(244, 182, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-fe-lateral.jpg', 2, '2026-09-30 05:46:07'),
(245, 182, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-fe-trasera.jpg', 3, '2026-09-30 05:46:07'),
(246, 181, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-lateral.jpg', 2, '2026-09-30 05:46:07'),
(247, 181, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-trasera.jpg', 3, '2026-09-30 05:46:07'),
(248, 183, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-ultra-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(249, 183, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-ultra-lateral.jpg', 2, '2026-09-30 05:46:07'),
(250, 183, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s25-ultra-trasera.jpg', 3, '2026-09-30 05:46:07'),
(251, 184, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(252, 185, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-fe-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(253, 185, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-fe-lateral.jpg', 2, '2026-09-30 05:46:07'),
(254, 185, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-fe-trasera.jpg', 3, '2026-09-30 05:46:07'),
(255, 184, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-lateral.jpg', 2, '2026-09-30 05:46:07'),
(256, 186, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-plus-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(257, 186, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-plus-lateral.jpg', 2, '2026-09-30 05:46:07'),
(258, 186, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-plus-trasera.jpg', 3, '2026-09-30 05:46:07'),
(259, 184, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-trasera.jpg', 3, '2026-09-30 05:46:07'),
(260, 189, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-ultra-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(261, 189, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-ultra-lateral.jpg', 2, '2026-09-30 05:46:07'),
(262, 189, 'assets/img/productos/Celulares/samsung/samsung-galaxy-s26-ultra-trasera.jpg', 3, '2026-09-30 05:46:07'),
(263, 192, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-flip8-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(264, 192, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-flip8-lateral.jpg', 2, '2026-09-30 05:46:07'),
(265, 192, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-flip8-trasera.jpg', 3, '2026-09-30 05:46:07'),
(266, 193, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(267, 193, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-lateral.jpg', 2, '2026-09-30 05:46:07'),
(268, 193, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-trasera.jpg', 3, '2026-09-30 05:46:07'),
(269, 194, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-ultra-detalle-camara.jpg', 1, '2026-09-30 05:46:07'),
(270, 194, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-ultra-lateral.jpg', 2, '2026-09-30 05:46:07'),
(271, 194, 'assets/img/productos/Celulares/samsung/samsung-galaxy-z-fold8-ultra-trasera.jpg', 3, '2026-09-30 05:46:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_proveedor`
--

CREATE TABLE `producto_proveedor` (
  `producto_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_variantes`
--

CREATE TABLE `producto_variantes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `nombre_color` varchar(100) NOT NULL DEFAULT 'Único',
  `codigo_color` varchar(20) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_variantes`
--

INSERT INTO `producto_variantes` (`id`, `producto_id`, `nombre_color`, `codigo_color`, `stock`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'Negro', NULL, 30, 'activo', '2026-09-29 15:28:31'),
(2, 2, 'Transparente', NULL, 25, 'activo', '2026-09-29 15:28:31'),
(3, 3, 'Blanco', NULL, 28, 'activo', '2026-09-29 15:28:31'),
(4, 4, 'Blanco', NULL, 22, 'activo', '2026-09-29 15:28:31');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variante_imagenes`
--

CREATE TABLE `variante_imagenes` (
  `id` int(11) NOT NULL,
  `variante_id` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `imagen_principal` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indices de la tabla `logistica_mayorista`
--
ALTER TABLE `logistica_mayorista`
  ADD PRIMARY KEY (`pedido_id`);

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
  ADD KEY `indice_productos_catalogo` (`activo`,`categoria`,`marca`,`id`),
  ADD KEY `indice_productos_categoria_id` (`categoria_id`);

--
-- Indices de la tabla `producto_caracteristicas`
--
ALTER TABLE `producto_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto_caracteristicas_producto` (`producto_id`);

--
-- Indices de la tabla `producto_imagen_referencia`
--
ALTER TABLE `producto_imagen_referencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_producto_imagen_referencia_ruta` (`producto_id`,`ruta_imagen`),
  ADD KEY `idx_producto_imagen_referencia_orden` (`producto_id`,`orden`,`id`);

--
-- Indices de la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  ADD PRIMARY KEY (`producto_id`),
  ADD KEY `idx_producto_proveedor_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto_variantes_producto` (`producto_id`);

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
-- Indices de la tabla `variante_imagenes`
--
ALTER TABLE `variante_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variante_imagenes_variante` (`variante_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `migraciones`
--
ALTER TABLE `migraciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=324;

--
-- AUTO_INCREMENT de la tabla `producto_caracteristicas`
--
ALTER TABLE `producto_caracteristicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_imagen_referencia`
--
ALTER TABLE `producto_imagen_referencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT de la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT de la tabla `variante_imagenes`
--
ALTER TABLE `variante_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Filtros para la tabla `logistica_mayorista`
--
ALTER TABLE `logistica_mayorista`
  ADD CONSTRAINT `fk_log_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `producto_caracteristicas`
--
ALTER TABLE `producto_caracteristicas`
  ADD CONSTRAINT `fk_producto_caracteristicas_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_imagen_referencia`
--
ALTER TABLE `producto_imagen_referencia`
  ADD CONSTRAINT `fk_ref_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  ADD CONSTRAINT `fk_pp_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pp_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  ADD CONSTRAINT `fk_producto_variantes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `variante_imagenes`
--
ALTER TABLE `variante_imagenes`
  ADD CONSTRAINT `fk_variante_imagenes_variante` FOREIGN KEY (`variante_id`) REFERENCES `producto_variantes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


SET FOREIGN_KEY_CHECKS=1;
