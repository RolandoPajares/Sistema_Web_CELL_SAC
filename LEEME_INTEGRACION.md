# Sistema Web CELL SAC — proyecto unificado

Esta entrega integra los cuatro ZIP siguiendo el mapa de procedencia. El backend y el SQL son los de Pajares. Las diferencias de presentación y los límites detectados están en `docs/INFORME_INTEGRACION.md`.

## Ejecutar con XAMPP

1. Extrae `Sistema_Web_CELL_SAC` dentro de `C:\xampp\htdocs\`.
2. Inicia Apache y MySQL.
3. En phpMyAdmin importa **`database/md_tecnologia_digital_cell_completa_2026-09-28.sql`** en una instalación de prueba. El archivo original contiene `CREATE DATABASE`, `USE` y `DROP TABLE`: reemplaza las tablas de la base del mismo nombre. Respalda tu base si ya tiene información.
4. Revisa en `.env` los valores `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` de tu instalación. No necesitas cambiar los controladores.
5. Abre **http://localhost/Sistema_Web_CELL_SAC/public/**.

Requisitos: PHP 8.2 o superior, PDO MySQL, mbstring, fileinfo y MySQL/MariaDB. Se incluye `vendor/`. Apache necesita `mod_rewrite` y permitir el `.htaccess` de `public/`.

Alternativa de desarrollo, desde la carpeta del proyecto:

```bat
C:\xampp\php\php.exe -S 127.0.0.1:8080 -t public public/index.php
```

Abre http://127.0.0.1:8080/. Para alojamiento real, el directorio público del servidor debe ser exclusivamente `public/`.

## Cuentas del SQL original

Contraseña académica: `Demo2026!`.

| Rol | Correo |
|---|---|
| Administrador | admin@md.demo |
| Compras y logística | compras@md.demo |
| Ventas mayoristas | b2b@md.demo |
| Ventas minoristas | b2c@md.demo |
| Marketing | marketing@md.demo |
| Cliente minorista | minorista@md.demo |
| Cliente mayorista | mayorista@md.demo |

Los permisos siguen siendo los originales. `/mayorista` requiere cliente mayorista o administrador; el visitante es enviado al inicio de sesión.

## Qué incluye

- Pajares: base, administración, inicio, encabezado, pie y elementos exclusivos.
- David: catálogo y detalle, estilos/composiciones de compras y marketing compatibles con la base.
- Yaxon: Nosotros y sus recursos.
- Sebastián: cuentas minorista/mayorista, Contacto, SmartMatch y portal mayorista.
- Bootstrap Icons 1.11.3 local, con licencia, para evitar depender del CDN.

**Smartwatch:** no existe una pantalla independiente con ese nombre en Sebastián. Sí existe SmartMatch, que se integró. No se creó ni renombró una pantalla para simular que existía.

**Alcance:** integración de presentación; no implementa las variantes, galerías almacenadas en tablas nuevas, consultas adicionales de David, cotización persistente ni otras funciones pendientes de los originales. No reemplaza la base de datos por la de David. Lee los límites concretos en el informe.

El `README.md` heredado se conserva como documentación original; para instalar esta entrega utiliza este archivo, pues el README antiguo menciona nombres y rutas que no están en los ZIP recibidos.
