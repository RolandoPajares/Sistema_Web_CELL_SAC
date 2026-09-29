<?php
$ahora = time();
$estadoCampania = static function (array $campania) use ($ahora): string {
    if ((int) $campania['activo'] !== 1) {
        return 'inactive';
    }
    if (strtotime((string) $campania['inicia_en']) > $ahora) {
        return 'scheduled';
    }
    if (strtotime((string) $campania['finaliza_en']) < $ahora) {
        return 'finished';
    }
    return 'active';
};
$programadas = count(array_filter($campanias, static fn (array $campania): bool => $estadoCampania($campania) === 'scheduled'));
$finalizadas = count(array_filter($campanias, static fn (array $campania): bool => $estadoCampania($campania) === 'finished'));
$estadosCampania = ['active' => 'Activa', 'scheduled' => 'Programada', 'finished' => 'Finalizada', 'inactive' => 'Inactiva'];
$campaniaSeleccionada = $campanias[0] ?? null;
$imagenCampania = '';
if (is_array($campaniaSeleccionada) && is_string($campaniaSeleccionada['url_imagen'] ?? null)) {
    $fuente = trim($campaniaSeleccionada['url_imagen']);
    $esquema = strtolower((string) parse_url($fuente, PHP_URL_SCHEME));
    if (filter_var($fuente, FILTER_VALIDATE_URL) && in_array($esquema, ['http', 'https'], true)) {
        $imagenCampania = $fuente;
    } elseif ($fuente !== '' && preg_match('/^(?!.*\.\.)[A-Za-z0-9_\/. -]+$/D', $fuente)) {
        $imagenCampania = url(ltrim($fuente, '/'));
    }
}
$fechaInicioSeleccion = is_array($campaniaSeleccionada) ? strtotime((string) $campaniaSeleccionada['inicia_en']) : false;
$fechaFinSeleccion = is_array($campaniaSeleccionada) ? strtotime((string) $campaniaSeleccionada['finaliza_en']) : false;
$ubicacionesCampania = array_values(array_unique(array_map(static fn (array $campania): string => (string) $campania['ubicacion'], $campanias)));
$tarjetasKpi = [
    ['etiqueta' => 'Campañas activas', 'valor' => (string) $resumen['activas'], 'detalle' => 'Habilitadas en el sistema', 'icono' => 'bi-megaphone', 'tono' => 'azul'],
    ['etiqueta' => 'Programadas', 'valor' => (string) $programadas, 'detalle' => 'Con inicio futuro y habilitadas', 'icono' => 'bi-calendar-event', 'tono' => 'verde'],
    ['etiqueta' => 'Finalizadas', 'valor' => (string) $finalizadas, 'detalle' => 'Con fecha de fin vencida', 'icono' => 'bi-check-circle', 'tono' => 'violeta'],
    ['etiqueta' => 'Clics totales', 'valor' => number_format((int) $resumen['clics']), 'detalle' => number_format((int) $resumen['vistas']) . ' vistas registradas', 'icono' => 'bi-mouse', 'tono' => 'rojo'],
];
?>
<header class="admin-page-head">
    <div><h1>Campañas publicitarias <i class="bi bi-megaphone"></i></h1><p>Gestiona promociones, banners y difusión comercial con métricas registradas.</p></div>
    <div class="admin-page-actions"><button class="admin-primary-button" type="button" data-admin-dialog-open="campaign-dialog"><i class="bi bi-plus-lg"></i> Nueva campaña</button></div>
</header>
<?php if (!$baseDatosDisponible): ?><div class="admin-alert admin-alert--error">La base de datos de campañas no está disponible.</div><?php else: ?>
<?php if ($error): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-campaigns-page" data-admin-table-container>
    <div class="admin-campaigns-layout">
        <section class="admin-panel admin-campaigns-list">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-list-ul"></i><div><h2>Lista de campañas</h2><p><?= count($campanias) ?> registros reales · vistas y clics registrados</p></div></div></header>
            <div class="admin-campaigns-toolbar">
                <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar campañas..." aria-label="Buscar campañas"></label>
                <label class="admin-campaign-filter"><span>Estado</span><select data-campaign-status-filter aria-label="Filtrar por estado"><option value="">Todos los estados</option><?php foreach ($estadosCampania as $valor => $etiqueta): ?><option value="<?= e($valor) ?>"><?= e($etiqueta) ?></option><?php endforeach; ?></select></label>
                <label class="admin-campaign-filter"><span>Ubicación</span><select data-campaign-location-filter aria-label="Filtrar por ubicación"><option value="">Todas las ubicaciones</option><?php foreach ($ubicacionesCampania as $ubicacion): ?><option value="<?= e(mb_strtolower($ubicacion)) ?>"><?= e(ucfirst(str_replace('_', ' ', $ubicacion))) ?></option><?php endforeach; ?></select></label>
                <label class="admin-campaign-filter"><span>Periodo de inicio</span><select data-campaign-period-filter aria-label="Filtrar por periodo de inicio"><option value="all">Todo el periodo</option><option value="30">Últimos 30 días</option><option value="90">Últimos 3 meses</option></select></label>
            </div>
            <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Campaña</th><th>Inicio</th><th>Fin</th><th>Ubicación</th><th>Vistas</th><th>Clics</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
                <?php if ($campanias === []): ?><tr data-campaign-empty><td colspan="8" class="admin-table-empty">Aún no hay campañas registradas.</td></tr><?php endif; ?>
                <?php foreach ($campanias as $campania): $estadoActual = $estadoCampania($campania); $inicioTimestamp = strtotime((string) $campania['inicia_en']); $finTimestamp = strtotime((string) $campania['finaliza_en']); $fuente = trim((string) ($campania['url_imagen'] ?? '')); $esquema = strtolower((string) parse_url($fuente, PHP_URL_SCHEME)); $imagenFila = filter_var($fuente, FILTER_VALIDATE_URL) && in_array($esquema, ['http', 'https'], true) ? $fuente : (($fuente !== '' && preg_match('/^(?!.*\.\.)[A-Za-z0-9_\/. -]+$/D', $fuente)) ? url(ltrim($fuente, '/')) : ''); ?>
                    <tr data-data-row data-campaign-status="<?= e($estadoActual) ?>" data-campaign-location="<?= e(mb_strtolower((string) $campania['ubicacion'])) ?>" data-campaign-time="<?= $inicioTimestamp ?>">
                        <td><div class="admin-campaign-name"><?php if ($imagenFila !== ''): ?><img src="<?= e($imagenFila) ?>" alt="" loading="lazy"><?php else: ?><span class="admin-campaign-image-empty"><i class="bi bi-image"></i></span><?php endif; ?><div><strong><?= e((string) $campania['nombre']) ?></strong><small><?= e((string) $campania['titulo']) ?></small><button type="button" class="admin-campaign-preview-link" data-campaign-preview-select data-preview-id="<?= (int) $campania['id'] ?>" data-preview-name="<?= e((string) $campania['nombre']) ?>" data-preview-title="<?= e((string) $campania['titulo']) ?>" data-preview-description="<?= e((string) ($campania['descripcion'] ?? '')) ?>" data-preview-location="<?= e(ucfirst(str_replace('_', ' ', (string) $campania['ubicacion']))) ?>" data-preview-start="<?= $inicioTimestamp !== false ? e(date('d/m/Y', $inicioTimestamp)) : '—' ?>" data-preview-end="<?= $finTimestamp !== false ? e(date('d/m/Y', $finTimestamp)) : '—' ?>" data-preview-start-iso="<?= $inicioTimestamp !== false ? e(date('Y-m-d', $inicioTimestamp)) : '' ?>" data-preview-end-iso="<?= $finTimestamp !== false ? e(date('Y-m-d', $finTimestamp)) : '' ?>" data-preview-status="<?= e($estadosCampania[$estadoActual]) ?>" data-preview-status-key="<?= e($estadoActual) ?>" data-preview-image="<?= e($imagenFila) ?>" data-preview-edit="<?= e(url('admin/campaigns?edit=' . (int) $campania['id'])) ?>">Ver vista previa</button></div></div></td>
                        <td><?= $inicioTimestamp !== false ? e(date('d/m/Y', $inicioTimestamp)) : '—' ?></td><td><?= $finTimestamp !== false ? e(date('d/m/Y', $finTimestamp)) : '—' ?></td><td><span class="admin-status admin-status--info"><?= e(ucfirst(str_replace('_', ' ', (string) $campania['ubicacion']))) ?></span></td><td><?= (int) $campania['vistas'] ?></td><td><?= (int) $campania['clics'] ?></td><td><span class="admin-status admin-campaign-status admin-campaign-status--<?= e($estadoActual) ?>"><?= e($estadosCampania[$estadoActual]) ?></span></td><td><div class="admin-table-actions"><a class="admin-action-icon" href="<?= e(url('admin/campaigns?edit=' . (int) $campania['id'])) ?>" aria-label="Editar campaña"><i class="bi bi-pencil"></i></a><?php if ((int) $campania['activo'] === 1): ?><form method="post" action="<?= e(url('admin/campaigns/' . (int) $campania['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="admin-action-icon is-danger" data-confirm="¿Desactivar esta campaña?" aria-label="Desactivar campaña"><i class="bi bi-slash-circle"></i></button></form><?php endif; ?></div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($campanias !== []): ?><tr data-campaign-filter-empty hidden><td colspan="8" class="admin-table-empty">No se encontraron campañas con esos criterios.</td></tr><?php endif; ?>
            </tbody></table></div>
            <div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-campaigns-aside">
            <section class="admin-panel admin-campaign-preview">
                <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-eye-fill"></i><div><h2>Vista previa de campaña</h2><p>Contenido de una campaña real</p></div></div><?php if ($campaniaSeleccionada !== null): ?><a class="admin-secondary-button admin-campaign-edit-link" data-campaign-edit-link href="<?= e(url('admin/campaigns?edit=' . (int) $campaniaSeleccionada['id'])) ?>"><i class="bi bi-pencil"></i> Editar campaña</a><?php endif; ?></header>
                <?php if ($campaniaSeleccionada === null): ?><div class="admin-empty admin-campaign-empty">La vista previa estará disponible cuando existan campañas registradas.</div>
                <?php else: ?>
                    <div class="admin-campaign-preview-image" data-campaign-preview-image-wrap><img data-campaign-preview-image src="<?= e($imagenCampania) ?>" alt="<?= $imagenCampania !== '' ? 'Imagen de ' . e((string) $campaniaSeleccionada['titulo']) : '' ?>" <?= $imagenCampania === '' ? 'hidden' : '' ?>><div class="admin-campaign-image-placeholder" data-campaign-image-placeholder <?= $imagenCampania !== '' ? 'hidden' : '' ?>><i class="bi bi-image"></i><span>Esta campaña no tiene imagen registrada.</span></div></div>
                    <div class="admin-campaign-preview-details">
                        <div class="admin-campaign-detail"><i class="bi bi-file-text"></i><div><strong>Nombre de la campaña</strong><span data-campaign-preview-name><?= e((string) $campaniaSeleccionada['nombre']) ?></span></div></div>
                        <div class="admin-campaign-detail"><i class="bi bi-card-text"></i><div><strong>Descripción</strong><span data-campaign-preview-description><?= e((string) (($campaniaSeleccionada['descripcion'] ?? '') ?: 'Sin descripción registrada.')) ?></span></div></div>
                        <div class="admin-campaign-detail"><i class="bi bi-geo-alt"></i><div><strong>Ubicación</strong><span data-campaign-preview-location><?= e(ucfirst(str_replace('_', ' ', (string) $campaniaSeleccionada['ubicacion']))) ?></span></div></div>
                        <div class="admin-campaign-detail"><i class="bi bi-calendar-event"></i><div><strong>Periodo</strong><span><time data-campaign-preview-start datetime="<?= $fechaInicioSeleccion !== false ? e(date('Y-m-d', $fechaInicioSeleccion)) : '' ?>"><?= $fechaInicioSeleccion !== false ? e(date('d/m/Y', $fechaInicioSeleccion)) : '—' ?></time> – <time data-campaign-preview-end datetime="<?= $fechaFinSeleccion !== false ? e(date('Y-m-d', $fechaFinSeleccion)) : '' ?>"><?= $fechaFinSeleccion !== false ? e(date('d/m/Y', $fechaFinSeleccion)) : '—' ?></time></span></div></div>
                        <div class="admin-campaign-detail"><i class="bi bi-circle-fill"></i><div><strong>Estado</strong><span><b class="admin-status admin-campaign-status admin-campaign-status--<?= e($estadoCampania($campaniaSeleccionada)) ?>" data-campaign-preview-status><?= e($estadosCampania[$estadoCampania($campaniaSeleccionada)]) ?></b></span></div></div>
                    </div>
                <?php endif; ?>
            </section>
        </aside>
    </div>
</div>

<dialog class="admin-dialog" id="campaign-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
    <header class="admin-dialog-header"><div><h2><?= $edicion ? 'Editar campaña' : 'Nueva campaña' ?></h2><p>Programa cuándo y dónde se mostrará la promoción.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><form method="post" action="<?= e(url('admin/campaigns')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($edicion['id'] ?? 0) ?>"><div class="admin-form-grid">
        <label class="admin-form-group"><span>Nombre interno *</span><input name="nombre" maxlength="120" value="<?= e($edicion['nombre'] ?? '') ?>" required></label>
        <label class="admin-form-group"><span>Ubicación *</span><select name="ubicacion"><?php foreach (['emergente' => 'Popup al ingresar', 'lateral' => 'Banner lateral', 'barra_superior' => 'Barra superior'] as $valor => $etiqueta): ?><option value="<?= e($valor) ?>" <?= ($edicion['ubicacion'] ?? 'emergente') === $valor ? 'selected' : '' ?>><?= e($etiqueta) ?></option><?php endforeach; ?></select></label>
        <label class="admin-form-group is-full"><span>Título visible *</span><input name="titulo" maxlength="160" value="<?= e($edicion['titulo'] ?? '') ?>" required></label>
        <label class="admin-form-group is-full"><span>Descripción</span><textarea name="descripcion" maxlength="500" rows="3"><?= e($edicion['descripcion'] ?? '') ?></textarea></label>
        <label class="admin-form-group"><span>Texto del botón</span><input name="texto_boton" maxlength="80" value="<?= e($edicion['texto_boton'] ?? 'Ver oferta') ?>"></label><label class="admin-form-group"><span>Destino</span><input name="url_boton" maxlength="255" value="<?= e($edicion['url_boton'] ?? 'catalog') ?>"></label>
        <label class="admin-form-group is-full"><span>Imagen (ruta o URL)</span><input name="url_imagen" maxlength="255" value="<?= e($edicion['url_imagen'] ?? '') ?>"></label>
        <label class="admin-form-group"><span>Precio anterior</span><input type="number" step=".01" min="0" name="precio_anterior" value="<?= e($edicion['precio_anterior'] ?? '') ?>"></label><label class="admin-form-group"><span>Precio oferta</span><input type="number" step=".01" min="0" name="precio_oferta" value="<?= e($edicion['precio_oferta'] ?? '') ?>"></label>
        <label class="admin-form-group"><span>Inicio *</span><input type="datetime-local" name="inicia_en" value="<?= e(isset($edicion['inicia_en']) ? date('Y-m-d\\TH:i', strtotime((string) $edicion['inicia_en'])) : date('Y-m-d\\TH:i')) ?>" required></label><label class="admin-form-group"><span>Fin *</span><input type="datetime-local" name="finaliza_en" value="<?= e(isset($edicion['finaliza_en']) ? date('Y-m-d\\TH:i', strtotime((string) $edicion['finaliza_en'])) : date('Y-m-d\\TH:i', strtotime('+30 days'))) ?>" required></label>
        <label class="admin-form-group is-full"><span><input type="checkbox" name="activo" value="1" <?= !isset($edicion['activo']) || (int) $edicion['activo'] === 1 ? 'checked' : '' ?>> Campaña activa</span></label>
    </div><div class="admin-form-actions"><button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button><button class="admin-primary-button" type="submit">Guardar campaña</button></div></form></div>
</dialog>
<?php endif; ?>
