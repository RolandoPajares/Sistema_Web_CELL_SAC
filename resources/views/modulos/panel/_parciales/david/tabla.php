<?php

/**
 * @var array<array-key, mixed> $tablaDavid
 * @var array<array-key, mixed> $interfaz
 */ ?><section class="panel modulo-filtros" data-module-filters <?= $tablaDavid['atributoFiltrosProductoOculto'] ?>>
    <label><i class="bi bi-search"></i><input type="search" placeholder="Buscar en gestión de productos..." data-filter-search></label>
    <select class="module-filter-select" data-filter-status>
        <option value="">Todos los estados</option>
        <option>Publicado</option>
        <option>Stock bajo</option>
        <option>Borrador</option>
    </select>
    <button class="btn btn-primary" type="button" data-apply-filters>Aplicar filtros</button>
</section>
<div class="modulo-listado-real modulo-claro" data-filter-table>
    <section class="panel tabla-mockup">
        <div class="titulo-panel">
            <div>
                <i class="bi <?= e($interfaz['icono'] ?? 'bi-list') ?>"></i>
                <h2><?= e($tablaDavid['titulo_vista']) ?></h2>
            </div>
            <button type="button" data-export-table><i class="bi bi-download"></i> Exportar</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <?php foreach ($tablaDavid['columnas'] as $etiquetaColumna): ?>
                            <th><?= e($etiquetaColumna) ?></th>
                        <?php endforeach; ?>
                        <th <?= $tablaDavid['atributoColumnasAlertaOcultas'] ?>>Actualizar stock</th>
                        <th <?= $tablaDavid['atributoColumnasAlertaOcultas'] ?>>Proveedor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr <?= $tablaDavid['atributoFilasTablaVaciasOculto'] ?>>
                        <td colspan="<?= (int) $tablaDavid['colspan'] ?>">No hay registros disponibles.</td>
                    </tr>
                    <?php foreach ($tablaDavid['filas'] as $fila): ?>
                        <tr data-filter-row data-record='<?= e($fila['json']) ?>'>
                            <?php foreach ($fila['celdas'] as $celda): ?>
                                <td>
                                    <span class="estado <?= e($celda['clase_estado']) ?>" <?= $celda['atributoEstadoOculto'] ?>><?= e($celda['valor']) ?></span>
                                    <span <?= $celda['atributoPrecioOculto'] ?>><?= e($celda['valor_precio']) ?></span>
                                    <span <?= $celda['atributoValorOculto'] ?>><?= e($celda['valor']) ?></span>
                                </td>
                            <?php endforeach; ?>
                            <td <?= $tablaDavid['atributoAccionesAlertaOcultas'] ?>><a class="btn btn-primary btn-sm" href="<?= e($tablaDavid['urlModuloInventario']) ?>#editor"><i class="bi bi-box-arrow-in-down"></i> Gestionar stock</a></td>
                            <td <?= $tablaDavid['atributoAccionesAlertaOcultas'] ?>><button class="btn btn-outline btn-sm" type="button" data-provider-detail><i class="bi bi-person-lines-fill"></i> Ver contacto</button></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-filter-empty hidden>
                        <td colspan="<?= (int) $tablaDavid['colspan'] ?>">No hay registros que coincidan con los filtros.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    <aside class="panel detalle-mockup" data-provider-panel hidden <?= $tablaDavid['atributoPanelProveedorOculto'] ?>>
        <div class="titulo-panel">
            <div><i class="bi bi-truck"></i>
                <h2>Contacto del proveedor</h2>
            </div>
            <button type="button" data-close-provider><i class="bi bi-x-lg"></i></button>
        </div>
        <dl class="datos-detalle">
            <div>
                <dt>Producto</dt>
                <dd data-p-product>—</dd>
            </div>
            <div>
                <dt>Proveedor</dt>
                <dd data-p-name>—</dd>
            </div>
            <div>
                <dt>Correo</dt>
                <dd data-p-email>—</dd>
            </div>
            <div>
                <dt>Teléfono</dt>
                <dd data-p-phone>—</dd>
            </div>
        </dl>
        <small>Si aparece “Sin proveedor asignado”, primero debe vincularse un proveedor al producto.</small>
    </aside>
</div>