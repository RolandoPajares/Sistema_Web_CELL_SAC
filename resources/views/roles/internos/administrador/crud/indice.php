<?php
/**
 * @var array<int, array<string, mixed>> $registros
 * @var array<int, array<string, mixed>> $camposFormularioVista
 * @var array<string, string> $columnas
 
 * @var string $tituloModulo
 * @var string $iconoModulo
 * @var string $descripcionModulo
 * @var string $botonNuevo
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var string $tituloModuloMinuscula
 * @var string $atributoRegistrosVaciosOculto
 * @var mixed $edicion
 * @var string $urlFormularioCrud
 */
?>
<header class="admin-page-head">
    <div>
        <h1>
            <?= e($tituloModulo) ?> <i class="bi <?= e($iconoModulo) ?>"></i>
        </h1>
        <p>
            <?= e($descripcionModulo) ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <button class="admin-primary-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> <?= e($botonNuevo) ?>
        </button>
    </div>
</header>
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<div class="admin-toolbar">
    <label class="admin-toolbar-search"><i class="bi bi-search"></i><input type="search" data-table-search placeholder="Buscar en el listado..."></label><span class="admin-planned">Filtros adicionales: próxima iteración</span>
</div>
<section class="admin-panel" data-admin-table-container>

    <header class="admin-panel-header">
        <div class="admin-panel-title">
            <i class="bi <?= e($iconoModulo) ?>"></i>
            <div>
                <h2>
                Listado de <?= e($tituloModuloMinuscula) ?>
                </h2>
                <p>
                <?= count($registros) ?> registros reales
                </p>
            </div>
        </div>
    </header>

    <div class="admin-table-wrap">
        <table class="admin-table" data-admin-table>
            <thead>
                <tr>
                <?php foreach ($columnas as $etiqueta): ?>
                    <th>
                <?= e($etiqueta) ?>
                    </th>
                <?php endforeach; ?>
                    <th>
                Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
        <tr <?= $atributoRegistrosVaciosOculto ?>><td colspan="<?= count($columnas) + 1 ?>" class="admin-table-empty">Aún no existen registros.</td></tr>
        <?php foreach ($registros as $registro): ?><tr data-data-row><?php foreach ($registro['celdas_vista'] as $celda): ?><td>
            <span class="<?= e($celda['clase_html_vista']) ?>"><?= e($celda['valor']) ?></span>

                    </td>
                <?php endforeach; ?>
                    <td>
                        <div class="admin-table-actions">
                            <a class="admin-action-icon" href="<?= e($registro['url_editar_vista']) ?>" aria-label="Editar"><i class="bi bi-pencil"></i>
                            </a>
                            <form <?= $registro['atributoDesactivarOculto'] ?> method="post" action="<?= e($registro['url_desactivar_vista']) ?>">
                <?= csrf_field() ?>
                                <button class="admin-action-icon is-danger" data-confirm="¿Deseas desactivar este registro?" aria-label="Desactivar"><i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
</section>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">

<header class="admin-dialog-header">
    <div>
        <h2>
            <?= $edicion ? 'Editar registro' : e($botonNuevo) ?>
        </h2>
        <p>
            La información se valida en el servidor antes de guardarse.
        </p>
    </div>
    <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i>
    </button>
</header>

<div class="admin-dialog-body">
    <form method="post" action="<?= e($urlFormularioCrud) ?>">
        <?= csrf_field() ?>
        <div class="admin-form-grid">
        <?php foreach ($camposFormularioVista as $campoFormulario): ?>
            <label class="admin-form-group<?= e($campoFormulario['clase']) ?>"><span><?= e($campoFormulario['etiqueta']) ?><?= e($campoFormulario['marcadorRequerido']) ?></span>
                <?= $campoFormulario['controlHtml'] ?>
            </label>
        <?php endforeach; ?>

        </div>
        <div class="admin-form-actions">
            <button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar
            </button>
            <button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar' ?>
            </button>
        </div>
    </form>
</div>
</dialog>
