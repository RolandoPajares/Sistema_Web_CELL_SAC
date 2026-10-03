<?php

declare(strict_types=1);

namespace App\Servicios\Productos; // Define el espacio de nombres para la clase ProductoServicio

use App\DTO\Productos\FiltroProducto;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\DAO\Contratos\RepositorioCategoriaInterfaz;
use App\Modelos\Productos\Producto;

//declaro la clase ProductoServicio como final para evitar que sea extendida, 
//asegurando que su comportamiento permanezca consistente y no pueda ser modificado por herencia.
final class ProductoServicio
{
    // declaro el constructor de la clase ProductoServicio, que recibe dos dependencias:
    // un repositorio de productos y un repositorio de categorías.
    public function __construct(
        private RepositorioProductoInterfaz $productos,
        private ?RepositorioCategoriaInterfaz $categorias = null,
    ) {
    }

    // metodo público que verifica si la conexión a la base de datos está disponible,
    // devolviendo un valor booleano que indica el estado de disponibilidad.
    public function conexionDisponible(): bool
    {
        return $this->productos->estaDisponible(); // Verifica si la conexión a la base de datos está disponible
    }

    /**
     * Devuelve los productos destacados que pueden mostrarse en la página.
     *
     * @return array<int, array<string, mixed>>
     */
    // metodo público que obtiene una lista de productos destacados, 
    // limitando la cantidad según el parámetro proporcionado.
    public function destacados(int $limite = 20): array
    {
        $limite = max(0, $limite);
        if ($limite === 0) {
            return []; // Si el límite es cero, devuelve un arreglo vacío
        }

        // Obtiene todos los productos activos y los agrupa por categoría 
        $productosPorCategoria = [];
        // Itera sobre los productos destacados y los organiza por categoría,
        // asegurando que se incluyan productos de diferentes categorías en la selección final.
        foreach ($this->productos->ofertasPopulares() as $producto) {

            $categoria = trim(Producto::desdeRegistro($producto)->categoria()); // Obtiene la categoría del producto y la limpia de espacios en blanco
            $claveCategoria = mb_strtolower($categoria !== '' ? $categoria : 'otros'); // Usa la categoría en minúsculas como clave, o 'otros' si está vacía
            $productosPorCategoria[$claveCategoria][] = $producto;
        }

        // Intercala categorias para incluir variedad del catalogo entre las cuatro filas.
        $seleccion = [];

        // Itera sobre los productos agrupados por categoría, 
        //agregando uno de cada categoría a la selección final
        for ($indiceCategoria = 0; count($seleccion) < $limite; $indiceCategoria++) {
            $seAgregoProducto = false;

            // Itera sobre cada categoría y agrega un producto a la selección si está disponible
            foreach ($productosPorCategoria as $productosCategoria) {
                if (!isset($productosCategoria[$indiceCategoria])) { // isset verifica si el índice existe en el arreglo, evitando errores de acceso a índices no definidos
                    continue;
                }
                  // Agrega el producto a la selección y marca que se agregó un producto
                $seleccion[] = $productosCategoria[$indiceCategoria];
                $seAgregoProducto = true;
                if (count($seleccion) >= $limite) { // count verifica la cantidad de elementos en el arreglo, asegurando que no se exceda el límite establecido
                    break;
                }
            }
               // Si no se agregó ningún producto en esta iteración, rompe el bucle para evitar iteraciones innecesarias
            if (!$seAgregoProducto) {
                break;
            }
        }

        return $seleccion;
    }

    /**
     * Obtiene el producto solicitado para la operación de catálogo indicada.
     * @return array<int, array<string, mixed>>
     */

    // metodo público que busca productos activos según los criterios de búsqueda proporcionados,
    // devolviendo un arreglo con los productos que coinciden con la consulta, marca y categoría
    public function buscar(?string $consulta, ?string $marca, ?string $categoria): array
    {
        $consulta = trim((string) $consulta);
        $marca = trim((string) $marca);
        $categoria = trim((string) $categoria);

        // Filtra los productos activos según los criterios de búsqueda y devuelve el resultado
        return array_values(array_filter(
            $this->todosActivos(),

            // Función de filtro que verifica si un producto coincide con los criterios de búsqueda
            static function (array $producto) use ($consulta, $marca, $categoria): bool {
                $modelo = Producto::desdeRegistro($producto);
                $coincideConsulta = $modelo->coincideConTexto($consulta); // Verifica si el producto coincide con la consulta de texto
                $coincideCategoria = $categoria === '' || $modelo->categoria() === $categoria;

            // Devuelve verdadero si el producto coincide con la consulta, la marca y la categoría; de lo contrario, devuelve falso
                return $coincideConsulta
                    && ($marca === '' || $modelo->marca() === $marca)
                    && $coincideCategoria;
            }
        ));
    }

    /**
     * Devuelve los productos de la página solicitada junto con los datos de paginación.
     *
     * @return array{productos:array<int,array<string,mixed>>,total:int,pagina:int,por_pagina:int,ultima_pagina:int}
     */

    // paginar recibe un objeto FiltroProducto que contiene los criterios de búsqueda y paginación, y devuelve un arreglo con los productos filtrados y la información de paginación correspondiente.
    public function paginar(FiltroProducto $filtro): array
    {
        return $this->productos->paginar($filtro);
    }

    /**
     * Busca el registro activo que coincide con el identificador o filtro indicado.
     * @return array<string, mixed>|null
     */
    // metodo público que busca un producto activo según el identificador proporcionado,
    // devolviendo un arreglo con los datos del producto si se encuentra, o null si no
    public function buscarActivo(int $idProducto): ?array
    {
        return $this->productos->buscarActivo($idProducto);
    }

    /**
     * Obtiene el producto activo con sus imágenes y características asociadas.
     * @return array<string, mixed>|null
     */
    // metodo público que busca un producto activo junto con sus imágenes y características relacionadas,
    // devolviendo un arreglo con los datos completos del producto si se encuentra, o null si
    public function buscarActivoConRelaciones(int $idProducto): ?array
    {
        // Complementa la ficha activa con sus imágenes y características sin trasladar consultas a las vistas.
        $producto = $this->productos->buscarActivo($idProducto);
        if ($producto === null) {
            return null;
        }
          
        // Obtiene las imágenes del producto y las imágenes relacionadas desde la tabla de relaciones, eliminando duplicados y valores vacíos
        $imagenes = [(string) ($producto['url_imagen'] ?? '')];
        $imagenes = array_merge($imagenes, $this->productos->imagenesRelacionadas($idProducto)); // Obtiene las imágenes relacionadas desde la tabla de relaciones

        $producto['imagenes'] = array_values(array_unique(array_filter(array_map( // Elimina duplicados y valores vacíos de las rutas de imágenes
            static fn (string $ruta): string => trim($ruta), // Limpia espacios en blanco de las rutas
            $imagenes
        ))));

        // Obtiene las características del producto desde la tabla de relaciones y las agrega al arreglo del producto
        $producto['caracteristicas'] = $this->productos->caracteristicas($idProducto);

        return $producto;
    }

    /**
     * Devuelve los registros disponibles para el panel de administración.
     * @return array<int, array<string, mixed>>
     */
    public function todosParaAdministrador(): array
    {
        return $this->productos->todosParaAdministrador();
    }

    /**
     * Calcula los indicadores resumidos del panel de administración.
     *
     * @return array{total:int,activos:int,inactivos:int,stock_bajo:int}
     */

    // metodo público que calcula un resumen de los productos para el panel de administración,
    // devolviendo un arreglo con el total de productos, la cantidad de activos, inact
    public function resumenAdministrativo(): array
    {
        // Obtiene todos los productos disponibles para el administrador y calcula un resumen de su estado
        $productos = $this->todosParaAdministrador();
        $resumen = ['total' => count($productos), 'activos' => 0, 'inactivos' => 0, 'stock_bajo' => 0];

        // Itera sobre los productos y actualiza el resumen según su estado y stock
        foreach ($productos as $registro) {
            $producto = Producto::desdeRegistro($registro);
            if ($producto->estaActivo()) {
                $resumen['activos']++;
                if ($producto->tieneStockBajo()) {
                    $resumen['stock_bajo']++;
                }
            } else {
                $resumen['inactivos']++;
            }
        }

        return $resumen;
    }

    /**
     * Busca un producto por su identificador para mostrarlo en la administración.
     * @return array<string, mixed>|null
     */

    // metodo público que busca un producto por su identificador para mostrarlo en la administración,
    // devolviendo un arreglo con los datos del producto si se encuentra, o null si no
    public function buscarParaAdministrador(int $idProducto): ?array
    {
        return $this->productos->buscarParaAdministrador($idProducto);
    }

    /**
     * Valida los datos y crea o actualiza el producto según el identificador recibido.
     * @param array<string, mixed> $datos
     */
    // metodo público que valida los datos de un producto y crea o actualiza el registro correspondiente según el identificador recibido,
    // devolviendo el identificador del producto creado o actualizado
    public function guardar(array $datos, ?int $idProducto = null): int
    {
        // Verifica si la conexión a la base de datos está disponible antes de realizar operaciones de guardado
        if (!$this->productos->estaDisponible()) {
            throw new \RuntimeException('La base de datos no está disponible.');
        }

        $datos = $this->resolverCategoria($datos);

           // Valida si el producto existe y si hay otro producto con la misma marca y nombre antes de actualizar o crear un nuevo registro
        if ($idProducto !== null && $idProducto > 0) {
            //  Verifica si el producto existe antes de intentar actualizarlo; si no, lanza una excepción
            if ($this->productos->buscarParaAdministrador($idProducto) === null) {
                throw new \DomainException('El producto no existe.');
            }
            // Verifica si ya existe otro producto con la misma marca y nombre antes de actualizar el registro
            if (
                $this->productos->existeConMarcaYNombre(
                    (string) $datos['marca'],
                    (string) $datos['nombre'],
                    $idProducto
                )
            ) {
                // Lanza una excepción si ya existe otro producto con la misma marca y nombre, evitando duplicados
                throw new \DomainException('Ya existe otro producto con la misma marca y nombre.');
            }
            $this->productos->actualizar($idProducto, $datos);

            return $idProducto;
        }

// Verifica si ya existe un producto con la misma marca y nombre antes de crear un nuevo registro
        if ($this->productos->existeConMarcaYNombre((string) $datos['marca'], (string) $datos['nombre'])) {
            throw new \DomainException('Ya existe un producto con la misma marca y nombre.');
        }

        // Valida el stock inicial y lanza una excepción si no es un número entero válido entre 1 y 2147483647
        $stockInicial = filter_var(
            $datos['existencias'] ?? 0,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 2147483647]]
        );
        if ($stockInicial === false) {
            throw new \DomainException('El stock inicial debe ser un número entero entre 1 y 2147483647.');
        }
        $datos['existencias'] = $stockInicial;

        return $this->productos->crear($datos);
    }

    /**
     * Elimina «o desactivar» respetando las relaciones registradas.
     */

    // metodo público que elimina o desactiva un producto según su identificador,
    public function eliminarODesactivar(int $idProducto): string
    {
        // Verifica si la conexión a la base de datos está disponible antes de realizar operaciones de eliminación o desactivación
        if (!$this->productos->estaDisponible()) {
            throw new \RuntimeException('La base de datos no está disponible.');
        }
           
        // Verifica si el producto existe y está activo antes de intentar eliminarlo o desactivarlo
        if ($idProducto <= 0 || $this->productos->buscarActivo($idProducto) === null) {
            throw new \DomainException('El producto no existe o ya fue desactivado.');
        }
        // Intenta eliminar físicamente el producto; si no es posible, lo desactiva y devuelve el estado correspondiente
        if ($this->productos->eliminarFisicamente($idProducto)) {
            return 'deleted';
        }
         
        // Si no se puede eliminar físicamente, desactiva el producto y devuelve el estado correspondiente
        $this->productos->desactivar($idProducto);

        return 'deactivated';
    }

    
    // metodo público que cuenta la cantidad de productos activos en el sistema,
    // devolviendo un valor entero que representa el total de productos activos
    public function contarActivos(): int
    {
        return $this->productos->contarActivos();
    }

   
    // metodo público que suma la cantidad de existencias para los productos activos,
    // devolviendo un valor entero que representa el stock total de productos activos
    public function stockTotal(): int
    {
        return $this->productos->stockTotal(); // Devuelve la suma de existencias para los productos activos
    }

    /**
     * Devuelve todos los registros activos del repositorio.
     * @return array<int, array<string, mixed>>
     */

    // metodo público que obtiene todos los productos activos del repositorio,
    // devolviendo un arreglo con los datos de cada producto activo
    public function todosActivos(): array
    {
        return $this->productos->todosActivos();
    }

    /**
     * Recupera las características registradas para el producto indicado.
     *
     * @return array<int, array{nombre:string,valor:string}>
     */

    // metodo público que obtiene las características asociadas a un producto según su identificador,
    // devolviendo un arreglo con el nombre y valor de cada característica registrada
    public function caracteristicas(int $idProducto): array
    {
        return $this->productos->caracteristicas($idProducto);
    }

    /**
     * Procesa «categoría» y devuelve el resultado correspondiente.
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */

    // metodo privado que procesa la categoría de un producto y devuelve los datos actualizados,
    // verificando si la categoría existe y está activa, y lanzando una excepción si no
    private function resolverCategoria(array $datos): array
    {
        // Si no se ha proporcionado un repositorio de categorías, devuelve los datos sin cambios
        if ($this->categorias === null) {
            return $datos; // Devuelve los datos sin cambios si no hay un repositorio de categorías disponible
        }
       
        // Intenta buscar la categoría por su identificador o nombre, 
        // y lanza una excepción si no existe o está inactiva

        $categoria = (int) ($datos['categoria_id'] ?? 0) > 0
            ? $this->categorias->buscarActiva((int) $datos['categoria_id'])
            : $this->categorias->buscarPorNombre((string) ($datos['categoria'] ?? ''));

            // Verifica si la categoría existe y está activa; si no, lanza una excepción
        if ($categoria === null || (int) ($categoria['activo'] ?? 1) !== 1) {
            throw new \DomainException('La categoría seleccionada no existe o está inactiva.'); // Lanza una excepción si la categoría no existe o está inactiva
        }
          
        // Actualiza los datos del producto con el identificador y nombre de la categoría encontrada
        $datos['categoria_id'] = (int) $categoria['id'];
        $datos['categoria'] = (string) $categoria['nombre'];

        return $datos; // Devuelve los datos actualizados con la categoría resuelta
    }
}
