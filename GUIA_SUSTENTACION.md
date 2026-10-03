# Guía de sustentación — Sistema Web CELL SAC

Esta guía describe el código que existe en el repositorio y el alcance que alcanzaron sus verificaciones. El sistema usa PHP 8.2, PDO/MySQL y una arquitectura propia organizada por responsabilidades. No es un framework externo.

## 1. Mapa del proyecto

| Carpeta | Responsabilidad | Ejemplos del proyecto |
| --- | --- | --- |
| `public/` | Entrada HTTP y recursos públicos | `public/index.php`, CSS y JavaScript |
| `bootstrap/` | Autocarga, entorno, configuración y composición inicial | `bootstrap/aplicacion.php`, `bootstrap/funciones.php` |
| `routes/` | Registro de rutas y middleware | `routes/publicas.php`, `routes/administrador.php`, `routes/roles.php` |
| `app/Controladores/` | Interpretar solicitudes y coordinar el caso de uso | `CatalogoController`, `ProcesoCompraController` |
| `app/Servicios/` | Reglas y operaciones de negocio | `ProcesoCompraServicio`, `InventarioServicio` |
| `app/DAO/` | Consultas y persistencia con PDO | `ProductoDAO`, `PedidoDAO`, `InventarioDAO` |
| `app/Modelos/` | Reglas y conceptos propios del dominio | `Productos\Producto` |
| `app/DTO/` y `app/Validacion/` | Datos de entrada tipados y validación de solicitudes | `FiltroProducto`, `SolicitudInicioSesion` |
| `app/Nucleo/` | HTTP, base de datos, contenedor, enrutamiento y renderizado | `Solicitud`, `Respuesta`, `Conexion`, `Vista` |
| `app/Middleware/` | Reglas aplicadas antes de despachar una ruta | autenticación, rol, CSRF, límite de solicitudes |
| `app/Nucleo/Presentacion/` | Preparación de datos que necesita una vista | `PresentadorTarjetaProducto`, `PresentadorDetalleProducto` |
| `resources/views/` | HTML y plantillas PHP | `publico/catalogo/indice.php`, `modulos/compra/carrito/indice.php` |
| `tests/` | Pruebas unitarias, de rutas y de repositorios | `tests/Unit`, `tests/Feature`, `tests/Integration` |

## 2. Quién hace qué

- **Controlador:** recibe `Solicitud`, llama validadores y servicios, y decide qué `Respuesta` devolver. Por ejemplo, `CatalogoController` prepara el flujo de catálogo y `AutenticacionController` coordina el inicio de sesión.
- **Servicio:** reúne las reglas del caso de uso. `ProcesoCompraServicio` vuelve a leer producto, existencias y precio durante la compra; `InventarioServicio` valida los datos del movimiento antes de delegar.
- **DAO (objeto de acceso a datos):** consulta o modifica la base de datos. `ProductoDAO` y `UsuarioDAO` usan consultas PDO preparadas; no deberían decidir el HTML de una pantalla.
- **Modelo:** representa reglas de un concepto de negocio. `Producto` centraliza el precio vigente y los subtotales en centavos.
- **Presentador:** adapta registros a etiquetas, enlaces y otros valores de visualización. `PresentadorTarjetaProducto` prepara las tarjetas del catálogo.
- **Vista:** genera HTML a partir de los datos que recibe. Usa bucles y condiciones de presentación, escape como `e()` y el campo CSRF como `csrf_field()`; no contiene consultas SQL.

La separación reduce las reglas duplicadas: la misma política de precio de `Producto` se puede usar al presentar y al procesar una compra.

## 3. Recorrido de una solicitud

1. El servidor web dirige la petición a `public/index.php`.
2. `public/index.php` solicita a `bootstrap/aplicacion.php` la configuración inicial y el contenedor.
3. El bootstrap carga el entorno, registra servicios y conecta interfaces de repositorio con DAOs concretos. También incluye los grupos de rutas.
4. Se captura la petición como `Solicitud`. El middleware global de encabezados de seguridad envuelve el despacho.
5. `Enrutador` busca método y ruta, ejecuta el middleware de esa ruta y resuelve el controlador desde `Contenedor`.
6. El controlador coordina validación, servicios y datos de presentación.
7. `Vista::renderizar()` compone los datos y la plantilla, captura el HTML y lo devuelve dentro de `Respuesta`.
8. `Respuesta::enviar()` escribe encabezados, estado y contenido al cliente.

Las rutas GET/POST y sus middleware se pueden revisar en `routes/publicas.php` y `routes/administrador.php`. Un POST de compra, por ejemplo, requiere el middleware de acceso correspondiente y `CsrfMiddleware`.

## 4. Flujos funcionales

### Inicio y cierre de sesión

El POST `/login` pasa por límite de solicitudes y CSRF. `AutenticacionController::iniciarSesion()` valida la entrada con `SolicitudInicioSesion`, y `AutenticacionServicio` busca el correo en `UsuarioDAO`. El servicio compara la contraseña con `password_verify()`, regenera la sesión y guarda solo `id`, `nombre`, `correo` y `rol` bajo la clave `user`. El controlador redirige a administración o al panel según el rol.

El cierre de sesión se hace con POST `/logout`, autenticación y CSRF. El servicio destruye la sesión. La existencia de un enlace oculto en una vista no reemplaza el middleware de ruta.

### Catálogo y detalle

`CatalogoController` obtiene productos y categorías mediante servicios, valida los filtros con `SolicitudFiltroCatalogo`, construye un `FiltroProducto` y solicita paginación. Los presentadores preparan tarjetas, opciones y enlaces que conservan los filtros. El detalle se busca en el servidor por ID y se entrega a la vista ya preparado.

`ProductoDAO` realiza las consultas; `resources/views/publico/catalogo/indice.php` representa el formulario, las tarjetas y la paginación. Los textos se escapan en la vista antes de insertarlos en HTML.

### Carrito

`CarritoServicio` mantiene en la sesión un mapa de `idProducto => cantidad`. Al agregar, limita la cantidad al stock disponible. Para mostrar el resumen, vuelve a consultar productos activos y calcula los importes desde los datos actuales del producto. El contenido de la sesión no se trata como fuente confiable de precio ni de stock.

### Confirmación de compra

`ProcesoCompraServicio::procesarCompra(int $idUsuario)` obtiene el carrito y ejecuta la operación dentro de `GestorTransaccionesInterfaz`. Para cada artículo, pide al repositorio el producto activo bloqueado para actualización, vuelve a comprobar stock y calcula el precio vigente. Después crea el pedido en estado pendiente, guarda sus detalles con el precio histórico y registra una salida de inventario. El carrito se limpia una vez que la transacción termina correctamente.

Este flujo crea un pedido pendiente; no cobra una tarjeta ni integra un proveedor de pagos. La prueba MySQL añadida comprueba el bloqueo del producto con dos conexiones independientes, pero no simula dos solicitudes HTTP completas de checkout compitiendo a la vez.

### Inventario

`InventarioServicio::registrar()` valida ID, cantidad positiva, tipo (`entrada`, `salida` o `ajuste`), nota y usuario responsable. `InventarioDAO::registrarMovimiento()` abre una transacción, bloquea la fila del producto con `FOR UPDATE`, calcula el nuevo stock y rechaza resultados negativos. Luego inserta el movimiento y actualiza existencias. Una excepción hace que la transacción revierta.

## 5. PHP: variables, funciones, métodos e instancias

### Variables y parámetros

Una variable local empieza con `$`; por ejemplo, `$idUsuario`. Un parámetro recibe un valor al llamar un método:

```php
public function procesarCompra(int $idUsuario): int
```

El tipo `int` documenta y restringe el tipo esperado. El valor de retorno también se declara como `int`.

### Propiedades y constructor

PHP permite declarar una propiedad desde un parámetro del constructor. En `ProcesoCompraServicio`:

```php
public function __construct(
    private RepositorioProductoInterfaz $productos,
    private RepositorioPedidoInterfaz $pedidos,
)
```

`$productos` y `$pedidos` quedan disponibles como propiedades del objeto. El contenedor proporciona las implementaciones concretas registradas en el bootstrap.

### Funciones, métodos y creación de objetos

Una función global se puede llamar sin crear un objeto; una función definida dentro de una clase es un método. `Producto::desdeRegistro($registro)` es un método estático. En `Vista`, `new Respuesta($contenido, $estado)` instancia una respuesta. La mayoría de dependencias de la aplicación las crea `Contenedor`, en vez de que cada controlador elija sus DAOs.

### Espacios de nombres y autocarga

La clase `App\Servicios\Compra\ProcesoCompraServicio` declara `namespace App\Servicios\Compra;` y vive bajo `app/Servicios/Compra/`. `composer.json` declara el mapeo PSR-4 `App\\` → `app/` y `Tests\\` → `tests/`. `bootstrap/aplicacion.php` carga el autoloader de Composer si existe y conserva un autocargador PSR-4 propio para `App\\`.

`Contenedor` enlaza, por ejemplo, `RepositorioProductoInterfaz` con `ProductoDAO`, resuelve dependencias tipadas del constructor y guarda las instancias que ya creó.

### PDO, SQL preparada y transacciones

Un DAO separa la consulta de los valores recibidos:

```php
$sentencia = $this->pdo()->prepare(
    'SELECT * FROM usuarios WHERE correo = ? LIMIT 1'
);
$sentencia->execute([$correo]);
```

PDO envía los parámetros aparte del texto SQL. `Conexion` configura excepciones, resultados asociativos y `PDO::ATTR_EMULATE_PREPARES => false`. `Conexion::transaccion()` abre y confirma la transacción; si falla, revierte. Si ya existe una transacción en esa conexión, utiliza un savepoint.

### Cómo se mantiene el HTML separado

El controlador reúne datos y el presentador adapta los campos visuales. La plantilla itera sobre esos datos y escapa el contenido dinámico. Las consultas quedan en DAOs y las decisiones de stock/precio quedan en servicios o el modelo. Por ejemplo, la vista de catálogo muestra `tarjetasProducto`; no ejecuta `SELECT` ni calcula el precio de compra.

## 6. Qué es real, qué es demostrativo y qué falta

**Con persistencia y comportamiento comprobados en las pruebas:** autenticación con hash, catálogo y detalle de productos, operaciones CRUD administrativas cubiertas, carrito de sesión, creación de pedidos pendientes, detalles de pedido y movimientos de inventario. La suite también comprueba rutas, roles y solicitudes CSRF.

**Demostrativo:** `DatosDemostracionPanel` contiene filas, métricas, tarjetas y datos de ejemplo que alimentan varios paneles por rol. El historial personal de compras mostrado en el panel no es un historial real asociado al cliente. El asistente mayorista muestra conversaciones y prompts de ejemplo; su comparativa de portátiles sí parte de productos activos y características del catálogo. La existencia de estas pantallas no implica que haya servicios externos de IA, marketing o pago conectados.

**Pendiente o fuera de alcance:** integración de pago; consulta real de pedidos personales por propietario en el panel del cliente; especificación completa de permisos por operación/propietario para funcionalidades cuyo comportamiento de negocio sigue sin definirse; y pruebas visuales completas en navegador. No se agregaron estas funcionalidades durante la verificación.

## 7. Guion de exposición de cinco minutos

1. **Minuto 1 — Estructura.** “El proyecto es una aplicación PHP organizada por capas. Las rutas reciben solicitudes, los controladores coordinan, los servicios aplican reglas, los DAOs hablan con MySQL y las vistas presentan datos.”
2. **Minuto 2 — Catálogo.** “El usuario filtra el catálogo. El controlador valida los filtros, el servicio consulta productos, el presentador arma las tarjetas y la vista escapa los valores al generar HTML.”
3. **Minuto 3 — Compra y precio.** “El carrito guarda identificadores y cantidades en sesión. Al comprar, el servidor vuelve a leer el producto, precio y stock. La oferta cero es válida; los importes se calculan en centavos para evitar acumular errores decimales.”
4. **Minuto 4 — Inventario y seguridad.** “El pedido, sus detalles y la salida de inventario usan una transacción. El DAO bloquea la fila con `FOR UPDATE`. Las rutas también aplican middleware de rol, autenticación y CSRF según la operación.”
5. **Minuto 5 — Evidencia y límites.** “PHPUnit corrió contra una base MySQL exclusiva terminada en `_test`. Pasaron 98 pruebas y 1339 aserciones. Lint y PHPStan también quedaron limpios. No pude hacer inspección visual porque esta sesión no tenía navegador; el panel de historial y los servicios de pago/IA no deben presentarse como funciones reales.”

## 8. Quince preguntas exigentes

1. **¿Cómo evita una inyección SQL?** Los DAOs usan `prepare()` y `execute()` para valores. PDO tiene preparaciones emuladas desactivadas en `Conexion`.
2. **¿Qué ocurre si el precio enviado por el navegador fue manipulado?** El checkout no usa ese importe: vuelve a leer el producto activo y obtiene el precio con `Producto::precioEfectivoEnCentimos()`.
3. **¿Qué significa que `precio_oferta` sea `0`?** Es una oferta válida. Se usa el precio base solo si la clave falta o su valor es `null`; cero no se trata como ausencia.
4. **¿Por qué usar centavos?** Precio y cantidad se multiplican como enteros para conservar el redondeo de moneda y comprobar el máximo del esquema decimal.
5. **¿Qué impide vender dos veces la última unidad?** El producto se lee con `FOR UPDATE` dentro de la transacción de compra; otra conexión espera el bloqueo y luego encuentra las existencias actualizadas. La prueba existente comprueba ese bloqueo, no dos checkouts HTTP completos.
6. **¿Qué revierte una falla durante la persistencia?** `Conexion::transaccion()` revierte su transacción o savepoint. Una prueba de integración fuerza una excepción y comprueba que las escrituras del DAO no queden guardadas.
7. **¿El pedido se cobra al crearse?** No. La operación crea un pedido pendiente; no hay procesamiento de pago integrado.
8. **¿Cómo se actualiza inventario manualmente?** El servicio valida movimiento e identidad del operador; el DAO bloquea la fila, aplica entrada/salida/ajuste y guarda movimiento y existencias en la misma transacción.
9. **¿Por qué comprobar el middleware si el botón se oculta?** La interfaz solo controla visibilidad. Un cliente puede construir la petición directa; la autorización debe estar también en las rutas/controladores del servidor.
10. **¿Cómo se valida CSRF?** Los POST protegidos incluyen `CsrfMiddleware`; las pruebas comprueban que un token inválido se rechaza antes de llegar al controlador.
11. **¿Qué guarda la sesión de login?** Solo el ID, nombre, correo y rol permitidos. Antes se verifica el hash de contraseña y se regenera el identificador de sesión.
12. **¿Por qué hay interfaces de repositorio?** Permiten que servicios dependan de contratos y que el contenedor conecte esos contratos con DAOs concretos; las pruebas unitarias pueden sustituir dependencias.
13. **¿Qué resuelve un presentador?** Formatea datos de salida —como etiquetas, enlaces, imágenes y texto de stock— para no mover esa preparación a la plantilla ni mezclarla con SQL.
14. **¿Cómo se evitó que PHPUnit usara la base de la aplicación?** `tests/bootstrap.php` exige configuración explícita del proceso, sufijo `_test`, comparación contra el nombre de `.env` y coincidencia de credenciales/host/puerto antes de incluir el bootstrap. Sin esos datos falla antes de abrir una conexión.
15. **¿Puede afirmar que todo el sistema está terminado?** No. Las comprobaciones muestran una suite verde y análisis estático limpios, pero no equivalen a validación visual completa, prueba de pagos, historial personal real ni dos compras HTTP simultáneas.

## 9. Lista breve para revisión manual en navegador

- En escritorio y móvil: inicio, catálogo, filtros, paginación, ordenamiento, detalle, imágenes y estados vacíos.
- Probar añadir, cambiar cantidad y quitar productos; revisar una compra con stock suficiente y otra con stock insuficiente. Confirmar que el pedido queda pendiente.
- Iniciar y cerrar sesión; probar accesos permitidos y denegados por rol y un POST sin token CSRF válido.
- En administración, revisar productos, categorías, pedidos e inventario, incluidos formularios inválidos y mensajes de error.
- Comparar panel genérico y variante David; abrir el editor compartido desde ambos recorridos.
- Revisar consola del navegador, solicitudes fallidas, foco/teclado, desbordes horizontales y lectura de tablas en móvil.

Esta lista queda pendiente de ejecución visual completa. Durante Fase 6 no había proveedor de navegador disponible; se hicieron comprobaciones HTTP, no una revisión visual de escritorio/móvil.

## 10. Límites de la evidencia

- La suite registrada terminó con **98 pruebas y 1339 aserciones**, sin fallos, omisiones, advertencias ni deprecaciones. Incluyó pruebas unitarias, de rutas/autorización y de repositorios MySQL.
- La prueba de concurrencia usa dos conexiones PDO y verifica la espera del bloqueo y el stock posterior. No ejecuta dos flujos HTTP de checkout completos en paralelo.
- La integración MySQL demuestra rollback de escrituras de DAO ante una excepción. No equivale a una prueba completa que provoque un fallo a mitad de cada paso real de checkout y compruebe pedido, detalles, movimiento y stock conjuntamente.
- El chequeo HTTP fue una comprobación de respuesta/renderizado para las rutas principales; no sustituye una inspección de diseño responsive, formularios en navegador ni consola visual.
- El panel conserva datos demostrativos. El estado verde de las pruebas no convierte esas métricas ni el historial de ejemplo en datos operativos.

