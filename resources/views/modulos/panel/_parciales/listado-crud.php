<?php
/**
 * @var array<array-key, mixed> $tablaModulo
 */ ?><section class="panel module-table-panel">
    <div class="table-toolbar">
        <div><h2>Listado</h2><small><?= $tablaModulo['cantidad_registros'] ?> registros</small></div>
        <label class="table-search"><i class="bi bi-search"></i><input type="search" placeholder="Buscar en la tabla" data-table-search></label>
    </div>
    <div class="table-responsive">
        <table class="table data-table">
            <thead>
                <tr>
                    <th <?= $tablaModulo['hay_registros'] ? 'hidden' : '' ?>>Información</th>
                    <?php foreach ($tablaModulo['columnas'] as $columna): ?>
                        <th><?= e($columna['etiqueta']) ?></th>
                    <?php endforeach; ?>
                    <th <?= $tablaModulo['atributoColumnaAccionesOculta'] ?>>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr data-table-empty-state <?= $tablaModulo['atributoTablaVaciaOculto'] ?>><td colspan="<?= (int) max(1, $tablaModulo['colspan_vacio']) ?>">Aún no existen registros o la base de datos no está disponible.</td></tr>
                <tr data-table-no-results hidden><td colspan="<?= (int) max(1, $tablaModulo['colspan_vacio']) ?>">No hay registros que coincidan con la búsqueda.</td></tr>
                <?php foreach ($tablaModulo['filas'] as $fila): ?>
                    <tr data-table-data-row>
                        <?php foreach ($fila['celdas'] as $celda): ?>
                            <td>
                                <span class="status-pill" <?= $celda['atributoEstadoOculto'] ?>><?= e($celda['valor']) ?></span>
                                <span <?= $celda['atributoPrecioOculto'] ?>><?= e($celda['valor_precio']) ?></span>
                                <span <?= $celda['atributoValorOculto'] ?>><?= e($celda['valor']) ?></span>
                            </td>
                        <?php endforeach; ?>
                        <td class="table-actions" <?= $tablaModulo['atributoColumnaAccionesOculta'] ?>>
                            <a class="icon-btn" aria-label="Editar" href="<?= e($fila['url_editar']) ?>#editor" <?= $fila['atributoEditarOculto'] ?>><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= e($fila['url_desactivar']) ?>" data-confirm="¿Deseas desactivar este registro?" <?= $fila['atributoDesactivarOculto'] ?>>
                                <?= csrf_field() ?>
                                <button class="icon-btn danger" aria-label="Desactivar"><i class="bi bi-slash-circle"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
