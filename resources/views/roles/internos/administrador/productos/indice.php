<?php
/**
 * Definición de tipos para las variables que recibe la vista (PHPDoc).
 * @var string $atributoBaseDatosNoDisponibleOculto
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var string $atributoContenidoProductosOculto
 * @var array<array-key, mixed> $categoriasFiltro
 * @var array<array-key, mixed> $productos
 * @var string $atributoProductosVaciosOculto
 * @var array<array-key, mixed> $edicion
 * @var array<array-key, mixed> $marcasPermitidasVista
 * @var array<array-key, mixed> $categorias
 */
?>
<header class="admin-page-head">
    <div>
        <h1>
            Productos <i class="bi bi-box-seam"></i>
        </h1>
        <p>
            Gestiona el catálogo comercial. Las existencias se modifican exclusivamente desde Inventario.
        </p>
    </div>
    <div class="admin-page-actions">
        <button class="admin-primary-button" type="button" data-admin-dialog-open="product-dialog">
            <i class="bi bi-plus-lg"></i> Nuevo producto
        </button>
    </div>
</header>

<!-- Alerta condicional si la base de datos no está disponible (controlada por el atributo oculto) -->
<div class="admin-alert admin-alert--error" <?= $atributoBaseDatosNoDisponibleOculto ?>>
    La base de datos no está disponible.
</div>

<!-- Alerta condicional para mostrar un mensaje de error escapando la salida por seguridad -->
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>>
    <?= e($error) ?>
</div>

<!-- Alerta condicional para mostrar un mensaje de éxito escapado -->
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>>
    <?= e($exito) ?>
</div>

<!-- Contenedor principal condicional de productos -->
<div <?= $atributoContenidoProductosOculto ?>>
    <?php 
        // Incluye dinámicamente el componente externo de tarjetas KPI de administración
        require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; 
    ?>
    
    <div data-admin-table-container>
        <div class="admin-toolbar admin-products-toolbar">
            <label class="admin-toolbar-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    type="search"
                    data-table-search
                    placeholder="Buscar por nombre, marca o categoría..."
                    aria-label="Buscar productos">
            </label>
            <label class="admin-products-filter">
                <span>Categoría</span>
                <select data-product-category-filter aria-label="Filtrar por categoría">
                    <option value="">Todas las categorías</option>
                    <?php 
                        // Recorre el arreglo de categorías de filtro para generar las opciones del <select>
                        foreach ($categoriasFiltro as $nombreCategoria): 
                    ?>
                        <option value="<?= e($nombreCategoria) ?>"><?= e($nombreCategoria) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="admin-products-filter">
                <span>Estado</span>
                <select data-product-state-filter aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </select>
            </label>
        </div>

        <section class="admin-panel">
            <header class="admin-panel-header">
                <div class="admin-panel-title">
                    <i class="bi bi-box"></i>
                    <div>
                        <h2>Listado de productos</h2>
                        <!-- Cuenta y muestra el número total de registros reales de productos -->
                        <p><?= count($productos) ?> registros reales</p>
                    </div>
                </div>
            </header>

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila que se oculta o muestra si la lista de productos está vacía -->
                        <tr data-product-empty <?= $atributoProductosVaciosOculto ?>>
                            <td colspan="7" class="admin-table-empty">Aún no existen productos.</td>
                        </tr>
                        
                        <?php 
                            // Bucle para recorrer cada producto dentro del arreglo de productos
                            foreach ($productos as $producto): 
                        ?>
                            <tr
                                data-data-row
                                data-product-category="<?= e($producto['categoria']) ?>"
                                data-product-state="<?= (int) $producto['activo'] === 1 ? 'active' : 'inactive' ?>">
                                
                                <td class="admin-product-cell">
                                    <!-- Imagen del producto (con atributo para ocultarla si no existe) -->
                                    <img src="<?= e($producto['imagen_admin_vista']) ?>" alt="" loading="lazy" <?= $producto['atributoImagenOculta'] ?>>
                                    <!-- Icono placeholder alternativo si el producto no tiene imagen -->
                                    <span class="admin-product-image-placeholder" aria-hidden="true" <?= $producto['atributoIconoOculto'] ?>>
                                        <i class="bi <?= e($producto['icono_admin_vista']) ?>"></i>
                                    </span>
                                    <span>
                                        <!-- Muestra la marca y el nombre del producto unidos y seguros -->
                                        <b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b>
                                        <small>
                                            MDP<?= e($producto['codigo_admin_vista']) ?>
                                            <!-- Operador ternario: si existe almacenamiento, lo concatena con un punto medio -->
                                            <?= $producto['almacenamiento'] ? ' · ' . e($producto['almacenamiento']) : '' ?>
                                        </small>
                                    </span>
                                </td>
                                <td><?= e($producto['categoria']) ?></td>
                                <!-- Formatea y muestra el precio con la función personalizada formatear_dinero() -->
                                <td><?= e(formatear_dinero($producto['precio'])) ?></td>
                                <!-- Convierte el stock/existencias a entero de manera segura -->
                                <td><b><?= (int) $producto['existencias'] ?></b></td>
                                <td>
                                    <!-- Muestra la clase CSS y la etiqueta de estado de forma dinámica -->
                                    <span class="admin-status <?= e($producto['clase_estado_vista']) ?>">
                                        <?= e($producto['estado_vista']) ?>
                                    </span>
                                </td>
                                <td><?= e($producto['fecha_creacion_vista']) ?></td>
                                <td>
                                    <div class="admin-table-actions">
                                        <!-- Enlace para editar el producto apuntando a su ID correspondiente -->
                                        <a class="admin-action-icon" href="<?= e(url_interna('admin/products/' . (int) $producto['id'] . '/edit')) ?>" aria-label="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Formulario POST para desactivar el producto (oculto según atributo específico) -->
                                        <form method="post" action="<?= e(url_interna('admin/products/' . (int) $producto['id'] . '/deactivate')) ?>" <?= $producto['atributoDesactivarOculto'] ?>>
                                            <!-- Inserta el campo de protección contra ataques CSRF -->
                                            <?= csrf_field() ?>
                                            <button class="admin-action-icon is-danger" data-confirm="¿Desactivar este producto?" aria-label="Desactivar">
                                                <i class="bi bi-eye-slash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr data-product-filter-empty hidden>
                            <td colspan="7" class="admin-table-empty">
                                No se encontraron productos con los criterios seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination" data-admin-pagination></div>
        </section>
    </div>

    <!-- Modal/Diálogo para la creación o edición de un producto -->
    <!-- Evalúa si $edicion tiene datos para definir si el diálogo se abre automáticamente ("1" o "0") -->
    <dialog class="admin-dialog" id="product-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
        <header class="admin-dialog-header">
            <div>
                <!-- Cambia el título del modal dependiendo de si se está editando o creando un producto -->
                <h2><?= $edicion ? 'Editar producto' : 'Nuevo producto' ?></h2>
                <p>Los campos se validan nuevamente en el servidor.</p>
            </div>
            <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>
        <div class="admin-dialog-body">
            <!-- La acción del formulario cambia dinámicamente: ruta con ID si es edición, o ruta general si es creación -->
            <form method="post" action="<?= e(url_interna($edicion ? 'admin/products/' . (int) $edicion['id'] : 'admin/products')) ?>">
                <!-- Token CSRF de seguridad -->
                <?= csrf_field() ?>
                <div class="admin-form-grid">
                    <label class="admin-form-group is-full">
                        <span>Nombre del producto *</span>
                        <!-- Rellena el campo con el valor existente si se está editando, o vacío si es nuevo -->
                        <input name="name" maxlength="160" value="<?= e($edicion['nombre'] ?? '') ?>" required>
                    </label>
                    <label class="admin-form-group">
                        <span>Marca *</span>
                        <select name="brand" required>
                            <option value="">Selecciona una marca</option>
                            <?php 
                                // Recorre el listado de marcas permitidas para armar las opciones del selector
                                foreach ($marcasPermitidasVista as $marcaPermitida): 
                            ?>
                                <option
                                    value="<?= e($marcaPermitida['valor']) ?>"
                                    <!-- Marca la opción como 'selected' si coincide con la permitida -->
                                    <?= $marcaPermitida['seleccionada'] ? 'selected' : '' ?>>
                                    <?= e($marcaPermitida['valor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-form-group">
                        <span>Categoría *</span>
                        <select name="category_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php 
                                // Recorre todas las categorías disponibles para el selector del formulario
                                foreach ($categorias as $categoria): 
                            ?>
                                <option
                                    value="<?= (int) $categoria['id'] ?>"
                                    <!-- Compara si la categoría actual coincide con la del producto en edición para seleccionarla -->
                                    <?= (int) ($edicion['categoria_id'] ?? 0) === (int) $categoria['id'] ? 'selected' : '' ?>>
                                    <?= e($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-form-group">
                        <span>Precio (S/) *</span>
                        <input name="price" type="number" min="0.01" step=".01" value="<?= e($edicion['precio'] ?? '') ?>" required>
                    </label>
                    <label class="admin-form-group">
                        <?php if ($edicion): ?>
                            <span>Stock actual</span>
                            <input value="<?= (int) $edicion['existencias'] ?>" disabled>
                            <small>Todo cambio de stock se registra desde Inventario.</small>
                        <?php else: ?>
                            <span>Stock inicial *</span>
                            <input name="stock" type="number" min="1" max="2147483647" step="1" required inputmode="numeric" placeholder="Mínimo 1 unidad">
                            <small>Ingresa al menos 1 unidad. Los cambios posteriores se registran desde Inventario.</small>
                        <?php endif; ?>
                    </label>
                    <label class="admin-form-group">
                        <span>Capacidad</span>
                        <input name="storage" maxlength="80" value="<?= e($edicion['almacenamiento'] ?? '') ?>">
                    </label>
                    <label class="admin-form-group">
                        <span>Color</span>
                        <input name="color" maxlength="80" value="<?= e($edicion['color'] ?? '') ?>">
                    </label>
                    <label class="admin-form-group is-full">
                        <span>Imagen del producto (ruta)</span>
                        <input
                            name="image_url"
                            maxlength="255"
                            placeholder="assets/img/productos/mi-producto.jpeg"
                            value="<?= e($edicion['url_imagen'] ?? '') ?>">
                        <small>
                            Debe existir dentro de <code>public/assets/</code>; por ejemplo
                            <code>assets/img/productos/nombre-del-producto.jpeg</code>.
                        </small>
                    </label>
                    <label class="admin-form-group is-full">
                        <span>Descripción</span>
                        <textarea name="description" rows="4"><?= e($edicion['descripcion'] ?? '') ?></textarea>
                    </label>
                </div>
                <!-- Campo oculto para enviar una etiqueta opcional del producto -->
                <input type="hidden" name="badge" value="<?= e($edicion['etiqueta'] ?? '') ?>">
                <div class="admin-form-actions">
                    <button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button>
                    <button class="admin-primary-button" type="submit">
                        <i class="bi bi-check2-circle"></i> 
                        <!-- Cambia el texto del botón de envío según el contexto (Guardar cambios vs Registrar producto) -->
                        <?= $edicion ? 'Guardar cambios' : 'Registrar producto' ?>
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</div>
