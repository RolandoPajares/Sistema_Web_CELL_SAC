<?php
$campos = (array) ($configuracionModulo['campos'] ?? []);
$esCrud = (bool) ($configuracionModulo['crud'] ?? false);
$soloCrear = (bool) ($configuracionModulo['create_only'] ?? false);
$soloActualizar = (bool) ($configuracionModulo['update_only'] ?? false);
$editando = is_array($registroEdicion) && !$soloCrear;
$desactivables = ['categorias', 'proveedores', 'clientes', 'clientes-mayoristas', 'cotizaciones', 'compras'];
?>
<header class="admin-page-head">
    <div>
        <span class="eyebrow"><?= e(\App\Soporte\Autorizacion\AccesoRol::label((string) ($interfaz['rol'] ?? ''))) ?></span>
        <h1><?= e($interfaz['titulo']) ?></h1>
        <p><?= e($interfaz['descripcion']) ?></p>
    </div>
    <div class="acciones-cabecera">
        <button class="boton-fecha" type="button"><i class="bi bi-calendar3"></i> Hoy, <?= e(date('d M Y')) ?> <i class="bi bi-chevron-down"></i></button>
        <?php if ($esCrud && !$soloActualizar) :
            ?><a class="btn btn-primary" href="#editor"><i class="bi bi-plus-lg"></i> Nuevo registro</a><?php
        else :
            ?><button class="btn btn-primary" type="button"><i class="bi bi-plus-lg"></i> Nueva gestión</button><?php
        endif; ?>
    </div>
</header>

<?php if ($exito) :
    ?><div class="alert alert-success" role="status"><?= e($exito) ?></div><?php
endif; ?>
<?php if ($error) :
    ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php
endif; ?>

<div class="metricas-mockup module-kpis">
    <?php foreach ($interfaz['metricas'] as $indice => $metrica) : ?>
        <article class="metrica-mockup <?= e($metrica['color']) ?>"><div class="metrica-icono"><i class="bi <?= e($metrica['icono']) ?>"></i></div><div><span><?= e($metrica['etiqueta']) ?></span><strong><?= e($metrica['valor']) ?></strong><small><b>↑ <?= 12 + ($indice * 4) ?>%</b> vs. periodo anterior</small></div><svg class="mini-tendencia" viewBox="0 0 90 34"><polyline points="2,30 18,23 32,26 49,14 64,18 88,3"/></svg></article>
    <?php endforeach; ?>
</div>

<?php if ($esCrud) : ?>
<div class="module-workspace <?= $soloActualizar ? 'single' : '' ?>">
    <section class="panel module-table-panel">
        <div class="table-toolbar">
            <div><h2>Listado</h2><small><?= count($registros) ?> registros</small></div>
            <label class="table-search"><i class="bi bi-search"></i><input type="search" placeholder="Buscar en la tabla" data-table-search></label>
        </div>
        <div class="table-responsive">
            <table class="table data-table">
                <thead><tr>
                    <?php if ($registros !== []) : ?>
                        <?php foreach (array_keys($registros[0]) as $columna) : ?>
                            <?php if (str_ends_with((string) $columna, '_id')) {
                                continue;
                            } ?>
                            <th><?= e(ucfirst(str_replace('_', ' ', (string) $columna))) ?></th>
                        <?php endforeach; ?>
                    <?php else :
                        ?><th>Información</th><?php
                    endif; ?>
                    <th>Acciones</th>
                </tr></thead>
                <tbody>
                <?php if ($registros === []) : ?>
                    <tr><td colspan="8">Aún no existen registros o la base de datos no está disponible.</td><td></td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $registro) : ?>
                    <tr>
                        <?php foreach ($registro as $columna => $valor) : ?>
                            <?php if (str_ends_with((string) $columna, '_id')) {
                                continue;
                            } ?>
                            <td>
                                <?php if (in_array($columna, ['activo', 'estado'], true)) : ?>
                                    <span class="status-pill"><?= e((string) $valor) ?></span>
                                <?php elseif ($columna === 'total') :
                                    ?><?= money($valor) ?>
                                <?php else :
                                    ?><?= e((string) $valor) ?><?php
                                endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="table-actions">
                            <?php if (!$soloCrear) :
                                ?><a class="icon-btn" aria-label="Editar" href="<?= e(url('panel/' . $modulo) . '?edit=' . (int) $registro['id']) ?>#editor"><i class="bi bi-pencil"></i></a><?php
                            endif; ?>
                            <?php if (in_array($modulo, $desactivables, true)) : ?>
                                <form method="post" action="<?= e(url('panel/' . $modulo . '/' . (int) $registro['id'] . '/deactivate')) ?>" data-confirm="¿Deseas desactivar este registro?">
                                    <?= csrf_field() ?><button class="icon-btn danger" aria-label="Desactivar"><i class="bi bi-slash-circle"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if (!$soloActualizar || $editando) : ?>
    <aside class="panel module-editor" id="editor">
        <div class="editor-head">
            <div><span class="eyebrow"><?= $editando ? 'Edición' : 'Nuevo' ?></span><h2><?= $editando ? 'Actualizar registro' : 'Crear registro' ?></h2></div>
            <?php if ($editando) :
                ?><a class="icon-btn" href="<?= e(url('panel/' . $modulo)) ?>" aria-label="Cerrar"><i class="bi bi-x-lg"></i></a><?php
            endif; ?>
        </div>
        <form method="post" action="<?= e($editando ? url('panel/' . $modulo . '/' . (int) $registroEdicion['id']) : url('panel/' . $modulo)) ?>">
            <?= csrf_field() ?>
            <?php foreach ($campos as $nombre => $campo) : ?>
                <?php
                $tipo = (string) ($campo['type'] ?? 'text');
                $valor = (string) ($registroEdicion[$nombre] ?? '');
                if ($tipo === 'date' && $valor === '') {
                    $valor = date('Y-m-d');
                }
                $claveOpciones = match ($nombre) {
                    'producto_id' => 'productos', 'proveedor_id' => 'proveedores', 'cliente_id' => 'clientes', default => ''
                };
    ?>
                <label class="form-group"><span><?= e($campo['label'] ?? $nombre) ?><?= !empty($campo['required']) ? ' *' : '' ?></span>
                    <?php if ($tipo === 'textarea') : ?>
                        <textarea name="<?= e($nombre) ?>" maxlength="<?= (int) ($campo['max'] ?? 500) ?>"><?= e($valor) ?></textarea>
                    <?php elseif ($tipo === 'select') : ?>
                        <select name="<?= e($nombre) ?>">
                            <?php foreach ((array) ($campo['options'] ?? []) as $clave => $etiqueta) :
                                ?><option value="<?= e($clave) ?>" <?= $valor === (string) $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option><?php
                            endforeach; ?>
                        </select>
                    <?php elseif ($tipo === 'select-data') : ?>
                        <select name="<?= e($nombre) ?>" required><option value="">Selecciona una opción</option>
                            <?php foreach ((array) ($opciones[$claveOpciones] ?? []) as $opcion) :
                                ?><option value="<?= (int) $opcion['id'] ?>" <?= $valor === (string) $opcion['id'] ? 'selected' : '' ?>><?= e($opcion['etiqueta']) ?></option><?php
                            endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input class="input" type="<?= e($tipo) ?>" name="<?= e($nombre) ?>" value="<?= e($valor) ?>" <?= !empty($campo['required']) ? 'required' : '' ?> <?= isset($campo['min']) ? 'min="' . e($campo['min']) . '"' : '' ?>>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
            <button class="btn btn-primary full-width"><i class="bi bi-check2-circle"></i> <?= $editando ? 'Guardar cambios' : 'Registrar' ?></button>
        </form>
    </aside>
    <?php endif; ?>
</div>
<?php else : ?>
    <?php require __DIR__ . '/_parciales/tipos/' . (in_array($interfaz['tipo'], ['kanban', 'analitica', 'contenido', 'tarjetas', 'perfil', 'historial'], true) ? $interfaz['tipo'] : 'tabla') . '.php'; ?>
<?php endif; ?>
