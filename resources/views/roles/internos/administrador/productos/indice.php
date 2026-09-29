<header class="admin-page-head">
    <div><span class="eyebrow">Productos</span>
        <h1>Gestión de productos</h1>
        <p>Administra tu catálogo de productos, precios, stock y disponibilidad.</p>
    </div>
    <div class="acciones-cabecera"><a class="btn btn-primary" href="#editor-producto"><i class="bi bi-plus-lg"></i> Nuevo producto</a><a class="btn btn-ghost" href="<?= e(url('catalog')) ?>"><i class="bi bi-eye"></i> Ver catálogo</a></div>
</header>
<?php if (!$baseDatosDisponible): ?><div class="alert alert-error">Ejecuta <code>php bin/console migrate</code> primero.</div><?php else: ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?><?php if ($exito): ?><div class="alert alert-success"><?= e($exito) ?></div><?php endif; ?>
    <div class="metricas-mockup module-kpis">
        <article class="metrica-mockup azul">
            <div class="metrica-icono"><i class="bi bi-box-seam"></i></div>
            <div><span>Total de productos</span><strong><?= count($productos) ?></strong><small><b>↑ 12%</b> vs. mes anterior</small></div>
        </article>
        <article class="metrica-mockup verde">
            <div class="metrica-icono"><i class="bi bi-check-circle"></i></div>
            <div><span>Publicados</span><strong><?= count(array_filter($productos, fn($p) => (int) $p['activo'] === 1)) ?></strong><small><b>↑ 8%</b> vs. mes anterior</small></div>
        </article>
        <article class="metrica-mockup ambar">
            <div class="metrica-icono"><i class="bi bi-file-earmark"></i></div>
            <div><span>Borradores</span><strong>28</strong><small>Pendientes de revisión</small></div>
        </article>
        <article class="metrica-mockup rojo">
            <div class="metrica-icono"><i class="bi bi-exclamation-triangle"></i></div>
            <div><span>Stock bajo</span><strong><?= count(array_filter($productos, fn($p) => (int) $p['existencias'] <= 8)) ?></strong><small>Requieren atención</small></div>
        </article>
    </div>
    <section class="panel modulo-filtros"><label><i class="bi bi-search"></i><input type="search" placeholder="Buscar por nombre, SKU o marca" data-table-search></label><button>Las categorías <i class="bi bi-chevron-down"></i></button><button>Las marcas <i class="bi bi-chevron-down"></i></button><button>Todos los estados <i class="bi bi-chevron-down"></i></button></section>
    <div class="module-workspace productos-workspace">
        <section class="panel module-table-panel">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($productos as $producto): ?><tr>
                                <td>MDP<?= str_pad((string) $producto['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td><span class="producto-tabla-icono"><i class="bi bi-phone"></i></span><b><?= e($producto['nombre']) ?></b><small><?= e($producto['almacenamiento'] ?? '') ?> · <?= e($producto['color'] ?? '') ?></small></td>
                                <td><?= e($producto['categoria']) ?></td>
                                <td><?= e($producto['marca']) ?></td>
                                <td><?= money($producto['precio']) ?></td>
                                <td><?= (int) $producto['existencias'] ?></td>
                                <td><span class="estado <?= (int) $producto['activo'] === 1 ? 'estado--verde' : 'estado--ambar' ?>"><?= (int) $producto['activo'] === 1 ? 'Publicado' : 'Inactivo' ?></span></td>
                                <td class="table-actions"><a class="icon-btn" href="<?= e(url('admin/products/' . (int) $producto['id'] . '/edit')) ?>#editor-producto" aria-label="Editar"><i class="bi bi-pencil"></i></a><?php if ((int) $producto['activo'] === 1): ?><form method="post" action="<?= e(url('admin/products/' . (int) $producto['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="icon-btn danger" data-confirm="¿Ocultar este producto?" aria-label="Ocultar"><i class="bi bi-eye-slash"></i></button></form><?php endif; ?></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
        <aside class="panel module-editor" id="editor-producto">
            <div class="editor-head">
                <div><span class="eyebrow"><?= $edicion ? 'Editar producto' : 'Nuevo producto' ?></span>
                    <h2><?= $edicion ? e($edicion['nombre']) : 'Datos del producto' ?></h2>
                </div><?php if ($edicion): ?><a class="icon-btn" href="<?= e(url('admin/products')) ?>"><i class="bi bi-x-lg"></i></a><?php endif; ?>
            </div>
            <div class="carga-imagen-producto"><i class="bi bi-image"></i><button type="button">Cambiar imagen</button><small>JPG, PNG o WebP. Máx. 5MB</small></div>
            <form method="post" action="<?= e(url($edicion ? 'admin/products/' . (int) $edicion['id'] : 'admin/products')) ?>"><?= csrf_field() ?><label class="form-group"><span>Nombre del producto *</span><input class="input" name="name" maxlength="160" value="<?= e($edicion['nombre'] ?? '') ?>" required></label>
                <div class="editor-dos"><label class="form-group"><span>Marca *</span><input class="input" name="brand" maxlength="80" value="<?= e($edicion['marca'] ?? '') ?>" required></label><label class="form-group"><span>Categoría *</span><select name="category"><?php foreach (\App\Soporte\Productos\CategoriasProducto::ALL as $categoria): ?><option value="<?= e($categoria) ?>" <?= ($edicion['categoria'] ?? '') === $categoria ? 'selected' : '' ?>><?= e($categoria) ?></option><?php endforeach; ?></select></label></div><label class="form-group"><span>Descripción</span><textarea name="description" rows="3"><?= e($edicion['descripcion'] ?? '') ?></textarea></label>
                <div class="editor-dos"><label class="form-group"><span>Precio (S/) *</span><input class="input" name="price" type="number" min="0.01" step=".01" value="<?= e($edicion['precio'] ?? '') ?>" required></label><label class="form-group"><span>Stock *</span><input class="input" name="stock" type="number" min="0" value="<?= e($edicion['existencias'] ?? 0) ?>" required></label><label class="form-group"><span>Capacidad</span><input class="input" name="storage" value="<?= e($edicion['almacenamiento'] ?? '') ?>"></label><label class="form-group"><span>Color</span><input class="input" name="color" value="<?= e($edicion['color'] ?? '') ?>"></label></div><input type="hidden" name="badge" value="<?= e($edicion['etiqueta'] ?? '') ?>"><button class="btn btn-primary full-width"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar producto' ?></button>
            </form>
        </aside>
    </div>
<?php endif; ?>