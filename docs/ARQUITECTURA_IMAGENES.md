# Arquitectura de imágenes

## Alcance

Este documento registra la organización de imágenes realizada el 27 de septiembre de 2026. La tarea no convierte formatos, no altera píxeles, no cambia estilos visuales y no incorpora imágenes nuevas.

La regla de ubicación es:

- `public/assets/img/`: imágenes estáticas versionadas que forman parte del diseño o contenido institucional del proyecto.
- `public/uploads/`: contenido dinámico realmente subido o administrado como archivo por la aplicación.

Una ruta escrita manualmente en una campaña no convierte por sí sola un archivo versionado en upload. En el estado auditado no existe un flujo de subida (`$_FILES`, `is_uploaded_file()` o `move_uploaded_file()`); por ello no se crearon subcarpetas vacías bajo `public/uploads/`.

## Matriz previa a la migración

| Imagen actual | Tipo | Uso actual comprobado | Nueva ruta | Referencias afectadas |
|---|---|---|---|---|
| `public/assets/img/logo.jpeg` | ESTÁTICA | Identidad visual en encabezados, sidebar y fondo de cuenta | `public/assets/img/marca/logo.jpeg` | Dos vistas y `public/assets/css/modulos/cuenta/cuenta.css` |
| `public/assets/img/hero-devices.png` | ESTÁTICA | Composición visual del hero de inicio | `public/assets/img/publico/inicio/secciones/hero-devices.png` | Vista pública de inicio |
| `public/assets/img/local.jpeg` | ESTÁTICA | Fotografía institucional de la página Nosotros | `public/assets/img/publico/nosotros/local.jpeg` | Vista pública Nosotros |
| `public/assets/img/exhibicion1.jpg` | ESTÁTICA | Fotografía del local; fallback y ejemplo de ruta para campañas | `public/assets/img/publico/inicio/banners/exhibicion1.jpg` | `bootstrap/funciones.php` y formulario administrativo de campañas |
| `public/assets/img/exhibicion2.jpg` | ESTÁTICA / HUÉRFANA | Serie fotográfica del local; sin referencia ejecutable encontrada | `public/assets/img/publico/inicio/banners/exhibicion2.jpg` | Ninguna referencia de código |
| `public/assets/img/exhibicion3.jpg` | ESTÁTICA / HUÉRFANA | Serie fotográfica del local; sin referencia ejecutable encontrada | `public/assets/img/publico/inicio/banners/exhibicion3.jpg` | Ninguna referencia de código |
| `public/assets/img/exhibicion4.jpg` | ESTÁTICA / HUÉRFANA | Serie fotográfica del local; sin referencia ejecutable encontrada | `public/assets/img/publico/inicio/banners/exhibicion4.jpg` | Ninguna referencia de código |

Las cuatro fotografías `exhibicion*.jpg` son archivos versionados, comparten dimensiones de 720 × 1280 y muestran exhibidores del local. Se mantienen juntas. No se clasifican como uploads porque no fueron creadas por una operación de carga de la aplicación.

## Árbol original

```text
public/
├── assets/img/
│   ├── exhibicion1.jpg
│   ├── exhibicion2.jpg
│   ├── exhibicion3.jpg
│   ├── exhibicion4.jpg
│   ├── hero-devices.png
│   ├── local.jpeg
│   └── logo.jpeg
└── uploads/
    └── .gitkeep
```

## Árbol final

```text
public/
├── assets/img/
│   ├── marca/
│   │   └── logo.jpeg
│   └── publico/
│       ├── inicio/
│       │   ├── banners/
│       │   │   ├── exhibicion1.jpg
│       │   │   ├── exhibicion2.jpg
│       │   │   ├── exhibicion3.jpg
│       │   │   └── exhibicion4.jpg
│       │   └── secciones/
│       │       └── hero-devices.png
│       └── nosotros/
│           └── local.jpeg
└── uploads/
    └── .gitkeep
```

No se crearon carpetas para catálogo, contacto, autenticación, productos, categorías, Comercio Inteligente, panel, estados, comunes, perfiles, temporal ni roles porque no hay imágenes reales que colocar en ellas.

## Imágenes estáticas

### Marca

`marca/logo.jpeg` es la única imagen actual de identidad. No existen favicon, isotipo, logo blanco ni SVG propios; no se generaron variantes.

### Páginas públicas

- `publico/inicio/secciones/hero-devices.png`: recurso del hero de inicio.
- `publico/inicio/banners/exhibicion1-4.jpg`: serie fotográfica estática del local. La primera sirve además como fallback de campaña.
- `publico/nosotros/local.jpeg`: fotografía institucional de la página Nosotros.

No hay imágenes propias para catálogo, contacto o autenticación. Sus interfaces usan CSS e iconos Bootstrap Icons.

### Comercio Inteligente y panel

No existen imágenes bitmap propias de SmartMatch, Comparador, Asistente IA, Optimizador o panel. Esas interfaces usan CSS e iconografía externa; no se crearon directorios vacíos.

## Imágenes dinámicas y campañas

`public/uploads/` queda reservado para archivos efectivamente subidos por el sistema. Actualmente solo contiene `.gitkeep` y no existe backend de upload.

Las campañas guardan `url_imagen` como texto y admiten una ruta local o URL externa. El código actual no copia ni valida archivos y la publicidad renderizada no consume todavía `campaign_image()`. Si se implementa una carga real de banners, entonces deberá crearse `public/uploads/campanias/` y almacenarse allí la ruta resultante.

La versión SQL registrada en Git, ausente del árbol de trabajo durante esta tarea, contiene referencias preexistentes a `assets/img/showcase1.jpg` y `assets/img/showcase3.jpg`; esos archivos no existen en el historial revisado. No se restauraron ni modificaron los SQL eliminados por el usuario. Este desfase debe resolverse cuando `database/` vuelva a estar en el árbol activo.

## Productos

No existen imágenes de producto ni placeholders bitmap. Las tarjetas actuales generan representaciones visuales con HTML/CSS. Por ello no se crearon `assets/img/productos/` ni `uploads/productos/`.

## Convenciones

- Nombres nuevos en minúsculas, sin espacios ni tildes y separados por guiones.
- Conservar extensión y contenido original durante movimientos organizativos.
- Usar `asset('assets/img/...')` para recursos estáticos.
- Usar rutas bajo `uploads/...` solo para archivos validados y persistidos por una funcionalidad de carga.
- Evitar duplicar una imagen por página o rol; una imagen compartida debe tener una sola fuente.
- No usar `docs/evidencias/` como fuente de recursos públicos.

## Cómo agregar imágenes en el futuro

### Recurso estático

1. Identificar la página o responsabilidad real.
2. Reutilizar el directorio existente más específico; crear otro solo si habrá un archivo real.
3. Usar un nombre estable conforme a la convención.
4. Referenciarlo con `asset()` o una ruta CSS relativa calculada desde el archivo CSS.
5. Buscar la ruta anterior y ejecutar las pruebas antes de confirmar el cambio.

### Archivo dinámico

Antes de habilitar uploads se debe implementar validación de tamaño, extensión y MIME real; nombres aleatorios seguros; prevención de sobrescritura; rechazo de PHP y formatos ejecutables; permisos adecuados y protección del directorio para impedir ejecución de scripts. Solo entonces debe crearse la subcarpeta funcional correspondiente.

## Riesgos conocidos

1. `public/uploads/` no tiene una regla propia que impida ejecutar PHP. No hay carga de archivos actual, pero esta protección es obligatoria antes de implementar una.
2. El formulario de campañas acepta una ruta o URL como texto sin descargar ni validar un archivo; es comportamiento preexistente y no fue reescrito.
3. `exhibicion2-4.jpg` no tienen consumidor ejecutable actual. Se conservaron como huérfanas por pertenecer a una serie institucional coherente; no existe evidencia suficiente para eliminarlas.
4. Los SQL eliminados del árbol de trabajo contenían referencias rotas a `showcase1.jpg` y `showcase3.jpg`.

## Registro de cambios y validación

Se movieron las siete imágenes originales sin renombrarlas. Sus hashes Git antes/después coinciden, por lo que el contenido binario no cambió.

Referencias actualizadas:

- PHP de bootstrap: 1 (`campaign_image()`), exclusivamente el fallback estático.
- Vistas: 5 (encabezado, sidebar, inicio, Nosotros y ejemplo del formulario de campañas).
- CSS: 1 (`cuenta.css`). Además de adaptar la ubicación, se corrigió el número de niveles relativos: la ruta anterior `../../img/logo.jpeg` resolvía erróneamente a `assets/css/img/logo.jpeg`; la ruta final `../../../img/marca/logo.jpeg` existe.
- JavaScript: 0; no contiene rutas de imágenes.
- SQL activo: 0; `database/` estaba eliminado del árbol de trabajo antes de esta tarea y no fue restaurado.

Validaciones finales:

- Búsqueda de las cuatro rutas antiguas referenciadas: 0 coincidencias fuera de documentación.
- Existencia de las seis rutas literales nuevas: comprobada.
- Integridad de las siete imágenes: idéntica a los blobs originales de Git.
- `php -l` sobre los seis PHP modificados: 0 errores.
- PHPUnit: 72 pruebas, 840 aserciones, 5 omitidas y 2 errores preexistentes por conexión MySQL sin credenciales (`user ''@'localhost'`). No hubo fallo de asset o ruta de imagen.
- El log runtime creado durante PHPUnit fue eliminado; `storage/logs/.gitkeep` se conservó.

No se modificaron DAO, Services, Controllers, consultas, esquema, roles, permisos, sesiones, autenticación, CSRF, PDO ni autoload. Tampoco se cambiaron dimensiones, formatos, nombres, contenido gráfico ni propiedades visuales.
