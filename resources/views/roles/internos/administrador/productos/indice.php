<?php $specMap=[]; foreach(($caracteristicasEdicion??[]) as $c){$specMap[$c['nombre']]=$c['valor'];} ?>
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
            <?php if ($edicion): ?><div class="reference-image-admin"><b>Imagen referencial del producto</b><?php if(!empty($imagenReferencia)): ?><div class="reference-preview"><img src="<?= e(asset($imagenReferencia)) ?>" alt="Imagen referencial"><form method="post" action="<?= e(url('admin/products/'.(int)$edicion['id'].'/reference-image/delete')) ?>"><?= csrf_field() ?><button class="icon-btn danger" data-confirm="¿Eliminar imagen referencial?"><i class="bi bi-trash3"></i></button></form></div><?php endif; ?><form method="post" enctype="multipart/form-data" action="<?= e(url('admin/products/'.(int)$edicion['id'].'/reference-image')) ?>" class="reference-upload"><?= csrf_field() ?><input type="file" name="reference_image" accept="image/jpeg,image/png,image/webp" required><button class="btn btn-primary" type="submit"><i class="bi bi-image"></i> <?= $imagenReferencia?'Cambiar imagen':'Subir imagen' ?></button></form><small>Esta imagen se mostrará en la tarjeta del catálogo. Las fotos por color se administran abajo.</small></div><?php else: ?><div class="carga-imagen-producto"><i class="bi bi-images"></i><div><b>Imagen referencial</b><small>Primero registra el producto para poder subir su imagen.</small></div></div><?php endif; ?>
            <form method="post" action="<?= e(url($edicion ? 'admin/products/' . (int) $edicion['id'] : 'admin/products')) ?>"><?= csrf_field() ?><label class="form-group"><span>Nombre del producto *</span><input class="input" name="name" maxlength="160" value="<?= e($edicion['nombre'] ?? '') ?>" required></label>
                <div class="editor-dos"><label class="form-group"><span>Marca *</span><input class="input" name="brand" maxlength="80" value="<?= e($edicion['marca'] ?? '') ?>" required></label><label class="form-group"><span>Categoría *</span><select name="category"><?php foreach (\App\Soporte\Productos\CategoriasProducto::ALL as $categoria): ?><option value="<?= e($categoria) ?>" <?= ($edicion['categoria'] ?? '') === $categoria ? 'selected' : '' ?>><?= e($categoria) ?></option><?php endforeach; ?></select></label></div><label class="form-group"><span>Descripción</span><textarea name="description" rows="3"><?= e($edicion['descripcion'] ?? '') ?></textarea></label>
                <div class="editor-dos"><label class="form-group"><span>Precio (S/) *</span><input class="input" name="price" type="number" min="0.01" step=".01" value="<?= e($edicion['precio'] ?? '') ?>" required></label><label class="form-group"><span>Stock *</span><input class="input" name="stock" type="number" min="0" value="<?= e($edicion['existencias'] ?? 0) ?>" required></label><label class="form-group"><span>Capacidad</span><input class="input" name="storage" value="<?= e($edicion['almacenamiento'] ?? '') ?>"></label><label class="form-group"><span>Color</span><input class="input" name="color" value="<?= e($edicion['color'] ?? '') ?>"></label></div><div class="product-spec-admin"><h3>Ficha técnica para comparación</h3><div class="editor-dos"><label class="form-group"><span>Memoria RAM</span><input class="input" name="spec_ram" value="<?= e($specMap['Memoria RAM'] ?? '') ?>" placeholder="Ej. 8GB"></label><label class="form-group"><span>Memoria interna</span><input class="input" name="spec_storage" value="<?= e($specMap['Memoria Interna'] ?? ($edicion['almacenamiento'] ?? '')) ?>" placeholder="Ej. 256GB"></label><label class="form-group"><span>Batería</span><input class="input" name="spec_battery" value="<?= e($specMap['Batería'] ?? '') ?>" placeholder="Ej. 5000 mAh"></label><label class="form-group"><span>Procesador y generación</span><input class="input" name="spec_processor" value="<?= e($specMap['Procesador y generación'] ?? '') ?>" placeholder="Ej. Snapdragon 8 Gen 3"></label></div></div><input type="hidden" name="badge" value="<?= e($edicion['etiqueta'] ?? '') ?>"><button class="btn btn-primary full-width"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar producto' ?></button>
            </form>
            <?php if ($edicion): ?>
            <section class="variant-admin" id="variantes-producto">
              <div class="variant-admin-title"><h3>Colores e imágenes</h3><p>Cada color puede tener varias fotos. En el detalle, al elegir el color cambiará la galería.</p></div>
              <form class="variant-create" method="post" action="<?= e(url('admin/products/' . (int)$edicion['id'] . '/variants')) ?>"><?= csrf_field() ?>
                <input class="input" name="variant_name" placeholder="Color: Negro, Azul..." required><input class="input color-hex" type="color" name="variant_hex" value="#1f2937" title="Color visual"><input class="input" type="number" name="variant_stock" min="0" value="0" placeholder="Stock"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Añadir color</button>
              </form>
              <div class="variant-list"><?php foreach (($variantesEdicion ?? []) as $variante): ?>
                <article class="variant-box"><div class="variant-box-head"><span class="admin-swatch" style="--swatch:<?= e($variante['codigo_color'] ?: '#d8dee9') ?>"></span><div class="variant-box-info"><b><?= e($variante['nombre_color']) ?></b><small>Stock: <?= (int)$variante['stock'] ?></small></div><form class="variant-delete" method="post" action="<?= e(url('admin/products/'.(int)$edicion['id'].'/variants/'.(int)$variante['id'].'/delete')) ?>"><?= csrf_field() ?><button type="submit" class="variant-delete-btn" data-confirm="¿Eliminar el color <?= e($variante['nombre_color']) ?> y todas sus imágenes?" title="Eliminar color"><i class="bi bi-trash3"></i><span>Eliminar color</span></button></form></div>
                  <div class="variant-images"><?php foreach (($variante['imagenes'] ?? []) as $img): ?><div class="variant-image"><img src="<?= e(asset($img['ruta_imagen'])) ?>" alt=""><form method="post" action="<?= e(url('admin/products/'.(int)$edicion['id'].'/images/'.(int)$img['id'].'/delete')) ?>"><?= csrf_field() ?><button type="submit" data-confirm="¿Eliminar esta imagen?" title="Eliminar"><i class="bi bi-trash3"></i></button></form></div><?php endforeach; ?><?php if(empty($variante['imagenes'])):?><div class="variant-empty">Sin imágenes</div><?php endif;?></div>
                  <form class="variant-upload" method="post" enctype="multipart/form-data" action="<?= e(url('admin/products/'.(int)$edicion['id'].'/variants/'.(int)$variante['id'].'/images')) ?>"><?= csrf_field() ?><label><i class="bi bi-cloud-arrow-up"></i><span>Seleccionar imágenes</span><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required></label><button class="btn btn-primary" type="submit">Subir</button></form>
                </article><?php endforeach; ?></div>
            </section>
            <?php endif; ?>
        </aside>
    </div>
<?php endif; ?>