# MD Technology Digital Cell

Monolito modular de comercio en PHP 8.2+ y MySQL. La aplicacion usa Front Controller, MVC, servicios, DAO con PDO, middleware, sesiones seguras, CSRF y migraciones versionadas.

## Inicio rápido en XAMPP

1. Conserva la carpeta `MD-Technology-Digital-Cell-UND1-CORREGIDO` dentro de `C:\xampp\htdocs\dwa2\`.
2. Copia `.env.example` como `.env` y revisa los valores `DB_*`.
3. Inicia Apache y MySQL desde XAMPP.
4. Importa `database/md_tecnologia_digital_cell.sql` desde phpMyAdmin. Como alternativa, crea la base `md_tecnologia_digital_cell` y ejecuta `C:\xampp\php\php.exe bin\console migrate`.
5. Abre `http://localhost/dwa2/MD-Technology-Digital-Cell-UND1-CORREGIDO/public/`.

El proyecto incluye `vendor/`, por lo que puede ejecutarse de inmediato. Composer solo es necesario si se desea reinstalar o actualizar las dependencias de desarrollo.

## Usuarios de demostración

La importación SQL y la migración final crean cuentas académicas de prueba. Todas usan la clave `Demo2026!` y deben cambiarse antes de una publicación real.

| Experiencia | Correo |
|---|---|
| Administrador | `admin@md.demo` |
| Compras y logística | `compras@md.demo` |
| Ventas mayoristas | `b2b@md.demo` |
| Ventas minoristas | `b2c@md.demo` |
| Marketing digital | `marketing@md.demo` |
| Cliente minorista | `minorista@md.demo` |
| Cliente mayorista | `mayorista@md.demo` |

El visitante no necesita cuenta. Cada sesión recibe un menú propio y las rutas `/panel/...` vuelven a validar el rol aunque se escriban manualmente.

## Cobertura de mockups

El proyecto implementa **52 interfaces navegables** correspondientes al documento visual entregado. Los dashboards y módulos ya no reutilizan una pantalla genérica: cada experiencia presenta métricas, filtros y composición acordes con su función. La correspondencia completa está documentada en `docs/MATRIZ_INTERFACES_MOCKUPS.md`.

## Publicidad dinámica

Las pantallas públicas y la cuenta del cliente incluyen promociones contextuales sin alterar el flujo de compra:

- Un único popup grande automático al ingresar por cada perfil durante la sesión.
- Un banner compacto por sección o categoría importante, con espera entre impactos.
- Máximo de cuatro impactos automáticos por sesión y perfil.
- Ofertas diferenciadas para visitantes, clientes minoristas y clientes mayoristas.
- Contextos específicos para inicio, catálogo, categorías, producto, carrito, checkout, postcompra y cuenta.
- Cierre inmediato, tecla `Escape` y botón discreto para reabrir voluntariamente la oferta.
- Las campañas creadas desde `/admin/campaigns` alimentan el popup de visitantes y conservan el seguimiento básico de vistas y clics.

El estado de frecuencia se guarda únicamente en `sessionStorage`; no se almacena información personal ni se utilizan rastreadores externos.

## Requisitos

- PHP 8.2 o superior con `pdo_mysql`, `mbstring`, `json`, `openssl` y `fileinfo`.
- MySQL 8.0+ o MariaDB compatible.
- Composer 2.
- Apache con `mod_rewrite` o Nginx.

## Instalacion

1. Ejecuta `composer install`.
2. Copia `.env.example` a `.env`.
3. Crea una base MySQL vacia y un usuario dedicado con permisos sobre esa base.
4. Configura `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
5. Ejecuta `php bin/console migrate`.
6. Crea el administrador inicial:

```bash
php bin/console create:admin "Administrador" admin@example.com "una-clave-segura"
```

No existe instalador web. `/setup` no es una ruta valida.

## Servidor web

El unico DocumentRoot permitido es `public/`. No expongas la raiz del repositorio, porque contiene configuracion, migraciones, pruebas y archivos de entorno.

Para desarrollo local:

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

En Apache, `public/.htaccess` envia las rutas inexistentes al Front Controller. En Nginx configura `try_files $uri $uri/ /index.php?$query_string;`.

## Configuracion

La configuracion se carga desde `.env`. Los valores seguros por defecto son `APP_ENV=production` y `APP_DEBUG=false`. En desarrollo se puede usar:

```dotenv
APP_ENV=local
APP_DEBUG=true
SESSION_SECURE=false
```

En produccion usa HTTPS, `SESSION_SECURE=true`, credenciales MySQL dedicadas y nunca el usuario `root`. Los errores tecnicos se escriben en `storage/logs/app.log` y no se muestran al visitante.

## Comandos

```bash
php bin/console migrate
php bin/console migrate:status
php bin/console create:admin "Nombre" correo@dominio.com "clave"
composer test
composer lint
composer analyse
```

## Base de datos

El esquema principal en español está en `database/md_tecnologia_digital_cell.sql` y su versión ejecutable por consola está en `database/migrations/2026_09_27_000001_esquema_espanol.sql`. La tabla `migraciones` registra `migracion`, `lote` y `ejecutada_en`.

La base configurada es `md_tecnologia_digital_cell`. Sus tablas activas son `usuarios`, `productos`, `pedidos`, `detalle_pedidos`, `registros_auditoria`, `mensajes_contacto`, `campanas_publicitarias`, `perfiles_inteligentes_productos`, `categorias`, `proveedores`, `clientes`, `cotizaciones`, `compras`, `movimientos_inventario` y `migraciones`.

El checkout se ejecuta en una transaccion, bloquea productos con `SELECT ... FOR UPDATE` y descuenta con una actualizacion condicionada para impedir stock negativo.

## Pruebas

La suite se divide en:

- `tests/Unit`: servicios y rollback del checkout.
- `tests/Integration`: repositorios PDO contra MySQL real.
- `tests/Feature`: routing y paginas publicas.
- `tests/Security`: CSRF y autorizacion administrativa.

Las pruebas de integracion solo se habilitan si `DB_TEST_DATABASE` termina en `_test`. Configura ademas `DB_TEST_HOST`, `DB_TEST_PORT`, `DB_TEST_USERNAME` y `DB_TEST_PASSWORD`. Nunca apuntes esas variables a produccion.

## Estructura

- `app/Dominio`: objetos y reglas de dominio puntuales.
- `app/Http`: controladores, solicitudes, middleware, enrutamiento y respuestas.
- `app/Servicios`: casos de negocio de la aplicación.
- `app/DAO`: consultas y CRUD con PDO, separados de controladores y servicios.
- `app/Infraestructura`: conexión PDO, migraciones y registro de eventos.
- `app/Repositorios/Contratos`: interfaces usadas por servicios comprobables e implementadas por los DAO.
- `resources/views`: vistas PHP.
- `public`: Front Controller y todos los assets publicos.
- `storage`: logs, cache y sesiones no versionados.
- `docs/INFORME_TECNICO_UNIDAD_1.pdf`: informe académico completo conforme a la guía oficial.
- `docs/INFORME_TECNICO.md`: resumen de cambios, pruebas y clasificación académica.
- `docs/MAPA_IMPLEMENTACION.md`: correspondencia entre mockup, ruta, rol y backend.

## Convención de idioma

Los archivos, clases, métodos, variables y comentarios propios usan nombres en español. Se mantienen sin traducir los términos técnicos exigidos o convencionales del lenguaje y del curso, entre ellos `namespace`, `use`, `Controller`, `DAO`, `PDO`, `GET` y `POST`.

El encabezado y el pie públicos están centralizados en `resources/views/componentes/encabezado.php` y `resources/views/componentes/pie_pagina.php`. La plantilla reutilizable se encuentra en `resources/views/plantillas/aplicacion.php`.

## Seguridad y produccion

Todas las operaciones de estado usan CSRF y metodos POST; los productos se desactivan sin borrar referencias historicas. Login y registro tienen rate limiting. Las sesiones usan cookies HttpOnly, SameSite, modo estricto y regeneracion tras autenticar. Se envian CSP, `nosniff`, Referrer-Policy y Permissions-Policy; HSTS solo se envia bajo HTTPS.

Antes de desplegar ejecuta Composer, PHPUnit, PHPCS, PHPStan y las migraciones sobre una base vacia. Empaqueta sin `.git/`, `.env`, `vendor/` (si se instala en destino), logs, cache, sesiones ni uploads reales.

## MD SmartCommerce (actualizacion 22/09/2026)

Esta entrega incorpora una capa de innovacion comercial sobre el e-commerce existente:

- **SmartMatch**: recomendador de celulares por presupuesto, uso y prioridad, con porcentaje de coincidencia y puntajes explicables.
- **Comparador inteligente**: compara hasta tres equipos mediante rendimiento, camara, bateria y calidad/precio.
- **Optimizador por presupuesto / Modo emprendedor**: genera propuestas automaticas para variedad, unidades, margen estimado o equipos premium.
- **MD Assistant**: asistente virtual local que interpreta consultas de necesidad y presupuesto y devuelve productos disponibles. No depende de una API externa.
- **Carrito inteligente**: propone complementos, indica compatibilidad por marca/universal y facilita venta cruzada.
- **Dashboard empresarial inteligente**: muestra ventas mensuales, rotacion, semaforo de stock y estimacion basica de dias de inventario.
- **Publicidad de alta visibilidad**: barra superior, popup, lateral, dock inferior tras scroll/tiempo y recordatorio contextual, todos cerrables y limitados por sesion.

### Puesta en marcha despues de reemplazar el proyecto

```bat
cd C:\xampp\htdocs\dwa2\MD-Technology-Digital-Cell-UND1-CORREGIDO
C:\xampp\php\php.exe bin\console migrate
```

Luego abrir:

`http://localhost/dwa2/MD-Technology-Digital-Cell-UND1-CORREGIDO/public/`

La migración `2026_09_27_000001_esquema_espanol.sql` crea el esquema completo y agrega los accesorios de demostración utilizados por el carrito inteligente.
