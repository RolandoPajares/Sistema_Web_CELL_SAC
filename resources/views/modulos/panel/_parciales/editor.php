<?php
/**
 * @var string $atributoEditorOculto
 * @var string $etiquetaEditorVista
 * @var string $tituloEditorVista
 * @var string $urlModuloPanel
 * @var string $atributoCerrarEditorOculto
 * @var string $urlFormularioPanel
 * @var array<array-key, mixed> $camposFormulario
 * @var string $textoGuardarEditorVista
 */ ?><aside class="panel module-editor" id="editor" <?= $atributoEditorOculto ?>>
        <div class="editor-head">
            <div><span class="eyebrow"><?= e($etiquetaEditorVista) ?></span><h2><?= e($tituloEditorVista) ?></h2></div>
            <a class="icon-btn" href="<?= e($urlModuloPanel) ?>" aria-label="Cerrar" <?= $atributoCerrarEditorOculto ?>><i class="bi bi-x-lg"></i></a>
        </div>
        <form method="post" action="<?= e($urlFormularioPanel) ?>">
            <?= csrf_field() ?>
            <?php foreach ($camposFormulario as $campoFormulario): ?>
                <label class="form-group"><span><?= e($campoFormulario['etiqueta_vista']) ?><?= e($campoFormulario['marcaRequeridoVista']) ?></span>
                <?= $campoFormulario['controlHtml'] ?>
                </label>
            <?php endforeach; ?>
            <button class="btn btn-primary full-width"><i class="bi bi-check2-circle"></i> <?= e($textoGuardarEditorVista) ?></button>
        </form>
    </aside>
