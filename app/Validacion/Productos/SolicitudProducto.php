<?php

declare(strict_types=1);

namespace App\Validacion\Productos;

use App\Modelos\Productos\Producto;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudProducto
{
    /** @var array<int, string> Marcas habilitadas para el catálogo comercial. */
    public const MARCAS_PERMITIDAS = ['Samsung', 'Apple', 'OPPO', 'Xiaomi', 'Honor', 'JBL', 'Beats'];

    /**
     * Comprueba que los datos cumplan las reglas antes de continuar.
     * @return array<string, mixed>
     */

    // Validación de datos de producto
    public static function validar(Solicitud $solicitud, bool $permitirStockInicial = true): array
    {
        // Función anónima para obtener y limpiar los valores de entrada
        $texto = static function (string $campo) use ($solicitud): string {
            $valor = $solicitud->entrada($campo, '');
            return is_scalar($valor) ? trim((string) $valor) : '';
        };

        // Inicializa los datos del producto con valores predeterminados
        $datos = [
            'marca' => $texto('brand'),
            'nombre' => $texto('name'),
            'categoria' => $texto('category'),
            'categoria_id' => 0,
            'precio' => 0.0,
            'almacenamiento' => $texto('storage'),
            'color' => $texto('color'),
            'etiqueta' => $texto('badge'),
            'descripcion' => $texto('description'),
            'url_imagen' => $texto('image_url'),
        ];

        $errores = []; // Inicializa un array para almacenar los errores de validación

        // Validación de la marca del producto
        if ($datos['marca'] === '' || mb_strlen($datos['marca']) > 80) {
            $errores['marca'] = 'La marca es obligatoria y admite hasta 80 caracteres.';
        } else {
            $marcaCanonica = null;
            foreach (self::MARCAS_PERMITIDAS as $marcaPermitida) {
                if (mb_strtolower($marcaPermitida) === mb_strtolower($datos['marca'])) {
                    $marcaCanonica = $marcaPermitida;
                    break;
                }
            }

            // Si la marca no es válida, se agrega un error al array de errores
            if ($marcaCanonica === null) {
                $errores['marca'] = 'Selecciona una marca permitida: Samsung, Apple, OPPO, Xiaomi, Honor, JBL o Beats.';
            } else {
                $datos['marca'] = $marcaCanonica;
            }
        }

        // Validación del nombre del producto
        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 160) {
            $errores['nombre'] = 'El nombre es obligatorio y admite hasta 160 caracteres.';
        }

        // Validación de la categoría del producto
        $idCategoria = filter_var($solicitud->entrada('category_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idCategoria === false && $datos['categoria'] === '') {
            $errores['categoria'] = 'Selecciona una categoría válida.';
        } elseif ($idCategoria !== false) {
            $datos['categoria_id'] = (int) $idCategoria;
        }

        // Validación del precio del producto
        $precio = $solicitud->entrada('price');
        if (!is_scalar($precio) || !is_numeric($precio) || !is_finite((float) $precio)
            || (float) $precio <= 0 || (float) $precio > Producto::MAXIMO_CENTIMOS / 100
        ) {
            $errores['precio'] = 'El precio debe ser mayor que cero.';
        } else {
            $datos['precio'] = (float) $precio;
        }

        // Validación del stock inicial del producto si se permite
        if ($permitirStockInicial) {
            $stockInicial = filter_var(
                $solicitud->entrada('stock', '0'),
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 2147483647]]
            );
            // Si el stock inicial no es válido, se agrega un error al array de errores
            if ($stockInicial === false) {
                $errores['stock'] = 'El stock inicial debe ser un número entero entre 1 y 2147483647.';
            } else {
                $datos['existencias'] = $stockInicial;
            }
        }

        // Validación de campos opcionales con límites de caracteres
        foreach (['almacenamiento' => 80, 'color' => 80, 'etiqueta' => 80] as $campo => $limite) {
        // Si la longitud del campo excede el límite, se agrega un error al array de errores    
        if (mb_strlen($datos[$campo]) > $limite) {
                $errores[$campo] = 'El campo ' . $campo . ' admite hasta ' . $limite . ' caracteres.';
            }
        }

        // Validación de la URL de la imagen del producto
        if (strlen($datos['url_imagen']) > 255) {
            $errores['url_imagen'] = 'La ruta de la imagen admite hasta 255 caracteres.';
        } elseif ($datos['url_imagen'] !== '' && !self::esRutaImagenValida($datos['url_imagen'])) {
            $errores['url_imagen'] = 'Usa una ruta de imagen existente dentro de assets/ o una URL HTTPS válida.';
        }

        // Validación de la descripción del producto
        if (strlen($datos['descripcion']) > 65535) {
            $errores['descripcion'] = 'La descripción es demasiado larga.';
        }

        // Si hay errores de validación, se lanza una excepción con los errores
        if ($errores) {
            throw new ExcepcionValidacion($errores);
        }

        return $datos;
    }

    /**
     * Valida que la imagen use HTTPS o una ruta local existente dentro de public/assets.
     */
    private static function esRutaImagenValida(string $ruta): bool
    {
        // Limpia la ruta y obtiene el esquema (http, https, etc.)
        $esquema = strtolower((string) parse_url($ruta, PHP_URL_SCHEME));
        if (in_array($esquema, ['http', 'https'], true)) {
            return $esquema === 'https' && filter_var($ruta, FILTER_VALIDATE_URL) !== false;
        }

        // Valida que la ruta relativa sea segura y que el archivo exista en public/assets
        $rutaRelativa = ltrim($ruta, '/');
        if (!preg_match('#^assets/[A-Za-z0-9_./-]+$#D', $rutaRelativa) || str_contains($rutaRelativa, '..')) {
            return false;
        }

        // Comprueba si el archivo existe en la carpeta public
    
        return is_file(dirname(__DIR__, 3) . '/public/' . $rutaRelativa); // Devuelve true si el archivo existe, false en caso contrario
    }
}
