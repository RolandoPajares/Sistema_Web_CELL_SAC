# BASE_REFACTORIZACION.md — Fase 1 (Auditoría, sin cambios de código)

> Informe generado en Fase 1. **No se modificó ningún archivo de código, configuración, CSS/JS/SQL ni rutas.**
> Leyenda de evidencia: **[EJECUCIÓN]** = comprobado ejecutando comandos; **[INSPECCIÓN]** = comprobado leyendo código estáticamente; **[RIESGO]** = sospecha razonable sin comprobar en ejecución.

---

## 1) Estado de Git

- **[INSPECCIÓN]** Repositorio: `https://github.com/RolandoPajares/Sistema_Web_CELL_SAC.git`, rama `main`, HEAD `2d0339f`.
- **[INSPECCIÓN]** Árbol de trabajo con ~216 archivos modificados (cambios previos del usuario, **preservados**: no se restauró, no se hizo commit, no se crearon ramas).
- **[INSPECCIÓN]** `README.md` y `LEEME_INTEGRACION.md` aparecen eliminados en el árbol de trabajo; se recuperaron temporalmente desde Git (a `Temp`, fuera del proyecto) para leer su contenido. Siguen eliminados en el árbol.
- **[INSPECCIÓN]** Único archivo creado en esta fase: este informe, en la raíz (no existe carpeta `docs`).

## 2) Arquitectura y flujos

- **[INSPECCIÓN]** Entrada: `public/index.php` → `bootstrap/aplicacion.php` (contenedor `Aplicacion`) + `bootstrap/funciones.php` (helpers: `e()`, `money()`, `url()`, `asset()`, `csrf_field()`, `product_image_url()`, `product_category_icon()`, `product_visual()`, `current_user()`).
- **[INSPECCIÓN]** Rutas en `routes/`: `publicas.php` (catálogo/carrito/compra/login), `administrador.php` (`/admin/...`), `roles.php` (`/panel/{module}` genérico por rol), `api.php`. Middlewares: `RolMiddleware` (por módulo), `AccesoRutaRolMiddleware`.
- **[INSPECCIÓN]** Capas: `app/Controladores` → `app/Servicios` → `app/DAO` (contratos en `app/DAO/Contratos`), DTOs, `app/Modelos/Producto`, `app/Validacion` (p. ej. `SolicitudProducto`, `SolicitudInicioSesion`), `app/Nucleo` (HTTP `Solicitud`/`Respuesta`, `Presentacion/Vista` + `CompositorVistas`, `BaseDatos/Conexion`), `app/Soporte` (`AccesoRol`, `MensajeFlashServicio`, `DatosDemostracionPanel`, `CatalogoInterfaces`, `LimitadorSolicitudes`, registro en archivo).
- **[INSPECCIÓN]** Presentadores existentes: `app/Nucleo/Presentacion/Productos/PresentadorTarjetaProducto.php` y `PresentadorDetalleProducto.php` (devuelven arrays con claves `*_vista`: `precio_oferta`, `precio_original`, `descuento`, `texto_stock`, `clase_stock`, `imagenes`, etc.).
- **[INSPECCIÓN]** Flujos leídos:
  - **Catálogo/detalle**: `CatalogoController` (listado + filtros + paginación + modal) y `ProductoController::detalle` usan los presentadores; las vistas `publico/catalogo/*` son solo presentación.
  - **Carrito**: `CarritoController` → `CarritoServicio` (carrito en sesión `cart` = `id => cantidad`; `resumen()` recarga productos con `ProductoDAO::buscarActivo()` y calcula subtotal con `precio`).
  - **Compra**: `ProcesoCompraController` → `ProcesoCompraServicio` (valida stock con `buscarActivoParaActualizar(... FOR UPDATE)`, crea pedido + detalles + reduce stock en transacción, con fallback a `ProductoDAO::reducirStock`).
  - **Login/registro**: `AutenticacionController` → `AutenticacionServicio` (hash `password_verify`, regeneración de sesión, redirect a `admin` si rol `administrador`, si no `panel`; registro fija rol `cliente_minorista`; rate-limit con `LimitadorSolicitudes`).
  - **Panel genérico por rol**: `PanelRolController` (configuración de módulos inline + `ModuloDAO` genérico con whitelists de tabla/columnas + `SolicitudModulo::validar`).
  - **Admin**: `PanelAdministradorController` (KPIs reales desde `PanelAdministradorServicio` + `InteligenciaNegocioServicio`), `AdministradorProductoController` (CRUD con auditoría `product.updated/deleted/deactivated`), `AdministradorFuturoController` (reportes y auditoría con datos reales).
- **[INSPECCIÓN]** Las vistas de administración ya siguen un patrón claro: el controlador prepara claves derivadas `*_vista` (fechas formateadas, clases de estado, URLs, porcentajes) y la vista solo itera y escapa.

## 3) Inventario de vistas

- **[INSPECCIÓN]** Total: **62 archivos** `.php` en `resources/views/` (plantillas `aplicacion/administrador/interno`, componentes, públicas, módulos, roles, comercio-inteligente, errores).
- **[INSPECCIÓN]** Lógica en vistas: las vistas solo usan `e()`, `money()`, `url()`, `asset()`, `csrf_field()`, `nl2br(e())`, `foreach/if` simples y contadores `count()`; **no** consultan BD ni calculan negocio. Los archivos más extensos (`panel/indice.php`, `panel/tablero.php`, `catalogo/indice.php`, `crud/proveedores.php`, `reportes/indice.php`) son réplicas de maquetación, no lógica.
- **[INSPECCIÓN]** Formularios detectados (método/acción/campos relevantes):
  - `publico/catalogo/_tarjeta-producto.php` y `publico/inicio/_tarjeta-producto.php`: POST a `url('cart')` con `add=<id>` + `csrf_field()`; GET a `url('catalog')` con `producto,q,brand,cat,min_price,max_price,sort,page` (solo catálogo).
  - `publico/catalogo/indice.php`: filtros GET (mismos parámetros), modal de detalle con POST a `cart`.
  - `publico/catalogo/detalle.php`: POST `cart` (`add`), migas con `enlaceCategoria`.
  - `roles/internos/administrador/*`: POST con `csrf_field()` a `admin/campaigns`, `admin/inventory`, `admin/products`, `admin/audit` (GET filtros `desde,hasta,usuario,entidad`), `admin/reports` (GET `tipo,desde,hasta`), CRUD genérico (`$urlFormularioCrud`) con campos dinámicos (`textarea/select/select-data/input`).
  - `modulos/panel/indice.php` + `_parciales/david/indice.php`: tabla + editor POST a `$urlFormularioPanel`, desactivar por registro vía `$rutasRegistrosPanel[id]['desactivar']`, confirmación con `data-confirm`.
  - `modulos/cuenta/panel.php`: logout POST + navegación por `$navegacion` (lista de pedidos en `_parciales/lista-pedidos.php`).
- **[INSPECCIÓN]** `data-*` usados por JS (contrato a conservar): `data-table-search`, `data-admin-table`, `data-admin-table-container`, `data-admin-pagination`, `data-admin-dialog-open/close`, `data-confirm`, `data-data-row`, `data-campaign-*` (status/location/time/preview-*/edit-link/empty), `data-supplier-*` (state/city/empty/filter-empty), `data-inventory-*` (category/state/time/empty/filter-empty), `data-audit-detail-*` (id/date/user/action/entity/ip/old/new), `data-modal-*`, `data-gallery-*`, `data-main-product-image`, `data-image-placeholder`, `data-product-detail`, `data-module-date`/`data-module-date-label`, `data-auto-open`, `data-preview-*`.
- **[INSPECCIÓN]** Scripts JS referenciados en vistas: p. ej. `assets/js/david/producto.js?v=20260929-4` (catálogo), además del CSS/layout compartido.
- **[INSPECCIÓN]** Duplicación de vistas: `modulos/panel/indice.php` vs `modulos/panel/_parciales/david/indice.php` comparten ~100 líneas idénticas (tabla + editor); `componentes/administracion/tarjetas-kpi.php` se reutiliza bien (requerido en 4+ vistas). **Código muerto**: `componentes/tarjeta-producto.php` no se incluye desde ninguna vista ni controlador (verificado con búsqueda global en `app`, `routes` y `resources`).

## 4) Hallazgos priorizados (con destino recomendado)

Estado de las 9 hipótesis previas:

| # | Hipótesis | Estado | Evidencia |
|---|---|---|---|
| H1 | Existen presentadores para producto | **CONFIRMADO** | `app/Nucleo/Presentacion/Productos/PresentadorTarjetaProducto.php`, `PresentadorDetalleProducto.php`; usados por `CatalogoController` y `ProductoController::detalle` [INSPECCIÓN] |
| H2 | La ficha de producto repite la preparación del presentador dentro de la vista | **NO CONFIRMADO** | `ProductoController::detalle` usa `PresentadorDetalleProducto` y pasa claves sueltas; `publico/catalogo/detalle.php` y el modal de `catalogo/indice.php` son solo presentación [INSPECCIÓN] |
| H3 | Lógica de cálculo dentro de vistas (campañas, auditoría, inventario, proveedores, reportes) | **NO CONFIRMADO** (ya resuelto) | Los cálculos/porcentajes/fechas se hacen en los controladores (`AdministradorFuturoController`, `PanelAdministradorController`, `AdministradorProductoController`: `$porcentajesCiudad`, `$proveedoresPorCiudadDestacadas`, claves `*_vista`) y las vistas solo pintan [INSPECCIÓN] |
| H4 | `PanelRolController` depende directamente de un DAO | **CONFIRMADO** | Constructor `private ModuloDAO $modulos`; `guardar()/actualizar()/desactivar()` llaman a `modulos->crear/actualizar/desactivar` sin capa de servicio [INSPECCIÓN] |
| H5 | `ModuloDAO::registrarMovimiento()` duplica `InventarioDAO::registrarMovimiento()` | **CONFIRMADO** | Misma lógica de transacción + bloqueo `FOR UPDATE` + ajuste de existencias duplicada en `app/DAO/Panel/ModuloDAO.php` y `app/DAO/Inventario/InventarioDAO.php` (además `ProductoDAO::reducirStock` es una tercera variante usada como fallback en `ProcesoCompraServicio`) [INSPECCIÓN] |
| H6 | Panel genérico y variante "david" duplican preparación/vistas | **CONFIRMADO** | `PanelRolController.php:119` activa `$usarInterfazDavid` para los roles `compras_logistica` y `marketing`; `modulos/panel/indice.php` y `modulos/panel/_parciales/david/indice.php` duplican tabla + editor (~100 líneas; diferencias: selector de fecha, métricas "—", columna Acciones oculta en inventario); además `_parciales/tipos/*`, `david/tablero.php`, `david/tabla.php` [INSPECCIÓN] |
| H7 | Datos demo hardcodeados | **CONFIRMADO** | `app/Soporte/Presentacion/DatosDemostracionPanel.php` (métricas/favoritos/recomendados ficticios), consumido por `PanelRolController` (líneas 17 y 77); `CatalogoInterfaces.php` con fichas de valores fijos ("246", "198", "28", "14"...) [INSPECCIÓN] |
| H8 | Catálogo usa `precio_oferta`, carrito/compra usan `precio` | **CONFIRMADO** | Tarjetas y modal muestran `precio_oferta` (presentadores); `CarritoServicio::resumen()` y `ProcesoCompraServicio` calculan con `$producto['precio']` (columna base de `productos`); `ProductoDAO::buscarActivo()` hace `SELECT *`. Riesgo de negocio: el cliente ve precio ofertado pero paga precio base [INSPECCIÓN] |
| H9 | Permisos sin granularidad de operación/propietario | **CONFIRMADO** | `RolMiddleware` solo valida `AccesoRol::can(rol, modulo)`; las rutas POST `panel/{module}`, `panel/{module}/{id}`, `panel/{module}/{id}/desactivar` usan el mismo middleware; no hay verificación de propietario (p. ej. cualquier rol con acceso a `pedidos` puede cambiar el estado de cualquier pedido). Mitigación parcial: `crud`/`create_only`/`update_only` por módulo [INSPECCIÓN] |

Hallazgos adicionales:

- **[INSPECCIÓN]** Código muerto: `resources/views/componentes/tarjeta-producto.php` no se incluye en ninguna parte y además usa el campo `precio` (incoherente con los presentadores). Destino recomendado: eliminación (previa confirmación del usuario) o reemplazo por el presentador.
- **[INSPECCIÓN]** `ModuloDAO` es un DAO "genérico" con whitelists de tabla/columnas por módulo: superficie grande y centralizadora; el manejo de mensajes flash queda mezclado en el controlador (aceptable, pero conviene servicio).
- **[RIESGO]** Carrito en sesión sin límite de antigüedad: los precios se recalculan al momento del pago (correcto para consistencia, pero junto con H8 puede cobrar un precio distinto al mostrado).

Destinos recomendados (para Fase 2+):
1. `PanelRolController` → extraer `Servicios/Panel/PanelModuloServicio` (configuración de módulos + reglas crud/update_only/create_only) y mantener el DAO solo para SQL.
2. Unificar `registrarMovimiento` en `InventarioDAO` (o servicio de inventario) y hacer que `ModuloDAO` delegue; eliminar la duplicación.
3. Decidir política de precio única (los presentadores ya normalizan `precio_oferta ?? precio`): hacer que carrito/compra usen el mismo precio mostrado — **cambio funcional**, documentar como fix aparte de la reorganización.
4. Roles/permisos: definir matriz operación×módulo (crear/editar/desactivar/ver) y propiedad de registro cuando aplique (pedidos del cliente).
5. Datos demo: mantener `DatosDemostracionPanel` como fuente única ya aislada, marcando qué debe conectarse a datos reales más adelante.

## 5) Funcionalidades reales vs demostración

- **Reales (BD vía PDO)**: catálogo + filtros + paginación, detalle y modal, carrito en sesión, proceso de compra transaccional (pedido + detalles + stock), login/registro, CRUD admin de productos con auditoría real (`AuditoriaServicio`: `product.updated/deleted/deactivated`), campañas (`CampaniaDAO`), movimientos de inventario con transacción, reportes/auditoría con consultas reales (`InteligenciaNegocioServicio`, `AuditoriaServicio`), módulos genéricos por rol (`ModuloDAO`) cuando hay BD — **[INSPECCIÓN]**.
- **Demostración (datos fijos)**: métricas y listados del panel genérico por rol (`DatosDemostracionPanel`: favoritos, recomendados, variaciones "↑ x%"), fichas de `CatalogoInterfaces` (números fijos tipo "246 productos"), alertas de auditoría ("Sin fuente de alertas disponible", botón "Exportar · Próxima iteración" deshabilitado), mini-tendencias SVG fijas en el panel genérico, asistente IA (`AsistenteAdministradorServicio` con respuestas simuladas) — **[INSPECCIÓN]**.
- **Degradación sin BD**: los controladores detectan `conexionDisponible()` y muestran el aviso "La base de datos no está disponible. Ejecuta las migraciones CLI." — **[INSPECCIÓN]**.

## 6) Tests ejecutados y omitidos

- **[EJECUCIÓN]** Lint de sintaxis: `php bin/verificar_sintaxis.php` con PHP 8.2.12 (CLI en `C:\xampp\php\php.exe`): **203 archivos PHP sin errores de sintaxis**.
- **[EJECUCIÓN]** `php -v` → PHP 8.2.12. **`php` no está en el PATH global** (documentado; usar `C:\xampp\php\php.exe`).
- **[EJECUCIÓN]** PHPUnit **no ejecutable**: `vendor/` está incompleto — `vendor/phpunit/phpunit` no existe (sí existe `phpstan`), por lo que `composer test` falla. No se instaló nada (restricción de la fase). Acción futura: `composer install` y re-ejecutar.
- **[INSPECCIÓN]** Suite existente (18 archivos): `tests/Unit/*` (Arquitectura, ArquitecturaVistas, AccesoRol, AutenticacionServicio, CarritoServicio, ProcesoCompraServicio, ProductoServicio, SolicitudProducto, CatalogoEstilos, CatalogoInterfaces), `tests/Feature/*` (5, requieren MySQL de la app o se saltan), `tests/Integration/RepositoriosPdoTest` (exige `DB_TEST_DATABASE` terminado en `_test` — aislado por diseño; usa migraciones propias y rollback).
- **[EJECUCIÓN/OMISIÓN]** No se ejecutaron Feature/Integration: Feature usan la BD configurada de la aplicación (con rollback en transacción) y la restricción de fase prohíbe tocar la BD real; Integration está bien aislado pero requiere variables `DB_TEST_*` que no están definidas. `phpstan` (`vendor/bin/phpstan`) existe pero no se ejecutó para no extender el alcance; disponible para Fase 2.
- **[RIESGO]** No se validó en ejecución el comportamiento HTTP real (no se levantó servidor en la fase).

## 7) Contratos visuales a preservar

- **[INSPECCIÓN]** Rutas y nombres de parámetros: `catalog` con `q, brand, cat, min_price, max_price, sort, page, producto`; `cart` POST `add`; `admin/campaigns?edit=<id>`; módulos `panel/{module}` con `?edit=<id>`; `admin/reports?tipo&desde&hasta`; `admin/audit?desde&hasta&usuario&entidad`; logout POST.
- **[INSPECCIÓN]** Clases y estructura: `admin-page-head`, `admin-panel(-header/-title)`, `admin-table`/`admin-table-wrap`/`admin-table-empty`, `admin-status--*`, `admin-dialog` (elemento `<dialog>` nativo) con `data-auto-open`, `admin-form-grid/-group/-actions`, `admin-alert--error/success`, `admin-pagination`, `admin-chart(-column)` con `--chart-height`, `product-card`, `catalog-*`, `home-*`, `cuenta-*`, `metricas-mockup`/`module-kpis`, `status-pill`, `icon-btn danger`.
- **[INSPECCIÓN]** Atributos `data-*` consumidos por JS (lista completa en la sección 3) — cualquier reorganización de vistas debe mantenerlos idénticos.
- **[INSPECCIÓN]** Comportamiento: mensajes flash en `alert-success/alert-error` (panel) y `admin-alert` (admin); confirmaciones con `data-confirm`; paginación cliente (`data-admin-pagination`); buscador de tabla (`data-table-search`); galería de imágenes con miniaturas/prev/next; modal de producto en catálogo con `data-modal-*`.
- **[INSPECCIÓN]** Responsive/maquetación: se conservará tal cual; Fase 2 no toca CSS.

## 8) Plan concreto de Fase 2

Principios acordados: preservar rutas, formularios, diseño, responsive y comportamiento; lógica de negocio en `Servicios`, SQL en `DAO`; reutilizar los presentadores existentes y `CompositorVistas`; mantener el modelo `Producto`; identificadores en español; evitar una clase por vista; separar los fixes funcionales de la reorganización; separar la extracción de datos demo de su futura conexión a datos reales.

Primeros pasos propuestos (archivos concretos):
1. **Desacoplar `PanelRolController` de `ModuloDAO`** (H4): crear `app/Servicios/Panel/PanelModuloServicio.php` que encapsule `$configuraciones` (hoy inline en el controlador, líneas ~410–438) y delegue en `ModuloDAO`. El controlador queda solo en HTTP + vista. Archivos: `app/Controladores/Panel/PanelRolController.php`, nuevo servicio, `app/DAO/Panel/ModuloDAO.php`.
2. **Unificar movimientos de inventario** (H5): dejar la lógica transaccional en `app/DAO/Inventario/InventarioDAO.php` (o un `Servicios/Inventario/InventarioServicio`) y que `ModuloDAO::registrarMovimiento()` delegue; validar contra `tests/Integration/RepositoriosPdoTest` cuando haya BD de prueba.
3. **Política de precio única** (H8, cambio funcional separado): en `CarritoServicio::resumen()` y `ProcesoCompraServicio` usar el mismo precio que muestra el catálogo (`precio_oferta ?? precio`, igual que los presentadores). Documentar el impacto en pedidos existentes.
4. **Reutilizar tarjetas de producto** (H6/H1): las dos vistas `_tarjeta-producto.php` (inicio y catálogo) comparten el presentador; evaluar un único parcial alimentado por `PresentadorTarjetaProducto` (sin unificar clases CSS si cambian estilos por contexto) y eliminar `componentes/tarjeta-producto.php` (código muerto) tras confirmación.
5. **Permisos** (H9): matriz de permisos operación×módulo en `AccesoRol` (o configuración de módulo con `puede_crear/editar/desactivar`) y chequeo de propietario para pedidos/cotizaciones del cliente; `RolMiddleware` y `PanelRolController` consultan el mismo servicio.
6. **Datos demo** (H7): mantener `DatosDemostracionPanel` como única fuente (ya lo es para `PanelRolController`) y añadir etiqueta visible "datos de demostración" o marcado en `CatalogoInterfaces`, dejando preparada la sustitución por servicios reales.
7. **Duplicación david/genérico** (H6): fusionar la parte común (tabla + editor) en un parcial compartido con parámetros (`$mostrarAcciones`, `$selectorFecha`, `$metricasPendientes`), conservando las clases CSS y `data-*` exactos de ambas variantes.
8. **Tooling**: ejecutar `composer install` para completar `vendor/` y correr `composer test` (Unit) + `analyse` (phpstan nivel 4) + `style` (PSR12) tras cada paso; Features/Integration solo contra BD de prueba (`DB_TEST_DATABASE` terminado en `_test`).

Criterio de fin de Fase 2: lint 203+ archivos OK, Unit en verde, `phpstan` sin regresiones, rutas y `data-*` intactos (verificables con `ArquitecturaVistasTest` y `NavegacionRolesTest`), y sin cambios funcionales no documentados.

## 9) Fase 2 — limpieza de vistas y preparación de datos

### Referencia y alcance

- **[INSPECCIÓN]** Se conservó el árbol de trabajo que ya estaba modificado al iniciar esta fase. No se ejecutaron `reset`, `clean`, restauraciones, commits ni push.
- **[EJECUCIÓN]** Lint inicial: `C:\xampp\php\php.exe bin\verificar_sintaxis.php` — 203 archivos PHP sin errores.
- **[INSPECCIÓN]** Las 62 vistas de `resources/views/` se revisaron recursivamente. Las plantillas ya basadas en datos preparados se conservaron; no se alteraron archivos correctos para aumentar el número de cambios.
- **[INSPECCIÓN]** El plan enumerado en la sección 8 era una propuesta histórica. En esta ejecución se respetaron los límites de Fase 2: no se creó `PanelModuloServicio` y no se iniciaron los cambios funcionales de Fase 3.

### Cambios realizados en esta fase

Los archivos siguientes recibieron cambios de Fase 2. Algunos ya estaban modificados o sin seguimiento antes de esta fase; la lista no atribuye a Fase 2 sus diferencias anteriores.

**Presentadores y composición de vistas:**

- `app/Nucleo/Presentacion/Administracion/PresentadorCampanias.php`
- `app/Nucleo/Presentacion/Administracion/PresentadorCrudAdministrativo.php`
- `app/Nucleo/Presentacion/Administracion/PresentadorInformesAdministrador.php`
- `app/Nucleo/Presentacion/Administracion/PresentadorInventario.php`
- `app/Nucleo/Presentacion/Administracion/PresentadorPedidosAdministrador.php`
- `app/Nucleo/Presentacion/Administracion/PresentadorTableroAdministrador.php`
- `app/Nucleo/Presentacion/Catalogo/PresentadorCatalogo.php`
- `app/Nucleo/Presentacion/ComercioInteligente/PresentadorComercioInteligente.php`
- `app/Nucleo/Presentacion/Panel/PresentadorPanelRol.php`
- `app/Nucleo/Presentacion/Productos/PresentadorDetalleProducto.php` (ajustado)
- `app/Nucleo/Presentacion/CompositorVistas.php`

**Controladores coordinadores actualizados:**

- `app/Controladores/Campanias/AdministradorCampaniaController.php`
- `app/Controladores/Catalogo/CatalogoController.php`
- `app/Controladores/Categorias/CategoriaController.php`
- `app/Controladores/Clientes/ClienteController.php`
- `app/Controladores/ComercioInteligente/ComercioInteligenteController.php`
- `app/Controladores/Inventario/InventarioController.php`
- `app/Controladores/Panel/AdministradorFuturoController.php`
- `app/Controladores/Panel/PanelAdministradorController.php`
- `app/Controladores/Panel/PanelRolController.php`
- `app/Controladores/Pedidos/AdministradorPedidoController.php`
- `app/Controladores/Productos/AdministradorProductoController.php`
- `app/Controladores/Productos/ProductoController.php`
- `app/Controladores/Proveedores/ProveedorController.php`

**Vistas editadas:**

- `resources/views/modulos/panel/_parciales/david/tablero.php`
- `resources/views/roles/internos/administrador/campanias/indice.php`

También se actualizó este informe, `BASE_REFACTORIZACION.md`.

### Lógica extraída y destino

- El panel por rol delega a `PresentadorPanelRol` la preparación de campos, columnas, acciones, rutas de registros, distribución y opciones de periodo. `CompositorVistas` prepara la información compartida por layouts.
- El tablero administrativo, inventario, campañas, pedidos, informes/auditoría y listados de categorías, clientes, proveedores y productos delegan fechas, estados, iconos/imágenes, URLs, porcentajes, filtros, opciones de formulario y tarjetas KPI a presentadores agrupados por responsabilidad. Los controladores conservan consultas, validación, coordinación del servicio y renderizado.
- El detalle del producto mantiene `PresentadorDetalleProducto`; ahora el enlace de categoría y los atributos de la ficha también salen de ese presentador. Se conserva `PresentadorTarjetaProducto` para tarjetas existentes.
- `PresentadorCatalogo` prepara marcas/categorías, enlaces que conservan los filtros y enlaces de paginación. `CatalogoController` mantiene la validación de la solicitud y consulta el servicio.
- `PresentadorComercioInteligente` prepara imágenes, iconos, enlaces, métricas, productos mayoristas y filas comparativas. La selección y los datos de la comparativa B2B siguen originándose en `ProductoServicio::todosActivos()` y `ProductoServicio::caracteristicas()`; la tabla no contiene nombres, especificaciones ni precios de laptops incrustados en la vista o en el presentador. Los nombres de fila, como «Precio registrado» y «Existencias», son rótulos de presentación.
- En las dos vistas editadas se sustituyeron bloques de preparación por iteraciones sobre datos ya preparados y se verticalizó el HTML. Se conservaron los contratos existentes de formularios, rutas, clases, IDs y `data-*`.
- La comparación de productos B2B sigue mostrando los productos activos encontrados en el catálogo y sus características asociadas; si la consulta no devuelve laptops, la vista mantiene su estado vacío. No se añadió contenido de demostración nuevo.

### Componentes y datos conservados

- Se reutilizan `PresentadorTarjetaProducto`, `PresentadorDetalleProducto`, `CompositorVistas` y los helpers visuales ya existentes.
- No se añadieron modelos, DTO globales, bibliotecas ni una clase por plantilla. Los presentadores nuevos cubren grupos concretos de preparación administrativa, catálogo, panel y comercio inteligente.
- Los datos de demostración actuales permanecen sin cambios: `DatosDemostracionPanel`, `CatalogoInterfaces` y los arreglos de conversaciones/prompts del asistente mayorista continúan siendo datos de demostración. No se conectaron funcionalidades nuevas ni se añadieron etiquetas visibles.
- `resources/views/componentes/tarjeta-producto.php` se conserva como candidato para la fase 5 según la inspección previa; no se borró ni se alteró.

### Ejemplos del cambio

Antes, el controlador preparaba cada imagen, icono y URL dentro de `array_map`:

```php
$productos = array_map(static function (array $producto): array {
    // imagen, icono y enlace para la vista
    return $producto;
}, $productos);
```

Ahora el controlador coordina y entrega la colección al presentador:

```php
$productos = PresentadorComercioInteligente::presentarComparacion($productos);
```

Antes, `CatalogoController` creaba una closure de URLs y un bucle de paginación; ahora esa construcción se centraliza en:

```php
$enlacesVista = PresentadorCatalogo::presentarEnlaces($filtros, $paginacion);
```

Los nombres de claves usados por las vistas siguen siendo los mismos.

### Verificación

- **[EJECUCIÓN]** Lint final: `C:\xampp\php\php.exe bin\verificar_sintaxis.php` — 212 archivos PHP sin errores de sintaxis.
- **[EJECUCIÓN]** PHPStan nivel 4: `vendor/bin/phpstan analyse app --autoload-file=bootstrap/funciones.php --level=4 --no-progress --debug` — sin errores.
- **[EJECUCIÓN]** `git diff --check` sobre archivos rastreados de Fase 2 — sin errores de espacios finales; Git solo avisó de conversiones LF/CRLF.
- **[EJECUCIÓN]** El `git diff --check` global reporta 334 líneas con espacios finales en el árbol de trabajo ya sucio, incluidas rutas ajenas a esta fase. No se hizo una limpieza global para no tocar cambios previos. Los archivos presentadores nuevos no contienen espacios finales.
- **[INSPECCIÓN]** El escaneo de `resources/views/` no encontró closures/funciones, `array_map`/`array_filter`, ordenamientos, formateo de fechas, construcción de query strings, JSON ni cálculos gráficos dentro de las plantillas revisadas. Permanecen los bucles, condiciones sencillas, helpers de escape y formato permitidos.
- **[BLOQUEO]** PHPUnit no está disponible (`vendor/phpunit/phpunit/phpunit` no existe). Por ello no se ejecutó `ArquitecturaVistasTest` ni el resto de Unit/Feature.
- **[OMISIÓN SEGURA]** `DB_TEST_DATABASE` no está definido; no se ejecutaron pruebas Feature/Integration que pudieran usar la base configurada ni se accedió a una base de datos real.
- **[BLOQUEO]** No se pudo renderizar el HTML/DOM con datos de una BD de prueba. La comprobación realizada fue estática; no se afirma que el diseño se haya verificado en navegador.

### Pendientes para Fase 3

Sin cambios en esta fase:

1. Definir y aplicar una sola política para `precio_oferta` frente a `precio` en carrito y compra.
2. Añadir autorización por operación y propietario de registro donde corresponda.
3. Unificar el proceso transaccional de movimientos de inventario.
4. Reorganizar los casos de uso del panel genérico sin añadir un servicio que solo reenvíe llamadas al DAO.

La posible eliminación del componente antiguo queda para Fase 5, después de verificar referencias dinámicas y pruebas disponibles.

### Flujo actual de preparación

El controlador recibe la solicitud y consulta los servicios existentes. Después entrega los registros y el contexto necesario a un presentador concreto; este devuelve un array con valores de presentación, URLs y etiquetas. La vista recibe esas claves y las representa con HTML, bucles simples y `e()`, `formatear_dinero()` o los helpers ya existentes. Las reglas de negocio, consultas, autorización, CSRF y rutas no se modificaron como parte de Fase 2.
## 10) Fase 3 — reglas de precio, autorización e inventario
### Alcance y referencia
- **[INSPECCIÓN]** Se conservó el árbol de trabajo previo. No se ejecutaron `reset`, `clean`, restauraciones, commits, push ni las fases 4, 5 o 6.
- **[EJECUCIÓN]** Lint inicial: `C:\xampp\php\php.exe bin/verificar_sintaxis.php` — 212 archivos PHP sin errores.
- **[EJECUCIÓN]** PHPStan inicial nivel 4 sobre `app` — sin errores.
- **[INSPECCIÓN]** `composer.json` no declara scripts automáticos previos a la instalación, pero no existe `composer.lock`. No se instalaron ni resolvieron dependencias.
- **[BLOQUEO]** `vendor/bin/phpunit` es solo el lanzador; falta `vendor/phpunit/phpunit/phpunit`. PHPUnit no pudo ejecutarse.
- **[OMISIÓN SEGURA]** `tests/bootstrap.php` solo carga `bootstrap/aplicacion.php`. `DB_TEST_DATABASE` no está configurado; no se ejecutaron suites Feature/Integration con acceso potencial a la base configurada.
- **[ACLARACIÓN FASE 4]** La versión de la plantilla en `HEAD` contenía productos, especificaciones y precios de laptops escritos directamente en ella. Como el árbol ya tenía cambios sin confirmar al inicio de las fases, esa diferencia no permite determinar en qué momento se incorporó la preparación dinámica. En el estado actual, la comparación B2B obtiene productos y características mediante `ProductoServicio::todosActivos()` y `caracteristicas()`; no se añadieron datos de demostración de productos.
### A. Precio efectivo
- `Producto::precioEfectivoEnCentimos()` es la política única: una oferta no nula y válida, incluido `0.00`, tiene prioridad; solo `NULL` o ausencia usa `precio`. Importe negativo, no numérico, no finito o superior al límite del esquema se rechaza con `DomainException`.
- `Producto::subtotalEnCentimos()` y `MAXIMO_CENTIMOS` respetan el límite `DECIMAL(10,2)`. El validador de productos usa el mismo límite.
- Las tarjetas y el detalle, el catálogo, las recomendaciones y comparaciones inteligentes, el portal mayorista, el carrito y el proceso de confirmación usan el precio de dominio. Los filtros de rango y el orden por precio del `ProductoDAO` emplean `COALESCE(precio_oferta, precio)`.
- El carrito calcula subtotales y total en centavos. Al confirmar, `ProcesoCompraServicio` vuelve a cargar cada producto con bloqueo dentro de la transacción, calcula de nuevo el precio vigente del servidor y guarda ese importe como precio unitario y total. No lee importes del formulario. Los detalles históricos no se recalculan ni modifican.
- Los precios por volumen ya preparados para el portal conservan sus factores existentes; se calculan desde el precio efectivo y se redondean a dos decimales. Esto registra pedidos; no integra pagos electrónicos.
### B. Autorización por operación y alcance de pedidos
`AccesoRol::puedeAcceder()` controla ver/listar por módulo. `AccesoRol::puedeOperar()` implementa la matriz de escritura y `PanelRolController` aplica además `crud`, `create_only`, `update_only` y los módulos que admiten desactivación en cada POST.
| Rol | Operaciones de escritura autorizadas en el panel genérico |
| --- | --- |
| `administrador` | Permisos globales de política; el panel redirige a rutas administrativas, que conservan su middleware de administrador y CSRF. |
| `compras_logistica` | Crear movimientos de inventario; crear, editar y desactivar proveedores y compras. |
| `ventas_mayoristas` | CRUD de clientes mayoristas y cotizaciones; cambiar estado de pedidos mayoristas. |
| `ventas_minoristas` | CRUD de clientes minoristas; cambiar estado de pedidos minoristas. |
| `marketing` | Solo lectura/listado en los módulos habilitados. |
| `cliente_minorista`, `cliente_mayorista` | Solo lectura/listado habilitado; no pueden crear, editar, desactivar ni cambiar estados comerciales. |
- Los módulos no listados para escritura permanecen de consulta, incluso si el rol puede abrirlos. La denegación real ocurre en backend; los formularios y acciones del panel también reflejan los permisos.
- Las consultas del panel separan pedidos minoristas y mayoristas por el rol de la cuenta asociada. Las actualizaciones de estado aplican el mismo filtro en `PedidoDAO`; el DAO genérico ya no ofrece una actualización de pedido sin ese alcance.
- El panel de cliente conserva sus listas demostrativas. Ahora retorna antes de consultar listados o resúmenes operativos; no se conectó esa demostración a registros nuevos. No existe actualmente una vista de pedidos personales respaldada por BD. El detalle real de pedidos continúa en una ruta protegida por `AdministradorMiddleware`.
- Las combinaciones cuya autorización funcional no está definida en el código permanecen solo lectura: por ejemplo, escritura de Marketing en campañas/promociones y acciones de clientes sobre cotizaciones. Tampoco se habilitaron operaciones para garantías, devoluciones o reclamaciones sin un caso de uso CRUD existente.
- Las rutas y el middleware CSRF no cambiaron. La denegación directa informa el motivo mediante el mensaje del panel.
### C. Recorrido transaccional de inventario y compra
- `InventarioServicio::registrar()` valida identificador, tipo, cantidad positiva, responsable y notas. `InventarioDAO::registrarMovimiento()` conserva el bloqueo del producto activo, la lectura del stock bloqueado, el cálculo entrada/salida/ajuste, el registro de movimiento y la actualización de existencias dentro de `Conexion::transaccion()`.
- Se eliminó la segunda implementación SQL privada `ModuloDAO::registrarMovimiento()`. El panel genérico envía los movimientos a `InventarioServicio`.
- `ProcesoCompraServicio` ahora requiere `RepositorioInventarioInterfaz`. El contenedor ya tenía ese contrato enlazado a `InventarioDAO`; se revisaron los consumidores y se actualizó la prueba que instanciaba la compra directamente.
- La implementación de compra carga y bloquea el producto, comprueba stock, guarda pedido y detalles, y registra una salida por línea usando la misma `Conexion`. La transacción anidada del DAO usa el savepoint de infraestructura; el código solicita la reversión conjunta ante una excepción. El rollback real de este recorrido no se ha verificado en MySQL. El fallback de checkout a `ProductoDAO::reducirStock()` se retiró; el método y su contrato se mantienen porque `RepositoriosPdoTest` lo consume directamente.
- No se descuenta stock por dos recorridos ni se registra un movimiento duplicado al confirmar un pedido.
### D. Coordinación del panel
- `PanelRolController` coordina autorización, validación de la solicitud, mensajes, redirección y selección del servicio. Reutiliza `CategoriaServicio`, `ClienteServicio`, `ProveedorServicio`, `InventarioServicio` y `PedidoServicio` en sus casos correspondientes.
- `ModuloDAO` conserva SQL de módulos genéricos que no tienen servicio específico. Ya no coordina movimientos de inventario ni actualiza pedidos sin filtro de segmento. No se creó `PanelModuloServicio` ni un servicio de reenvío.
- Se conservaron rutas, nombres de campos, estructura HTML, clases, IDs, atributos `data-*` y formularios. No se modificó CSS ni se conectaron datos de demostración a procesos nuevos.
### Archivos modificados durante Fase 3
Los archivos ya modificados o sin seguimiento al iniciar esta fase siguen siendo cambios previos; esta lista solo identifica los que recibieron ediciones durante Fase 3.
- Modelo, validación, consultas y presentación: `app/Modelos/Productos/Producto.php`, `app/Validacion/Productos/SolicitudProducto.php`, `app/DAO/Productos/ProductoDAO.php`, `app/Nucleo/Presentacion/Productos/PresentadorTarjetaProducto.php`, `app/Nucleo/Presentacion/Productos/PresentadorDetalleProducto.php`, `app/Nucleo/Presentacion/ComercioInteligente/PresentadorComercioInteligente.php` y `app/Servicios/ComercioInteligente/ComercioInteligenteServicio.php`.
- Compra e inventario: `app/Servicios/Compra/CarritoServicio.php`, `app/Servicios/Compra/ProcesoCompraServicio.php` y `app/Servicios/Inventario/InventarioServicio.php`.
- Autorización y panel: `app/Soporte/Autorizacion/AccesoRol.php`, `app/Controladores/Panel/PanelRolController.php`, `app/DAO/Panel/ModuloDAO.php`, `app/DAO/Contratos/RepositorioPedidoInterfaz.php`, `app/DAO/Pedidos/PedidoDAO.php` y `app/Servicios/Pedidos/PedidoServicio.php`.
- Pruebas: `tests/Unit/AccesoRolTest.php`, `tests/Unit/ProcesoCompraServicioTest.php`; se añadieron `tests/Unit/ProductoPrecioTest.php`, `tests/Unit/PedidoServicioTest.php`, `tests/Unit/InventarioServicioTest.php` y `tests/Feature/PanelAutorizacionTest.php`.
- Este informe, `BASE_REFACTORIZACION.md`.
### Verificación y bloqueos
- **[EJECUCIÓN]** Lint final: `C:\xampp\php\php.exe bin/verificar_sintaxis.php` — 216 archivos PHP sin errores.
- **[EJECUCIÓN]** PHPStan nivel 4 sobre `app` — sin errores.
- **[EJECUCIÓN]** Comprobaciones PHP aisladas sin PHPUnit: oferta cero, redondeo a centavos, subtotal múltiple, rechazo de oferta negativa, filtro/orden SQL del catálogo, matriz de permisos, listados minorista/mayorista y rechazo de una actualización fuera del segmento. Se usó SQLite en memoria; estas comprobaciones no verifican los bloqueos ni el rollback de MySQL.
- **[EJECUCIÓN]** Comprobación HTTP aislada: POST con CSRF inválido devolvió 419; el cliente no pudo cambiar directamente un estado y `update_only` rechazó creación. Las rutas comprobadas no consultaron una BD.
- **[BLOQUEO]** Las pruebas PHPUnit añadidas y las suites existentes no se pudieron ejecutar: falta el paquete PHPUnit detrás de `vendor/bin/phpunit` y no hay `composer.lock` para instalar dependencias bloqueadas.
- **[OMISIÓN SEGURA]** No se ejecutó ninguna prueba Feature/Integration contra la base configurada. Los casos de bloqueo de stock MySQL, producto inactivo, operaciones entrada/salida/ajuste persistidas y rollback del DAO quedan pendientes de una BD aislada de pruebas.
- **[INSPECCIÓN]** `git diff --check` señaló espacios finales en comentarios preexistentes de `ProductoDAO.php` (líneas 99, 109, 127, 128, 136 y 147 del archivo actual) y un aviso LF/CRLF. No se hizo limpieza ajena a los cambios de esta fase.
- **[DECISIÓN PENDIENTE]** Las operaciones de Marketing sobre campañas/promociones, las acciones de clientes sobre cotizaciones y las operaciones de garantías/devoluciones/reclamaciones requieren una regla de negocio explícita antes de habilitar escritura.
### Explicación breve para la sustentación
La corrección fija una sola fuente de precio para que el catálogo, el carrito y el pedido coincidan, y conserva el precio unitario histórico de cada pedido. La implementación realiza las operaciones de inventario con bloqueo dentro de la transacción de compra y solicita revertirlas ante un fallo; falta verificar ese rollback con MySQL. La matriz backend evita que la visibilidad de un módulo implique permisos de escritura y limita cada equipo operativo a su función; las comprobaciones aisladas no tocaron la base real.

## 11) Fase 4 — identificadores, comentarios y documentación

### Referencia y alcance

- **[INSPECCIÓN]** Se conservó el árbol de trabajo que ya contenía cambios de fases anteriores y otros cambios locales. No se ejecutaron `reset`, `clean`, restauraciones, commits, push ni cambios de datos.
- **[EJECUCIÓN]** Antes de los renombrados: lint de 216 archivos PHP sin errores y PHPStan nivel 4 sin errores.
- **[INSPECCIÓN]** Se revisaron los archivos propios de `app`, `bootstrap`, `routes`, `config`, `resources`, `public/assets/js`, `bin` y `tests`. No se renombraron directorios ni clases propias; se conservaron vendor, rutas, claves persistidas, parámetros HTTP, columnas, sesiones y selectores de interfaz.
- **[ACLARACIÓN]** Como no existe una captura del árbol al inicio de Fase 4, Git no permite atribuir cada diferencia de archivo exclusivamente a esta fase. Los grupos siguientes identifican las definiciones y referencias que se uniformaron; las listas de fases 2 y 3 conservan su atribución histórica.
- **[OMISIÓN SEGURA]** No se instalaron dependencias ni se ejecutaron pruebas contra la base configurada.

### Archivos y grupos afectados

- Definiciones centrales: `bootstrap/funciones.php`, `app/Soporte/GeneradorUrl.php`, `app/Nucleo/Entorno.php`, `app/Soporte/Sesion/GestorSesion.php`, `app/Nucleo/Http/Solicitud.php`, `app/Soporte/Autorizacion/AccesoRol.php`, `app/DAO/Contratos/RepositorioProductoInterfaz.php`, `app/DAO/Productos/ProductoDAO.php` y `app/Servicios/Productos/ProductoServicio.php`.
- Consumidores de esas funciones y métodos: referencias PHP en `app`, `bootstrap`, `routes`, `config`, `resources/views`, `bin` y `tests`, incluyendo llamadas directas, implementaciones de interfaces, dobles de prueba, callbacks de funciones y claves compartidas entre presentadores y vistas.
- Identificadores de presentación: `app/Nucleo/BaseDatos/Conexion.php`, `app/Nucleo/Presentacion/CompositorVistas.php`, `app/Nucleo/Presentacion/Panel/PresentadorPanelRol.php`, `resources/views/componentes/administracion/asistente.php`, `resources/views/modulos/panel/_parciales/david/tablero.php` y `resources/views/roles/internos/administrador/crud/proveedores.php`.
- JavaScript propio: `public/assets/js/administrador.js`, `public/assets/js/david/panel.js` y el comentario de `public/assets/js/david/producto.js`; también se revisó `public/assets/js/aplicacion.js` para comprobar que sus nombres internos y contratos DOM permanecieran coherentes.
- Pruebas y documentación: referencias en `tests/Unit`, `tests/Feature` y `tests/Integration`, con ajustes concretos en `tests/Unit/ArquitecturaTest.php`, `tests/Feature/NavegacionRolesTest.php` y `tests/Integration/RepositoriosPdoTest.php`; este informe, `BASE_REFACTORIZACION.md`.

### Nombres principales

| Antes | Ahora | Alcance |
| --- | --- | --- |
| `app()` | `resolver_servicio()` | Resolución del contenedor |
| `config()` | `configuracion()` | Lectura de configuración |
| `money()` | `formatear_dinero()` | Formato de importes |
| `url()` / `asset()` | `url_interna()` / `url_recurso_estatico()` | Generación de URL interna y de recursos |
| `product_image_url()` / `product_category_icon()` | `url_imagen_producto()` / `icono_categoria_producto()` | Presentación de productos |
| `redirect()` / `response()` | `redirigir()` / `respuesta_http()` | Respuestas HTTP |
| `current_user()` / `is_admin()` / `user_role()` / `user_can()` | `usuario_actual()` / `es_administrador()` / `rol_usuario_actual()` / `usuario_puede_acceder()` | Sesión y permisos |
| `cart_count()` / `product_visual()` | `unidades_carrito()` / `representacion_visual_marca()` | Carrito y presentación |
| `active_campaigns()` / `campaign_url()` / `campaign_image()` | `campanias_activas()` / `url_campania()` / `url_imagen_campania()` | Campañas |
| `GeneradorUrl::asset()` | `GeneradorUrl::urlRecursoEstatico()` | Recursos estáticos |
| `Entorno::load()` / `Entorno::bool()` | `Entorno::cargarDesdeArchivo()` / `Entorno::leerBooleano()` | Entorno |
| `GestorSesion::start()` | `GestorSesion::iniciar()` | Sesión |
| `Solicitud::capture()` / `normalizePath()` | `Solicitud::capturarActual()` / `Solicitud::normalizarRuta()` | Solicitudes HTTP |
| `AccesoRol::normalize()` / `can()` / `navigation()` / `label()` | `normalizarRol()` / `puedeAcceder()` / `navegacion()` / `etiqueta()` | Acceso por rol |
| `ACCESS` / `OPERATION_ACCESS` / `NAVIGATION` | `ACCESOS` / `ACCESOS_POR_OPERACION` / `NAVEGACION` | Constantes propias de acceso |
| `RepositorioProductoInterfaz::delete()` | `eliminarFisicamente()` | Contrato de repositorio e implementación DAO/servicio |
| `$categoriaId`, `$productoId`, `$pedidoId`, `$usuarioId` | `$idCategoria`, `$idProducto`, `$idPedido`, `$idUsuario` | Parámetros, variables y pruebas |
| `$quickQueries`, `$maxCompra`, `$dbname`, `$prov` | `$consultasRapidas`, `$compraMaxima`, `$nombreBaseDatosPredeterminado`, `$proveedor` | Contexto de vista, conexión y presentación |
| `inputBusqueda`, `selectEstado`, `inputFechaFiltro`, `detalleMockup`, `archivoBlob` | `campoBusqueda`, `selectorEstado`, `campoFechaFiltro`, `detalleTablaFlotante`, `archivoCsv` | Variables locales de JavaScript; selectores DOM sin cambios |
| `test...Dashboard...` / provider `dashboards` | `test...Tablero...` / `tableros` | Pruebas; se conserva el prefijo `test` |

Se actualizaron también `$repositorioConfig`, `$item` y `$label` a `$repositorioConfiguracion`, `$detalleProducto` y `$etiquetaColumna`, y los ayudantes de prueba `get()`/`post()` a `solicitarGet()`/`enviarPost()`. Las claves internas de las vistas que acompañaban esos datos se actualizaron en sus consumidores. No se modificó el formato de solicitudes, SQL ni datos persistidos.

### Criterios y excepciones conservadas

- Se usaron nombres en español que describen la responsabilidad o el dato, con el orden `idEntidad` para identificadores. Se conservaron índices cortos (`$i`, `$j`) en recorridos simples y nombres técnicos cuando describen una API o formato.
- Permanecen `__construct`, `setUp`, `tearDown` y el prefijo PHPUnit `test`; son convenciones del lenguaje o de la herramienta. También se conservan `PDO`, `password_hash()`, `password_verify()`, `prepare()`, `execute()`, `fetch()`, `Closure` y APIs del navegador.
- `Enrutador::post()` y `Enrutador::delete()` nombran verbos HTTP. Se mantienen `e()`, `csrf_field()` y `csrf_token()` por ser ayudantes compartidos por las vistas y los formularios de seguridad.
- Se preservaron rutas y parámetros públicos, nombres de campos y claves HTTP, de sesión y base de datos, además de nombres de tablas, columnas, clases CSS, IDs, atributos `data-*`, claves del DOM, textos visibles y contratos del navegador. Las palabras inglesas de esos contratos no son identificadores internos por traducir.
- No se renombraron clases: los nombres propios ya estaban en español y se conserva el mapeo PSR-4 existente bajo `App\\`.

### Comentarios y documentación

- Se retiraron comentarios repetitivos que solo copiaban el nombre de un constructor o repetían lo que la siguiente instrucción ya expresaba; se conservaron los PHPDoc de tipos útiles para análisis estático.
- Se aclaró que `password_hash()` genera el hash que se almacena y que `password_verify()` compara la contraseña recibida con ese hash.
- Se corrigieron descripciones de captura y normalización de solicitudes, registro de rutas GET/POST/DELETE, búsqueda de configuración, eliminación física o lógica de productos, carrito, campañas, imágenes, repositorios y opciones de contenedor.
- Los comentarios de la prueba de transacción describen el comportamiento que el caso intenta verificar; no se presentan como evidencia de una ejecución MySQL.
- Se corrigió la atribución temporal de la comparación B2B: que `HEAD` tuviera laptops incrustadas no permite fechar el cambio si el árbol de trabajo ya estaba modificado. La fuente actual continúa siendo el catálogo activo y sus características.
- Se dejó explícito que los pedidos personales del panel siguen siendo demostrativos, que aún hay decisiones de permisos de escritura pendientes y que SQLite no acredita los bloqueos ni el rollback real de MySQL.

### Verificación

- **[EJECUCIÓN]** Lint final: `C:\xampp\php\php.exe bin\verificar_sintaxis.php` — 216 archivos PHP sin errores.
- **[EJECUCIÓN]** PHPStan nivel 4 sobre `app` con `bootstrap/funciones.php` como archivo de carga — sin errores. Una primera pasada detectó una referencia restante a `$dbname`; se actualizó y la pasada final quedó limpia.
- **[EJECUCIÓN]** `node --check` en los cuatro archivos JavaScript de `public/assets/js` — sin errores de sintaxis.
- **[EJECUCIÓN]** Comprobación de autocarga PSR-4: 120 símbolos de `app` coinciden con su ruta y se cargan automáticamente. Comprobación de rutas: 66 manejadores registrados apuntan a clases y métodos existentes.
- **[EJECUCIÓN]** Búsqueda de referencias: sin llamadas a los ayudantes ni métodos anteriores; `Enrutador::post()` y `Enrutador::delete()` se conservan porque registran verbos HTTP. Se comprobó también el callback de imágenes usado por `array_map`.
- **[BLOQUEO]** PHPUnit no ejecutó la suite. `vendor/bin/phpunit` existe como lanzador, pero no está el paquete `vendor/phpunit/phpunit/phpunit`; la invocación solo mostró el error de inclusión. No se instalaron dependencias.
- **[OMISIÓN SEGURA]** `DB_TEST_DATABASE` no está definida. No se ejecutaron pruebas Feature/Integration con acceso a una base configurada ni se modificó una base de datos.

### Pendientes para Fase 5

1. Verificar referencias dinámicas y pruebas disponibles antes de eliminar componentes candidatos, incluido `resources/views/componentes/tarjeta-producto.php`, señalado en la inspección anterior.
2. Retomar la limpieza/eliminación de archivos obsoletos solo con una referencia clara y sin incluir las eliminaciones locales preexistentes que se conservaron en este trabajo.
3. Mantener para su definición funcional las operaciones todavía pendientes de permisos, como campañas de Marketing y cotizaciones de clientes. La verificación transaccional real en MySQL requiere una base aislada de pruebas.

Al cerrar la Fase 4, aún no se habían iniciado las Fases 5 ni 6.

## 12) Fase 5 — limpieza de duplicaciones y archivos obsoletos

### Referencia y alcance

- **[INSPECCIÓN]** Se leyó este informe y se revisó el estado local antes de editar. El árbol ya contenía cambios, eliminaciones y archivos sin seguimiento; el estado inicial quedó guardado en `%TEMP%\fase5_refactor_20261001\estado_git_inicial.txt`.
- **[EJECUCIÓN]** Se guardaron copias de los archivos existentes que se editaron o eliminaron en `%TEMP%\fase5_refactor_20261001\referencia_fuentes\`, antes de aplicar cambios. Las comparaciones de esta fase usan esas copias, no `HEAD`.
- **[EJECUCIÓN]** Lint inicial: `C:\xampp\php\php.exe bin\verificar_sintaxis.php` — 216 archivos PHP sin errores.
- **[EJECUCIÓN]** PHPStan inicial, nivel 4 sobre `app` con `bootstrap/funciones.php` — sin errores.
- **[INSPECCIÓN]** `vendor/bin/phpunit` existe, pero falta el paquete `vendor/phpunit/phpunit/phpunit`. `DB_TEST_DATABASE` no está definida en el proceso; la clave de `.env.example` está vacía. No se ejecutaron pruebas que pudieran acceder a una base real.
- No se usaron `reset`, `clean`, restauraciones, commits, push ni cambios de dependencias.

### Cambios realizados

**Componente de producto obsoleto:**

- Se eliminó `resources/views/componentes/tarjeta-producto.php`. La inspección incluyó `require`/`include`, las vistas pasadas a `Vista::renderizar()`, controladores, rutas, configuración, scripts y pruebas. No se encontró una inclusión ni una ruta dinámica local del componente; su única referencia ejecutable era la lista de archivos esenciales de `ArquitecturaVistasTest`.
- `InicioController` y `CatalogoController` preparan las tarjetas con `PresentadorTarjetaProducto`; las vistas de inicio y catálogo incluyen sus propios parciales activos, `publico/inicio/_tarjeta-producto.php` y `publico/catalogo/_tarjeta-producto.php`. La prueba arquitectónica ahora verifica esos dos parciales.
- El componente eliminado esperaba el arreglo crudo `$producto` y formaba su propio enlace y precio. No se retiró CSS: sus selectores visuales también los usan las tarjetas actuales. No se borraron imágenes, rutas de recursos, archivos de almacenamiento ni datos de producto.

**Editor compartido del panel:**

- Se movió el bloque de edición común a `resources/views/modulos/panel/_parciales/editor.php`. `modulos/panel/indice.php` y `modulos/panel/_parciales/david/indice.php` lo incluyen desde sus rutas correspondientes. El parcial utiliza las variables ya disponibles en el ámbito de la vista; no añade parámetros, banderas ni lógica de negocio.
- Se mantuvieron los formularios, nombres de campo, clases, ID `editor`, escape con `e()`, `csrf_field()`, enlaces y condiciones de visibilidad. No se unificaron las tablas ni los encabezados: la variante David oculta acciones en inventario, tiene su propia tabla no CRUD y usa selector de fecha; la variante genérica conserva sus indicadores y sus parciales por tipo.

**Comentarios sin contenido útil:**

- Se retiraron seis descripciones genéricas del tipo «Describe la responsabilidad del método» en `AdministradorFuturoController`, `ComercioInteligenteController` y `PanelRolController`. Se conservaron las anotaciones `@param` y `@return` que aportan tipos; no cambiaron firmas ni cuerpos de métodos.

No se modificaron rutas, JavaScript, CSS, precios, permisos, CSRF, consultas, seguridad, modelo `Producto`, reglas de compra ni persistencia.

### Archivos con cambios atribuibles a esta fase

Los siguientes cambios se identificaron al compararlos con las copias previas; varios archivos ya estaban modificados al inicio.

- **Nuevo:** `resources/views/modulos/panel/_parciales/editor.php`.
- **Modificados:** `app/Controladores/ComercioInteligente/ComercioInteligenteController.php`, `app/Controladores/Panel/AdministradorFuturoController.php`, `app/Controladores/Panel/PanelRolController.php`, `resources/views/modulos/panel/indice.php`, `resources/views/modulos/panel/_parciales/david/indice.php`, `tests/Unit/ArquitecturaVistasTest.php` y `BASE_REFACTORIZACION.md`.
- **Eliminado:** `resources/views/componentes/tarjeta-producto.php`.

### Candidatos conservados

- Se conservaron separados los parciales de tarjeta de inicio y catálogo. Comparten la preparación de datos mediante el presentador, pero mantienen estructura, clases contextuales y acciones distintas; unirlos requeriría condicionales de presentación sin una mejora clara de lectura.
- Se conservaron los bloques de tabla de los paneles genérico y David porque difieren en acciones, contenido no CRUD y controles. Solo el editor tenía la misma responsabilidad y el mismo HTML.
- No se retiraron otros métodos, clases, imágenes, respaldos, archivos SQL ni recursos: no se obtuvo evidencia suficiente de que fueran prescindibles o podían proceder de referencias almacenadas en la base de datos.

### Verificación

- **[EJECUCIÓN]** Lint final: `C:\xampp\php\php.exe bin\verificar_sintaxis.php` — 216 archivos PHP sin errores (215 después de retirar el componente y antes de añadir el parcial compartido).
- **[EJECUCIÓN]** PHPStan nivel 4 con el alcance y archivo de carga anteriores — sin errores.
- **[EJECUCIÓN]** Render controlado del parcial con datos ficticios: se comparó el contenido del editor contra las copias previas. Coinciden los controles `textarea`, `select`, `select-data` e `input`, CSRF, `action` y enlace de cierre; la comparación omitió únicamente los saltos de línea finales y, para David, la indentación que ya difería. Esto verifica el fragmento, no la página completa ni el diseño en navegador.
- **[INSPECCIÓN]** Los dos `require` resuelven al parcial compartido. No se tocaron clases ni rutas, por lo que no se requirieron cambios de autocarga o manejadores.
- **[OMISIÓN SEGURA]** No se ejecutaron PHPUnit ni pruebas con base de datos por el paquete ausente y la falta de una base de pruebas configurada. No se consultó ni modificó una base real. No se cambió JavaScript, así que no correspondía ejecutar `node --check`.
- **[EJECUCIÓN]** `git diff --check` global reportó 327 hallazgos de espacios finales en el árbol rastreado ya modificado y avisos LF/CRLF. La comparación con las copias iniciales de los archivos tocados no encontró espacios finales nuevos; tampoco los hay en el parcial nuevo. No se hizo limpieza global.

### Pendientes para Fase 6

1. Ejecutar `ArquitecturaVistasTest` y la suite cuando el paquete PHPUnit del proyecto esté disponible.
2. Renderizar en navegador inicio, catálogo y ambas variantes del panel con datos de prueba para revisar diseño y comportamiento responsive.
3. Verificar en una base MySQL aislada las transacciones y los permisos que quedaron pendientes de fases anteriores; SQLite o una base real no sustituyen ese entorno.
4. Retomar otros candidatos de limpieza solo con referencias y consumidores comprobables. Esta fase no inició Fase 6.

## 13) Fase 6 — verificación final, correcciones comprobadas y guía

### Punto de partida y alcance

- **[EJECUCIÓN]** El estado inicial de Git y las copias de los archivos que se editaron se guardaron fuera del proyecto en %TEMP%\fase6_verificacion_20261001\estado_git_inicial.txt y %TEMP%\fase6_verificacion_20261001\referencia_fuentes\. La comparación de esta fase se hizo contra esas copias, porque el árbol ya tenía cambios, archivos eliminados y archivos sin seguimiento de las fases anteriores.
- **[EJECUCIÓN]** No se ejecutaron reset, clean, restauraciones, commits ni push. .env no se editó. La única base creada y usada fue la base aislada de pruebas descrita abajo.
- **[EJECUCIÓN]** Lint inicial: C:\xampp\php\php.exe bin\verificar_sintaxis.php — 216 archivos sin errores. PHPStan inicial, nivel 4 sobre app con bootstrap\funciones.php — sin errores.
- **[INSPECCIÓN]** PHP 8.2.12; composer.json requiere PHP ^8.2. No había composer.lock. Los scripts de Composer fueron revisados; las dependencias se instalaron sin ejecutar scripts ni plugins (--no-scripts --no-plugins) y sin editar archivos de vendor.
- **[EJECUCIÓN]** Se generó composer.lock con PHPUnit 10.5.65, PHPStan 2.2.16 y PHP_CodeSniffer 3.13.6, compatibles con las restricciones del proyecto. Composer se ejecutó desde el PHAR local y php_zip.dll se habilitó solo para ese proceso; no se modificó php.ini. composer validate --no-check-publish --no-interaction validó los manifiestos; quedó el aviso existente de que no se declara una licencia y un aviso de caché no escribible.

### Aislamiento de pruebas MySQL

- **[INSPECCIÓN]** Antes de ejecutar PHPUnit se revisaron tests/bootstrap.php, bootstrap/aplicacion.php, carga de entorno, configuración PDO y conexiones. Se reforzó tests/bootstrap.php para exigir configuración de pruebas explícita y detener el proceso antes de cargar la aplicación si está incompleta, no coincide o apunta a la base de la aplicación.
- **[EJECUCIÓN]** El guard exige un nombre válido terminado en _test, comprueba que sea distinto tanto de la base de la aplicación como del nombre configurado en .env, y compara base, host, puerto, usuario y contraseña entre las variables de aplicación y las variables de prueba. Una ejecución negativa sin configurar el entorno fue rechazada antes de abrir una conexión.
- **[EJECUCIÓN]** Se creó cell_phase6_20261001115301_75a6d951_test, comparando primero el destino con el nombre efectivo de la aplicación. El esquema se cargó desde el SQL del proyecto: 21 instrucciones CREATE TABLE y 54 ALTER TABLE; no se importaron los INSERT del volcado. Se añadieron únicamente siete cuentas, una categoría y un producto ficticios. La base se conservó para reproducibilidad. No se imprimieron credenciales ni se consultó o modificó la base de la aplicación.
- **[INSPECCIÓN]** La configuración de PHPUnit se entrega al proceso de ejecución desde %TEMP%\fase6_verificacion_20261001\ejecutar_phpunit.ps1; no se escribió una configuración de base de pruebas en .env.

### Errores comprobados y correcciones mínimas

- Se corrigieron fixtures y expectativas obsoletos: marca inválida en pruebas CRUD, columnas omitidas del fixture SQLite de pedidos, categoría faltante en productos de integración, stock de producto que debe cambiar mediante movimientos del libro de inventario, expectativa de título real del catálogo y ruta base correcta para las pruebas CLI.
- Se ajustó el fixture de precios para afirmar que el servicio registra el movimiento de inventario y no reduce stock directamente a través de ProductoDAO.
- Se añadió al presentador de detalle el campo etiqueta que el controlador ya leía. La corrección elimina una clave indefinida al presentar un producto sin etiqueta.
- Se declaró la propiedad de categoría usada por la prueba CRUD para eliminar una deprecación de propiedad dinámica de PHP.
- Se añadió una prueba MySQL con dos conexiones PDO independientes. La segunda conexión espera el bloqueo FOR UPDATE; después de confirmar la salida en la primera, observa stock cero y un movimiento. Comprueba el bloqueo de fila bajo una secuencia controlada, no dos solicitudes HTTP completas de checkout compitiendo a la vez.
- Los cambios de código atribuibles a esta fase, comparados con las copias externas, se limitan a tests/bootstrap.php, seis archivos de pruebas bajo tests/Feature, tests/Integration y tests/Unit, y app/Nucleo/Presentacion/Productos/PresentadorDetalleProducto.php. composer.lock es nuevo; composer.json ya existía al inicio de la fase. Los otros cambios documentales son este informe y GUIA_SUSTENTACION.md.

### Resultados de verificación

- **[EJECUCIÓN]** Suite PHPUnit completa con PHP 8.2.12 y PHPUnit 10.5.65: **98 pruebas, 1339 aserciones, 0 fallidas, 0 errores, 0 omitidas, sin advertencias ni deprecaciones**. Incluye precio base/oferta cero/precios inválidos, catálogo, inicio de sesión por rol, autorización de solicitudes directas, CSRF, CRUD cubierto, operaciones de inventario, rollback de escrituras DAO y bloqueo MySQL con dos conexiones.
- **[EJECUCIÓN]** Lint PHP final: C:\xampp\php\php.exe bin\verificar_sintaxis.php — 216 archivos sin errores.
- **[EJECUCIÓN]** PHPStan nivel 4 sobre app, con bootstrap\funciones.php — sin errores.
- **[EJECUCIÓN]** node --check sobre los cuatro archivos JavaScript de public/assets/js — sintaxis correcta.
- **[EJECUCIÓN]** Las pruebas arquitectónicas verificaron el mapeo PSR-4, la existencia de las clases y métodos de rutas, y las vistas literales referenciadas.
- **[EJECUCIÓN]** Se ejecutó git diff --check global: reportó 326 líneas con espacios finales y 79 avisos LF/CRLF repartidos por el árbol que ya estaba modificado antes de Fase 6; no se hizo una limpieza global. Las comparaciones con las copias previas no encontraron espacios finales nuevos en archivos editados durante Fase 6, y composer.lock y GUIA_SUSTENTACION.md tampoco contienen espacios finales.

### Comprobación HTTP y límites visuales

- **[EJECUCIÓN]** Se inició temporalmente el servidor PHP enlazado a 127.0.0.1:8765 con configuración exclusiva de la base _test y se detuvo al terminar. Respuestas: /, /catalog, catálogo filtrado/paginado, /products/1, /login y CSS de catálogo devolvieron HTTP 200; el producto era el fixture ficticio. /admin/products redirigió al login sin autenticación. El puerto quedó sin escucha.
- **[BLOQUEO]** No había navegador disponible en la sesión: el proveedor in-app no pudo abrirse y no había otros navegadores enumerados. Por ello no se afirma una revisión visual completa de escritorio, móvil, consola o formularios. La lista para revisión manual está en GUIA_SUSTENTACION.md.

### Funciones reales, demostrativas y pendientes

- **[INSPECCIÓN]** La autenticación con hash, el catálogo, el carrito de sesión, la creación de pedidos pendientes y el movimiento de inventario corresponden a flujos implementados. La suite comprobó las partes indicadas arriba; los resultados no prueban cada recorrido visual ni todos los datos de producción.
- **[INSPECCIÓN]** DatosDemostracionPanel aún genera métricas, tablas, tarjetas y ejemplos para paneles. El historial personal de compras de esa presentación no debe describirse como historial persistido del cliente. El asistente B2B tiene conversaciones y prompts de ejemplo, aunque la comparativa de portátiles se arma con productos activos y características del catálogo. No hay proveedor de pagos conectado ni debe afirmarse que las respuestas provengan de un servicio externo de IA.
- **[PENDIENTE]** Probar dos checkouts HTTP completos concurrentes y una falla inducida a mitad del checkout real, comprobando en MySQL que pedido, detalles, salida y existencias reviertan juntos. La prueba de rollback de esta fase cubre escrituras de DAO bajo transacción, y la prueba de concurrencia cubre el bloqueo de producto.
- **[PENDIENTE]** Completar la definición funcional de permisos de escritura y de propietario para operaciones que aún no tienen política de negocio cerrada, así como una consulta real del historial de compras personales. No se inventaron permisos ni se añadieron estas funciones.
- **[PENDIENTE]** Ejecutar una revisión visual manual en navegador en escritorio y móvil, cubriendo catálogo, compra, login, administración, ambos paneles y el editor compartido.

### Documentación y veredicto

- GUIA_SUSTENTACION.md resume la arquitectura y flujos reales, incluye fragmentos pequeños de código, un guion de cinco minutos, quince preguntas con respuestas y una lista de revisión manual.
- Esta fase queda cerrada respecto de las verificaciones ejecutables disponibles y las correcciones comprobadas. La refactorización completa puede darse por cerrada **con límites explícitos**: no hay evidencia visual completa, pago integrado, historial personal persistido ni prueba de dos checkouts HTTP concurrentes. La suite verde, lint y PHPStan no bastan para afirmar que el sistema esté 100 % funcional o listo para producción.

## 14) Correcciones de la auditoría de vistas, clientes y productos

- **[INSPECCIÓN] Vistas:** la prueba ahora distingue argumentos literales de `renderizar()` de la concatenación dinámica de controles y comprueba por separado los destinos permitidos del panel.
- **[INSPECCIÓN] Clientes:** el servicio del panel fija el segmento según el módulo; antes de editar o desactivar consulta el registro real y el DAO vuelve a exigir el segmento en la escritura. El administrador conserva el servicio sin restricción de segmento. El rol ya separaba los permisos de operación y se conserva esa matriz. El selector minorista solo ofrece «Minorista» y el módulo mayorista no acepta un campo `tipo`.
- **[INSPECCIÓN] Productos:** la baja física bloquea el producto y consulta con bloqueo los detalles de pedidos y los movimientos antes de borrar. Si hay cualquiera de esos historiales, el servicio desactiva el producto; las excepciones inesperadas siguen propagándose. No se alteraron claves foráneas, existencias ni movimientos.
- **[INSPECCIÓN] JavaScript conservado:** ambas plantillas cargan la lista final del compositor; `aplicacion.js` permanece centralizado allí y las dos ramas corresponden a plantillas distintas.
- **[EJECUCIÓN] Pruebas enfocadas:** 13 pruebas y 199 aserciones correctas (vistas, segmentos de cliente y baja de producto).
- **[EJECUCIÓN] Suite completa:** 106 pruebas y 1453 aserciones correctas, sin fallos ni pruebas omitidas. PHPUnit se ejecutó mediante la guarda del proyecto, que verificó una base ficticia terminada en `_test`, distinta de la base de aplicación y de la configurada en `.env`.
- **[EJECUCIÓN] Lint PHP:** `C:\xampp\php\php.exe bin\verificar_sintaxis.php` — 241 archivos sin errores de sintaxis.
- **[EJECUCIÓN] PHPStan nivel 4:** la ejecución terminó con cuatro hallazgos en dos presentadores ajenos a esta corrección: tres llamadas `is_array()` redundantes según su PHPDoc, y una forma de retorno incompleta en `PresentadorComercioInteligente::presentarObjetivosOptimizador()`. No se suprimieron ni modificaron esos hallazgos.
- **[BLOQUEO VISUAL]** No había navegador ni aplicación disponible en la sesión; por eso no se verificaron visualmente los formularios ni la consola.
