<header class="admin-page-head">
    <div><span class="eyebrow">Marketing Digital</span>
        <h1>MD Ads - Campañas</h1>
        <p>Crea, gestiona y optimiza tus campañas publicitarias en todos los canales.</p>
    </div><a class="btn btn-primary" href="#editor-campania"><i class="bi bi-plus-lg"></i> Nueva campaña</a>
</header>
<div class="metricas-mockup">
    <article class="metrica-mockup azul">
        <div class="metrica-icono"><i class="bi bi-megaphone"></i></div>
        <div><span>Campañas activas</span><strong><?= count(array_filter($campanias ?? [], fn($c) => (int) $c['activo'] === 1)) ?></strong><small><b>↑ 33%</b> vs. mes anterior</small></div>
    </article>
    <article class="metrica-mockup verde">
        <div class="metrica-icono"><i class="bi bi-wallet2"></i></div>
        <div><span>Presupuesto invertido</span><strong>S/ 8,450</strong><small><b>↑ 12%</b> vs. mes anterior</small></div>
    </article>
    <article class="metrica-mockup violeta">
        <div class="metrica-icono"><i class="bi bi-mouse"></i></div>
        <div><span>Clics</span><strong>12,580</strong><small><b>↑ 28%</b> vs. mes anterior</small></div>
    </article>
    <article class="metrica-mockup ambar">
        <div class="metrica-icono"><i class="bi bi-funnel"></i></div>
        <div><span>Conversiones</span><strong>1,240</strong><small><b>↑ 22%</b> vs. mes anterior</small></div>
    </article>
</div>

<?php if (!$baseDatosDisponible): ?>
    <div class="alert alert-error">Ejecuta <code>php bin/console migrate</code> para habilitar el modulo de publicidad.</div>
<?php else: ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($exito): ?><div class="alert alert-success"><?= e($exito) ?></div><?php endif; ?>

    <div class="campanias-workspace">
        <div class="panel campaign-editor" id="editor-campania">
            <div class="section-head">
                <div>
                    <h2><?= $edicion ? 'Editar campaña' : 'Nueva campaña' ?></h2>
                    <p>Programa cuándo y dónde aparecerá una promoción.</p>
                </div>
                <?php if ($edicion): ?><a class="btn btn-ghost" href="<?= e(url('admin/campaigns')) ?>">Nueva campaña</a><?php endif; ?>
            </div>
            <form method="post" action="<?= e(url('admin/campaigns')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) ($edicion['id'] ?? 0) ?>">
                <div class="campaign-form-grid">
                    <label class="form-group"><span>Nombre interno</span><input class="input" name="nombre" value="<?= e($edicion['nombre'] ?? '') ?>" placeholder="Ej. Semana Samsung" required></label>
                    <label class="form-group"><span>Ubicacion</span>
                        <select name="ubicacion">
                            <?php foreach (['emergente' => 'Popup al ingresar', 'lateral' => 'Banner vertical lateral', 'barra_superior' => 'Barra superior'] as $valor => $etiqueta): ?>
                                <option value="<?= e($valor) ?>" <?= ($edicion['ubicacion'] ?? 'emergente') === $valor ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="form-group campaign-span-2"><span>Título visible</span><input class="input" name="titulo" value="<?= e($edicion['titulo'] ?? '') ?>" placeholder="Ej. ¡Oferta Flash!" required></label>
                    <label class="form-group campaign-span-2"><span>Descripción</span><textarea name="descripcion" rows="3" placeholder="Mensaje promocional breve"><?= e($edicion['descripcion'] ?? '') ?></textarea></label>
                    <label class="form-group"><span>Texto del botón</span><input class="input" name="texto_boton" value="<?= e($edicion['texto_boton'] ?? 'Ver oferta') ?>"></label>
                    <label class="form-group"><span>Destino</span><input class="input" name="url_boton" value="<?= e($edicion['url_boton'] ?? 'catalog') ?>" placeholder="catalog o URL externa"></label>
                    <label class="form-group campaign-span-2"><span>Imagen (ruta o URL)</span><input class="input" name="url_imagen" value="<?= e($edicion['url_imagen'] ?? '') ?>" placeholder="assets/img/publico/inicio/banners/exhibicion1.jpg"></label>
                    <label class="form-group"><span>Precio anterior</span><input class="input" type="number" step="0.01" min="0" name="precio_anterior" value="<?= e($edicion['precio_anterior'] ?? '') ?>"></label>
                    <label class="form-group"><span>Precio oferta</span><input class="input" type="number" step="0.01" min="0" name="precio_oferta" value="<?= e($edicion['precio_oferta'] ?? '') ?>"></label>
                    <label class="form-group"><span>Inicio</span><input class="input" type="datetime-local" name="inicia_en" value="<?= e(isset($edicion['inicia_en']) ? date('Y-m-d\TH:i', strtotime((string) $edicion['inicia_en'])) : date('Y-m-d\TH:i')) ?>" required></label>
                    <label class="form-group"><span>Fin</span><input class="input" type="datetime-local" name="finaliza_en" value="<?= e(isset($edicion['finaliza_en']) ? date('Y-m-d\TH:i', strtotime((string) $edicion['finaliza_en'])) : date('Y-m-d\TH:i', strtotime('+30 days'))) ?>" required></label>
                </div>
                <label class="campaign-check"><input type="checkbox" name="activo" value="1" <?= !isset($edicion['activo']) || (int) $edicion['activo'] === 1 ? 'checked' : '' ?>> Campaña activa</label>
                <div class="campaign-actions"><button class="btn btn-primary"><i class="bi bi-megaphone"></i> Guardar campaña</button></div>
            </form>
        </div>

        <section class="campaign-list-section">
            <div class="panel table-wrap">
                <table class="table">
                    <tr>
                        <th>Campaña</th>
                        <th>Formato</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                        <th>Vistas</th>
                        <th>Clics</th>
                        <th>CTR</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($campanias as $campania): ?>
                        <?php $tasaClics = (int) $campania['vistas'] > 0 ? ((int) $campania['clics'] / (int) $campania['vistas']) * 100 : 0; ?>
                        <tr>
                            <td><strong><?= e($campania['nombre']) ?></strong><br><small><?= e($campania['titulo']) ?></small></td>
                            <td><span class="campaign-type"><?= e($campania['ubicacion']) ?></span></td>
                            <td><?= e(date('d/m/Y', strtotime((string) $campania['inicia_en']))) ?><br><small>hasta <?= e(date('d/m/Y', strtotime((string) $campania['finaliza_en']))) ?></small></td>
                            <td><?= (int) $campania['activo'] === 1 ? '<span class="status-on">Activa</span>' : '<span class="status-off">Inactiva</span>' ?></td>
                            <td><?= (int) $campania['vistas'] ?></td>
                            <td><?= (int) $campania['clics'] ?></td>
                            <td><?= number_format($tasaClics, 1) ?>%</td>
                            <td>
                                <a class="btn btn-ghost" href="<?= e(url('admin/campaigns?edit=' . (int) $campania['id'])) ?>">Editar</a>
                                <?php if ((int) $campania['activo'] === 1): ?>
                                    <form method="post" action="<?= e(url('admin/campaigns/' . (int) $campania['id'] . '/deactivate')) ?>" style="display:inline">
                                        <?= csrf_field() ?><button class="btn btn-ghost" data-confirm="¿Desactivar esta campaña?">Desactivar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>
    </div>
<?php endif; ?>
