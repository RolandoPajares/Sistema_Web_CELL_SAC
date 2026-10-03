<?php

/**
 * @var array<array-key, mixed> $tarjetasKpi
 */ ?>
<div class="admin-kpi-grid">
    <?php foreach ($tarjetasKpi as $tarjeta): ?>
        <article class="admin-kpi-card admin-kpi-card--<?= e($tarjeta['tono'] ?? 'azul') ?>">
            <span class="admin-kpi-icon"><i class="bi <?= e($tarjeta['icono'] ?? 'bi-bar-chart') ?>"></i></span>
            <div>
                <small><?= e($tarjeta['etiqueta']) ?></small>
                <strong><?= e((string) $tarjeta['valor']) ?></strong>
                <span><?= e($tarjeta['detalle'] ?? 'Dato actual de MySQL') ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</div>