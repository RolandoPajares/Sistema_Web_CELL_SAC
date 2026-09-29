# Informe de integración

Fecha: 29 de septiembre de 2026.
Entrega: `PROYECTO_UNIFICADO_FINAL.zip`.

## 1. Procedencia

| Autor | Integración definitiva |
|---|---|
| PAJARES | Proyecto base completo. Administración, inicio público, encabezado, pie, backend, configuración, rutas, permisos, SQL, pruebas, documentación y recursos exclusivos. |
| DAVID | Vista de catálogo (idéntica en origen salvo enlace al componente), tarjeta específica, detalle de producto, CSS de catálogo/detalle/compras, diferencias del panel compartido y JS de calendarios/filtros/galería. Composiciones internas seleccionadas únicamente para compras y marketing. |
| YAXON (ZIP `jaxon`) | `publico/nosotros/indice.php` idéntico byte por byte, foto `local.jpg` y las reglas originales de layout/tarjeta, acotadas a `/about`. |
| SEBASTIÁN | Contacto con formulario original, imágenes y CSS; panel y CSS de clientes minorista/mayorista; SmartMatch; portal mayorista y su CSS (estos últimos ya eran idénticos a Pajares). |

Marketing comparte originalmente la composición de MD Ads entre las versiones. Se mantuvo ese diseño y se conservaron los bloques dinámicos de campañas de Pajares: importar literalmente la vista de David habría sustituido datos reales por cifras fijas. El CSS propio de marketing ya era idéntico entre proyectos.

## 2. Conflictos y decisiones

- **Archivos compartidos:** las vistas de panel también sirven a ventas. Se eligieron los parciales de David únicamente para `compras_logistica` y `marketing`. Ventas conserva la versión de Pajares. No se añadieron rutas ni un segundo panel administrativo.
- **Ventas mayoristas/minoristas:** no existe una asignación inequívoca de autor en las instrucciones. Se conservó Pajares y no se importó trabajo de Yaxon fuera de Nosotros.
- **Smartwatch/SmartMatch:** no hay ruta/vista independiente Smartwatch en Sebastián. Hay iconos de relojes en vistas genéricas y una página SmartMatch desarrollada. Se integró SmartMatch y se informa la diferencia; no se declara entregado un módulo inexistente.
- **Catálogo frente a Inicio:** ambos usaban la misma tarjeta y CSS. Se conserva la tarjeta de Pajares para Inicio; el catálogo utiliza su propio parcial de David. Las composiciones son distintas y necesarias, no dos catálogos navegables.
- **CSS de catálogo:** se extrajeron solo las reglas añadidas por David. Sus variables de color se aplican al contenido de catálogo, sin afectar encabezado ni pie.
- **Nosotros:** no se importó el layout global de Yaxon. Sus cambios están limitados a `/about`, manteniendo intactos los recursos globales de Pajares.
- **Detalle de producto:** la versión de David espera variantes, imágenes por variante, características, complementos y similares que el backend base no suministra. Se conserva su presentación compatible con el producto existente; no se envía `variant_id` al carrito. Los bloques sin complementos/similares no se muestran. Se utiliza el marcador visual original de David cuando no existe una imagen en los datos recibidos. No se inventan asociaciones con fotografías.
- **Compras:** el DAO de Pajares entrega `existencias`; la tabla visual de David se adaptó a ese nombre, sin modificar consultas. Las series por período, actividad y prioridades adicionales de David no se conectaron a nuevas consultas. Aparecen estados pendientes y se desactiva el selector de período que no tiene soporte en la base.
- **Indicadores internos:** se evitó importar como reales los valores fijos de David. Los indicadores nuevos sin un dato compatible se muestran con `—` y una explicación de pendiente.
- **Campañas:** se preservan las métricas y listados dinámicos de Pajares y su destino de campañas por rol. Las anclas importadas se ajustaron a destinos existentes.
- **Pedidos mayoristas:** los enlaces del panel de Sebastián hacia `panel/pedidos` se corrigieron a `panel/pedidos-mayoristas` para ese rol. No se modificaron los permisos.
- **Iconos:** se incluyó la misma versión Bootstrap Icons 1.11.3 y su licencia. Solo cambió la URL del CSS en las tres plantillas; no cambió el diseño administrativo ni público.

## 3. Duplicados y limpieza

- Cada ruta conserva una única pantalla definitiva; no se incluyeron carpetas completas de los otros tres proyectos dentro del resultado.
- Se eliminó la segunda carga de `recomendador.css` que venía embebida en SmartMatch; lo carga la plantilla una sola vez.
- Se importaron únicamente los bloques JS nuevos de David, sin copiar de nuevo su `aplicacion.js` global.
- No se duplicaron headers, footers, controladores, DAO ni bases de datos.
- Se excluyeron el historial `.git`, temporales `tmp`, sesiones, registros de ejecución y cachés. Se mantienen los directorios de almacenamiento y las dependencias `vendor/`.
- No se eliminó ningún módulo funcional exclusivo de Pajares. Los parciales de David y la tarjeta específica tienen usos distintos a los componentes que permanecen para ventas e Inicio.
- Se conservan `.jpeg` originales y `.jpg` añadidos cuando las vistas tienen referencias diferentes; no se sobrescribieron imágenes protegidas del Inicio.

## 4. Aislamiento de estilos y JavaScript

| Archivo/carpeta nueva | Alcance |
|---|---|
| `public/assets/css/david/catalogo.css` | Diferencias del catálogo de David; únicamente `/catalog`. |
| `public/assets/css/david/panel.css` | Diferencias visuales internas; solo compras y marketing. |
| `public/assets/css/yaxon/nosotros.css` | Diseño Nosotros de Yaxon; solo `/about`. |
| `public/assets/js/david/producto.js` | Galería del detalle, sin reemplazar JS global. |
| `public/assets/js/david/panel.js` | Calendarios, filtros, exportación y contacto de proveedor cuando los datos existen. |
| `public/assets/vendor/bootstrap-icons/` | Recurso local común, misma versión del CDN original. |

Los CSS de Sebastián permanecen en sus rutas originales de módulo/rol: no fue necesario crear otra carpeta con copias. El CSS de detalle de David se limitó al contenido principal para no alterar los componentes públicos protegidos.

## 5. Ajustes de rutas y presentación

- `resources/views/publico/catalogo/indice.php`: solo cambia la ruta del `require` a la tarjeta de catálogo.
- `resources/views/plantillas/administrador.php`: solo cambia la ruta del CSS de iconos al archivo local. Todos los demás bytes de esta plantilla son iguales a Pajares.
- `resources/views/plantillas/aplicacion.php` y `interno.php`: ruta local de iconos y carga contextual del JS adicional.
- `app/Soporte/Presentacion/CatalogoEstilos.php`: selección de CSS por página/rol. Es una modificación de presentación, no de lógica de negocio.
- Panel de cuenta: corrección de destinos de pedidos por rol.
- MD Ads: corrección de anclas de navegación; se mantienen consultas y acciones del backend base.
- Los parciales de panel se seleccionan desde las vistas existentes, sin cambiar controladores ni registros de rutas.

La lista exacta de archivos distintos, su origen cuando se puede establecer por igualdad y las huellas SHA-256 están en `MANIFIESTO_INTEGRACION.json`.

## 6. Verificaciones realizadas

| Verificación | Resultado |
|---|---|
| Sintaxis PHP propia del proyecto | 194 archivos sin errores. |
| Suite original con PHP 8.3.6 y MariaDB 10.11, importando el SQL de Pajares en una instancia temporal | 81 pruebas, 1.170 aserciones, sin fallos ni errores; 4 omitidas. |
| Motivo de las pruebas omitidas | 3 requieren `DB_TEST_DATABASE` separado con sufijo `_test`; 1 requiere PDO SQLite. No se declaran ejecutadas. |
| Renderizado por rol/ruta | 128 combinaciones, sin excepciones ni advertencias PHP. Incluye respuestas 302/403 esperadas por permisos. |
| Navegador | 20 recorridos/pantallas, incluidos tamaños de escritorio y móvil; sin errores JavaScript ni imágenes rotas y sin desbordamiento horizontal de página en las capturas comprobadas. |
| Referencias literales locales de assets e imports CSS | Sin archivos faltantes detectados. |
| JS nuevo | Ambos archivos pasan `node --check`. |
| Preservación de archivos protegidos | 136 archivos idénticos byte por byte, más la plantilla administrativa idéntica excepto la URL local de iconos. |
| Backend y base de datos | DAO, controladores, servicios, núcleo, middleware, validación, rutas, bootstrap, configuración y SQL conservados desde Pajares. |

Se verificaron los accesos por enlace. El pie original muestra «Compras mayoristas» también a usuarios que no tienen permiso: la ruta existe y devuelve 403 para esos roles, conforme al middleware original. Se conservó el pie solicitado y no se relajó la autorización. No se declara que todos los roles tengan acceso a todos los enlaces.

## 7. Límites existentes que esta integración no resuelve

1. Algunos paneles de clientes y de ventas conservan métricas, pedidos o historial de demostración de los originales. No se añadieron registros nuevos para simular funcionamiento. No es una auditoría ni una conversión integral de todos los mockups a BD.
2. El portal mayorista original deriva a Contacto mediante GET; no constituye un nuevo flujo persistente de cotización. También conserva las reglas de precios por volumen de la vista original.
3. El SQL y backend adicional de David no se importaron: no quedan implementadas sus variantes, galería persistente, consultas analíticas, movimientos/logística adicionales ni carga de imágenes de producto. Las pantallas compatibles conservan datos reales disponibles o estados pendientes.
4. El backend de catálogo base selecciona los productos y precios reales de su propia BD; no suministra las imágenes por variante del esquema de David. Por ello se conserva el marcador gráfico de respaldo del catálogo de David.
5. No se creó un nuevo módulo Smartwatch, porque no está en los archivos de Sebastián.

**Confirmación:** no se modificaron las reglas de negocio, consultas SQL, autenticación, sesiones, permisos ni archivos de base de datos. Los cambios se limitan a composición de vistas, recursos, enlaces y selección contextual de estilos/scripts. Esta entrega integra el diseño compatible y conserva las limitaciones documentadas; no se presenta como una implementación nueva de funciones ausentes.
